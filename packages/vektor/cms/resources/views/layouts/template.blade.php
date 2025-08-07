@php
use Illuminate\Support\Facades\View;

$data = [];
if (isset($section['attributes']['data']) && !empty($section['attributes']['data'])) {
    $data = $section['attributes']['data'];
}
@endphp
@if (isset($section['attributes']['template']) && !empty($section['attributes']['template']) && View::exists($section['attributes']['template']))
    @include($section['attributes']['template'], $data)
@endif