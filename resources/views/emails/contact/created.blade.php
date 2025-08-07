@component('mail::message')
# Contact Form Submitted

@php
    echo "<pre>"; var_dump($data); echo "</pre>";
@endphp

Thanks,<br />
{{ config('app.name') }}
@endcomponent
