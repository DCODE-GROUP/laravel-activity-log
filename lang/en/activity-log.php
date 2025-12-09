<?php

return [

    'headings' => [
        'title' => 'Activity Log',
        'page_title' => 'Activity Log - Page',
        'subjects' => ':model mentioned you in a comment on :entity ',
        'confirm_delete_comment' => 'Confirm Delete Comment',
    ],

    'fields' => [
        'updated_model' => 'Updated',
        'user' => 'User',
        'type' => 'Type',
        'description' => 'Description',
        'diff' => 'Diff',
        'meta' => 'Meta',
        'created_at' => 'Created at',
        'created_by' => 'Created by',
        'to' => 'To',
        'subject' => 'Subject',
        'collapsed_view' => 'Collapsed view',
        'my_activities' => 'My activities',
        'system' => 'System',
        'loading' => 'Loading...',
        'no_result' => 'No results',
        'date' => 'Date',
    ],

    'actions' => [
        'create' => ' created a ',
        'update' => ' updated a ',
        'delete' => ' deleted a ',
        'add_comment' => ' added a comment',
        'update_entity' => 'updated an entity',
    ],

    'buttons' => [
        'download_phone_call' => 'Download Phone Call',
        'comment' => 'Comment',
        'save' => 'Save',
        'preview_email' => '  Preview Email',
        'preview_sms' => '  Preview SMS',
        'delete' => 'Delete',
        'edit' => 'Edit',
        'cancel' => 'Cancel',
        'resend' => 'Resend',
        'resent' => 'Resent',
    ],

    'placeholders' => [
        'add_comment' => 'Add your comment...',
        'search_description' => 'Search by description',
    ],

    'words' => [
        'edited' => 'Edited',
        'loading' => 'Loading ...',
        'created_at' => 'Created',
        'views' => 'views',
        'reads' => 'reads',
        'read_more' => 'Read more',
        'read_less' => 'Read less',
        'send_email' => ' sent a notification email to ',
        'read_email' => ' read an email ',
        'send_sms' => ' sent a notification SMS to ',
        'read_read' => ' read an email ',
        'delete_note' => 'Delete note?',
        'delete_note_content' => 'Are you sure you want to delete this note? This action cannot be undone.',
    ],

    'phases' => [
        'opened_on' => 'Opened on',
        'email_has_not_been_opened' => 'Email is unopened.',
        'sms_has_not_been_opened' => 'Sms hasn\'t been opened.',
    ],

    'exceptions' => [
        'model_key' => 'No model key has been found for :model',
    ],

];
