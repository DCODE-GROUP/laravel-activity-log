<?php

namespace Dcodegroup\ActivityLog\Listeners;

use Dcodegroup\ActivityLog\Models\ActivityLog;
use Illuminate\Mail\Events\MessageSent;
use Symfony\Component\Mime\Email;

class ActivityLogMessageSentListener
{
    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        $model = data_get($event->data, 'mailableModel', false);
        if ($model && method_exists($model::class, 'createCommunicationLog') && method_exists($model::class, 'createActivityLog')) {
            $message = $event->message;
            $communicationLog = $model->createCommunicationLog([
                'subject' => $message->getSubject(),
                'cc' => $this->formatAddressField($message, 'Cc'),
                'bcc' => $this->formatAddressField($message, 'Bcc'),
            ], $this->formatAddressField($message, 'To'), $message->getBody()->bodyToString() ?: '');
            $model->createActivityLog([
                'type' => ActivityLog::TYPE_NOTIFICATION,
                'title' => __('activity-log.words.send_email') . $this->formatAddressField($message, 'To'),
                'description' => '',
                'communication_log_id' => $communicationLog->id,
            ]);
        }
    }

    /**
     * Format address strings for sender, to, cc, bcc.
     */
    public function formatAddressField(Email $message, string $field): ?string
    {
        $headers = $message->getHeaders();

        return $headers->get($field)?->getBodyAsString();
    }
}
