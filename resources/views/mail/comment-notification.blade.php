@component('mail::message')
    {{ $content }}
@component('mail::button', ['url' => $action, 'color' => 'primary']) 
    Click here!!
@endcomponent 
Regards,
@endcomponent
@isset($readUrl)
    <img style="width:1px;height:1px;opacity:0" src="{{$readUrl}}" />
@endisset
