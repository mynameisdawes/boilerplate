@component('mail::message')
## Hello!

Click the button below to log in to your account:

@component('mail::button', ['url' => $url])
Log In to Your Account
@endcomponent

This link will expire in {{ config('passwordless_auth.token_lifetime') }} minutes.

If you didn't request this login link, please ignore this email.

<hr style="border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;">

<p style="font-size: 12px; color: #718096;">
    If you're having trouble clicking the button, copy and paste the URL below into your web browser:<br>
    <a href="{{ $url }}" style="word-break: break-all;">{{ $url }}</a>
</p>
@endcomponent
