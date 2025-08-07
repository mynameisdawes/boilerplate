<div class="gallery max-w-full mx-auto">
    <swiper
    :modules="swiper_modules"
    :slides-per-view="1"
    :space-between="16"
    :pagination="{ clickable: true, type: 'bullets' }"
    @swiper="onSwiper"
    :thumbs="{ swiper: thumbs_swiper }"
    >
        <swiper-slide v-for="image in productScope.images">
            <a @click.stop.prevent="productScope.openImageModal(image)" class="gallery_zoom">
                <img width="800" height="800" :src="image" :alt="productScope.config.name_label" />
            </a>
        </swiper-slide>
    </swiper>
    <c-modal :trigger="productScope.is_image_modal_shown" @open="productScope.openImageModal" @close="productScope.closeImageModal">
        <img width="1000" height="1000" :src="productScope.image_modal_src" :alt="productScope.config.name_label" />
    </c-modal>
    <label v-if="productScope.active_colour_label" class="active_colour_label">@{{ productScope.active_colour_label }}</label>
    <swiper
        class="mt-4"
        :modules="swiper_modules"
        @swiper="onThumbsSwiper"
        slides-per-view="auto"
        :center-insufficient-slides="true"
        space-between="10"
    >
        <swiper-slide v-for="image in productScope.images" v-if="productScope.images.length > 1">
            <img width="100" height="100" :src="image" :alt="productScope.config.name_label" />
        </swiper-slide>
    </swiper>
</div>