import Konva from "konva";

const OPTIMAL_DPI = 300;
const PIXELS_PER_DPI_PER_MM = 1 / 25.4;
const PIXELS_PER_MM = OPTIMAL_DPI * PIXELS_PER_DPI_PER_MM;

let design_canvas = {
    name: "c-design_canvas",
    props: [
        "side",
        "position",
        "upload_reference",
        "step",
        "base_image_url",
        "color",
        "container",
        "user_has_returned"
    ],
    data() {
        return {
            boundaryBox: null,
            canvas: {
                whRatio: 1200 / 1200, // Insert here the width / height of the garment image
                container: null,
                w: null,
                h: null,
                x: null,
                y: null,
            },
            konvaBg: null,
            konvaImg: null,
            konvaObj: null,
            uploadPixels: Infinity,
            layer: null,
            newUrl: null,
            stage: null,
        };
    },
    computed: {
        baseImgUrl() {
            return this.base_image_url;
        },
        boundaries() {
            return this.side.positions[this.position]?.boundaries || {};
        },
        boundColor() {
            // Slightly weird looking value ensures that the boundaries are transparent until defined by the user
            if (this.step === 3 || !this.boundaries.x || !this.boundaries.y || !this.boundaries.w || !this.upload_reference.selected) {
                return "transparent";
            }
            return this.lightOrDark === "light" ? "black" : "white";
        },
        boundErrorColor() {
            const negative = getComputedStyle(document.documentElement).getPropertyValue("--color_negative") || "red";
            const rgb1 = this.splitColor(negative);
            const lab1 = this.rgb2lab(rgb1);
            const rgb2 = this.splitColor(this.color);
            const lab2 = this.rgb2lab(rgb2);

            const deltaE = this.calculateDeltaE(lab1, lab2);

            return deltaE > 35 ? negative : `rgb(${255 - rgb1.r}, ${255 - rgb1.g}, ${255 - rgb1.b})`;
        },
        canvasContainerID() {
            return `canvas-container-${this.container}`;
        },
        currBounds() {
            // Determines the height and width of the boundaryBox
            const b = this.boundaries;
            const c = this.canvas;
            return {
                x: b.x * c.w,
                w: b.w * c.w,
                y: b.y * c.h,
                h: (b.w * c.w) * (b.mm.h / b.mm.w),
            };
        },
        dimensions() {
            // Returns millimetre values
            const up = this.upload_reference;
            const r = up.rect;
            const cb = this.currBounds;
            const mmmax = this.boundaries.mm;

            const w = mmmax.w * (r.w / cb.w);
            const h = mmmax.h * (r.h / cb.h);
            let x = mmmax.w * (r.x - cb.x) / cb.w;
            let y = mmmax.h * (r.y - cb.y) / cb.h;

            // The logic below ensures the return of valid x and y values i.e. not negative values, due to slight largening of boundary
            x = Math.max(0, Math.min(mmmax.w - w, x));
            y = Math.max(0, Math.min(mmmax.h - h, y));

            return { h, w, x, y, area: mmmax.h * (up.h / cb.h) * mmmax.w * (up.w / cb.w) };
        },
        imageDpi() {
            // Take the max as width and height swap with rotation
            const dim = Math.max(this.dimensions.w, this.dimensions.h);
            const inches = dim * PIXELS_PER_DPI_PER_MM;
            return this.uploadPixels / inches;
        },
        lightOrDark() {
            // Variables for red, green, blue values
            const { r, g, b } = this.splitColor(this.color);

            // HSP equation from http://alienryderflex.com/hsp.html
            const hsp = Math.sqrt(0.299 * (r * r) + 0.587 * (g * g) + 0.114 * (b * b));

            // Using the HSP value, determine whether the color is light or dark
            return hsp > 127.5 ? "light" : "dark";
        },
        maxUploadHeight() {
            // Determines the maximum possible value for this.upload_reference.h
            const up = this.upload_reference;
            const cb = this.currBounds;
            const r = this.konvaImg.getClientRect();

            // Returns maximum value, which is dependent on whether or not the w/h ratio of the upload is less than that of the boundary box
            return (r.width / r.height) < (cb.w / cb.h) ? cb.h * (up.h / r.height) : (cb.w * (up.w / r.width)) / up.whratio;
        },
        uploadArea() {
            // Area of the uploaded image
            const up = this.upload_reference;
            return up.h * up.w;
        },
        uploadIsAcceptable() {
            const r = this.upload_reference.rect;
            const cb = this.currBounds;
            const bb = {
                x: cb.x - 1,
                y: cb.y - 1,
                w: cb.w + 2,
                h: cb.h + 2,
            };
            return (
                r.x >= bb.x &&
                r.x + r.w <= bb.x + bb.w &&
                r.y >= bb.y &&
                r.y + r.h <= bb.y + bb.h
            ) || (
                    this.upload_reference.selected && !this.upload_reference.file.file_path
                ) || !this.upload_reference.selected;
        },
        uploadOffset() {
            // Allows the image to scale and rotate about its center point
            const up = this.upload_reference;
            return { x: up.w / 2, y: up.h / 2 };
        },
    },
    watch: {
        baseImgUrl() {
            this.setBase();
        },
        // Tidy up code and put deep watchers on all values which are currently being used in computed properties solely for use here
        boundaries: {
            deep: true,
            handler() {
                this.setBounds();
                this.updateBoundaryBox();
                this.$emit("update_previews", this.container);
            },
        },
        dimensions: {
            deep: true,
            handler(newVal) {
                this.$emit("mutate", this.container, {
                    dimensions: { ...newVal },
                    printPixelValues: {
                        w: newVal.w * PIXELS_PER_MM,
                        h: newVal.h * PIXELS_PER_MM,
                    },
                });
            },
        },
        imageDpi(newVal, oldVal) {
            if (this.upload_reference.file.file_extension !== "svg") {
                this.handleDpiWarning(newVal, oldVal);
            }
        },
        position() {
            this.newUrl = true;
            this.$emit("mutate", this.container, { rotation: 0, inputHeightVal: 0.5 });
            this.setKonvaImgPosition(this.konvaObj);
            this.setUploadRect();
        },
        "upload_reference.h"(newVal) {
            if (newVal) {
                this.updateUploadReference(newVal);
            }
        },
        "upload_reference.inputHeightVal"(newVal) {
            // inputHeightVal is a percentage of the maxUploadHeight, so we need to multiply the two to get the desired upload.h
            this.$emit("mutate", this.container, { h: newVal * this.maxUploadHeight });
        },
        "upload_reference.rotation"(newVal) {
            this.konvaImg.setRotation(newVal);
            this.layer.draw();
            this.$emit("mutate", this.container, { h: this.maxUploadHeight * this.upload_reference.inputHeightVal });
            this.setUploadRect();
        },
        "upload_reference.selected"(newVal) {
            if (newVal !== this.upload_reference.selected && this.upload_reference.file.file_path) {
                this.newUrl = false;
                this.konvaObj.src = newVal ? this.upload_reference.file.file_path : "";
            }
        },
        "upload_reference.file.file_path"(newVal) {
            this.konvaObj.src = newVal || "";
            this.layer.draw();
            this.newUrl = true;
            if (!newVal) {
                this.resetUploadReference();
            } else {
                this.loadUploadImage();
            }
        },
        uploadIsAcceptable(newVal) {
            // Recolours boundary box border dependent on whether or not there is a collision
            this.boundaryBox.stroke(newVal ? this.boundColor : this.boundErrorColor);
            this.layer.draw();
            this.$emit("outofbounds", !newVal);
            this.$emit("update_previews", this.container);
        },
        boundColor(newVal) {
            this.boundaryBox.stroke(this.uploadIsAcceptable ? newVal : this.boundErrorColor);
            this.layer.draw();
            this.$emit("update_previews", this.container);
        }
    },
    methods: {
        setKonvaImgPosition(konvaObj) {
            if (this.newUrl) {
                this.setNewUploadVals(konvaObj);
            } else {
                this.setSavedUploadVals();
            }

            this.setUpload();

            // Important for when going backwards and screen is resized / for if user exits progress and returns later in a different sizes screen
            this.$emit("mutate", this.container, { h: this.upload_reference.inputHeightVal * this.maxUploadHeight });
        },
        resetKonvaImgPosition() {
            const x = this.canvas.w * this.upload_reference.resize.x;
            const y = this.canvas.h * this.upload_reference.resize.y;
            this.konvaImg.x(x);
            this.konvaImg.y(y);
            this.$emit("mutate", this.container, { h: this.upload_reference.inputHeightVal * this.maxUploadHeight });
            this.layer.draw();
        },
        round(num, rounder) {
            return num + (rounder / 2) - ((num + (rounder / 2)) % rounder);
        },
        setBase() {
            // Sets the background img (garment) and boundary box dimensions and draws to canvas.
            if (this.base_image_url) {
                this.setBounds();
                this.layer.draw();
            }
        },
        setBounds() {
            const cb = this.currBounds;
            this.boundaryBox.setAttrs({
                x: cb.x - 1,
                width: cb.w + 2,
                y: cb.y - 1,
                height: cb.h + 2,
            });
        },
        setCanvSize() {
            const c = this.canvas;
            const w = parseInt(getComputedStyle(c.container).width);
            c.container.style.height = `${ w / c.whRatio }px`;
        },
        setNewUploadVals(konvaObj) {
            const up = this.upload_reference;
            const cb = this.currBounds;

            this.$emit("mutate", this.container, { whratio: konvaObj.width / konvaObj.height });

            let h, w;
            if (up.whratio < cb.w / cb.h) {
                h = cb.h * up.inputHeightVal;
                w = h * up.whratio;
            } else {
                w = cb.w * up.inputHeightVal;
                h = w / up.whratio;
            }

            this.$emit("mutate", this.container, { h, w });

            this.konvaImg.setAttrs({
                x: cb.x + (cb.w / 2),
                y: cb.y + up.h,
                rotation: 0,
            });
        },
        setSavedUploadVals() {
            this.konvaImg.setAttrs({
                x: this.canvas.w * this.upload_reference.resize.x,
                y: this.canvas.h * this.upload_reference.resize.y,
                rotation: this.upload_reference.rotation,
            });
        },
        setUpload() {
            this.konvaImg.setAttrs({
                width: this.upload_reference.w,
                height: this.upload_reference.h,
                offset: this.uploadOffset,
                image: this.konvaObj,
            });
            this.layer.draw();
            this.$emit("mutate", this.container, {
                resize: {
                    x: this.konvaImg.x() / this.canvas.w,
                    y: this.konvaImg.y() / this.canvas.h,
                }
            });
            this.$emit("update_previews", this.container);
        },
        setUploadRect() {
            const r = this.konvaImg.getClientRect();
            this.$emit("mutate", this.container, {
                rect: {
                    w: r.width,
                    h: r.height,
                    x: r.x,
                    y: r.y,
                }
            });
        },
        splitColor(color) {
            let r, g, b;

            // Check the format of the color, HEX or RGB?
            if (color.startsWith("rgb")) {
                // If RGB --> store the red, green, blue values in separate variables
                const rgb = color.match(/\d+/g);
                [r, g, b] = rgb.map(Number);
            } else {
                // If hex --> Convert it to RGB: http://gist.github.com/983661
                color = +`0x${color.slice(1).replace(color.length < 5 && /./g, "$&$&")}`;
                r = color >> 16;
                g = color >> 8 & 255;
                b = color & 255;
            }
            return { r, g, b };
        },
        rgb2lab({ r, g, b }) {
            r = r / 255; g = g / 255; b = b / 255;
            [r, g, b] = [r, g, b].map(v => v > 0.04045 ? Math.pow((v + 0.055) / 1.055, 2.4) : v / 12.92);
            let x = (r * 0.4124 + g * 0.3576 + b * 0.1805) / 0.95047;
            let y = (r * 0.2126 + g * 0.7152 + b * 0.0722) / 1.00000;
            let z = (r * 0.0193 + g * 0.1192 + b * 0.9505) / 1.08883;
            [x, y, z] = [x, y, z].map(v => v > 0.008856 ? Math.pow(v, 1 / 3) : (7.787 * v) + 16 / 116);
            return [(116 * y) - 16, 500 * (x - y), 200 * (y - z)];
        },
        calculateDeltaE(lab1, lab2) {
            const deltaL = lab1[0] - lab2[0];
            const deltaA = lab1[1] - lab2[1];
            const deltaB = lab1[2] - lab2[2];
            const c1 = Math.sqrt(lab1[1] ** 2 + lab1[2] ** 2);
            const c2 = Math.sqrt(lab2[1] ** 2 + lab2[2] ** 2);
            const deltaC = c1 - c2;
            const deltaH = Math.sqrt(Math.max(0, deltaA ** 2 + deltaB ** 2 - deltaC ** 2));
            const sc = 1 + 0.045 * c1;
            const sh = 1 + 0.015 * c1;
            return Math.sqrt(
                (deltaL / 1) ** 2 +
                (deltaC / sc) ** 2 +
                (deltaH / sh) ** 2
            );
        },
        handleDpiWarning(newVal, oldVal) {
            const steps = [300, 150, 72];
            const warningIndex = steps.findIndex((step, i) => newVal > (steps[i + 1] || 0) && newVal <= step);
            const oldIndex = steps.findIndex((step, i) => oldVal > (steps[i + 1] || 0) && oldVal <= step);

            if (warningIndex !== oldIndex) {
                this.$emit("dpi_warning", this.container, warningIndex >= 0 ? warningIndex + 1 : 0);
            }
        },
        updateUploadReference(newVal) {
            this.$emit("mutate", this.container, { w: newVal * this.upload_reference.whratio });
            this.konvaImg.width(this.upload_reference.w);
            this.konvaImg.height(newVal);
            this.konvaImg.offset(this.uploadOffset);
            this.layer.draw();
            this.setUploadRect();

            if (newVal === null) {
                this.$emit("update_previews", this.container);
            }
        },
        resetUploadReference() {
            this.setUploadRect();
            this.$emit("mutate", this.container, { rotation: 0, inputHeightVal: 0.5 });
            this.$emit("update_previews", this.container);
        },
        loadUploadImage() {
            const img = new Image();
            img.onload = () => {
                this.uploadPixels = Math.max(img.width, img.height);
                this.$emit("mutate", this.container, { recWidth: this.uploadPixels / PIXELS_PER_MM });
                this.$emit("dpi_warning", this.container, 0);
            };
            img.src = this.upload_reference.file.file_path;
        },
        updateBoundaryBox() {
            if (!this.uploadIsAcceptable) {
                this.boundaryBox.stroke(this.boundColor);
            }
        },
    },
    created() {
        if (this.user_has_returned && this.upload_reference.file.file_path) {
            this.newUrl = false;
            const img = new Image();
            img.onload = () => {
                // Take the max as width and height swap with rotation
                this.uploadPixels = Math.max(img.width, img.height);
                this.$emit("mutate", this.container, { recWidth: this.uploadPixels / PIXELS_PER_MM });
            };
            img.src = this.upload_reference.file.file_path;
            this.$emit("dpi_warning", this.container, 0);
        }
    },
    mounted() {
        // Wait for next tick to ensure correct canvas size
        this.$nextTick(() => {
            this.canvas.container = document.getElementById(this.canvasContainerID);
            this.setCanvSize();
            const cRect = this.canvas.container.getBoundingClientRect();
            Object.assign(this.canvas, {
                w: parseFloat(cRect.width),
                h: parseFloat(cRect.height),
                x: parseFloat(cRect.x),
                y: parseFloat(cRect.y),
            });

            this.layer = new Konva.Layer();
            this.stage = new Konva.Stage({
                container: this.canvasContainerID,
                width: this.canvas.w,
                height: this.canvas.h,
            });
            this.stage.add(this.layer);

            // baseImg refers to the background image - i.e. the image of the garment
            const baseImg = new Konva.Image({
                x: 0,
                y: 0,
                width: this.canvas.w,
                height: this.canvas.h,
                preventDefault: false,
            });
            this.layer.add(baseImg);

            this.konvaBg = new Image();
            this.konvaBg.onload = () => {
                baseImg.image(this.konvaBg);
                this.layer.draw();
            };
            this.$emit("update_previews", this.container);

            // konvaImg refers to the uploaded image
            this.konvaImg = new Konva.Image({
                draggable: true,
                dragBoundFunc: (pos) => {
                    // Maintains upload within bounds of canvas container
                    const r = this.konvaImg.getClientRect();
                    const offX = r.width / 2;
                    const offY = r.height / 2;
                    const x = Math.max(offX, Math.min(this.canvas.w - offX, pos.x));
                    const y = Math.max(offY, Math.min(this.canvas.h - offY, pos.y));
                    return { x, y };
                },
            });

            this.konvaObj = new Image();
            if (this.upload_reference.file.file_path) {
                this.konvaObj.src = this.upload_reference.file.file_path;
            }

            this.konvaObj.onload = () => {
                this.setKonvaImgPosition(this.konvaObj);
            };

            this.boundaryBox = new Konva.Rect({
                fill: "transparent",
                stroke: "transparent",
                strokeWidth: 1,
                preventDefault: false,
            });
            this.layer.add(this.boundaryBox);

            this.setBase();
            this.layer.add(this.konvaImg);
            this.layer.draw();

            this.layer.on("dragmove", () => {
                this.$emit("mutate", this.container, {
                    resize: {
                        x: this.konvaImg.x() / this.canvas.w,
                        y: this.konvaImg.y() / this.canvas.h,
                    }
                });
                this.setUploadRect();
            });

            this.layer.on("dragend", () => {
                this.$emit("update_previews", this.container);
            });

            window.addEventListener("resize", () => {
                setTimeout(() => {
                    // Don't resize if on final step! Maintains the canvas' size for previews
                    if (this.step !== 3) {
                        this.setCanvSize();
                        const cRect = this.canvas.container.getBoundingClientRect();

                        // Resets canvas data
                        Object.assign(this.canvas, {
                            x: parseFloat(cRect.x),
                            y: parseFloat(cRect.y),
                            w: parseFloat(cRect.width),
                            h: parseFloat(cRect.height)
                        });

                        this.setBounds();

                        // Resets Konva canvas, and garment image
                        this.stage.width(this.canvas.w);
                        this.stage.height(this.canvas.h);
                        baseImg.width(this.canvas.w);
                        baseImg.height(this.canvas.h);
                        this.setUploadRect();
                        this.resetKonvaImgPosition();
                    }
                }, 100);
            });
        });
    },
    template: `
        <div
        :id="canvasContainerID"
        class="canvas-container relative z-10"
        ></div>
    `
};

export default design_canvas;