@component('mail::message')
@if (!empty($quote['quote_number']))
# Quote Awaiting Approval Reminder [{{ $quote['quote_number'] }}]
@else
# Quote Awaiting Approval Reminder
@endif

<h2 class="lrg">Dear {{ $quote['data']['first_name'] }},</h2>

<h2 class="lrg">Just a quick reminder that your quote is ready, along with some proofs which you can see below.</h2>

@component('mail::button', ['url' => config('app.url') . '/quote/' . $quote['quote_id']])
    VIEW QUOTE
@endcomponent

<h2>Ordering</h2>
<p>This quote is ready to be <a href="{{ config('app.url') }}/quote/{{ $quote['quote_id'] }}">ordered</a> once you have approved the proofs online.</p>

<h2>Lead Times</h2>
<p>Order lead times are approximately 5-7 working days. We can sometimes expedite this if needed - please confirm this with us at point of order or <a href="{{ config('app.url') }}/contact">reach out</a> if you have any other questions before ordering.</p>

<h2>Your Proofs</h2>

<img src="{{ config('app.url') }}/preview/{{ $quote['quote_id'] }}/design01?image=true&size=400" alt="">

@component('mail::button', ['url' => config('app.url') . '/quote/' . $quote['quote_id']])
    DOWNLOAD PROOFS
@endcomponent

<x-mail::panel>
<h2>Viewing Proofs</h2>
<p>Visuals are not 100% true to scale. Please check all the details and print dimensions carefully to ensure they are correct.</p>
<p>We recommend viewing your proof on a desktop computer or a laptop using Adobe Acrobat or Adobe Reader. Smartphones or tablets can be unreliable for viewing
proofs.</p>
</x-mail::panel>
@endcomponent
