import noUiSlider from "noUiSlider";

let _range_slider = {
    name: 'c-range_slider',
    components: {
        "noUiSlider": noUiSlider
    },
    props: {
        modelValue: {
            required: true
        },
        side: {
            required: true
        }
    },
    emits: [
        "update:modelValue",
        "update_previews",
    ],
    computed: {
        rangeSliderRef() {
            return `range-slider-${this.side}`;
        },
    },
    mounted() {
        let self = this,
            rangeSlider = this.$refs[this.rangeSliderRef],
            bigInteger = 1000000;

        noUiSlider.create(rangeSlider, {
            start: [self.modelValue * bigInteger],
            step: 0.01,
            connect: 'lower',
            range: {
                'min': [0.01],
                'max': [bigInteger]
            }
        });

        rangeSlider.noUiSlider.on('slide', function(val) {
            self.$emit('update:modelValue', val / bigInteger);
        });

        rangeSlider.noUiSlider.on('end', function(val) {
            self.$emit('update_previews');
        });
    },

    template: `
        <div :ref="rangeSliderRef"></div>
    `
};

export default _range_slider;