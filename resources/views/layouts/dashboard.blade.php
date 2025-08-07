@extends('layouts.default')
@php
    $default_title = 'Dashboard';
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
    <section class="container-gutter:outer">
        <div class="document__header__actions">
            <div class="document__navigation_strip">
                <div class="container:xl">
                    <div class="content__wrapper">
                        <div class="content dashboard__navigation">
                            <nav><ul>
                                @include('partials.dashboard_navigation')
                            </ul></nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container:xl">
            @yield('content.dashboard')
        </div>
    </section>
@endsection