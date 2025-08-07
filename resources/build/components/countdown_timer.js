let _countdown_timer = {
    name: "c-countdown_timer",
    emits: [
        "ended"
    ],
    props: {
        targetDate: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            days: 0,
            hours: 0,
            minutes: 0,
            seconds: 0,
            less_than_day: false,
            reached_end: false,
            intervalId: null,
        };
    },
    computed: {
        days_padded() {
            return String(this.days).padStart(2, "0");
        },
        hours_padded() {
            return String(this.hours).padStart(2, "0");
        },
        minutes_padded() {
            return String(this.minutes).padStart(2, "0");
        },
        seconds_padded() {
            return String(this.seconds).padStart(2, "0");
        }
    },
    mounted() {
        this.calculateTimeRemaining();
        this.intervalId = setInterval(this.calculateTimeRemaining, 1000);
    },
    beforeUnmount() {
        clearInterval(this.intervalId);
    },
    methods: {
        calculateTimeRemaining() {
            const now = new Date();
            const end = new Date(this.targetDate);
            const diff = end - now;

            if (isNaN(diff) || diff <= 0) {
                this.days = this.hours = this.minutes = this.seconds = 0;
                this.less_than_day = true;
                clearInterval(this.intervalId);
                this.reached_end = true;
                this.$emit("ended");
                return;
            }

            const totalSecs = Math.floor(diff / 1000);
            this.days = Math.floor(totalSecs / (3600 * 24));
            this.hours = Math.floor((totalSecs % (3600 * 24)) / 3600);
            this.minutes = Math.floor((totalSecs % 3600) / 60);
            this.seconds = totalSecs % 60;

            this.less_than_day = diff < 24 * 3600 * 1000;
        },
    },
    template: `
    <slot
    :days="days"
    :hours="hours"
    :minutes="minutes"
    :seconds="seconds"
    :days_padded="days_padded"
    :hours_padded="hours_padded"
    :minutes_padded="minutes_padded"
    :seconds_padded="seconds_padded"
    :less_than_day="less_than_day"
    :reached_end="reached_end"
    ></slot>
    `
};

export default _countdown_timer;
