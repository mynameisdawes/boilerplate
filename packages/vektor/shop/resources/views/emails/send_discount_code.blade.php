@component('mail::message')

Here's your discount code: <strong>{{ $discount_code }}</strong>

Thanks,<br />
{{ config('app.name') }}
@endcomponent
