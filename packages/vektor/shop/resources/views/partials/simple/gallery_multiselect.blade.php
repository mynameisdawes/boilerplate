<div class="gallery max-w-full max-w-md:1t2e mx-auto relative:1t2e">
    <div :class="productScope.active_variation && productScope.images[productScope.active_variation].length > 1 ? 'grid:1t2e grid-cols-5:1t2e' : ''">
        <div class="col-span-4:1t2e pr-1:1t2e">
            <swiper
            :modules="swiper_modules"
            :pagination="{ clickable: true, type: 'bullets' }"
            :simulate-touch="false"
            :allow-touch-move="false"
            @swiper="productScope.onSwiper"
            >
                <swiper-slide v-for="image in productScope.images[productScope.active_variation]">
                    <a @click.stop.prevent="productScope.openImageModal(image)" class="gallery_zoom">
                        <img class="image--garment relative z-0 top-0" width="800" height="800" :src="image" />
                    </a>
                </swiper-slide>
            </swiper>
            <c-modal :trigger="productScope.is_image_modal_shown" @open="productScope.openImageModal" @close="productScope.closeImageModal">
                <img width="1000" height="1000" :src="productScope.image_modal_src" :alt="productScope.config.name_label" />
            </c-modal>
            <label v-if="productScope.active_colour_label" class="active_colour_label">@{{ productScope.active_colour_label }}</label>
        </div>
        <div class="col-start-5:1t2e pl-1:1t2e mt-4:3 overflow-scroll:3" v-show="productScope.active_variation && Object.keys(productScope.images[productScope.active_variation]).length > 1">
            <div v-if="productScope.selected_sorted.length > 0" class="grid:3 flex:1t2e thumbs--gallery" :class="{'grid-rows-2:3': productScope.selected_sorted.length > 1, 'justify-center': productScope.selected_sorted.length < 4}" v-if="productScope.active_variation">
                <template v-for="option in productScope.selected_sorted">
                    <template v-for="(image, idx) in productScope.images[option[productScope.primary]]">
                        <div class="relative thumb" :class="'row-start-' + ((idx % 2) + 1) + ':3'">
                            <a @click="productScope.setActiveVariation(option.colour);productScope.setSlide(idx)">
                                <img width="400" height="400" :src="image" :alt="productScope.config.name_label + ' thumb'" class="z-0 top-0"/>
                            </a>
                        </div>
                    </template>
                </template>
            </div>
        </div>
    </div>
</div>