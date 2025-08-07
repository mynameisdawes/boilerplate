<div class="gallery max-w-full mx-auto">
    <swiper
    :modules="swiper_modules"
    :pagination="{ clickable: true, type: 'bullets' }"
    :simulate-touch="false"
    :allow-touch-move="false"
    @swiper="productScope.onSwiper"
    :thumbs="{ swiper: thumbs_swiper }"
    >
        <swiper-slide v-if="productScope.active_variation" v-for="(side, label) in productScope.options.customisations">
            <c-design_canvas
            v-show="productScope.getActiveStep() < 3"
            :container="label"
            :base_image_url="productScope.builder_images[label + '_image']"
            :side="productScope.customisations[label]"
            :position="side.position"
            :color="productScope.active_colour"
            :upload_reference="side"
            :step="productScope.getActiveStep()"
            @mutate="productScope.mutateDesign"
            @update_previews="productScope.generatePreviews"
            @outofbounds="productScope.setCanvasError"
            @dpi_warning="productScope.setDpiWarning"
            :user_has_returned="{{ isset($customisations) ? "true" : "false" }}"
            ></c-design_canvas>
            <img class="image--garment relative z-10" v-show="productScope.getActiveStep() == 3" width="800" height="800" :src="side.preview" />
            <img class="image--garment relative z-0 top-0" width="800" height="800" :src="productScope.builder_images[label + '_image']" />
        </swiper-slide>
        <swiper-slide v-else v-for="(image, idx) in productScope.images">
            <img v-if="!productScope.active_variation" width="800" height="800" :src="image" />
        </swiper-slide>
    </swiper>
    <label v-if="productScope.active_colour_label" class="active_colour_label">@{{ productScope.active_colour_label }}</label>
    <swiper
        class="mt-4"
        :modules="swiper_modules"
        @swiper="onThumbsSwiper"
        slides-per-view="auto"
        :center-insufficient-slides="true"
        space-between="10"
        v-if="productScope.images.length > 1"
    >
        <template v-for="(side, label, idx) in productScope.options.customisations">
            <swiper-slide v-if="side.selected">
                <div class="relative thumb" :class="'row-start-' + ((idx % 2) + 1) + ':3'">
                    <a @click="productScope.setSlide(label)">
                        <img class="relative z-10"  width="400" height="400" :src="side.preview" :alt="productScope.config.name_label + ' thumb'" />
                        <img width="400" height="400" :src="productScope.builder_images[label + '_image']" :alt="productScope.config.name_label + ' thumb'" class="z-0 top-0 absolute"/>
                    </a>
                </div>
            </swiper-slide>
        </template>
    </swiper>
</div>