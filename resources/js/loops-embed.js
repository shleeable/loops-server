;(function () {
    'use strict'

    if (window.__loopsEmbedLoaded) return
    window.__loopsEmbedLoaded = true

    var origin = (function () {
        try {
            var current = document.currentScript
            if (current && current.src) {
                return new URL(current.src).origin
            }
            var scripts = document.getElementsByTagName('script')
            for (var i = scripts.length - 1; i >= 0; i--) {
                var src = scripts[i].src || ''
                if (/\/embed(\.min)?\.js(\?|$)/.test(src)) {
                    return new URL(src).origin
                }
            }
        } catch (_) {}
        return import.meta.env.VITE_APP_URL
    })()

    var SELECTOR = 'blockquote.loops-embed[data-shortcode]'
    var PROCESSED = 'data-loops-processed'
    var DEFAULT_W = 326
    var MIN_W = 325
    var MAX_W = 605
    var INITIAL_H = 680

    function buildSrc(blockquote) {
        var shortcode = blockquote.getAttribute('data-shortcode')
        var params = []

        var theme = blockquote.getAttribute('data-theme')
        if (theme) params.push('theme=' + encodeURIComponent(theme))

        var start = blockquote.getAttribute('data-start')
        if (start) params.push('t=' + encodeURIComponent(start))

        if (blockquote.getAttribute('data-controls') === '0') {
            params.push('controls=0')
        }

        var qs = params.length ? '?' + params.join('&') : ''
        return origin + '/embed/' + encodeURIComponent(shortcode) + qs
    }

    function clampWidth(value) {
        var n = parseInt(value, 10)
        if (isNaN(n)) return DEFAULT_W
        return Math.min(MAX_W, Math.max(MIN_W, n))
    }

    function transform(blockquote) {
        if (blockquote.getAttribute(PROCESSED) === '1') return
        blockquote.setAttribute(PROCESSED, '1')

        var shortcode = blockquote.getAttribute('data-shortcode')
        if (!shortcode) return

        var width = clampWidth(blockquote.getAttribute('data-width') || blockquote.style.maxWidth)

        var wrap = document.createElement('div')
        wrap.className = 'loops-embed-wrap'
        wrap.setAttribute('data-shortcode', shortcode)
        wrap.style.cssText = [
            'position:relative',
            'width:100%',
            'max-width:' + width + 'px',
            'min-width:' + MIN_W + 'px',
            'margin:0 auto',
            'background:transparent'
        ].join(';')

        var iframe = document.createElement('iframe')
        iframe.src = buildSrc(blockquote)
        iframe.setAttribute(
            'allow',
            'autoplay; fullscreen; picture-in-picture; encrypted-media; clipboard-write'
        )
        iframe.setAttribute('allowfullscreen', '')
        iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin')
        iframe.setAttribute('loading', 'lazy')
        iframe.setAttribute('scrolling', 'no')
        iframe.setAttribute('title', 'Loops video')
        iframe.style.cssText = [
            'display:block',
            'width:100%',
            'height:' + INITIAL_H + 'px',
            'border:0',
            'border-radius:12px',
            'background:#000',
            'transition:height .15s ease'
        ].join(';')

        wrap.appendChild(iframe)
        blockquote.parentNode.replaceChild(wrap, blockquote)
    }

    function transformAll(root) {
        var nodes = (root || document).querySelectorAll(SELECTOR)
        for (var i = 0; i < nodes.length; i++) transform(nodes[i])
    }

    window.addEventListener(
        'message',
        function (event) {
            var data = event.data
            if (!data || data.context !== 'loops-embed') return
            if (data.event !== 'resize' || typeof data.height !== 'number') return

            var iframes = document.querySelectorAll('.loops-embed-wrap iframe')
            for (var i = 0; i < iframes.length; i++) {
                if (iframes[i].contentWindow === event.source) {
                    iframes[i].style.height = Math.ceil(data.height) + 'px'
                    break
                }
            }
        },
        false
    )

    if ('MutationObserver' in window) {
        var observer = new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                var added = mutations[i].addedNodes
                for (var j = 0; j < added.length; j++) {
                    var node = added[j]
                    if (node.nodeType !== 1) continue
                    if (node.matches && node.matches(SELECTOR)) {
                        transform(node)
                    } else if (node.querySelectorAll) {
                        transformAll(node)
                    }
                }
            }
        })
        observer.observe(document.documentElement, { childList: true, subtree: true })
    }

    window.loopsEmbed = window.loopsEmbed || {}
    window.loopsEmbed.process = transformAll

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            transformAll()
        })
    } else {
        transformAll()
    }
})()
