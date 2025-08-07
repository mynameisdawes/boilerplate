@extends('layouts.default')
@php
    $default_title = 'Verify Email';
@endphp
@if (isset($page))
    @section('title', $page->title ? $page->title : $default_title)
    @section('meta_title', !empty($page->meta_title) ? $page->meta_title : $page->title)
    @section('meta_description', $page->meta_description)
    @section('meta_image', $page->meta_image)
@else
    @section('title', $default_title)
@endif

@section('content')
    <div class="pt-4 pb-12 pt-8:2 pb-16:2 py-12:3">
        <div class="container:sm">
            <div class="bg-background border-box p-8 p-10:3 relative mb-2">
                <h1 class="text-gradient">Verify Email</h1>
                <p>Thanks for registering! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.</p>

                @if (session('status') == 'verification-link-sent')
                    <c-message content="A new verification link has been sent to the email address you provided during registration." class="message--positive" :trigger="true" :required="true"></c-message>
                @endif

                <div class="flex items-center justify-between">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn bg-primary border-primary text-primary_contrasting hover:bg-background hover:border-primary hover:text-primary">Resend verification email</button>
                    </form>
                    <a href="{{ route('logout') }}" class="btn">Log out</a>
                </div>
            </div>
        </div>
    </div>
@endsection