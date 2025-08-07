@extends('layouts.default')
@php
    $default_title = 'Order Success';
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
    <div class="container-gutter:outer h-screen content-center">
        <div class="container:sm">
            <div class="text-center">
                <svg class="mx-auto text-primary fill-current" xmlns="http://www.w3.org/2000/svg" width="60px" height="60px" viewBox="0 0 60 60"><polygon points="51.483,5.936 20.39,37.029 8.517,25.157 0,33.674 20.39,54.064 60,14.453 "/></svg>
                <h1>Thank you for your order</h1>
                <p class="text-balance">We are currently processing your order. You will receive a confirmation email shortly.</p>
                <a class="mx-auto btn bg-primary border-primary text-primary_contrasting" href="{{ route('base') }}">Return to Homepage</a>
                <?php // echo '<pre>'; var_dump($_REQUEST); echo '</pre>';?>
            </div>
        </div>
    </div>
@endsection