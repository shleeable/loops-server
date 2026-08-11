<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\Profile;
use Illuminate\Support\Collection;

class DmActivityPubService
{
    public const JSON_LD_CONTEXT = [
        'https://www.w3.org/ns/activitystreams',
        [
            'ostatus' => 'http://ostatus.org#',
            'litepub' => 'http://litepub.social/ns#',
            'conversation' => 'ostatus:conversation',
            'directMessage' => 'litepub:directMessage',
            'sensitive' => 'as:sensitive',
            'Hashtag' => 'as:Hashtag',
        ],
    ];

    public function objectUri(Message $message): string
    {
        return url("/ap/dm/{$message->id}");
    }

    public function actorUri(Profile $profile): string
    {
        return $profile->getActorId();
    }

    public function inboxUrl(Profile $profile): string
    {
        return $profile->inbox_url;
    }

    /**
     * Unique personal delivery inboxes for a set of remote profiles.
     *
     * @param  Collection<int, Profile>  $profiles
     * @return list<string>
     */
    public function inboxUrls(Collection $profiles): array
    {
        return $profiles
            ->map(fn (Profile $profile) => $profile->inbox_url)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Every non-left participant except the message author — local AND remote.
     *
     * @return Collection<int, Profile>
     */
    public function audience(Message $message): Collection
    {
        $conversation = $message->conversation;
        $conversation->loadMissing('participants.profile');

        return $conversation->participants
            ->where('profile_id', '!=', $message->profile_id)
            ->where('state', '!=', ConversationParticipant::STATE_LEFT)
            ->map(fn (ConversationParticipant $participant) => $participant->profile)
            ->filter()
            ->values();
    }

    public function contextUri(Conversation $conversation): ?string
    {
        return $conversation->context_uri;
    }

    public function conversationUri(Conversation $conversation): ?string
    {
        return $conversation->remote_conversation_uri
            ?: $conversation->remote_context_uri
            ?: $conversation->context_uri;
    }

    public function inReplyToUri(Message $message): ?string
    {
        if ($message->relationLoaded('parent') && $message->parent) {
            return $message->parent->ap_object_uri;
        }

        if ($message->in_reply_to_id) {
            $parent = Message::withTrashed()
                ->whereKey($message->in_reply_to_id)
                ->first();

            if ($parent?->ap_object_uri) {
                return $parent->ap_object_uri;
            }
        }

        return Message::withTrashed()
            ->where('conversation_id', $message->conversation_id)
            ->where('id', '<', $message->id)
            ->whereNotNull('ap_object_uri')
            ->orderByDesc('id')
            ->value('ap_object_uri');
    }

    public function buildNote(Message $message): array
    {
        $sender = $message->sender;
        $audience = $this->audience($message);
        $conversation = $message->conversation;
        $contextUri = $this->contextUri($conversation);

        $note = [
            'id' => $message->ap_object_uri,
            'type' => 'Note',
            'attributedTo' => $this->actorUri($sender),
            'to' => $audience
                ->map(fn (Profile $profile) => $this->actorUri($profile))
                ->values()
                ->all(),
            'cc' => [],
            'published' => $message->created_at->toAtomString(),
            'content' => $this->renderContent($message),
            'sensitive' => false,
            'directMessage' => true,
            'tag' => $audience
                ->map(fn (Profile $profile) => [
                    'type' => 'Mention',
                    'href' => $this->actorUri($profile),
                    'name' => '@'.$this->webfinger($profile),
                ])
                ->values()
                ->all(),
        ];

        if ($inReplyTo = $this->inReplyToUri($message)) {
            $note['inReplyTo'] = $inReplyTo;
        }

        if ($contextUri) {
            $note['context'] = $contextUri;
            $note['conversation'] = $this->conversationUri($conversation) ?: $contextUri;
        }

        $attachments = $message->media->map->toApAttachment()->values()->all();
        if ($attachments) {
            $note['attachment'] = $attachments;
        }

        return $note;
    }

    public function buildCreate(Message $message): array
    {
        $note = $this->buildNote($message);

        return [
            '@context' => self::JSON_LD_CONTEXT,
            'id' => $message->ap_object_uri.'#activity',
            'type' => 'Create',
            'actor' => $this->actorUri($message->sender),
            'to' => $note['to'],
            'cc' => [],
            'published' => $note['published'],
            'directMessage' => true,
            'object' => $note,
        ];
    }

    public function buildDelete(Message $message): array
    {
        return [
            '@context' => self::JSON_LD_CONTEXT,
            'id' => $message->ap_object_uri.'#delete',
            'type' => 'Delete',
            'actor' => $this->actorUri($message->sender),
            'to' => $this->audience($message)
                ->map(fn (Profile $profile) => $this->actorUri($profile))
                ->values()
                ->all(),
            'object' => [
                'id' => $message->ap_object_uri,
                'type' => 'Tombstone',
            ],
        ];
    }

    protected function renderContent(Message $message): string
    {
        $parts = [];

        if (filled($message->body)) {
            $parts[] = e($message->body);
        }

        if ($message->type === Message::TYPE_LOOP_SHARE && $message->video) {
            $url = $message->video->shareUrl();
            $parts[] = '<a href="'.$url.'" rel="noopener noreferrer">'.$url.'</a>';
        }

        if (! $parts) {
            $parts[] = $message->media->isNotEmpty()
                ? '&#128247;'
                : '&#8203;';
        }

        return '<p>'.implode('<br />', $parts).'</p>';
    }

    protected function webfinger(Profile $profile): string
    {
        $domain = $profile->domain ?? parse_url(config('app.url'), PHP_URL_HOST);

        return $profile->username.'@'.$domain;
    }
}
