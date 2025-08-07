@component('mail::message')
@if (!empty($quote['quote_number']))
# New Quote Created [{{ $quote['quote_number'] }}]
@else
# New Quote Created
@endif

Admin,

Here's the quote you put together for {{ $quote['first_name'] }} {{ $quote['last_name'] }}, along with some proofs which you can see below.

@component('mail::button', ['url' => config('app.url') . '/quote/' . $quote['quote_id']])
    VIEW QUOTE
@endcomponent

<img src="{{ config('app.url') }}/preview/{{ $quote['quote_id'] }}/design01?image=true&size=400" alt="">

Thanks,<br />
{{ config('app.name') }}
@endcomponent