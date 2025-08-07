@extends('layouts.default')
@php
    $default_title = 'Map';
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
    <div class="pt-0:1t2e container-gutter:outer:3">
        <div class="container:xl:3">
            <c-map
            api_key="{{ config('map.api_key') }}"
            map_id="{{ config('map.id') }}"
            markers_url="api.markers.index"
            place_autocomplete_url="api.locations.autocomplete.places"
            place_geocode_url="api.locations.geocode.place"
            initial_latitude="51.51182707901139"
            initial_longitude="-0.12088979325227334"
            >
                <template v-slot:default="mapScope">
                    <div class="spinner__wrapper spinner--absolute" :class="{ is_loading: mapScope.is_loading == true }"><div class="spinner"></div></div>
                    <div class="map" :class="{'map_mode': mapScope.mode == 'map', 'list_mode': mapScope.mode == 'list'}"></div>
                    <div class="map_ui" v-if="mapScope.marker_venues.length > 0">
                        <div class="map_controls">
                            <div class="map_mode_search">
                                <div class="map_mode_toggle">
                                    <button @click.prevent="mapScope.setMapMode('map')" :class="{'is_selected': mapScope.mode == 'map'}">Map</button>
                                    <button @click.prevent="mapScope.setMapMode('list')" :class="{'is_selected': mapScope.mode == 'list'}">List</button>
                                </div>
                                <div class="map_search" :class="{'is_collapsed': mapScope.search_is_collapsed}" @click.prevent.stop="mapScope.toggleSearch()">
                                    <c-form :name="mapScope.forms.place_autocomplete.ref" :ref="mapScope.forms.place_autocomplete.ref" method="post" :field_values="mapScope.forms.place_autocomplete.field_values" :field_storage="mapScope.forms.place_autocomplete.field_storage" :field_validation_rules="mapScope.forms.place_autocomplete.validation_rules" :field_validation_messages="mapScope.forms.place_autocomplete.validation_messages">
                                        <template v-slot:fields="form">
                                            <c-input name="text" type="autocomplete" v-model="form.field_values.text" placeholder="Search venues by location" :suggestions="mapScope.place_suggestions" :suggestions_model="false" @fetch="mapScope.fetchPlaceSuggestions" @select="mapScope.geocodePlace" :select_first_suggestion="true" :suggestions_fuzzy_match="true"></c-input>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="19.89" viewBox="0 0 20 19.89" @click.prevent.stop="mapScope.toggleSearch()"><path class="fill-current" d="M20,18.19l-6.34-6.34A7.54,7.54,0,1,0,12,13.56l6.33,6.33ZM3.9,11.13a5.11,5.11,0,1,1,7.23,0A5.12,5.12,0,0,1,3.9,11.13Z"></path></svg>
                                        </template>
                                    </c-form>
                                </div>
                            </div>
                            <button v-if="mapScope.navigator_geolocation_supported" class="btn map_gps" @click.prevent="mapScope.getCurrentLocation()"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path class="fill-current" d="M20.9,10.5h3.1v3h-3.1c-.7,3.8-3.6,6.8-7.4,7.4v3.1h-3v-3.1c-3.8-.7-6.8-3.6-7.4-7.4H0v-3h3.1c.7-3.8,3.6-6.8,7.4-7.4V0h3v3.1c3.8.7,6.8,3.6,7.4,7.4ZM6,12c0,3.3,2.7,6,6,6s6-2.7,6-6-2.7-6-6-6-6,2.7-6,6ZM15,12c0,1.7-1.3,3-3,3s-3-1.3-3-3,1.3-3,3-3,3,1.3,3,3Z"/></svg></button>
                        </div>
                        <div class="map_venues">
                            <div class="venue" v-for="(venue, venue_idx) in mapScope.marker_venues" :venue_idx="venue_idx" :key="venue.marker_id">
                                <Transition name="venue-dismiss-fade">
                                    <div v-if="venue.is_selected" class="venue__dismiss" @click.prevent="mapScope.toggleVenue(null)"></div>
                                    <div v-else class="venue_distance">@{{ venue.distance }}<small>mi</small></div>
                                </Transition>
                                <div class="venue_info">
                                    <div class="venue_info_summary" @click.prevent="mapScope.toggleVenue(venue_idx)">
                                        <img v-if="venue.photo" loading="lazy" :src="venue.photo" :alt="venue.name" />
                                        <header>
                                            <p class="venue_name">@{{ venue.name }}</p>
                                            <p class="venue_address">@{{ venue.address }}</p>
                                        </header>
                                    </div>
                                    <c-panel_expand :is_expanded="!venue.is_selected" class="expand__panel--no_inner">
                                        <template v-slot:default>
                                            <div class="venue_btn_expand">
                                                <button class="btn" @click.prevent="mapScope.toggleVenue(venue_idx)">More Info</button>
                                                <a v-if="venue.is_bookable && venue.website_url" class="btn bg-primary_linear_gradient border-primary text-primary_contrasting" :href="venue.website_url" target="_blank">Book now</a>
                                            </div>
                                        </template>
                                    </c-panel_expand>
                                    <c-panel_expand :is_expanded="venue.is_selected" class="expand__panel--no_inner">
                                        <template v-slot:default>
                                            <div class="venue_info_detailed">
                                                <div v-if="venue.phone && venue.formatted.phone" class="venue_phone"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14"><path class="fill-current" d="M9.5,7.9l-2.3,2.6c-1.5-.9-2.8-2.2-3.7-3.7l2.6-2.3L5.2,0H0v1c0,7.2,5.9,13,13,13h1v-5.2l-4.5-.9Z"/></svg><a :href="'tel:' + venue.formatted.phone">@{{ venue.phone }}</a></div>
                                                <div v-if="venue.website_url && venue.formatted.website_url" class="venue_url"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14"><path class="fill-current" d="M7,0C3.1,0,0,3.1,0,7s3.1,7,7,7,7-3.1,7-7S10.9,0,7,0ZM12.1,7c0,.4,0,.9-.2,1.3-.4-1-1-2-1.9-3l1.1-1.1c.6.8,1,1.8,1,2.9ZM3.5,3.5c.8,0,2.3.5,3.8,1.8l-2,2c-1.4-1.8-1.8-3.2-1.8-3.9ZM8.7,6.7c1.5,1.7,1.8,3.2,1.8,3.8-.8,0-2.3-.5-3.8-1.8l2-2ZM9.9,2.8l-1.2,1.2c-1-.9-2-1.5-3-1.9.4-.1.8-.2,1.3-.2,1.1,0,2.1.3,2.9.9ZM1.9,7c0-.4,0-.9.2-1.3.4,1,1,2,1.9,3l-1.1,1.1c-.6-.8-1-1.8-1-2.9ZM4.1,11.2l1.2-1.2c1,.9,2,1.5,3,1.9-.4.1-.8.2-1.3.2-1.1,0-2.1-.3-2.9-.9Z"/></svg><a :href="venue.website_url" target="_blank">@{{ venue.formatted.website_url }}</a></div>
                                                <div class="venue_opening_times" v-if="venue.opening_times && venue.opening_times.length > 0">
                                                    <header>Opening Times</header>
                                                    <ul>
                                                        <li v-for="opening_time in venue.opening_times">
                                                            <div class="opening_times_day">@{{ opening_time.day }}</div>
                                                            <div class="opening_times_opening">@{{ opening_time.opening }}</div>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <a v-if="venue.is_bookable && venue.website_url" class="btn bg-primary_linear_gradient border-primary text-primary_contrasting w-full venue_booking_url" :href="venue.website_url" target="_blank">Book now</a>
                                            </div>
                                        </template>
                                    </c-panel_expand>
                                </div>
                            </div>
                        </div>
                        <div class="map_venues_nav">
                            <div class="swiper-button-prev" @click="mapScope.previousVenue"><img src="{{ url('assets/img/icons/swiper_button_prev.svg') }}" alt="Previous" width="150" height="150" /></div>
                            <div class="swiper-button-next" @click="mapScope.nextVenue"><img src="{{ url('assets/img/icons/swiper_button_next.svg') }}" alt="Next" width="150" height="150" /></div>
                        </div>
                    </div>
                </template>
            </c-map>
        </div>
    </div>
@endsection