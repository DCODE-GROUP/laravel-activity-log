<?php

namespace Dcodegroup\ActivityLog\Http\Services;

use Dcodegroup\ActivityLog\Jobs\SendCommentNotificationJob;
use Dcodegroup\ActivityLog\Models\ActivityLog;
use Dcodegroup\ActivityLog\Resources\ActivityLogCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActivityLogService
{
    public $activityLogModel;

    public $userModel;

    public $userRelationship;

    public $userSearchRelationship;

    public $userSearchTerm;

    public $communicationLogRelationship;

    public function __construct()
    {
        $this->activityLogModel = config('activity-log.activity_log_model');
        $this->userModel = config('activity-log.user_model');
        $this->userRelationship = config('activity-log.user_relationship');
        $this->userSearchRelationship = config('activity-log.user_search_relationship');
        $this->userSearchTerm = config('activity-log.user_search_term');
        $this->userSearchTerm = is_array($this->userSearchTerm) ? $this->userSearchTerm : [$this->userSearchTerm];
        $this->communicationLogRelationship = config('activity-log.communication_log_relationship');
    }

    public function getActivityLogs($model, $type = null): ActivityLogCollection
    {
        $relationships = [
            $this->userRelationship,
            $this->communicationLogRelationship,
            $this->communicationLogRelationship.'.reads',
            'reactions',
            'reactions.user',
        ];

        if (config('activity-log.attachment_model')) {
            $relationships[] = 'attachments.attachment';
        }

        return new ActivityLogCollection($model->activityLogs()
            ->when($type, fn (Builder $builder) => $builder->where('type', $type))
            ->with($relationships)
            ->where(fn (Builder $builder) => $builder
                ->whereNull('communication_log_id')
                ->orWhere(fn (Builder $builder) => $builder
                    ->whereNotNull('communication_log_id')
                    ->whereNot('title', 'like', '% read an %')
                    ->whereNot('title', 'like', '% view a %'))
            )
            ->orderByDesc('created_at')->get());
    }

    /**
     * Upload any new files on the request and return every attachment id the comment should end up with.
     */
    public function resolveAttachmentIds(Request $request, $model): Collection
    {
        $uploadedBy = $request->input('currentUser', 'System');

        $uploadedAttachmentIds = collect($request->file('attachments', []))
            ->map(function (UploadedFile $file) use ($model, $uploadedBy) {
                $type = $file->getMimeType() ? Str::before($file->getMimeType(), '/') : 'default';

                return $model->addMedia($file)
                    ->usingFileName($file->hashName())
                    ->withCustomProperties([
                        'original_filename' => $file->getClientOriginalName(),
                        'encoding_format' => $file->extension(),
                        'uploaded_by' => $uploadedBy,
                    ])
                    ->toMediaCollection($type)
                    ->getKey();
            });

        return collect($request->input('attachment_ids', []))
            ->when($request->filled('attachment_id'), fn (Collection $ids) => $ids->push($request->integer('attachment_id')))
            ->merge($uploadedAttachmentIds)
            ->map(fn ($attachmentId) => (int) $attachmentId)
            ->unique()
            ->values();
    }

    /**
     * Make the comment attachments match the given ids, detaching anything no longer present.
     */
    public function syncAttachments(ActivityLog $activityLog, Collection $attachmentIds): void
    {
        DB::transaction(function () use ($activityLog, $attachmentIds) {
            $activityLog->attachments()->whereNotIn('attachment_id', $attachmentIds->all())->delete();

            $existingAttachmentIds = $activityLog->attachments()
                ->pluck('attachment_id')
                ->map(fn ($attachmentId) => (int) $attachmentId);

            $activityLog->attachments()->createMany(
                $attachmentIds->diff($existingAttachmentIds)
                    ->map(fn (int $attachmentId) => ['attachment_id' => $attachmentId])
                    ->all()
            );
        });

        $activityLog->load('attachments.attachment');
    }

    public function mentionUserInComment(string $comment, ActivityLog $activityLog, ?array $mailable = null): ActivityLog
    {
        $activityLog->update(['meta' => $comment], ['timestamps' => false]);
        $regexp = '/@\[[^\]]*\]/';
        $mentionedUsers = Str::matchAll($regexp, trim($comment));
        foreach ($mentionedUsers as $key) {
            $identity = str($key)->replaceStart('@[', '')->replaceEnd(']', '')->toString();

            $this->userModel::query()
                ->with($this->userSearchRelationship)
                ->where(function (Builder $q) use ($identity) {
                    foreach ($this->userSearchTerm as $field) {
                        if (is_array($field)) {
                            $query = 'concat(';
                            foreach ($field as $item) {
                                $query .= collect($field)->first() !== $item ? $item : $item.", ' ', ";
                            }
                            $query .= ')';
                            $q->orWhere(DB::raw($query), $identity);

                            continue;
                        }
                        $parts = explode('.', $field);
                        if (count($parts) > 1) {
                            [$relation, $relationField] = $parts;
                            $q->orWhereHas($relation, fn (Builder $builder) => $builder->where($relationField, $identity));

                            continue;
                        }
                        $q->orWhere($field, $identity);
                    }
                })->get()->each(function ($userModel) use ($mailable, $key, &$comment) {
                    $email = $userModel->getActivityLogEmail();

                    if ($mailable) {
                        $model = $mailable['model'];
                        $data = [
                            'content' => $mailable['content'],
                            'title' => $mailable['title'],
                            'modelName' => $mailable['modelName'],
                        ];
                        if (method_exists($model, 'getActivityLogEmails') && ! in_array($email, $model->getActivityLogEmails())) {
                            // @phpstan-ignore-next-line
                            $data['action'] = ! empty($model->getMentionCommentUrl($userModel)) ? $model->getMentionCommentUrl($userModel) : $mailable['action'];
                        }

                        SendCommentNotificationJob::dispatch($email, $data, $model);
                    }
                    $to = is_array($email) ? implode(', ', $email) : $email;
                    $comment = str_replace($key, '<a class="activity__comment--tag" href="mailto:'.$to.'">@'.$userModel->getActivityLogUserName().'</a>', $comment);
                });
        }
        $activityLog->update(['description' => $comment], ['timestamps' => false]);

        return $activityLog;
    }
}
