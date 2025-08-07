import _config from "@/utilities/config.js";
_config.init();

import { _storage } from "@/utilities/api.js";

let _cms_events = {
    name: "c-cms_events",
    props: {
        paginate: {
            type: Boolean,
            default: true
        },
        per_pages: {
            type: Array,
            default() {
                return [4, 8];
            }
        }
    },
    data() {
        return {
            is_loading: false,
            events_type: 'upcoming',
            upcoming_events_fetched: false,
            upcoming_featured_events: [],
            upcoming_events: [],
            upcoming_pagination: {},
            past_events_fetched: false,
            past_events: [],
            past_pagination: {},
        };
    },
    computed: {
        upcoming_events_grouped() {
            if (this.upcoming_events.length > 0) {
                return this.upcoming_events.reduce((acc, event) => {
                    const formatted_group = event.formatted_group;
                    if (!acc[formatted_group]) {
                        acc[formatted_group] = [];
                    }
                    acc[formatted_group].push(event);
                    return acc;
                }, {});
            }
            return {};
        },
        past_events_grouped() {
            if (this.past_events.length > 0) {
                return this.past_events.reduce((acc, event) => {
                    const formatted_group = event.formatted_group;
                    if (!acc[formatted_group]) {
                        acc[formatted_group] = [];
                    }
                    acc[formatted_group].push(event);
                    return acc;
                }, {});
            }
            return {};
        }
    },
    methods: {
        getPastEvents(data) {
            this.is_loading = true;

            let timeout = 0;
            let payload = {};
            if (typeof(data) !== "undefined") {
                if (this.past_events_fetched) {
                    timeout = 600;
                }
                payload.data = data;
                if (this.paginate === true) {
                    payload.data.paginate = true;
                }
            }

            return _storage.post(_config.get("api.events.past"), (_response) => {
                if (_storage.isSuccess(_response)) {
                    let response = _storage.getResponseData(_response);

                    window.scrollTo({top: 0, behavior: 'smooth'});

                    if (_storage.hasPaginationData(response.past_events)) {
                        setTimeout(() => {
                            let paginated_events = _storage.getPaginationData(response.past_events);
                            this.past_events = paginated_events.data;
                            delete paginated_events.data;
                            this.past_pagination = paginated_events;

                            this.past_events_fetched = true;
                            this.is_loading = false;
                        }, timeout);
                    } else {
                        this.past_events = response.past_events;

                        this.past_events_fetched = true;
                        this.is_loading = false;
                    }
                }
            }, payload);
        },
        getUpcomingEvents(data) {
            this.is_loading = true;

            let timeout = 0;
            let payload = {};
            if (typeof(data) !== "undefined") {
                if (this.upcoming_events_fetched) {
                    timeout = 600;
                }
                payload.data = data;
                if (this.paginate === true) {
                    payload.data.paginate = true;
                }
            }

            return _storage.post(_config.get("api.events.upcoming"), (_response) => {
                if (_storage.isSuccess(_response)) {
                    let response = _storage.getResponseData(_response);

                    window.scrollTo({top: 0, behavior: 'smooth'});

                    if (_storage.hasPaginationData(response.upcoming_events)) {
                        setTimeout(() => {
                            this.upcoming_featured_events = response.upcoming_featured_events;

                            let paginated_events = _storage.getPaginationData(response.upcoming_events);
                            this.upcoming_events = paginated_events.data;
                            delete paginated_events.data;
                            this.upcoming_pagination = paginated_events;

                            this.upcoming_events_fetched = true;
                            this.is_loading = false;
                        }, timeout);
                    } else {
                        this.upcoming_featured_events = response.upcoming_featured_events;

                        this.upcoming_events = response.upcoming_events;

                        this.upcoming_events_fetched = true;
                        this.is_loading = false;
                    }
                }
            }, payload);
        },
        switchEventsType(events_type) {
            this.events_type = events_type;
        }
    },
    created() {
        this.is_loading = true;

        if (this.paginate == false && this.upcoming_events != null && this.upcoming_events.length == 0) {
            this.getPastEvents();
            this.getUpcomingEvents();
        }
    },
    template: `
    <slot
        :is_loading="is_loading"
        :per_pages="per_pages"
        :paginate="paginate"

        :events_type="events_type"
        :upcoming_events_fetched="upcoming_events_fetched"
        :upcoming_featured_events="upcoming_featured_events"
        :upcoming_events="upcoming_events"
        :upcoming_pagination="upcoming_pagination"
        :upcoming_events_grouped="upcoming_events_grouped"
        :past_events_fetched="past_events_fetched"
        :past_events="past_events"
        :past_pagination="past_pagination"
        :past_events_grouped="past_events_grouped"

        :getPastEvents="getPastEvents"
        :getUpcomingEvents="getUpcomingEvents"
        :switchEventsType="switchEventsType"
    ></slot>
    `
};

export default _cms_events;
