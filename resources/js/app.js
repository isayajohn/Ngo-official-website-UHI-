import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.data('dropdownMenu', ({ trigger = 'hover', delay = 500 } = {}) => ({
    open: false,
    timer: null,
    clearTimer() {
        if (this.timer) window.clearTimeout(this.timer);
        this.timer = null;
    },
    pointerEnter() {
        if (trigger !== 'hover') return;
        this.clearTimer();
        this.timer = window.setTimeout(() => { this.open = true; }, delay);
    },
    pointerLeave() {
        if (trigger !== 'hover') return;
        this.clearTimer();
        this.timer = window.setTimeout(() => { this.open = false; }, delay);
    },
    toggle() {
        this.clearTimer();
        this.open = !this.open;
    },
    close() {
        this.clearTimer();
        this.open = false;
    },
    closeAndFocus() {
        this.close();
        this.$refs.toggle?.focus();
    },
    destroy() { this.clearTimer(); },
}));
Alpine.data('impactCounter', (target, decimals) => ({
    display: new Intl.NumberFormat(document.documentElement.lang, { maximumFractionDigits: decimals }).format(target),
    observer: null,
    frame: null,
    init() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) return;
        this.observer = new IntersectionObserver(entries => {
            if (!entries.some(entry => entry.isIntersecting)) return;
            this.observer.disconnect();
            const start = performance.now();
            const formatter = new Intl.NumberFormat(document.documentElement.lang, { maximumFractionDigits: decimals });
            const animate = now => {
                const progress = Math.min(1, (now - start) / 350);
                this.display = formatter.format(target * (1 - Math.pow(1 - progress, 3)));
                if (progress < 1) this.frame = requestAnimationFrame(animate);
            };
            this.frame = requestAnimationFrame(animate);
        }, { threshold: 0.25 });
        this.observer.observe(this.$el);
    },
    destroy() { this.observer?.disconnect(); if (this.frame) cancelAnimationFrame(this.frame); },
}));
Alpine.data('heroCarousel', (total) => ({
    active: 0,
    total,
    timer: null,
    touchStartX: 0,
    visibilityHandler: null,
    reducedMotion: false,
    init() {
        this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.visibilityHandler = () => {
            if (document.hidden) this.stop();
            else if (!this.reducedMotion) this.start();
        };
        document.addEventListener('visibilitychange', this.visibilityHandler);
        if (!this.reducedMotion) this.start();
    },
    start() {
        if (document.hidden) return;
        this.stop();
        this.timer = window.setInterval(() => this.next(), 6500);
    },
    stop() {
        if (this.timer) window.clearInterval(this.timer);
        this.timer = null;
    },
    next() {
        this.active = (this.active + 1) % this.total;
    },
    previous() {
        this.active = (this.active - 1 + this.total) % this.total;
    },
    go(index) {
        this.active = index;
        if (!this.reducedMotion) this.start();
    },
    touchStart(event) {
        this.touchStartX = event.changedTouches[0]?.screenX ?? 0;
        this.stop();
    },
    touchEnd(event) {
        const delta = (event.changedTouches[0]?.screenX ?? 0) - this.touchStartX;
        if (Math.abs(delta) > 45) delta < 0 ? this.next() : this.previous();
        if (!this.reducedMotion) this.start();
    },
    destroy() {
        this.stop();
        if (this.visibilityHandler) document.removeEventListener('visibilitychange', this.visibilityHandler);
    },
}));
Alpine.start();
