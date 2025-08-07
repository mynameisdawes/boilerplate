let _utilities = {
    ucfirst(value) {
        if (typeof(value) !== "string") { return value; }
        return value.charAt(0).toUpperCase() + value.slice(1);
    },
    timestamp() {
        const now = new Date();
        const year = `${now.getFullYear()}`;
        const month = (now.getMonth() + 1).toString().padStart(2, "0");
        const day = now.getDate().toString().padStart(2, "0");
        const hour = now.getHours().toString().padStart(2, "0");
        const minute = now.getMinutes().toString().padStart(2, "0");
        const second = now.getSeconds().toString().padStart(2, "0");
        return `${year}-${month}-${day}_${hour}-${minute}-${second}`;
    }
};

export default _utilities;