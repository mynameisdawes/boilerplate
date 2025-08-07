@extends('layouts.default')
@php
    $default_title = $title;
@endphp
@if (isset($page))
    @section('title', $page->title ? $page->title : $default_title)
    @section('meta_title', !empty($page->meta_title) ? $page->meta_title : $page->title)
    @section('meta_description', $page->meta_description)
    @section('meta_image', $page->meta_image)
@else
    @section('title', $default_title)
@endif

@php
    $partials = "shop::partials." . ($customisable ? "customisable" : "simple");
@endphp

@section('content')
    <div class="container-gutter:outer">
        @include('shop::partials.show')
    </div>
@endsection