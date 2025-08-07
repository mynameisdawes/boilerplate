@extends('layouts.default')
@php
    $default_title = isset($product_type_title) && !empty($product_type_title) ? $product_type_title : config('shop.h1');
@endphp
@if (isset($page))
    @if ($page->title)
        @php
            $default_title = $page->title;
        @endphp
    @endif
    @section('title', $default_title)
    @section('meta_title', !empty($page->meta_title) ? $page->meta_title : $page->title)
    @section('meta_description', $page->meta_description)
    @section('meta_image', $page->meta_image)
@else
    @section('title', $default_title)
@endif

@section('content')
    <div class="container-gutter:outer">
        @if (isset($product_type_title) && !empty($product_type_title))
        <div class="document__header__actions">
            <div class="document__navigation_strip">
                <div class="container:xl">
                    <div class="content__wrapper">
                        <div class="content">
                            <ul class="breadcrumbs">
                                <li><a href="{{ route('shop.product.index') }}">Back to Shop</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="container:xl">
            <h1 class="text-gradient">{{ $default_title }}</h1>
            @if (!empty(config('shop.intro')))
                <p>{!! config('shop.intro') !!}</p>
            @endif
            <c-message :content="success_message" class="message--positive message--top" :trigger="is_success_message_shown" :autohide="true"></c-message>
            <c-message :content="error_message" class="message--negative message--top" :trigger="is_error_message_shown" :autohide="true"></c-message>
            @include('shop::partials.index', ['mode' => 'r'])
        </div>
    </div>
@endsection

@section('config')
@if (isset($product_type))
'shop.product_type': '{{ $product_type }}',
@endif
@endsection