<c-cms_events>
    <template v-slot:default="eventsScope">
        <div class="container-gutter:outer">
            <div class="spinner__wrapper" :class="{ is_loading: eventsScope.is_loading == true }">
                <div class="spinner"></div>
            </div>
            <div class="container:lg">
                <div class="collection events_entity_types">
                    <button class="btn" :class="{ 'is_selected': eventsScope.events_type == 'past' }" @click.prevent="eventsScope.switchEventsType('past')">Past Events</button>
                    <button class="btn" :class="{ 'is_selected': eventsScope.events_type == 'upcoming' }" @click.prevent="eventsScope.switchEventsType('upcoming')">Upcoming Events</button>
                </div>

                <div v-show="eventsScope.events_type == 'past'">
                    <template v-if="eventsScope.past_events.length > 0">
                        <section v-for="(event_section, event_section_month) in eventsScope.past_events_grouped" class="events_entity_section">
                            <header class="field__title">@{{ event_section_month }}</header>
                            <ul class="cms_entity_cards events_entity_cards">
                                <li v-for="event in event_section" :key="event.id" class="cms_entity_card">
                                    <div class="card_content">
                                        <div class="visual" v-if="event.formatted_meta_image">
                                            <img :src="event.formatted_meta_image" :alt="event.title">
                                        </div>
                                        <div class="textual">
                                            <div class="article">
                                                <header class="article_header" :style="{ 'view-transition-name': 'event_' + event.id }">
                                                    <div class="h2">@{{ event.title }}</div>
                                                    <div class="collection metadata">
                                                        <div v-if="event.formatted_date" v-html="event.formatted_date"></div>
                                                        <div v-if="event.formatted_time" v-html="event.formatted_time"></div>
                                                    </div>
                                                    <div v-if="event.performance_title">
                                                        <template v-if="event.performance_href">with <a :href="event.performance_href" target="_blank">@{{ event.performance_title }}</a></template>
                                                        <template v-else>with @{{ event.performance_title }}</template>
                                                    </div>
                                                </header>
                                                <c-panel_expand :is_expanded="false" class="expand__panel--no_inner">
                                                    <template v-slot:default>
                                                        <p v-html="event.formatted_description"></p>
                                                    </template>
                                                    <template v-slot:methods_below="panelScope">
                                                        <button class="btn" @click.prevent="panelScope.toggle">@{{ panelScope.is_expanded ? 'Read Less' : 'Read More' }}</button>
                                                    </template>
                                                </c-panel_expand>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </section>
                    </template>
                    <c-message v-if="eventsScope.past_events_fetched == true && eventsScope.past_events.length == 0" required="true" content="There are no past events yet" :trigger="true"></c-message>
                    <c-pagination v-show="eventsScope.past_events_fetched == true && eventsScope.past_events.length > 0 && eventsScope.paginate === true" :properties="eventsScope.past_pagination" :per_pages="eventsScope.per_pages" @change-pagination="eventsScope.getPastEvents" :interact_with_url="false"></c-pagination>
                </div>

                <div v-show="eventsScope.events_type == 'upcoming'">
                    <section v-if="eventsScope.upcoming_featured_events.length > 0" class="events_entity_section">
                        <header class="field__title">Featured</header>
                        <ul class="cms_entity_cards events_entity_cards">
                            <li v-for="event in eventsScope.upcoming_featured_events" :key="event.id" class="cms_entity_card is_featured" :class="{ is_today: event.is_today }">
                                <div class="card_content">
                                    <div class="visual" v-if="event.formatted_meta_image">
                                        <img :src="event.formatted_meta_image" :alt="event.title">
                                    </div>
                                    <div class="textual">
                                        <div class="article">
                                            <header class="article_header" :style="{ 'view-transition-name': 'event_' + event.id }">
                                                <div class="h2">@{{ event.title }}</div>
                                                <div class="collection metadata">
                                                    <div v-if="event.formatted_date" v-html="event.formatted_date"></div>
                                                    <div v-if="event.formatted_time" v-html="event.formatted_time"></div>
                                                </div>
                                                <div v-if="event.performance_title">
                                                    <template v-if="event.performance_href">with <a :href="event.performance_href" target="_blank">@{{ event.performance_title }}</a></template>
                                                    <template v-else>with @{{ event.performance_title }}</template>
                                                </div>
                                            </header>
                                            <c-panel_expand :is_expanded="false" class="expand__panel--no_inner">
                                                <template v-slot:default>
                                                    <p v-html="event.formatted_description"></p>
                                                </template>
                                                <template v-slot:methods_below="panelScope">
                                                    <button class="btn" @click.prevent="panelScope.toggle">@{{ panelScope.is_expanded ? 'Read Less' : 'Read More' }}</button>
                                                </template>
                                            </c-panel_expand>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </section>

                    <template v-if="eventsScope.upcoming_events.length > 0">
                        <section v-for="(event_section, event_section_month) in eventsScope.upcoming_events_grouped" class="events_entity_section">
                            <header class="field__title">@{{ event_section_month }}</header>
                            <ul class="cms_entity_cards events_entity_cards">
                                <li v-for="event in event_section" :key="event.id" class="cms_entity_card" :class="{ is_today: event.is_today }">
                                    <div class="card_content">
                                        <div class="visual" v-if="event.formatted_meta_image">
                                            <img :src="event.formatted_meta_image" :alt="event.title">
                                        </div>
                                        <div class="textual">
                                            <div class="article">
                                                <header class="article_header" :style="{ 'view-transition-name': 'event_' + event.id }">
                                                    <div class="h2">@{{ event.title }}</div>
                                                    <div class="collection metadata">
                                                        <div v-if="event.formatted_date" v-html="event.formatted_date"></div>
                                                        <div v-if="event.formatted_time" v-html="event.formatted_time"></div>
                                                    </div>
                                                    <div v-if="event.performance_title">
                                                        <template v-if="event.performance_href">with <a :href="event.performance_href" target="_blank">@{{ event.performance_title }}</a></template>
                                                        <template v-else>with @{{ event.performance_title }}</template>
                                                    </div>
                                                </header>
                                                <c-panel_expand :is_expanded="false" class="expand__panel--no_inner">
                                                    <template v-slot:default>
                                                        <p v-html="event.formatted_description"></p>
                                                    </template>
                                                    <template v-slot:methods_below="panelScope">
                                                        <button class="btn" @click.prevent="panelScope.toggle">@{{ panelScope.is_expanded ? 'Read Less' : 'Read More' }}</button>
                                                    </template>
                                                </c-panel_expand>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </section>
                    </template>
                    <c-message v-if="eventsScope.upcoming_events_fetched == true && eventsScope.upcoming_events.length == 0 && eventsScope.upcoming_featured_events.length == 0" required="true" content="There are no upcoming events yet" :trigger="true"></c-message>
                    <c-pagination v-show="eventsScope.upcoming_events_fetched == true && eventsScope.upcoming_events.length > 0 && eventsScope.paginate === true" :properties="eventsScope.upcoming_pagination" :per_pages="eventsScope.per_pages" @change-pagination="eventsScope.getUpcomingEvents" :interact_with_url="false"></c-pagination>
                </div>
            </div>
        </div>
    </template>
</c-cms_events>