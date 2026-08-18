const SAFE_PROTOCOLS = new Set(['http:', 'https:'])

export function useSafeUrl() {
    const parseSafeUrl = (value) => {
        if (typeof value !== 'string' || !value.trim()) return null

        let url
        try {
            url = new URL(value, window.location.origin)
        } catch {
            return null
        }

        return SAFE_PROTOCOLS.has(url.protocol) ? url : null
    }

    const isSameOrigin = (url) => !!url && url.origin === window.location.origin

    const safeExternalUrl = (value, fallback = null) => {
        const url = parseSafeUrl(value)
        return url ? url.href : fallback
    }

    const safeInternalPath = (value, fallback = '/') => {
        const url = parseSafeUrl(value)
        if (!isSameOrigin(url)) return fallback
        return `${url.pathname}${url.search}${url.hash}`
    }

    return { parseSafeUrl, isSameOrigin, safeExternalUrl, safeInternalPath }
}
