<?php

namespace Dcodegroup\ActivityLog\Jobs;

use Dcodegroup\ActivityLog\Mail\CommentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCommentNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected string $email, protected array $emailContent, protected $entityModel)
    {
        $this->onQueue(config('queue.queue_names.default'));
    }

    public function handle(): void
    {
        Mail::to($this->email)->send(new CommentNotification($this->emailContent, $this->entityModel));
    }
}
