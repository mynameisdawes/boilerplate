import _option_mixin from "@/mixins/options.js";

import _config from "@/utilities/config.js";
_config.init();

let _validation;
import("@/utilities/validation.js").then((exports) => { _validation = exports.default; })

import { default as c_secondary_table } from "./secondary_table.js";

let _size_multi_select = {
    props: {
		can_remove: {
			type: Boolean,
			default: true
		}
	},
    name: "c-size_multi_select",
    mixins: [_option_mixin],
    components: {
        "c-secondary_table": c_secondary_table
    },
    emits: [
		"option_removed"
	],
	data() {
		return {
			images: {}
		}
	},
    methods: {
		option_canvas(option) {
			if (this.product.builder_images !== undefined && this.product.builder_images[option] !== undefined) {
				const base_image = this.product.builder_images[option].side_1_image;
				if (this.product.options.customisations != null) {
					const width = 300;
					const height = 1200;
					let data = {
						customisations: {}
					};
					let promises = [];
					let customisation = this.product.options.customisations.side_1;
					if (customisation != null) {
						const canvas = document.createElement('canvas');
						var base = new Image();
						base.src = base_image;
						new Promise(innerResolve => {
							base.onload = function() {
								canvas.setAttribute('width', width);
								canvas.setAttribute('height', width);

								const context = canvas.getContext('2d');
								context.drawImage(base, 0, 0, width, width);
								var img = new Image();
								img.src = customisation.preview;
								img.onload = function() {
									context.drawImage(img, 0, 0, width, width);
									innerResolve();
								}
							}
						}).then(() => {
							this.images[option] = canvas.toDataURL('image/png');
						});
					}
				} else {
					this.images[option] = base_image ?? null;
				}
			} else {
				return null;
			}
		},
		option_image(option) {
			if (this.product.builder_images !== undefined && this.product.builder_images[option] !== undefined) {
				return this.product.builder_images[option].side_1_image ?? null;
			} else {
				return this.product.images[option] !== undefined ? this.product.images[option][0] : null;
			}
		},
		option_preview(option) {
			if (this.product.options.customisations !== undefined && this.product.options.customisations.side_1 !== undefined) {
				return this.product.options.customisations.side_1.preview ?? null;
			} else {
				return null;
			}
		},
		removeOption(option_idx) {
			this.$emit('option_removed', option_idx);
		}
	},
    template: `
    <div class="grid gap-6">
		<div v-for="option in product.selected_sorted" class="border-box p-4 bg-background relative">
			<span v-if="can_remove" class="remove_option" @click="removeOption(option)"></span>
			<c-secondary_table :image="option_image(option[product.primary])" :preview="option_preview(option[product.primary])" :validate="option[product.secondary]" :option="option" :variations="product.variations[product.secondary].variations"></c-secondary_table>
		</div>
	</div>
    `
}

export default _size_multi_select;
