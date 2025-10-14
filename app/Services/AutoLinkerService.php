<?php

namespace App\Services;

use App\Models\Hashtag;
use App\Models\Profile;

class AutoLinkerService
{
    private const MAX_TEXT_LEN = 20000;

    private const MAX_URLS = 100;

    private const MAX_MENTIONS = 100;

    private const MAX_TAGS = 100;

    public static function link(string $text, array $opts = []): string
    {
        if (mb_strlen($text) > self::MAX_TEXT_LEN) {
            $text = mb_substr($text, 0, self::MAX_TEXT_LEN);
        }

        $cfg = array_replace_recursive([
            'profile_base' => config('app.url'),
            'profile_rewriter' => null,
            'remote_profile_resolver' => null,
            'mention_validator' => null,
            'url_validator' => null,
            'hashtag_validator' => null,
            'tag_base' => rtrim((string) config('app.url'), '/').'/tag',
            'url' => [
                'max_ellipsis' => 30,
            ],
            'mention' => [
                'username_max' => (int) config('loops.autolinker.mentions.max_length', 64),
            ],
            'render' => [
                'remote_hide_domain' => (bool) config('loops.autolinker.mentions.hide_domain', false),
                'remote_remove_domain' => (bool) config('loops.autolinker.mentions.remove_domain', true),
                'mention_target_blank' => (bool) config('loops.autolinker.mentions.target_blank', false),
            ],

            'blocked' => [
                'wrap_urls' => false,
                'wrap_mentions' => false,
                'url_class' => '',
                'mention_class' => '',
            ],
        ], $opts);

        $appUrl = (string) config('app.url');
        if (! preg_match('~^https?://~i', $appUrl)) {
            $appUrl = 'https://'.ltrim($appUrl, '/');
        }
        $localDomain = strtolower((string) parse_url($appUrl, PHP_URL_HOST));

        $unameMax = (int) $cfg['mention']['username_max'];
        $domainRe = '(?:[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?)(?:\.(?:[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?))+';
        $unameRe = '[a-z0-9_][a-z0-9_\-]{0,'.max(0, $unameMax - 1).'}';

        $localUsers = [];
        $remoteAccts = [];
        $tagsWanted = [];

        if (strpos($text, '@') !== false) {
            if (preg_match_all('/(^|[\s\p{P}])@('.$unameRe.')(?:@('.$domainRe.'))?(?=$|[\s\p{P}])/iu', $text, $mm, PREG_SET_ORDER)) {
                foreach ($mm as $m) {
                    $user = $m[2] ?? '';
                    $domain = $m[3] ?? null;
                    if ($user === '') {
                        continue;
                    }
                    if ($domain) {
                        $acct = mb_strtolower($user.'@'.$domain);
                        $remoteAccts[$acct] = true;
                    } else {
                        $lu = mb_strtolower($user);
                        $localUsers[$lu] = true;
                    }
                    if (count($localUsers) + count($remoteAccts) >= (self::MAX_MENTIONS * 2)) {
                        break;
                    }
                }
            }
        }

        if (strpos($text, '#') !== false) {
            if (preg_match_all('/(^|[\s\p{P}])#([\p{L}\p{N}_]{1,100})/u', $text, $tm, PREG_SET_ORDER)) {
                foreach ($tm as $t) {
                    $tag = $t[2];
                    if (! empty($tag)) {
                        $tagsWanted[mb_strtolower($tag)] = $tag;
                        if (count($tagsWanted) >= (self::MAX_TAGS * 2)) {
                            break;
                        }
                    }
                }
            }
        }

        $profilesByUsername = collect();
        $allUsernames = array_keys($localUsers) + array_keys($remoteAccts);
        if (! empty($localUsers) || ! empty($remoteAccts)) {
            $all = array_values(array_unique(array_merge(array_keys($localUsers), array_keys($remoteAccts))));
            if (! empty($all)) {
                $profilesByUsername = Profile::query()
                    ->whereIn('username', $all)
                    ->get(['username', 'uri'])
                    ->keyBy(function ($p) {
                        return mb_strtolower($p->username);
                    });
            }
        }

        $hashtagsByName = collect();
        if (! empty($tagsWanted)) {
            $hashtagsByName = Hashtag::query()
                ->whereIn('name', array_values($tagsWanted))
                ->get(['name', 'can_autolink', 'is_banned'])
                ->keyBy(function ($h) {
                    return mb_strtolower($h->name);
                });
        }

        $domainBanCache = [];

        $isDomainBanned = function (?string $domain) use (&$domainBanCache): bool {
            if (! $domain) {
                return false;
            }
            $d = mb_strtolower($domain);
            if (! array_key_exists($d, $domainBanCache)) {
                $domainBanCache[$d] = app(SanitizeService::class)->isDomainBanned($d);
            }

            return $domainBanCache[$d];
        };

        $rewriteLocalProfile = $cfg['profile_rewriter'] ?? function (string $username, ?string $domain) use ($cfg): string {
            return rtrim((string) $cfg['profile_base'], '/').'/@'.$username;
        };

        $resolveRemoteProfile = $cfg['remote_profile_resolver'] ?? function (string $username, string $domain) use ($localDomain, $profilesByUsername): string {
            $acctKey = mb_strtolower($username.'@'.$domain);
            if ($localDomain && mb_strtolower($domain) === $localDomain) {
                return 'https://'.$domain.'/@'.$username;
            }
            if ($profilesByUsername->has($acctKey)) {
                return (string) $profilesByUsername->get($acctKey)->uri;
            }

            return 'https://'.$domain.'/@'.$username;
        };

        $mentionValidator = $cfg['mention_validator'] ?? function (string $username, ?string $domain) use ($localDomain, $profilesByUsername, $isDomainBanned): bool {
            if ($domain) {
                if ($localDomain && mb_strtolower($domain) === $localDomain) {
                    return $profilesByUsername->has(mb_strtolower($username));
                }
                if ($isDomainBanned($domain)) {
                    return false;
                }

                return $profilesByUsername->has(mb_strtolower($username.'@'.$domain));
            }

            return $profilesByUsername->has(mb_strtolower($username));
        };

        $urlValidator = $cfg['url_validator'] ?? function (string $url) use ($isDomainBanned): bool {
            $parts = parse_url($url);
            $host = $parts['host'] ?? null;
            $scheme = isset($parts['scheme']) ? mb_strtolower($parts['scheme']) : null;
            if (! $host || ! in_array($scheme, ['http', 'https'], true)) {
                return false;
            }
            if ($isDomainBanned($host)) {
                return false;
            }

            return true;
        };

        $hashtagValidator = $cfg['hashtag_validator'] ?? function (string $tag) use ($hashtagsByName): bool {
            $row = $hashtagsByName->get(mb_strtolower($tag));
            if ($row) {
                if (! $row->can_autolink || $row->is_banned) {
                    return false;
                }
            }

            return true;
        };

        $safe = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $place = ['url' => [], 'men' => [], 'tag' => []];
        $salt = bin2hex(random_bytes(8));
        $tok = fn (string $type, int $i): string => "<!--__{$salt}:{$type}_TOK_{$i}__-->";

        $counts = ['url' => 0, 'men' => 0, 'tag' => 0];

        if (strpos($safe, 'http') !== false || strpos($safe, 'www.') !== false) {
            $safe = preg_replace_callback(
                '~(?<![\'"=])(https?://[^\s<]+|www\.[^\s<]+)~iu',
                function ($m) use (&$place, &$counts, $tok, $cfg, $urlValidator) {
                    $raw = $m[0];
                    if ($counts['url'] >= self::MAX_URLS) {
                        return $raw;
                    }

                    $rawUnescaped = html_entity_decode($raw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $url = str_starts_with(mb_strtolower($rawUnescaped), 'http') ? $rawUnescaped : ('https://'.$rawUnescaped);

                    $i = count($place['url']);

                    if (! $urlValidator($url)) {
                        $place['url'][$i] = self::renderBlockedUrl($raw, $cfg);
                        $counts['url']++;

                        return $tok('URL', $i);
                    }

                    $place['url'][$i] = self::renderUrlAnchor($url, $cfg['url']['max_ellipsis']);
                    $counts['url']++;

                    return $tok('URL', $i);
                },
                $safe
            );
        }

        if (strpos($safe, '@') !== false) {
            $safe = preg_replace_callback(
                '/(^|[\s\p{P}])@('.$unameRe.')(?:@('.$domainRe.'))?(?=$|[\s\p{P}])/iu',
                function ($m) use (&$place, &$counts, $tok, $rewriteLocalProfile, $resolveRemoteProfile, $mentionValidator, $cfg) {
                    $lead = $m[1] ?? '';
                    $user = $m[2] ?? '';
                    $domain = $m[3] ?? null;

                    if ($counts['men'] >= self::MAX_MENTIONS) {
                        $acct = '@'.$user.($domain ? '@'.$domain : '');

                        return $lead.htmlspecialchars($acct, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    }

                    if (! $mentionValidator($user, $domain)) {
                        $blockedHtml = self::renderBlockedMention($user, $domain, $cfg);
                        $i = count($place['men']);
                        $place['men'][$i] = $blockedHtml;
                        $counts['men']++;

                        return $lead.$tok('MEN', $i);
                    }

                    if ($domain) {
                        $href = $resolveRemoteProfile($user, $domain);
                        $hrefA = self::escHref($href);

                        $label = '@<span>'.htmlspecialchars($user, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</span>';
                        if (! empty($cfg['render']['remote_remove_domain'])) {
                            // hide domain entirely
                        } elseif (! empty($cfg['render']['remote_hide_domain'])) {
                            $label .= '<span class="invisible">@'.htmlspecialchars($domain, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</span>';
                        } else {
                            $label .= '@'.htmlspecialchars($domain, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        }

                        $attrs = self::mentionLinkAttrs($cfg);
                        $html = '<span class="h-card" translate="no"><a href="'.$hrefA.'" class="u-url mention" aria-label="@'.
                            htmlspecialchars($user, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'@'.htmlspecialchars($domain, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').
                            '"'.$attrs.'>'.$label.'</a></span>';
                    } else {
                        $href = $rewriteLocalProfile($user, null);
                        $hrefA = self::escHref($href);
                        $attrs = self::mentionLinkAttrs($cfg);
                        $html = '<span class="h-card" translate="no"><a href="'.$hrefA.'" class="u-url mention" aria-label="@'.
                            htmlspecialchars($user, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'"'.$attrs.'>@<span>'.
                            htmlspecialchars($user, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</span></a></span>';
                    }

                    $i = count($place['men']);
                    $place['men'][$i] = $html;
                    $counts['men']++;

                    return $lead.$tok('MEN', $i);
                },
                $safe
            );
        }

        if (strpos($safe, '#') !== false) {
            $safe = preg_replace_callback(
                '/(^|[\s\p{P}])#([\p{L}\p{N}_]{1,100})/u',
                function ($m) use (&$place, &$counts, $tok, $cfg, $hashtagValidator) {
                    $lead = $m[1];
                    $tag = $m[2];

                    if ($counts['tag'] >= self::MAX_TAGS) {
                        return $lead.'#'.htmlspecialchars($tag, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    }

                    if (! $hashtagValidator($tag)) {
                        return $lead.'#'.htmlspecialchars($tag, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    }

                    $href = rtrim((string) $cfg['tag_base'], '/').'/'.rawurlencode($tag);
                    $hrefA = self::escHref($href);

                    $html = '<a href="'.$hrefA.'" class="mention hashtag" rel="tag nofollow noopener noreferrer ugc">#<span>'.
                        htmlspecialchars($tag, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</span></a>';

                    $i = count($place['tag']);
                    $place['tag'][$i] = $html;
                    $counts['tag']++;

                    return $lead.$tok('TAG', $i);
                },
                $safe
            );
        }

        $lines = preg_split("/\r?\n/", $safe);
        $html = '';
        foreach ($lines as $line) {
            if ($line === '') {
                $html .= '<p>&nbsp;</p>';
            } else {
                $html .= '<p>'.$line.'</p>';
            }
        }

        if (! empty($place['url'])) {
            $html = preg_replace_callback(
                '/<!--__'.preg_quote($salt, '/').':URL_TOK_(\d+)__-->/',
                fn ($m) => $place['url'][(int) $m[1]] ?? $m[0],
                $html
            );
        }
        if (! empty($place['men'])) {
            $html = preg_replace_callback(
                '/<!--__'.preg_quote($salt, '/').':MEN_TOK_(\d+)__-->/',
                fn ($m) => $place['men'][(int) $m[1]] ?? $m[0],
                $html
            );
        }
        if (! empty($place['tag'])) {
            $html = preg_replace_callback(
                '/<!--__'.preg_quote($salt, '/').':TAG_TOK_(\d+)__-->/',
                fn ($m) => $place['tag'][(int) $m[1]] ?? $m[0],
                $html
            );
        }

        return $html;
    }

    private static function mentionLinkAttrs(array $cfg): string
    {
        $attrs = ' rel="nofollow noopener noreferrer ugc"';
        if (! empty($cfg['render']['mention_target_blank'])) {
            $attrs = ' target="_blank"'.$attrs;
        }

        return $attrs;
    }

    private static function renderUrlAnchor(string $url, int $maxVisible = 30): string
    {
        $hrefAttr = self::escHref($url);
        $display = self::prettyUrlDisplay($url, $maxVisible);

        return '<a href="'.$hrefAttr.'" target="_blank" rel="nofollow noopener noreferrer ugc" translate="no">'
            .$display.'</a>';
    }

    private static function renderBlockedUrl(string $rawEscapedInput, array $cfg): string
    {
        if (! empty($cfg['blocked']['wrap_urls'])) {
            $class = htmlspecialchars($cfg['blocked']['url_class'] ?? 'blocked url', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            return '<span class="'.$class.'" translate="no">'.$rawEscapedInput.'</span>';
        }

        return $rawEscapedInput;
    }

    private static function renderBlockedMention(string $user, ?string $domain, array $cfg): string
    {
        $acct = '@'.$user.($domain ? '@'.$domain : '');
        $acct = htmlspecialchars($acct, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        if (! empty($cfg['blocked']['wrap_mentions'])) {
            $class = htmlspecialchars($cfg['blocked']['mention_class'] ?? 'blocked mention', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            return '<span class="'.$class.'" translate="no">'.$acct.'</span>';
        }

        return $acct;
    }

    private static function prettyUrlDisplay(string $url, int $maxVisible): string
    {
        $scheme = '';
        $rest = $url;
        if (preg_match('~^(https?://)(.+)$~i', $url, $m)) {
            $scheme = $m[1];
            $rest = $m[2];
        }

        if (mb_strlen($rest) <= $maxVisible) {
            return ($scheme ? '<span class="invisible">'.htmlspecialchars($scheme, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</span>' : '')
                .'<span class="ellipsis">'.htmlspecialchars($rest, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</span>';
        }

        $visible = mb_substr($rest, 0, $maxVisible);
        $tail = mb_substr($rest, $maxVisible);

        return ($scheme ? '<span class="invisible">'.htmlspecialchars($scheme, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</span>' : '')
            .'<span class="ellipsis">'.htmlspecialchars($visible, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</span>'
            .'<span class="invisible">'.htmlspecialchars($tail, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</span>';
    }

    /**
     * Validate that href is http(s) URL; return escaped attribute value or '#'
     */
    private static function escHref(string $href): string
    {
        if (preg_match('~^/[^/].*~', $href)) {
            return htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        if (! preg_match('~^https?://~i', $href)) {
            return '#';
        }

        $parts = parse_url($href);
        $scheme = isset($parts['scheme']) ? mb_strtolower($parts['scheme']) : null;
        $host = $parts['host'] ?? null;
        if (! $host || ! in_array($scheme, ['http', 'https'], true)) {
            return '#';
        }

        return htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
