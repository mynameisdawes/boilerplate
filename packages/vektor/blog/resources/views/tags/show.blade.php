@extends('layouts.default')

@php
    $partial_variables = [];
@endphp

@if (isset($cms_entity))
    @section('title', $cms_entity->name)
    @section('meta_title', !empty($cms_entity->meta_title) ? $cms_entity->meta_title : $cms_entity->name)
    @section('meta_description', $cms_entity->formatted_meta_description)
    @section('meta_image', $cms_entity->formatted_meta_image)

    @php
        $partial_variables['cms_entity'] = $cms_entity;
    @endphp
@endif

@if (isset($cms_type))
    @php
        $partial_variables['cms_type'] = $cms_type;
    @endphp
@endif

@section('content')
    @include('posts::partials.index', $partial_variables)
@endsection