@component('mail::message')
    {{ $content }} mention you in a comment:
@component('mail::button', ['url' => $action, 'color' => 'primary']) 
    Click here!!
@endcomponent 
Regards,
@endcomponent