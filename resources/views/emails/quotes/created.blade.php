@component('mail::message')
@if (!empty($quote['quote_number']))
# New Quote Created [{{ $quote['quote_number'] }}]
@else
# New Quote Created
@endif

{{ $quote['first_name'] }} {{ $quote['last_name'] }},

We've put together a quote for you, along with some proofs which you can see below.

@component('mail::button', ['url' => config('app.url') . '/quote/' . $quote['quote_id']])
    VIEW QUOTE
@endcomponent

This quote is ready to be <a href="{{ config('app.url') }}/quote/{{ $quote['quote_id'] }}">ordered</a> once you have approved the proofs online.

@if ($quote['low_res_artwork_provided'] == true)
<p><strong>Low Resolution Proof:</strong> Please note that we have proofed this with low resolution artwork for design and quoting purposes. If you wanted go ahead with the quote, we will make sure that high resolution artwork is supplied and used before printing.</p>
@endif

Order lead times are approximately 5-7 working days. We can sometimes expedite this if needed - please confirm this with us at point of order or <a href="{{ config('app.url') }}/contact">reach out</a> if you have any other questions before ordering.

<img src="{{ config('app.url') }}/preview/{{ $quote['quote_id'] }}/design01?image=true&size=400" alt="">

@if (!empty($quote['notes']))
<p><strong>Customer Notes:</strong><br />{!! nl2br($quote['notes']) !!}</p>
@endif

<p><small>
    <strong>Important:</strong> Visuals are not 100% true to scale. Please check all the details and print dimensions carefully to ensure they are correct. We recommend viewing your proof on a desktop computer or a laptop using Adobe Acrobat or Adobe Reader. Smartphones or tablets can be unreliable for viewing proofs.
</small></p>


Thanks,<br />
{{ config('app.name') }}
@endcomponent