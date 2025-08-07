import { shallowRef, toRaw } from "vue";

import { Loader } from "@googlemaps/js-api-loader";
import { MarkerClusterer } from "@googlemaps/markerclusterer";

import _config from "@/utilities/config.js";
_config.init();

import { _api, _storage } from "@/utilities/api.js";
import _utilities from "@/utilities/utilities.js";
import _validation from "@/utilities/validation.js";

import debounce from "lodash/debounce";

const getCurrentLocationMarkerHtml = () => `
    <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120">
        <style>
            @keyframes pulse {
                0% { r: 0; fill-opacity: 0.5; stroke-opacity: 0.7; }
                80% { r: 40; fill-opacity: 0.05; stroke-opacity: 0.3; }
                100% { r: 60; fill-opacity: 0; stroke-opacity: 0; }
            }
            .pulse_1, .pulse_2, .pulse_3, .pulse_4 {
                fill: var(--color_primary);
                stroke: var(--color_primary_dark);
                stroke-width: 0.5;
                animation: pulse 6s infinite var(--transition_easing_cubic);
                animation-delay: -2.5s;
            }
            .pulse_2 { animation-delay: -3s; }
            .pulse_3 { animation-delay: -5.5s; }
            .pulse_4 { animation-delay: -6s; }
        </style>
        <circle class="pulse_1" cx="60" cy="60" r="60"/>
        <circle class="pulse_2" cx="60" cy="60" r="60"/>
        <circle class="pulse_3" cx="60" cy="60" r="60"/>
        <circle class="pulse_4" cx="60" cy="60" r="60"/>
        <circle fill="#FFFFFF" cx="60" cy="60" r="10"/>
        <circle fill="var(--color_primary)" cx="60" cy="60" r="6"/>
    </svg>
`;

const getMarkerHtml = () => `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="26" height="32" viewBox="0 0 26 32">
        <defs>
            <style>.marker_background { fill: url(#marker_gradient); stroke: }</style>
            <linearGradient id="marker_gradient" x1="0" y1="16" x2="26" y2="16" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#40404f"/><stop offset=".3" stop-color="#3b3b49"/><stop offset=".6" stop-color="#2f2f3b"/><stop offset="1" stop-color="#1a1a22"/><stop offset="1" stop-color="#17171f"/></linearGradient>
        </defs>
        <path class="marker_background" d="M22.2,3.9C17.1-1.3,8.9-1.3,3.8,3.9c-5.1,5.2-5.1,13.6,0,18.8l9.2,9.3,9.2-9.5c5.1-5,5.1-13.5,0-18.7ZM13,16c-1.5,0-2.7-1.3-2.7-2.8s1.2-2.8,2.7-2.8,2.7,1.3,2.7,2.8-1.2,2.8-2.7,2.8Z"/>
    </svg>
`;

const getSelectedMarkerHtml = () => `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="26" height="32" viewBox="0 0 26 32">
        <defs>
            <style>.selected_marker_background { fill: url(#selected_marker_gradient); }</style>
            <linearGradient id="selected_marker_gradient" x1="0" y1="16" x2="26" y2="16" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="var(--color_primary_light)"/><stop offset="1" stop-color="var(--color_primary_dark)"/></linearGradient>
        </defs>
        <path class="selected_marker_background" d="M22.2,3.9C17.1-1.3,8.9-1.3,3.8,3.9c-5.1,5.2-5.1,13.6,0,18.8l9.2,9.3,9.2-9.5c5.1-5,5.1-13.5,0-18.7ZM13,16c-1.5,0-2.7-1.3-2.7-2.8s1.2-2.8,2.7-2.8,2.7,1.3,2.7,2.8-1.2,2.8-2.7,2.8Z"/>
    </svg>
`;

const getMarkerClusterHtml = (count) => `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="26" height="32" viewBox="0 0 26 32">
        <defs>
            <style>.marker_text { fill: #FFFFFF; } .marker_background { fill: url(#marker_gradient); }</style>
            <linearGradient id="marker_gradient" x1="0" y1="16" x2="26" y2="16" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#40404f"/><stop offset=".3" stop-color="#3b3b49"/><stop offset=".6" stop-color="#2f2f3b"/><stop offset="1" stop-color="#1a1a22"/><stop offset="1" stop-color="#17171f"/></linearGradient>
        </defs>
        <path class="marker_background" d="M22.2,3.9C17.1-1.3,8.9-1.3,3.8,3.9c-5.1,5.2-5.1,13.6,0,18.8l9.2,9.3,9.2-9.5c5.1-5,5.1-13.5,0-18.7Z"/>
        <text class="marker_text" x="50%" y="50%" font-size="10" font-weight="bold" text-anchor="middle" dy="1">${count}</text>
    </svg>
`;

let fields = {
    place_autocomplete: {
        text: {
            validations: {
                rules: {
                    required: _validation.rules.required
                }
            }
        },
    },
};

let _map = {
    name: "c-map",
    props: {
        api_key: { required: true },
        map_id: { required: true },
        initial_markers: {
            type: Array,
            default() {
                return [];
            }
        },
        markers_url: { },
        marker_qty: { default: 20 },
        place_autocomplete_url: {},
        place_geocode_url: {},
        initial_latitude: {},
        initial_longitude: {},
    },
    data() {
        return {
            is_loading: false,
            googleMaps: null,
            map: null,
            map_bounds: null,
            mode: "map",
            markers: [],
            current_location_marker: null,
            navigator_geolocation_supported: false,
            watch_id: null,
            search_is_collapsed: true,
            marker_clusterer: null,
            marker_elements: [],
            marker_venues: [],
            latitude: null,
            longitude: null,
            place_suggestions: [],
            forms: {
                place_autocomplete: {
                    ref: "place_autocomplete",
                    action: null,
                    field_values: _validation.createFieldsData(fields.place_autocomplete),
                    field_storage: _validation.createFieldsStorage(fields.place_autocomplete),
                    validation_rules: _validation.createFieldsValidationRules(fields.place_autocomplete),
                    validation_messages: _validation.createFieldsValidationMessages(fields.place_autocomplete),
                }
            },
        };
    },
    watch: {
    },
    methods: {
        rad(x) {
            return (x * Math.PI) / 180;
        },
        calculateDistance(marker) {
            const earth_radius = 3961;
            if (this.latitude && this.longitude && marker.latitude && marker.longitude) {
                const mlat = parseFloat(marker.latitude);
                const mlng = parseFloat(marker.longitude);
                const dLat = this.rad(mlat - parseFloat(this.latitude));
                const dLong = this.rad(mlng - parseFloat(this.longitude));
                const a =
                    Math.sin(dLat / 2) ** 2 +
                    Math.cos(this.rad(this.latitude)) *
                        Math.cos(this.rad(mlat)) *
                        Math.sin(dLong / 2) ** 2;
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                marker.distance = Math.round(earth_radius * c * 100) / 100;
            } else {
                marker.distance = null;
            }
        },
        removeMarkers() {
            if (this.marker_elements.length) {
                this.marker_elements.forEach((marker) => {
                    marker.map = null;
                });
                this.marker_clusterer.clearMarkers();
                this.marker_elements = [];
                this.marker_venues = [];
            }
        },
        placeMarkers() {
            if (this.markers.length) {
                this.markers.forEach((marker) => this.calculateDistance(marker));
                const sorted_markers = [...this.markers].sort((a, b) => a.distance - b.distance);

                this.removeMarkers();

                let qty = this.marker_qty > 0 ? this.marker_qty : sorted_markers.length;

                for (let marker_idx = 0; marker_idx < qty && marker_idx < sorted_markers.length; marker_idx++) {
                    const marker = sorted_markers[marker_idx];

                    this.marker_venues[marker_idx] = marker;
                    this.marker_venues[marker_idx].is_selected = false;

                    const marker_element = document.createElement("div");
                    marker_element.innerHTML = getMarkerHtml();
                    marker_element.classList.add("map_marker");

                    this.marker_elements[marker_idx] = new this.googleMaps.marker.AdvancedMarkerElement({
                        map: toRaw(this.map),
                        position: new this.googleMaps.LatLng(
                            marker.latitude,
                            marker.longitude
                        ),
                        title: marker.name,
                        content: marker_element,
                    });

                    this.marker_elements[marker_idx].is_selected = false;

                    if (!marker.exclude_from_map_bounds) {
                        this.map_bounds.extend(
                            this.marker_elements[marker_idx].position
                        );
                    }

                    this.marker_elements[marker_idx].addListener("gmp-click", () => {
                        this.manageMarkerSelection(marker_idx, true);
                    });
                }

                const renderer = {
                    render: ({ count, position }) => {
                        const marker_element = document.createElement("div");
                        marker_element.innerHTML = getMarkerClusterHtml(count);
                        marker_element.classList.add("map_marker");

                        return new this.googleMaps.marker.AdvancedMarkerElement({
                            map: toRaw(this.map),
                            position,
                            zIndex: Number(this.googleMaps.Marker.MAX_ZINDEX) + count,
                            content: marker_element,
                        });
                    },
                };

                this.marker_clusterer = new MarkerClusterer({
                    map: toRaw(this.map),
                    markers: this.marker_elements,
                    renderer: renderer,
                });

                this.$nextTick(() => {
                    const map_venues = this.$el.querySelector(".map_venues");
                    if (map_venues) {
                        map_venues.scrollTo({ top: 0, left: 0, behavior: "smooth" });
                    }
                    this.map.fitBounds(this.map_bounds, this.calculateBoundsPadding());
                    setTimeout(() => {
                        this.map.setZoom(this.map.getZoom() - 1);
                    }, 100);
                });
            }
        },
        fetchMarkers() {
            if (this.markers_url) {
                return _storage.post(_config.get(this.markers_url), (_response) => {
                    if (_storage.isSuccess(_response)) {
                        return _storage.getResponseData(_response).then((response) => {
                            if (response.markers.length) {
                                response.markers.forEach((marker, idx) => {
                                    marker.marker_id = idx;
                                });
                                this.markers = response.markers;
                            }
                        });
                    } else {
                    }
                });
            } else {
                if (this.initial_markers.length > 0) {
                    return new Promise((resolve) => {
                        this.initial_markers.forEach((marker, idx) => {
                            marker.marker_id = idx;
                        });

                        this.markers = this.initial_markers;
                        resolve();
                    });
                } else {
                    return new Promise((resolve) => {
                        this.$el.remove();
                        resolve();
                    });
                }
            }
        },
        manageMarkerSelection(marker_idx, via_map) {
            const map_venues = this.$el.querySelector(".map_venues");
            const marker_venue_element = this.$el.querySelector(`[venue_idx="${marker_idx}"]`);
            const direction = map_venues ? getComputedStyle(map_venues).getPropertyValue("--direction") : null;

            let timeout = 350;
            if (direction === 'x' || marker_idx == null) {
                timeout = 0;
            }

            requestAnimationFrame(() => {
                if (marker_venue_element) {
                    const scroll_options = {
                        behavior: "smooth",
                    };
                    if (direction === "y") {
                        scroll_options.block = "start";
                        scroll_options.inline = "nearest";
                    }
                    if (direction === "x") {
                        scroll_options.block = "end";
                        scroll_options.inline = "start";
                    }
                    marker_venue_element.scrollIntoView(scroll_options);
                }
            });

            setTimeout(() => {
                this.marker_venues.forEach((marker, idx) => {
                    marker.is_selected = idx === marker_idx;
                });

                this.marker_elements.forEach((marker, idx) => {
                    const is_selected = idx === marker_idx;

                    if (marker.is_selected !== is_selected) {
                        marker.is_selected = is_selected;
                        marker.content.innerHTML = is_selected
                            ? getSelectedMarkerHtml()
                            : getMarkerHtml();
                    }

                    if (is_selected && !via_map) {
                        this.map.setZoom(14);
                        this.panTo(new this.googleMaps.LatLng(marker.position.lat, marker.position.lng));
                    }
                });

                if (marker_idx === null) {
                    if (!via_map) {
                        if (direction === 'y') {
                            this.map.fitBounds(this.map_bounds, this.calculateBoundsPadding());
                            this.map.setZoom(this.map.getZoom() - 1);
                        }
                    }
                    return;
                }
            }, timeout);
        },
        toggleVenue(marker_idx) {
            if (marker_idx == null) {
                this.manageMarkerSelection(null, false);
            } else {
                if (this.marker_venues[marker_idx].is_selected == false) {
                    this.manageMarkerSelection(marker_idx, false);
                } else {
                    this.manageMarkerSelection(null, false);
                }
            }
        },
        getCurrentSnappedItem() {
            const map_venues = this.$el.querySelector(".map_venues");
            if (!map_venues) return null;

            const items = Array.from(map_venues.children);
            const container_left =
                map_venues.getBoundingClientRect().left;

            let closest_index = 0;
            let closest_distance = Infinity;

            for (let i = 0; i < items.length; i++) {
                const item_left = items[i].getBoundingClientRect().left;
                const distance = Math.abs(item_left - container_left);

                if (distance < closest_distance) {
                    closest_distance = distance;
                    closest_index = i;
                }
            }

            return {
                item: items[closest_index],
                index: closest_index,
                prev_index: Math.max(0, closest_index - 1),
                next_index: Math.min(items.length - 1, closest_index + 1),
            };
        },
        previousVenue() {
            const current_item = this.getCurrentSnappedItem();

            if (current_item) {
                const prev_item = current_item.item.previousElementSibling;
                if (prev_item) {
                    prev_item.scrollIntoView({ behavior: "smooth", block: "nearest", inline: "start" });
                }
            }
        },
        nextVenue() {
            const current_item = this.getCurrentSnappedItem();

            if (current_item) {
                const next_item = current_item.item.nextElementSibling;
                if (next_item) {
                    next_item.scrollIntoView({ behavior: "smooth", block: "nearest", inline: "start" });
                }
            }
        },
        setMapMode(mode) {
            this.mode = mode;
            this.toggleVenue(null);
        },
        fetchPlaceSuggestions: debounce(function(value) {
            if (this.place_autocomplete_url && value) {
                let payload = {
                    data: {
                        text: value,
                        latitude: this.latitude,
                        longitude: this.longitude,
                    }
                };

                _storage.post(_config.get(this.place_autocomplete_url), (_response) => {
                    if (_storage.isSuccess(_response)) {
                        let response = _storage.getResponseData(_response);
                        this.place_suggestions = response.places;
                    } else {
                        this.place_suggestions = [];
                    }
                }, payload);
            }
        }, 500),
        geocodePlace(suggestion) {
            if (this.place_geocode_url && suggestion) {
                let payload = {
                    data: {
                        place_id: suggestion.value,
                    }
                };

                _storage.post(_config.get(this.place_geocode_url), (_response) => {
                    if (_storage.isSuccess(_response)) {
                        let response = _storage.getResponseData(_response);
                        if (response.address !== undefined) {
                            this.latitude = response.address.latitude;
                            this.longitude = response.address.longitude;
                            this.placeMarkers();
                        }
                    } else {
                    }
                }, payload);
            }
        },
        placeCurrentLocationMarker() {
            if (this.current_location_marker == null) {
                const marker_element = document.createElement("div");
                marker_element.innerHTML = getCurrentLocationMarkerHtml();
                marker_element.classList.add("map_current_location");

                this.current_location_marker = new this.googleMaps.marker.AdvancedMarkerElement({
                    map: toRaw(this.map),
                    position: new this.googleMaps.LatLng(this.latitude, this.longitude),
                    content: marker_element,
                });
            }
        },
        updateCurrentLocationMarker() {
            if (this.current_location_marker) {
                this.current_location_marker.position = new this.googleMaps.LatLng(this.latitude, this.longitude);
            }
        },
        watchCurrentLocation() {
            if (this.navigator_geolocation_supported) {
                this.watch_id = navigator.geolocation.watchPosition((position) => {
                    if (position) {
                        this.latitude = position.coords.latitude;
                        this.longitude = position.coords.longitude;
                        this.updateCurrentLocationMarker();
                    }
                }, (error) => {
                }, {
                    enableHighAccuracy: true,
                    maximumAge: 0
                });
            }
        },
        getCurrentLocation() {
            if (this.navigator_geolocation_supported) {
                this.is_loading = true;
                navigator.geolocation.getCurrentPosition((position) => {
                    if (position) {
                        this.latitude = position.coords.latitude;
                        this.longitude = position.coords.longitude;
                        this.placeCurrentLocationMarker();
                        this.placeMarkers();

                        if (!this.watch_id) {
                            this.watchCurrentLocation();
                        }
                    }
                    this.is_loading = false;
                }, (error) => {
                    this.is_loading = false;
                });
            }
        },
        toggleSearch() {
            const map_wrapper = this.$el;
            const mode = map_wrapper ? getComputedStyle(map_wrapper).getPropertyValue("--mode") : null;
            const map_search_input = this.$el.querySelector(".map_search input");

            if (this.search_is_collapsed) {
                this.search_is_collapsed = false;
                if (map_search_input && mode === 'sm') {
                    map_search_input.focus();
                }
            } else {
                this.search_is_collapsed = true;
            }

            if (map_search_input && mode === 'lg') {
                map_search_input.focus();
            }
        },
        calculateBoundsPadding() {
            const map_wrapper = this.$el;
            const mode = map_wrapper ? getComputedStyle(map_wrapper).getPropertyValue("--mode") : null;
            const map_controls = map_wrapper.querySelector(".map_controls");
            const map_venues = map_wrapper.querySelector(".map_venues");

            let options = {
                top: 0,
                bottom: 0,
                left: 0,
                right: 0
            };

            if (mode === 'sm') {
                if (map_controls) {
                    options.top = map_controls.offsetHeight;
                }
                if (map_venues) {
                    options.bottom = map_venues.offsetHeight;
                }
            }

            return options;
        },
        panTo(lat_lng) {
            const map_wrapper = this.$el;
            const mode = map_wrapper ? getComputedStyle(map_wrapper).getPropertyValue("--mode") : null;
            const map_controls = map_wrapper.querySelector(".map_controls");
            const map_venues = map_wrapper.querySelector(".map_venues");

            let offset_pixels = 0;

            if (mode === 'sm') {
                let top_offset = 0;
                let bottom_offset = 0;
                if (map_controls) {
                    top_offset = map_controls.offsetHeight;
                }
                if (map_venues) {
                    bottom_offset = map_venues.offsetHeight;
                }
                offset_pixels = (bottom_offset - top_offset) / 2;
            }

            if (offset_pixels !== 0) {
                const scale = Math.pow(2, this.map.getZoom());
                const world_coordinate_center = this.map.getProjection().fromLatLngToPoint(lat_lng);
                const pixel_offset = offset_pixels / scale;

                const new_center = this.map.getProjection().fromPointToLatLng(
                    new google.maps.Point(world_coordinate_center.x, world_coordinate_center.y + pixel_offset)
                );

                this.map.panTo(new_center);
            } else {
                this.map.panTo(lat_lng);
            }
        },
        mapResize: debounce(function() {
            this.map.fitBounds(this.map_bounds, this.calculateBoundsPadding());
            this.map.setZoom(this.map.getZoom() - 1);
        }, 200),
        initialize() {
            this.is_loading = true;
            this.map_bounds = new this.googleMaps.LatLngBounds();

            this.fetchMarkers()
                .then(() => {
                    this.map = new this.googleMaps.Map(this.$el.querySelector(".map"), {
                        // mapId: "DEMO_ID",
                        mapId: this.map_id,
                        center: { lat: 54.136696, lng: -2.988281 },
                        zoom: 5,
                        disableDefaultUI: true,
                        clickableIcons: false,
                        scrollwheel: true,
                    });

                    this.placeMarkers();
                    // this.placeCurrentLocationMarker();

                    window.addEventListener("resize", () => {
                        this.mapResize();
                    });

                    this.is_loading = false;
                })
                .catch((error) => {});
        },
    },
    mounted() {
        if (this.api_key) {
            const loader = new Loader({
                apiKey: this.api_key,
                version: "weekly",
                libraries: ["maps", "marker"],
            });

            loader
                .load()
                .then((google) => {
                    this.googleMaps = google.maps;
                    this.initialize();
                })
                .catch(() => {
                    this.$el.remove();
                });
        } else {
            this.$el.remove();
        }
    },
    created() {
        if (this.initial_latitude && this.initial_longitude) {
            this.latitude = parseFloat(this.initial_latitude);
            this.longitude = parseFloat(this.initial_longitude);
        }
        if (navigator.geolocation) {
            this.navigator_geolocation_supported = true;
        }
    },
    beforeUnmount() {
        window.removeEventListener("resize", this.mapResize);
        if (this.watch_id) { navigator.geolocation.clearWatch(this.watch_id) };
    },
    template: `
    <div class="map_wrapper">
        <slot
        :fetchPlaceSuggestions="fetchPlaceSuggestions"
        :getCurrentLocation="getCurrentLocation"
        :geocodePlace="geocodePlace"
        :nextVenue="nextVenue"
        :previousVenue="previousVenue"
        :setMapMode="setMapMode"
        :toggleVenue="toggleVenue"
        :toggleSearch="toggleSearch"

        :is_loading="is_loading"
        :forms="forms"
        :marker_venues="marker_venues"
        :mode="mode"
        :navigator_geolocation_supported="navigator_geolocation_supported"
        :place_suggestions="place_suggestions"
        :search_is_collapsed="search_is_collapsed"
        ></slot>
    </div>
    `,
};

export default _map;
