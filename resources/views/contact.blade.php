@extends('layouts.default')
@php
    $default_title = 'Contact';
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
        <div class="container:md">
            <h1 class="text-gradient">Contact</h1>
            @include('partials.forms.contact')
        </div>
    </div>
@endsection
