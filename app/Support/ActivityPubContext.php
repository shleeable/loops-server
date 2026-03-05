<?php

namespace App\Support;

use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\Video;

class ActivityPubContext
{
    public function forVideo(Video $video)
    {
        $visibility = $video->visibility ?? 2;

        return match ($visibility) {
            1 => $this->getVideoPublic(),
            2, 3, 4, 5 => $this->getDefault(),
            default => $this->getDefault(),
        };
    }

    public function forComment(Comment $comment)
    {
        $visibility = $comment->visibility ?? 2;

        return match ($visibility) {
            1 => $this->getVideoPublic(),
            2, 3, 4, 5 => $this->getDefault(),
            default => $this->getDefault(),
        };
    }

    public function forCommentInteractionPolicy(Comment $comment)
    {
        $visibility = $comment->visibility ?? 2;

        return $visibility === 1 ? [
            'canQuote' => [
                'automaticApproval' => [
                    'https://www.w3.org/ns/activitystreams#Public',
                ],
            ],
        ] : [];
    }

    public function forCommentReply(CommentReply $comment)
    {
        $visibility = $comment->visibility ?? 2;

        return match ($visibility) {
            1 => $this->getVideoPublic(),
            2, 3, 4, 5 => $this->getDefault(),
            default => $this->getDefault(),
        };
    }

    public function forCommentReplyInteractionPolicy(CommentReply $comment)
    {
        $visibility = $comment->visibility ?? 2;

        return $visibility === 1 ? [
            'canQuote' => [
                'automaticApproval' => [
                    'https://www.w3.org/ns/activitystreams#Public',
                ],
            ],
        ] : [];
    }

    public function forVideoInteractionPolicy(Video $video)
    {
        $visibility = $video->visibility ?? 2;

        $res = [];
        $permalink = null;

        if ($video->is_local) {
            $res['canLike'] = [
                'automaticApproval' => [
                    'https://www.w3.org/ns/activitystreams#Public',
                ],
            ];

            $res['canAnnounce'] = [
                'automaticApproval' => [
                    'https://www.w3.org/ns/activitystreams#Public',
                ],
            ];

            if ($video->comment_state === 4) {
                $res['canReply'] = [
                    'automaticApproval' => [
                        'https://www.w3.org/ns/activitystreams#Public',
                    ],
                ];
            } else {
                $permalink = $video->profile->permalink();
                $res['canReply'] = [
                    'manualApproval' => [
                        $permalink,
                    ],
                ];
            }

            if ($visibility === 1 && $video->comment_state === 4) {
                $res['canQuote'] = [
                    'automaticApproval' => [
                        'https://www.w3.org/ns/activitystreams#Public',
                    ],
                ];
            } else {
                if (! $permalink) {
                    $permalink = $video->profile->permalink();
                }

                $res['canQuote'] = [
                    'manualApproval' => [
                        $permalink,
                    ],
                ];
            }
        }

        return $res;
    }

    protected function getVideoPublic()
    {
        return [
            'https://www.w3.org/ns/activitystreams',
            'https://w3id.org/security/v1',
            [
                'quote' => 'https://w3id.org/fep/044f#quote',
                'quoteUri' => 'http://fedibird.com/ns#quoteUri',
                '_misskey_quote' => 'https://misskey-hub.net/ns#_misskey_quote',
                'quoteAuthorization' => [
                    '@id' => 'https://w3id.org/fep/044f#quoteAuthorization',
                    '@type' => '@id',
                ],
                'gts' => 'https://gotosocial.org/ns#',
                'interactionPolicy' => [
                    '@id' => 'gts:interactionPolicy',
                    '@type' => '@id',
                ],
                'canQuote' => [
                    '@id' => 'gts:canQuote',
                    '@type' => '@id',
                ],
                'canLike' => [
                    '@id' => 'gts:canLike',
                    '@type' => '@id',
                ],
                'canReply' => [
                    '@id' => 'gts:canReply',
                    '@type' => '@id',
                ],
                'canAnnounce' => [
                    '@id' => 'gts:canAnnounce',
                    '@type' => '@id',
                ],
                'automaticApproval' => [
                    '@id' => 'gts:automaticApproval',
                    '@type' => '@id',
                ],
                'manualApproval' => [
                    '@id' => 'gts:manualApproval',
                    '@type' => '@id',
                ],
            ],
        ];
    }

    protected function getDefault()
    {
        return [
            'https://www.w3.org/ns/activitystreams',
            'https://w3id.org/security/v1',
        ];
    }
}
