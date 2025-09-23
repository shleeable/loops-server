import { useRouter } from "vue-router";

export function useUtils() {
    const router = useRouter();

    const normalizeDomain = (input = "") => {
        let d = String(input).trim().toLowerCase();
        d = d.replace(/^https?:\/\//i, "");
        d = d.replace(/^[a-z]+:\/\//i, "");
        d = d.replace(/@.*/, "");
        d = d.replace(/\/.*$/, "");
        d = d.replace(/\?.*$/, "");
        d = d.replace(/#.*$/, "");
        d = d.replace(/:\d+$/, "");
        d = d.replace(/^\.+|\.+$/g, "");
        return d;
    };

    const isValidDomain = (domain) => {
        if (!domain) return false;
        if (domain.length > 253) return false;
        if (domain.includes("..")) return false;

        const labels = domain.split(".");
        if (labels.length < 2) return false;

        const labelRe = /^(?!-)(?:[a-z0-9-]{1,63})(?<!-)$/i;

        for (const label of labels) {
            if (!labelRe.test(label)) return false;
            if (label.includes("_")) return false;
        }

        if (/^\d+$/.test(labels[labels.length - 1])) return false;

        return true;
    };

    const formatNumber = (num) => {
        return new Intl.NumberFormat().format(num);
    };

    const formatCount = (count) => {
        if (!count) return "0";

        const formatter = Intl.NumberFormat("en", {
            notation: "compact",
            maximumFractionDigits: 1,
        });

        return formatter.format(count);
    };

    const formatDate = (date) => {
        return new Date(date).toLocaleDateString("en-US", {
            year: "numeric",
            month: "short",
            day: "numeric",
        });
    };

    const formatDateTime = (date) => {
        return new Date(date).toLocaleDateString("en-US", {
            year: "numeric",
            month: "short",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    };

    const truncateMiddle = (str, maxLen, separator = "...") => {
        if (str.length <= maxLen) {
            return str;
        }

        const sepLen = separator.length;
        const availableLen = maxLen - sepLen;

        if (availableLen <= 0) {
            return str.substring(0, maxLen);
        }

        const startLen = Math.ceil(availableLen / 2);
        const endLen = Math.floor(availableLen / 2);

        const start = str.substring(0, startLen);
        const end = str.substring(str.length - endLen);

        return start + separator + end;
    };

    const textTruncate = (text, maxLength = 50, suffix = "...") => {
        if (!text) {
            return "";
        }

        if (text.length <= maxLength) {
            return text;
        }

        return text.slice(0, maxLength) + suffix;
    };

    const formatTimeAgo = (dateString, style = "narrow", locale = "en") => {
        const now = new Date();

        let date;
        try {
            if (
                dateString.includes("Z") ||
                dateString.includes("+") ||
                dateString.includes("-")
            ) {
                date = new Date(dateString);
            } else {
                const utcString = dateString.includes("T")
                    ? dateString + "Z"
                    : dateString + "T00:00:00Z";
                date = new Date(utcString);
            }

            if (isNaN(date.getTime())) {
                date = new Date(dateString);
            }
        } catch (error) {
            date = new Date(dateString);
        }

        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 30) return "Just now";

        const rtf = new Intl.RelativeTimeFormat(locale, {
            numeric: "always",
            style: style,
        });

        const units = [
            { unit: "year", seconds: 31536000 },
            { unit: "month", seconds: 2628000 },
            { unit: "week", seconds: 604800 },
            { unit: "day", seconds: 86400 },
            { unit: "hour", seconds: 3600 },
            { unit: "minute", seconds: 60 },
        ];

        for (const { unit, seconds } of units) {
            const interval = Math.floor(diffInSeconds / seconds);
            if (interval >= 1) {
                return rtf.format(-interval, unit);
            }
        }

        return "Just now";
    };

    const formatContentDate = (date) => {
        const contentDate = new Date(date);
        const now = new Date();

        const diffTime = now.getTime() - contentDate.getTime();

        if (diffTime < 0) {
            const year = contentDate.getUTCFullYear();
            const month = contentDate.getUTCMonth() + 1;
            const day = contentDate.getUTCDate();
            return `${year}-${month}-${day}`;
        }

        const diffMinutes = Math.floor(diffTime / (1000 * 60));
        const diffHours = Math.floor(diffTime / (1000 * 60 * 60));

        if (diffMinutes < 1) {
            return "Just now";
        }

        if (diffMinutes < 60) {
            return `${diffMinutes} minute${diffMinutes === 1 ? "" : "s"} ago`;
        }

        if (diffHours < 24) {
            return `${diffHours} hour${diffHours === 1 ? "" : "s"} ago`;
        }

        const currentYear = now.getUTCFullYear();
        const contentYear = contentDate.getUTCFullYear();

        if (contentYear === currentYear) {
            const month = contentDate.getUTCMonth() + 1;
            const day = contentDate.getUTCDate();
            return `${month}-${day}`;
        } else {
            const year = contentDate.getUTCFullYear();
            const month = contentDate.getUTCMonth() + 1;
            const day = contentDate.getUTCDate();
            return `${year}-${month}-${day}`;
        }
    };

    const formatDuration = (seconds) => {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return `${minutes}:${remainingSeconds.toString().padStart(2, "0")}`;
    };

    const formatBytes = (kb) => {
        if (kb === 0) return "0 KB";

        const units = ["KB", "MB", "GB", "TB", "PB"];
        const base = 1024;

        let value = kb;
        let unitIndex = 0;

        while (value >= base && unitIndex < units.length - 1) {
            value /= base;
            unitIndex++;
        }

        const rounded = Math.round(value * 10) / 10;
        const formatted =
            rounded % 1 === 0 ? rounded.toString() : rounded.toFixed(1);

        return `${formatted} ${units[unitIndex]}`;
    };

    const goBack = () => {
        if (window.history.length > 1) {
            router.go(-1);
        } else {
            router.push("/");
        }
    };

    return {
        normalizeDomain,
        isValidDomain,
        formatNumber,
        formatCount,
        formatDate,
        formatContentDate,
        formatDateTime,
        truncateMiddle,
        formatTimeAgo,
        formatDuration,
        formatBytes,
        textTruncate,
        goBack,
    };
}
