@extends('layouts.default')
@php
    $default_title = 'Email Has Been Sent!';
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
    <div class="container-gutter:outer">
        <div class="container:sm">
            <div class="bg-background border-box p-8 p-10:3">
                <h1 class="text-gradient">Email has been sent!</h1>
                <p>We have sent password recovery instructions to your email.</p>

                <p class="text-sm">Did not receive the email? Check your spam filter, <a class="text-secondary" href="{{ route('password.request') }}">try again</a>, or <a class="text-primary" href="{{ route('contact') }}">contact us</a>.</p>
            </div>
        </div>
    </div>
@endsection