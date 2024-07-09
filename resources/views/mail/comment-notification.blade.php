@component('mail::message')
    {{ $title }}
    {{ $content }}
    @isset($action)
        @component('mail::button', ['url' => $action, 'color' => 'primary'])
           View {{ $modelName }}
        @endcomponent
    @endisset
    Regards,
@endcomponent
@isset($readUrl)
    <img style="width:1px;height:1px;opacity:0" src="{{$readUrl}}" />
@endisset
