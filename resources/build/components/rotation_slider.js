let _rotation_slider = {
    name: "c-rotation_slider",
    props: {
        modelValue: {
            required: true
        }
    },
    emits: [
        "update:modelValue",
        "update_previews",
    ],
    data() {
        return {
            target: null,
            rotator: null,
            active: false,
            center: { x: 0, y: 0 },
            angle: 0,
            startAngle: 0,
            R2D: 180/Math.PI,
            rotation: 0,
        }
    },

    methods: {
        start(e) {
            // e.preventDefault();
            let x, y,
                self = this,
                touch = e.type.toLowerCase() !== "mousedown",
                ref = self.rotator.getBoundingClientRect(),
                top = ref.top,
                left = ref.left,
                height = ref.height,
                width = ref.width;
            self.center = {
                x: left + (width / 2),
                y: top + (height / 2)
            };

            x = touch ?
                e.touches[0].clientX - self.center.x :
                e.clientX - self.center.x;
            y = touch ?
                e.touches[0].clientY - self.center.y :
                e.clientY - self.center.y;
            self.startAngle = self.R2D * Math.atan2(y, x);

            return self.active = true;
        },
        rotate(e) {
            let self = this;
            if (!self.active) return;
            // e.preventDefault();
            let value,
                touch = e.type.toLowerCase() !== "mousemove",
                x = touch ?
                    e.touches[0].clientX - self.center.x :
                    e.clientX - self.center.x,
                y = touch ?
                    e.touches[0].clientY - self.center.y :
                    e.clientY - self.center.y,
                d = self.R2D * Math.atan2(y, x);

            self.rotation = this.round(d - self.startAngle, 15);

            self.rotation = (360 + self.rotation) % 360;
            value = (self.rotation + self.angle) % 360;
            //ensures that we are always working with a value < 360

            this.$emit("update:modelValue", value);
        },
        stop() {
            let self = this;
            if (self.active) {
                self.angle += self.rotation;
                this.$emit("update_previews");
                return self.active = false;
            }

        },
        round(num, rounder) {
            let y = num + (rounder / 2);
            return y - (y % rounder);
        },
    },

    created() {
        this.angle = this.modelValue;
    },

    mounted() {
        this.target = this.$refs.rotatorThumb;
        this.rotator = this.$refs.rotatorBase;
        document.addEventListener("mousemove", this.rotate, false);
        document.addEventListener("mouseup", this.stop, false);
        document.addEventListener("touchmove", this.rotate, false);
        document.addEventListener("touchend", this.stop, false);
    },

    template: `
        <div class="round-slider mx-auto" :style="{ 'background-image': 'conic-gradient(var(--color_primary) ' + modelValue + 'deg, var(--color_border) ' + modelValue + 'deg)' }">
            <div
                class="round-slider__inner"
                ref="rotatorBase"
                :style="{ transform: 'rotate(' + modelValue + 'deg)' }"
                >
                <div
                    class="round-slider__dragger"
                    ref="rotatorThumb"
                    @mousedown.prevent="start"
                    @touchstart.passive="start"
                    ></div>
            </div>
        </div>
    `
};

export default _rotation_slider;