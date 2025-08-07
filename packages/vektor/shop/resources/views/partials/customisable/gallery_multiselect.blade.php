<div class="gallery max-w-full mx-auto relative:1t2e">
    <div :class="productScope.active_variation && (Object.keys(productScope.builder_images[productScope.active_variation]).length > 1 || productScope.images.length > 1) ? 'grid:1t2e grid-cols-5:1t2e' : ''">
        <div class="col-span-4:1t2e pr-1:1t2e">
            <swiper
            :modules="swiper_modules"
            :pagination="{ clickable: true, type: 'bullets' }"
            :simulate-touch="false"
            :allow-touch-move="false"
            @swiper="productScope.onSwiper"
            >
                <swiper-slide v-if="productScope.active_variation" v-for="(side, label) in productScope.options.customisations">
                    <c-design_canvas
                    v-show="productScope.getActiveStep() < 3"
                    :container="label"
                    :base_image_url="productScope.builder_images[productScope.active_variation][label + '_image']"
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
                    <img class="image--garment relative z-0 top-0" width="800" height="800" :src="productScope.builder_images[productScope.active_variation][label + '_image']" />
                </swiper-slide>
                <swiper-slide v-else v-for="(image, idx) in productScope.images">
                    <img v-if="!productScope.active_variation" width="800" height="800" :src="image" />
                </swiper-slide>
            </swiper>
            <label v-if="productScope.active_colour_label" class="active_colour_label">@{{ productScope.active_colour_label }}</label>
        </div>
        <div class="col-start-5:1t2e pl-1:1t2e mt-4:3 overflow-scroll:3" v-show="(productScope.active_variation && Object.keys(productScope.builder_images[productScope.active_variation]).length > 1) || (!productScope.active_variation && productScope.images.length > 1)">
            <div class="colour__selection hidden:3 pb-2:1t2e" v-if="productScope.variations.colour != null && productScope.getActiveStep() != 1">
                <a :class="{ 'expand__trigger--expanded':  productScope.panels.colour_select.is_expanded }" class="expand__trigger no-underline block flex items-center justify-between pr-6 small" @click="productScope.panels.colour_select.is_expanded = !productScope.panels.colour_select.is_expanded">
                    <input type="radio" class="radio color dummy" :name="'dummy_' + productScope.active_colour" checked="true" :style="{ 'background-color': productScope.active_colour, 'border-color': productScope.active_colour }" disabled="true" aria-hidden="true">
                </a>
                <c-panel_expand class="expand__panel--no_inner absolute" :is_expanded="productScope.panels.colour_select.is_expanded">
                    <c-input :name="productScope.variations[productScope.primary].name" :label="productScope.variations[productScope.primary].label" v-model="productScope.options.selected" :type="productScope.variationInputType(productScope.variations[productScope.primary].name)" :options="productScope.configurations" :allow_null="false"></c-input>
                </c-panel_expand>
            </div>
            <div v-if="productScope.selected_sorted.length > 0 && productScope.active_variation" class="grid:3 flex:1t2e thumbs--gallery" :class="{'grid-rows-2:3': productScope.selected_sorted.length > 1, 'justify-center': productScope.selected_sorted.length < 4}">
                <template v-for="option in productScope.selected_sorted">
                    <template v-for="(side, label, idx) in productScope.options.customisations">
                        <template v-if="side.selected">
                            <div class="relative thumb" :class="{
                                'row-start-1:3': idx % 2 == 0,
                                'row-start-2:3': idx % 2 != 0
                                }">
                                <a @click="productScope.setActiveVariation(option.colour); productScope.setSlide(label)">
                                    <img class="relative z-10"  width="400" height="400" :src="side.preview" :alt="productScope.config.name_label + ' thumb'" />
                                    <img width="400" height="400" :src="productScope.builder_images[option.colour][label + '_image']" :alt="productScope.config.name_label + ' thumb'" class="z-0 top-0 absolute" :class="{'active': productScope.active_variation == option.colour && productScope.swiper.activeIndex == idx}"/>
                                </a>
                            </div>
                        </template>
                    </template>
                </template>
            </div>
        </div>
    </div>
</div>