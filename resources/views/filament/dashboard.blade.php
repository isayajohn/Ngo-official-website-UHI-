<x-filament-panels::page>
    <div class="ughi-dashboard-shell">
        <header class="ughi-dashboard-hero">
            <div class="ughi-dashboard-hero-orb ughi-dashboard-hero-orb-one" aria-hidden="true"></div>
            <div class="ughi-dashboard-hero-orb ughi-dashboard-hero-orb-two" aria-hidden="true"></div>
            <p class="ughi-dashboard-kicker">USTAWI GLOBAL HEALTH INITIATIVE</p>
            <h1>Dashboard</h1>
            <p>Welcome to UGHI</p>
            <span>Manage your website using the modules available to your role. Dashboard figures reflect current database records.</span>
        </header>
        <div class="ughi-dashboard-widgets">
            <x-filament-widgets::widgets :widgets="$this->getVisibleWidgets()" :columns="$this->getColumns()" />
        </div>
    </div>
</x-filament-panels::page>

<script>
    (() => {
        if (window.__ughiDashboardPointerWipe) return;
        window.__ughiDashboardPointerWipe = true;

        document.addEventListener('pointermove', (event) => {
            const card = event.target.closest?.('.ughi-stat-card');
            if (!card) return;

            const bounds = card.getBoundingClientRect();
            card.style.setProperty('--ughi-pointer-x', `${event.clientX - bounds.left}px`);
            card.style.setProperty('--ughi-pointer-y', `${event.clientY - bounds.top}px`);
        }, { passive: true });
    })();
</script>
