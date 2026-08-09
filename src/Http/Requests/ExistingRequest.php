<?php

namespace Dcodegroup\ActivityLog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExistingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'modelClass' => [
                'required',
                'string',
            ],
            'modelId' => [
                'required_without:filter.id',
                Rule::exists($this->input('modelClass'), 'id'),
            ],
        ];

        $attachmentModel = config('activity-log.attachment_model');

        if (! $attachmentModel) {
            return array_merge($rules, [
                'attachment_id' => ['prohibited'],
                'attachment_ids' => ['prohibited'],
                'attachments' => ['prohibited'],
            ]);
        }

        return array_merge($rules, [
            'attachment_id' => ['nullable', 'integer', Rule::exists($attachmentModel, 'id')],
            'attachment_ids' => ['nullable', 'array'],
            'attachment_ids.*' => ['integer', 'distinct', Rule::exists($attachmentModel, 'id')],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file'],
        ]);
    }
}
