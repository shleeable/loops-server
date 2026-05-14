class LoopsEmbed {
    constructor(root) {
        this.root = root
        this.video = root.querySelector('video')
        this.progressFill = root.querySelector('.progress-fill')
        this.likeBtn = root.querySelector('.action-like')
        this.shortcode = root.dataset.shortcode
        this.muted = root.dataset.muted === '1'
        this.startTime = parseInt(root.dataset.start || '0', 10)
        this.videoUrl = root.dataset.url || ''

        this.wasPausedByVisibility = false
        this.lastTimeUpdate = 0

        this.init()
    }

    init() {
        this.setupVisibilityHandling()
        this.setupVideoEvents()
        this.setupClickToToggle()
        this.setupCustomControls()
        this.setupLikeButton()
        this.setupShareButton()
        this.setupStartOverlay()
        this.setupMessageBridge()
        this.setupErrorHandling()
        this.setupResizeReporting()

        if (this.startTime > 0) {
            this.video.addEventListener(
                'loadedmetadata',
                () => {
                    this.video.currentTime = this.startTime
                },
                { once: true }
            )
        }
    }

    setupVisibilityHandling() {
        if (!('IntersectionObserver' in window)) return

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting && !this.video.paused) {
                        this.video.pause()
                        this.wasPausedByVisibility = true
                    } else if (
                        entry.isIntersecting &&
                        this.wasPausedByVisibility &&
                        !this.root.classList.contains('is-pristine')
                    ) {
                        this.video.play().catch(() => {})
                        this.wasPausedByVisibility = false
                    }
                })
            },
            { threshold: 0.5 }
        )

        observer.observe(this.root)
    }

    setupVideoEvents() {
        const events = ['play', 'pause', 'ended', 'timeupdate', 'volumechange', 'loadedmetadata']
        events.forEach((evt) => {
            this.video.addEventListener(evt, () => {
                if (evt === 'timeupdate') {
                    this.updateProgress()
                    const now = Math.floor(this.video.currentTime)
                    if (now === this.lastTimeUpdate) return
                    this.lastTimeUpdate = now
                }

                this.postMessage(evt, {
                    currentTime: this.video.currentTime,
                    duration: this.video.duration,
                    muted: this.video.muted,
                    volume: this.video.volume
                })
            })
        })
    }

    updateProgress() {
        if (!this.progressFill) return
        const d = this.video.duration
        if (!isFinite(d) || d <= 0) return
        this.progressFill.style.width = (this.video.currentTime / d) * 100 + '%'
    }

    setupClickToToggle() {
        this.video.addEventListener('click', () => {
            if (this.video.paused) {
                this.video.play().catch(() => {})
            } else {
                this.video.pause()
            }
        })
    }

    setupLikeButton() {
        if (!this.likeBtn) return
        this.likeBtn.addEventListener('click', () => {
            const liked = this.likeBtn.classList.toggle('is-liked')
            this.postMessage('like-tapped', { liked })
        })
    }

    setupShareButton() {
        const btn = this.root.querySelector('.action-share')
        if (!btn || !this.videoUrl) return
        btn.addEventListener('click', async () => {
            const data = { title: 'Loops', url: this.videoUrl }
            if (navigator.share) {
                try {
                    await navigator.share(data)
                } catch (_) {}
            } else if (navigator.clipboard) {
                try {
                    await navigator.clipboard.writeText(this.videoUrl)
                } catch (_) {}
            }
            this.postMessage('share-tapped', { url: this.videoUrl })
        })
    }

    setupCustomControls() {
        const playBtn = this.root.querySelector('.control-play')
        const muteBtn = this.root.querySelector('.control-mute')
        const fsBtn = this.root.querySelector('.control-fullscreen')

        if (!playBtn) return

        const sync = () => {
            this.root.classList.toggle('is-playing', !this.video.paused)
            this.root.classList.toggle('is-muted', this.video.muted)
            playBtn.setAttribute('aria-label', this.video.paused ? 'Play' : 'Pause')
            muteBtn.setAttribute('aria-label', this.video.muted ? 'Unmute' : 'Mute')
        }
        sync()

        playBtn.addEventListener('click', (e) => {
            e.stopPropagation()
            if (this.video.paused) {
                this.video.play().catch(() => {})
            } else {
                this.video.pause()
            }
        })

        muteBtn.addEventListener('click', (e) => {
            e.stopPropagation()
            this.video.muted = !this.video.muted
        })

        if (fsBtn) {
            fsBtn.addEventListener('click', async (e) => {
                e.stopPropagation()
                try {
                    if (document.fullscreenElement) {
                        await document.exitFullscreen()
                    } else if (this.root.requestFullscreen) {
                        await this.root.requestFullscreen()
                    } else if (this.video.webkitEnterFullscreen) {
                        this.video.webkitEnterFullscreen()
                    }
                    fsBtn.setAttribute(
                        'aria-label',
                        document.fullscreenElement ? 'Exit fullscreen' : 'Enter fullscreen'
                    )
                } catch (_) {}
            })

            document.addEventListener('fullscreenchange', () => {
                const isFs = document.fullscreenElement === this.root
                this.root.classList.toggle('is-fullscreen', isFs)
                fsBtn.setAttribute('aria-label', isFs ? 'Exit fullscreen' : 'Enter fullscreen')
            })
        }

        ;['play', 'pause', 'volumechange'].forEach((evt) => {
            this.video.addEventListener(evt, sync)
        })
    }

    setupMessageBridge() {
        window.addEventListener('message', (event) => {
            const { data } = event
            if (!data || data.context !== 'loops-embed') return

            switch (data.action) {
                case 'play':
                    this.video.play().catch(() => {})
                    break
                case 'pause':
                    this.video.pause()
                    break
                case 'mute':
                    this.video.muted = true
                    break
                case 'unmute':
                    this.video.muted = false
                    break
                case 'seek':
                    if (typeof data.time === 'number' && isFinite(this.video.duration)) {
                        this.video.currentTime = Math.max(
                            0,
                            Math.min(data.time, this.video.duration)
                        )
                    }
                    break
                case 'getState':
                    this.postMessage('state', {
                        currentTime: this.video.currentTime,
                        duration: this.video.duration,
                        paused: this.video.paused,
                        muted: this.video.muted
                    })
                    break
            }
        })
    }

    setupStartOverlay() {
        const overlay = this.root.querySelector('.start-overlay')
        if (!overlay) return

        const start = (e) => {
            if (e) e.stopPropagation()
            this.video.muted = false
            this.video.play().catch(() => {})
            this.root.classList.remove('is-pristine')
            this.postMessage('user-start', {})
        }

        overlay.addEventListener('click', start)
    }

    setupErrorHandling() {
        this.video.addEventListener('error', () => {
            const error = this.video.error
            let message = 'Video could not be played'

            if (error) {
                switch (error.code) {
                    case error.MEDIA_ERR_NETWORK:
                        message = 'Network error — check your connection'
                        break
                    case error.MEDIA_ERR_DECODE:
                        message = 'Video could not be decoded'
                        break
                    case error.MEDIA_ERR_SRC_NOT_SUPPORTED:
                        message = 'Video format not supported'
                        break
                }
            }

            this.showError(message)
        })

        let stallTimer = null
        this.video.addEventListener('waiting', () => {
            clearTimeout(stallTimer)
            stallTimer = setTimeout(() => {
                if (this.video.readyState < 3 && !this.video.paused) {
                    const currentTime = this.video.currentTime
                    this.video.load()
                    this.video.currentTime = currentTime
                    this.video.play().catch(() => {})
                }
            }, 10000)
        })

        this.video.addEventListener('playing', () => clearTimeout(stallTimer))
    }

    setupResizeReporting() {
        const report = () => {
            const card = this.root.closest('.embed-card') || this.root
            this.postMessage('resize', {
                width: card.offsetWidth,
                height: card.offsetHeight
            })
        }

        if ('ResizeObserver' in window) {
            const ro = new ResizeObserver(report)
            ro.observe(this.root.closest('.embed-card') || this.root)
        } else {
            window.addEventListener('resize', report)
            window.addEventListener('load', report)
        }
    }

    showError(message) {
        const existing = this.root.querySelector('.embed-error')
        if (existing) return

        const el = document.createElement('div')
        el.className = 'embed-error'
        el.textContent = message
        this.root.appendChild(el)
        this.postMessage('error', { message })
    }

    postMessage(event, payload = {}) {
        if (window.parent === window) return

        window.parent.postMessage(
            {
                context: 'loops-embed',
                event,
                shortcode: this.shortcode,
                ...payload
            },
            '*'
        )
    }
}

function syncCaptionClamp() {
    document.querySelectorAll('.meta-caption-wrap').forEach((wrap) => {
        const caption = wrap.querySelector('.meta-caption')
        const more = wrap.querySelector('.meta-caption-more')
        if (!caption || !more) return

        const overflowing = caption.scrollHeight > caption.clientHeight + 1
        more.hidden = !overflowing
    })
}

function setupCaptionClamp() {
    syncCaptionClamp()

    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(syncCaptionClamp).catch(() => {})
    }

    window.addEventListener('resize', syncCaptionClamp, { passive: true })

    if ('ResizeObserver' in window) {
        const ro = new ResizeObserver(syncCaptionClamp)
        document.querySelectorAll('.meta-caption').forEach((el) => ro.observe(el))
    }
}

function init() {
    document.querySelectorAll('.embed-player').forEach((root) => {
        new LoopsEmbed(root)
    })

    setupCaptionClamp()
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init)
} else {
    init()
}
