<style>
    /* UGHI admin navigation: calm, high-contrast and easy to scan. */
    .fi-sidebar {
        background: linear-gradient(180deg, #06365f 0%, #042a4b 100%) !important;
        border-inline-end: 1px solid rgba(255, 255, 255, .08) !important;
        box-shadow: 12px 0 35px rgba(6, 46, 43, .12);
    }

    .fi-sidebar-header {
        min-height: 5.5rem;
        padding: 1.25rem 1.15rem !important;
        background: rgba(255, 255, 255, .045) !important;
        border-bottom: 1px solid rgba(255, 255, 255, .1) !important;
    }

    .fi-sidebar-header .fi-logo {
        filter: brightness(0) invert(1);
        opacity: .96;
    }

    .fi-sidebar-nav {
        padding: 1.15rem .8rem !important;
    }

    .fi-sidebar-group {
        margin-block: .25rem 1.15rem;
    }

    .fi-sidebar-group-btn {
        min-height: 2rem;
        padding: 0 .7rem !important;
        color: rgba(255, 255, 255, .52) !important;
    }

    .fi-sidebar-group-label {
        font-size: .66rem !important;
        font-weight: 700 !important;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .58) !important;
    }

    .fi-sidebar-group-btn > .fi-icon,
    .fi-sidebar-group-collapse-btn .fi-icon {
        color: rgba(255, 255, 255, .55) !important;
    }

    .fi-sidebar-group-collapse-btn {
        color: rgba(255, 255, 255, .45) !important;
    }

    .fi-sidebar-item {
        margin-block: .2rem;
    }

    .fi-sidebar-item-btn {
        min-height: 2.75rem;
        gap: .75rem !important;
        padding: .65rem .75rem !important;
        border: 1px solid transparent;
        border-radius: .8rem !important;
        color: rgba(255, 255, 255, .78) !important;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease, transform .18s ease;
    }

    .fi-sidebar-item-btn:hover {
        background: rgba(255, 255, 255, .09) !important;
        border-color: rgba(255, 255, 255, .1);
        color: #fff !important;
        transform: translateX(2px);
    }

    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
        background: #0a8b77 !important;
        border-color: rgba(255, 255, 255, .16);
        color: #fff !important;
        box-shadow: 0 8px 18px rgba(0, 0, 0, .14);
    }

    .fi-sidebar-item-icon {
        color: rgba(255, 255, 255, .62) !important;
    }

    .fi-sidebar-item-btn > .fi-icon,
    .fi-sidebar-item-btn > .fi-sidebar-item-icon,
    .fi-sidebar-item-btn > svg {
        color: rgba(255, 255, 255, .68) !important;
        stroke: currentColor;
    }

    .fi-sidebar-item.fi-active .fi-sidebar-item-icon,
    .fi-sidebar-item-btn:hover .fi-sidebar-item-icon {
        color: #fff !important;
    }

    .fi-sidebar-item-label {
        font-size: .84rem !important;
        font-weight: 550 !important;
        color: rgba(255, 255, 255, .82) !important;
    }

    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn > .fi-sidebar-item-label,
    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn > .fi-icon,
    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn > .fi-sidebar-item-icon,
    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn > svg,
    .fi-sidebar-item-btn:hover > .fi-sidebar-item-label {
        color: #fff !important;
    }

    .fi-sidebar-sub-group-items {
        margin: .25rem 0 .35rem 1.25rem !important;
        border-inline-start-color: rgba(255, 255, 255, .16) !important;
    }

    .fi-sidebar-footer {
        margin: .75rem .8rem 1rem;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .1) !important;
        border-radius: 1rem;
        background: rgba(0, 0, 0, .13) !important;
    }

    .fi-topbar {
        min-height: 4.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, .12) !important;
        background: linear-gradient(105deg, #06365f 0%, #075a9f 58%, #0870bd 100%) !important;
        box-shadow: 0 8px 20px rgba(6, 46, 43, .12) !important;
        color: #fff !important;
    }

    .fi-topbar .fi-logo {
        filter: brightness(0) invert(1);
        opacity: .96;
    }

    .fi-topbar > .fi-icon-btn .fi-icon,
    .fi-topbar .fi-topbar-item-btn > .fi-icon,
    .fi-topbar .fi-topbar-item-icon,
    .fi-topbar .fi-topbar-group-toggle-icon,
    .fi-topbar .fi-topbar-item-label,
    .fi-topbar .fi-user-menu-trigger,
    .fi-topbar .fi-topbar-item-btn {
        color: rgba(255, 255, 255, .88) !important;
    }

    .fi-topbar .fi-topbar-item-btn:hover,
    .fi-topbar .fi-topbar-item-btn:focus-visible,
    .fi-topbar .fi-icon-btn:hover,
    .fi-topbar .fi-icon-btn:focus-visible {
        background: rgba(255, 255, 255, .12) !important;
        color: #fff !important;
    }

    .fi-topbar .fi-topbar-item.fi-active .fi-topbar-item-btn,
    .fi-topbar .fi-topbar-item.fi-active .fi-topbar-item-label {
        color: #fff !important;
    }

    .fi-topbar .fi-global-search-field .fi-input-wrp {
        border-color: rgba(255, 255, 255, .2) !important;
        background: rgba(255, 255, 255, .12) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08) !important;
    }

    .fi-topbar .fi-global-search-field .fi-input,
    .fi-topbar .fi-global-search-field .fi-input::placeholder {
        color: rgba(255, 255, 255, .9) !important;
    }

    .fi-topbar .fi-global-search-field .fi-input::placeholder {
        color: rgba(255, 255, 255, .58) !important;
    }

    /* Keep Filament-generated action and account menus on the shared dropdown style. */
    .fi-dropdown-panel {
        overflow: hidden;
        border: 1px solid #e5ecea !important;
        border-radius: .75rem !important;
        background: #fff !important;
        box-shadow: 0 12px 28px rgb(18 59 45 / 14%) !important;
    }

    .fi-dropdown-panel .fi-dropdown-list-item-label { color: #374151 !important; }
    .fi-dropdown-panel .fi-dropdown-list-item:hover { background: #f6f8fa !important; }

    :is(.dark, [data-theme="dark"]) .fi-dropdown-panel {
        border-color: #34413e !important;
        background: #17211f !important;
    }

    :is(.dark, [data-theme="dark"]) .fi-dropdown-panel .fi-dropdown-list-item-label { color: #e5edf2 !important; }

    /* Keep every Filament calendar picker consistent with the shared date input. */
    .fi-fo-date-time-picker .fi-input-wrp {
        min-height: 2.75rem;
        border-radius: .75rem !important;
        border-color: #e5ecea !important;
        background: #fff !important;
        box-shadow: 0 1px 2px rgb(18 59 45 / 5%) !important;
    }

    .fi-fo-date-time-picker .fi-fo-date-time-picker-trigger { min-height: 2.75rem; }
    .fi-fo-date-time-picker .fi-fo-date-time-picker-panel { border-radius: .875rem !important; }

    :is(.dark, [data-theme="dark"]) .fi-fo-date-time-picker .fi-input-wrp {
        border-color: #34413e !important;
        background: #17211f !important;
    }
    :is(.dark, [data-theme="dark"]) .fi-dropdown-panel .fi-dropdown-list-item:hover { background: #202c29 !important; }

    /* Theme controls live inside the shared dropdown surface. */
    .fi-dropdown-panel .fi-theme-switcher-btn {
        min-width: 2.5rem;
        color: #65727e !important;
    }

    .fi-dropdown-panel .fi-theme-switcher-btn .fi-icon { color: currentColor !important; }
    .fi-dropdown-panel .fi-theme-switcher-btn.fi-active { color: #0870bd !important; background: #e8f2fa !important; }
    .fi-dropdown-panel .fi-dropdown-list-item-label { color: #374151 !important; }

    :is(.dark, [data-theme="dark"]) .fi-dropdown-panel .fi-theme-switcher-btn { color: #b7c3cb !important; }
    :is(.dark, [data-theme="dark"]) .fi-dropdown-panel .fi-theme-switcher-btn .fi-icon { color: currentColor !important; }
    :is(.dark, [data-theme="dark"]) .fi-dropdown-panel .fi-theme-switcher-btn.fi-active { color: #80dfc9 !important; background: #1d3734 !important; }
    :is(.dark, [data-theme="dark"]) .fi-dropdown-panel .fi-dropdown-list-item-label { color: #e5edf2 !important; }

    .ughi-dashboard-shell {
        position: relative;
        margin: -1rem -1rem -2rem;
        padding: 1rem 1rem 3rem;
        background: #f4f8ff;
        overflow: hidden;
        animation: ughi-dashboard-reveal .55s ease-out both;
    }

    .ughi-dashboard-hero {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        width: 100%;
        max-width: none;
        margin: 0 auto 1.5rem;
        padding: 2rem 2.25rem 1.75rem;
        border-radius: 1.5rem;
        background: linear-gradient(115deg, #f8fbff 0%, #edf5ff 50%, #eefcf9 100%);
        box-shadow: 0 18px 45px rgba(65, 113, 180, .08);
        animation: ughi-fade-up .65s cubic-bezier(.22, 1, .36, 1) both;
    }

    .ughi-dashboard-hero-orb {
        position: absolute;
        z-index: -1;
        width: 25rem;
        height: 12rem;
        border-radius: 50%;
        filter: blur(2px);
        opacity: .56;
        transform: rotate(25deg);
    }

    .ughi-dashboard-hero-orb-one {
        top: -8rem;
        right: 8%;
        background: #dfeaff;
        animation: ughi-orb-drift-one 14s ease-in-out infinite alternate;
    }

    .ughi-dashboard-hero-orb-two {
        top: -7rem;
        right: -8rem;
        background: #dcf8f2;
        animation: ughi-orb-drift-two 17s ease-in-out infinite alternate;
    }

    .ughi-dashboard-kicker {
        margin: 0 0 .65rem;
        color: #3475d1;
        font-size: .7rem;
        font-weight: 800;
        letter-spacing: .16em;
    }

    .ughi-dashboard-hero h1 {
        margin: 0;
        color: #142044;
        font-size: clamp(2.25rem, 4vw, 3.35rem);
        line-height: 1;
        letter-spacing: -.055em;
        font-weight: 800;
    }

    .ughi-dashboard-hero p:not(.ughi-dashboard-kicker) {
        margin: .8rem 0 .15rem;
        color: #172149;
        font-size: 1.35rem;
        font-weight: 700;
    }

    .ughi-dashboard-hero > span {
        display: block;
        color: #71809f;
        font-size: .98rem;
    }

    .ughi-dashboard-widgets {
        position: relative;
        width: 100%;
        max-width: none;
        margin: 0 auto;
    }

    .ughi-dashboard-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.1rem;
    }

    .ughi-dashboard-stat-grid-organization { grid-template-columns: repeat(3, minmax(0, 1fr)); }

    .ughi-people-table .fi-ta-ctn {
        overflow: hidden;
        border: 1px solid #e5ecea;
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 24px rgba(23, 33, 43, .045);
    }

    .ughi-people-table .fi-ta-header-toolbar { padding: 1rem 1.25rem; }
    .ughi-people-table .fi-ta-content { background: #fff; }
    .ughi-people-table .fi-ta-table thead { background: #f4f8f7; }
    .ughi-people-table .fi-ta-table thead th { color: #65727e; font-size: .72rem; font-weight: 700; letter-spacing: .055em; text-transform: uppercase; }
    .ughi-people-table .fi-ta-table tbody tr { transition: background-color .16s ease; }
    .ughi-people-table .fi-ta-table tbody tr:hover { background: #f6f8fa; }
    .ughi-people-table .fi-ta-record-actions { justify-content: flex-end; }

    .dark .ughi-people-table .fi-ta-ctn,
    .dark .ughi-people-table .fi-ta-content { border-color: #273641; background: #111b21; }
    .dark .ughi-people-table .fi-ta-table thead { background: #17252c; }
    .dark .ughi-people-table .fi-ta-table tbody tr:hover { background: #16252b; }

    .ughi-dashboard-stat-grid-department { grid-template-columns: repeat(5, minmax(0, 1fr)); }

    .ughi-dashboard-section-title {
        margin: 0 0 1rem;
        color: #142044;
        font-size: 1.05rem;
        font-weight: 750;
        letter-spacing: -.02em;
    }

    .ughi-dashboard-donations { margin-top: 2rem; }
    .ughi-dashboard-section-heading { display: flex; align-items: end; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
    .ughi-dashboard-section-heading .ughi-dashboard-section-title { margin-bottom: .25rem; }
    .ughi-dashboard-section-heading p { margin: 0; color: #8490aa; font-size: .82rem; }
    .ughi-dashboard-section-heading > a { display: inline-flex; align-items: center; gap: .45rem; color: #0870bd; font-size: .82rem; font-weight: 700; white-space: nowrap; }
    .ughi-dashboard-stat-grid-donations { grid-template-columns: repeat(2, minmax(0, 1fr)); }

    .ughi-stat-card {
        position: relative;
        isolation: isolate;
        display: flex;
        min-height: 10rem;
        gap: 1rem;
        overflow: hidden;
        padding: 1.35rem;
        border: 1px solid rgba(141, 171, 213, .18);
        border-radius: 1.15rem;
        background: rgba(255, 255, 255, .92);
        box-shadow: 0 10px 28px rgba(75, 114, 170, .09);
        animation: ughi-fade-up .7s cubic-bezier(.22, 1, .36, 1) both;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }

    .ughi-stat-card::before {
        position: absolute;
        z-index: 0;
        inset: 0;
        content: '';
        pointer-events: none;
        opacity: 0;
        background: radial-gradient(circle 150px at var(--ughi-pointer-x, 50%) var(--ughi-pointer-y, 50%), rgba(61, 139, 242, .2), transparent 72%);
        transition: opacity .24s ease;
    }

    .ughi-stat-card-blue::before { background: radial-gradient(circle 150px at var(--ughi-pointer-x, 50%) var(--ughi-pointer-y, 50%), rgba(61, 139, 242, .2), transparent 72%); }
    .ughi-stat-card-violet::before { background: radial-gradient(circle 150px at var(--ughi-pointer-x, 50%) var(--ughi-pointer-y, 50%), rgba(130, 85, 233, .18), transparent 72%); }
    .ughi-stat-card-mint::before { background: radial-gradient(circle 150px at var(--ughi-pointer-x, 50%) var(--ughi-pointer-y, 50%), rgba(7, 140, 112, .18), transparent 72%); }
    .ughi-stat-card-amber::before { background: radial-gradient(circle 150px at var(--ughi-pointer-x, 50%) var(--ughi-pointer-y, 50%), rgba(221, 121, 27, .2), transparent 72%); }

    .ughi-stat-card:hover::before { opacity: 1; }
    .ughi-stat-card > *:not(.ughi-stat-card-glow) { position: relative; z-index: 1; }

    .ughi-stat-card:nth-child(1) { animation-delay: .08s; }
    .ughi-stat-card:nth-child(2) { animation-delay: .14s; }
    .ughi-stat-card:nth-child(3) { animation-delay: .20s; }
    .ughi-stat-card:nth-child(4) { animation-delay: .26s; }
    .ughi-stat-card:nth-child(5) { animation-delay: .32s; }
    .ughi-stat-card:nth-child(6) { animation-delay: .38s; }
    .ughi-stat-card:nth-child(7) { animation-delay: .44s; }

    .ughi-stat-card:hover {
        border-color: rgba(61, 139, 242, .25);
        box-shadow: 0 16px 34px rgba(75, 114, 170, .15);
    }

    .ughi-stat-card-glow {
        position: absolute;
        z-index: -1;
        right: -2rem;
        bottom: -4rem;
        width: 11rem;
        height: 8rem;
        border-radius: 50%;
        opacity: .82;
        transform: rotate(-24deg);
        transition: transform .35s ease, opacity .35s ease;
    }

    .ughi-stat-card:hover .ughi-stat-card-glow {
        opacity: 1;
        transform: rotate(-18deg) scale(1.12);
    }

    .ughi-stat-card-blue .ughi-stat-card-glow { background: #e3f0ff; }
    .ughi-stat-card-violet .ughi-stat-card-glow { background: #f0eaff; }
    .ughi-stat-card-mint .ughi-stat-card-glow { background: #def8ef; }
    .ughi-stat-card-amber .ughi-stat-card-glow { background: #fff0dd; }

    .ughi-stat-icon {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        width: 3.2rem;
        height: 3.2rem;
        border-radius: 1rem;
        transition: transform .25s ease;
    }

    .ughi-stat-card:hover .ughi-stat-icon { transform: scale(1.08) rotate(-3deg); }

    .ughi-stat-icon-blue { color: #287ee6; background: #e5f1ff; }
    .ughi-stat-icon-violet { color: #8255e9; background: #f0eaff; }
    .ughi-stat-icon-mint { color: #078c70; background: #dff8ef; }
    .ughi-stat-icon-amber { color: #dd791b; background: #fff0dd; }

    .ughi-stat-copy { min-width: 0; padding-top: .05rem; }
    .ughi-stat-label { margin: 0; color: #64718f; font-size: .83rem; font-weight: 600; }
    .ughi-stat-value { margin: .25rem 0 .15rem; color: #142044; font-size: 2rem; line-height: 1; font-weight: 800; letter-spacing: -.045em; }
    .ughi-stat-description { max-width: 13rem; margin: 0; color: #8490aa; font-size: .77rem; line-height: 1.45; }

    .ughi-dashboard-widgets .fi-wi-chart {
        overflow: hidden;
        border: 1px solid rgba(141, 171, 213, .18) !important;
        border-radius: 1.15rem !important;
        background: rgba(255, 255, 255, .94) !important;
        box-shadow: 0 10px 28px rgba(75, 114, 170, .08) !important;
        animation: ughi-fade-up .7s .42s cubic-bezier(.22, 1, .36, 1) both;
        transition: transform .22s ease, box-shadow .22s ease;
    }

    .ughi-dashboard-widgets .fi-wi-chart:hover {
        box-shadow: 0 16px 34px rgba(75, 114, 170, .13) !important;
    }

    .ughi-dashboard-widgets .fi-wi-chart .fi-section-header {
        min-height: 4.7rem;
        padding: 1.25rem 1.35rem !important;
        border-bottom-color: #e8eef8 !important;
    }

    .ughi-dashboard-widgets .fi-wi-chart .fi-section-header-heading {
        color: #172149 !important;
        font-size: 1.05rem !important;
        font-weight: 750 !important;
    }

    .ughi-dashboard-widgets .fi-wi-chart .fi-section-header-description {
        color: #8490aa !important;
    }

    .ughi-dashboard-widgets .fi-wi-chart-frame {
        min-height: 18rem;
        padding: 1.35rem !important;
    }

    .ughi-dashboard-widgets .fi-empty-state-icon {
        color: #76aaf5 !important;
        background: #edf5ff !important;
    }

    .ughi-dashboard-widgets .fi-empty-state-heading {
        color: #172149 !important;
        font-weight: 750 !important;
    }

    .ughi-dashboard-widgets .fi-empty-state-description {
        color: #8490aa !important;
    }

    .ughi-dashboard-widgets .fi-empty-state-actions .fi-btn {
        border-radius: 999px !important;
        background: #3d8bf2 !important;
        box-shadow: 0 8px 18px rgba(61, 139, 242, .22);
    }

    /* Keep custom admin dashboards in sync with Filament's light/dark theme. */
    :is(.dark, [data-theme="dark"]) .ughi-dashboard-shell {
        color: #e5edf2;
        background: #0b1418;
    }

    :is(.dark, [data-theme="dark"]) .ughi-dashboard-hero {
        border: 1px solid #263b40;
        background: linear-gradient(115deg, #14252c 0%, #162733 52%, #12352f 100%);
        box-shadow: 0 18px 45px rgba(0, 0, 0, .2);
    }

    :is(.dark, [data-theme="dark"]) .ughi-dashboard-hero-orb-one { background: #244163; opacity: .28; }
    :is(.dark, [data-theme="dark"]) .ughi-dashboard-hero-orb-two { background: #145347; opacity: .34; }
    :is(.dark, [data-theme="dark"]) .ughi-dashboard-kicker { color: #83b6ff; }
    :is(.dark, [data-theme="dark"]) .ughi-dashboard-hero h1,
    :is(.dark, [data-theme="dark"]) .ughi-dashboard-hero p:not(.ughi-dashboard-kicker) { color: #f3f7fa; }
    :is(.dark, [data-theme="dark"]) .ughi-dashboard-hero > span { color: #b0bec8; }

    :is(.dark, [data-theme="dark"]) .ughi-dashboard-section-title { color: #e8f0f4; }
    :is(.dark, [data-theme="dark"]) .ughi-dashboard-section-heading p { color: #aab8c2; }
    :is(.dark, [data-theme="dark"]) .ughi-dashboard-section-heading > a { color: #69d4be; }

    :is(.dark, [data-theme="dark"]) .ughi-stat-card {
        border-color: #293b42;
        background: #142127;
        box-shadow: 0 10px 28px rgba(0, 0, 0, .2);
    }

    :is(.dark, [data-theme="dark"]) .ughi-stat-card:hover {
        border-color: #3c6970;
        box-shadow: 0 16px 34px rgba(0, 0, 0, .3);
    }

    :is(.dark, [data-theme="dark"]) .ughi-stat-card-blue .ughi-stat-card-glow { background: #1b3850; opacity: .68; }
    :is(.dark, [data-theme="dark"]) .ughi-stat-card-violet .ughi-stat-card-glow { background: #30264b; opacity: .68; }
    :is(.dark, [data-theme="dark"]) .ughi-stat-card-mint .ughi-stat-card-glow { background: #164238; opacity: .7; }
    :is(.dark, [data-theme="dark"]) .ughi-stat-card-amber .ughi-stat-card-glow { background: #49351f; opacity: .7; }
    :is(.dark, [data-theme="dark"]) .ughi-stat-icon-blue { color: #8fc2ff; background: #203b53; }
    :is(.dark, [data-theme="dark"]) .ughi-stat-icon-violet { color: #c0a8ff; background: #332a4c; }
    :is(.dark, [data-theme="dark"]) .ughi-stat-icon-mint { color: #75dfc1; background: #1d443a; }
    :is(.dark, [data-theme="dark"]) .ughi-stat-icon-amber { color: #ffc27c; background: #4b3825; }
    :is(.dark, [data-theme="dark"]) .ughi-stat-label { color: #c0ccd3; }
    :is(.dark, [data-theme="dark"]) .ughi-stat-value { color: #f3f7fa; }
    :is(.dark, [data-theme="dark"]) .ughi-stat-description { color: #aab8c2; }

    :is(.dark, [data-theme="dark"]) .ughi-dashboard-widgets .fi-wi-chart,
    :is(.dark, [data-theme="dark"]) .ughi-dashboard-widgets .fi-wi-chart .fi-section {
        border-color: #293b42 !important;
        background: #142127 !important;
        color: #e5edf2 !important;
        box-shadow: 0 10px 28px rgba(0, 0, 0, .2) !important;
    }

    :is(.dark, [data-theme="dark"]) .ughi-dashboard-widgets .fi-wi-chart .fi-section-header {
        border-bottom-color: #293b42 !important;
    }

    :is(.dark, [data-theme="dark"]) .ughi-dashboard-widgets .fi-wi-chart .fi-section-header-heading,
    :is(.dark, [data-theme="dark"]) .ughi-dashboard-widgets .fi-empty-state-heading { color: #edf4f7 !important; }

    :is(.dark, [data-theme="dark"]) .ughi-dashboard-widgets .fi-wi-chart .fi-section-header-description,
    :is(.dark, [data-theme="dark"]) .ughi-dashboard-widgets .fi-empty-state-description { color: #afbdc6 !important; }

    :is(.dark, [data-theme="dark"]) .ughi-dashboard-widgets .fi-empty-state-icon {
        color: #9bc4ff !important;
        background: #20374f !important;
    }

    :is(.dark, [data-theme="dark"]) .fi-page .fi-sc-tabs {
        border-color: #293b42;
        background: #142127;
        box-shadow: 0 12px 30px rgba(0, 0, 0, .2);
    }

    :is(.dark, [data-theme="dark"]) .fi-page .fi-sc-tabs > .fi-tabs {
        border-bottom-color: #293b42;
        background: linear-gradient(100deg, #17272e 0%, #172c29 100%);
    }

    :is(.dark, [data-theme="dark"]) .fi-page .fi-sc-tabs .fi-tabs-item { color: #bac7cf; }
    :is(.dark, [data-theme="dark"]) .fi-page .fi-sc-tabs .fi-tabs-item.fi-active { color: #83e0ca; background: rgba(8, 127, 107, .2); }

    @keyframes ughi-dashboard-reveal {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes ughi-fade-up {
        from { opacity: 0; transform: translateY(14px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes ughi-orb-drift-one {
        from { transform: translate3d(0, 0, 0) rotate(25deg); }
        to { transform: translate3d(-24px, 14px, 0) rotate(30deg); }
    }

    @keyframes ughi-orb-drift-two {
        from { transform: translate3d(0, 0, 0) rotate(-18deg); }
        to { transform: translate3d(22px, 16px, 0) rotate(-10deg); }
    }

    @media (prefers-reduced-motion: reduce) {
        .ughi-dashboard-shell,
        .ughi-dashboard-hero,
        .ughi-dashboard-hero-orb-one,
        .ughi-dashboard-hero-orb-two,
        .ughi-stat-card,
        .ughi-dashboard-widgets .fi-wi-chart {
            animation: none !important;
        }

        .ughi-stat-card,
        .ughi-stat-card::before,
        .ughi-stat-card-glow,
        .ughi-stat-icon,
        .ughi-dashboard-widgets .fi-wi-chart {
            transition: none !important;
        }

        .ughi-stat-card::before { display: none; }
    }

    /* Reusable vertical schema tabs: icon-led navigation beside a clear content panel. */
    .fi-page .fi-sc-tabs {
        overflow: visible;
        gap: 1rem;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .fi-page .fi-sc-tabs.fi-vertical > .fi-tabs {
        flex: 0 0 15rem;
        width: 15rem;
        max-width: 17rem;
        align-self: flex-start;
        gap: .35rem;
        overflow: visible;
        padding: .45rem;
        border: 0;
        border-radius: 1rem;
        background: transparent;
        box-shadow: none;
    }

    .fi-page .fi-sc-tabs.fi-vertical .fi-tabs-item {
        width: 100%;
        min-height: 2.85rem;
        justify-content: flex-start;
        gap: .65rem;
        padding: .7rem .9rem;
        border-radius: .7rem;
        color: #52616c;
        font-weight: 600;
    }

    .fi-page .fi-sc-tabs.fi-vertical .fi-tabs-item.fi-active {
        color: #fff;
        background: #0870bd;
        box-shadow: 0 3px 8px rgb(8 127 107 / 16%);
    }

    .fi-page .fi-sc-tabs.fi-vertical .fi-tabs-item.fi-active .fi-tabs-item-label,
    .fi-page .fi-sc-tabs.fi-vertical .fi-tabs-item.fi-active > .fi-icon { color: #fff; }

    .fi-page .fi-sc-tabs.fi-vertical .fi-tabs-item:not(.fi-active):hover {
        background: #eef5f3;
        color: #17212b;
    }

    .fi-page .fi-sc-tabs.fi-vertical .fi-sc-tabs-tab.fi-active {
        flex: 1 1 auto;
        min-width: 0;
        margin: 0;
        padding: 1.5rem;
        border: 1px solid #e5ecea;
        border-radius: 1rem;
        background: #f6f8fa;
    }

    .fi-page .fi-sc-tabs-tab .fi-section {
        border: 0;
        box-shadow: none;
    }

    /* Table-filter tabs use the same active brand treatment, while staying in a row. */
    .fi-page .fi-tabs:not(.fi-vertical) .fi-tabs-item {
        min-height: 2.65rem;
        border-radius: .7rem;
        font-weight: 600;
    }

    .fi-page .fi-tabs:not(.fi-vertical) .fi-tabs-item.fi-active {
        color: #fff;
        background: #0870bd;
    }

    .fi-page .fi-tabs:not(.fi-vertical) .fi-tabs-item.fi-active .fi-tabs-item-label,
    .fi-page .fi-tabs:not(.fi-vertical) .fi-tabs-item.fi-active > .fi-icon { color: #fff; }

    :is(.dark, [data-theme="dark"]) .fi-page .fi-sc-tabs.fi-vertical .fi-tabs-item { color: #bac7cf; }
    :is(.dark, [data-theme="dark"]) .fi-page .fi-sc-tabs.fi-vertical .fi-tabs-item:not(.fi-active):hover { background: #202c29; color: #e5edf2; }
    :is(.dark, [data-theme="dark"]) .fi-page .fi-sc-tabs.fi-vertical { border: 0; background: transparent; box-shadow: none; }
    :is(.dark, [data-theme="dark"]) .fi-page .fi-sc-tabs.fi-vertical > .fi-tabs { border: 0; background: transparent; box-shadow: none; }
    :is(.dark, [data-theme="dark"]) .fi-page .fi-sc-tabs.fi-vertical .fi-sc-tabs-tab.fi-active { border-color: #293b42; background: #142127; }

    @media (max-width: 767px) {
        .fi-page .fi-sc-tabs.fi-vertical { flex-direction: column; }
        .fi-page .fi-sc-tabs.fi-vertical > .fi-tabs { flex: 0 0 auto; width: 100%; max-width: none; }
        .fi-page .fi-sc-tabs.fi-vertical .fi-sc-tabs-tab.fi-active { width: 100%; padding: 1rem; }
    }

    /* Empty resource tables fill the available list page instead of collapsing to a short card. */
    .fi-page.fi-resource-list-records-page:has(.fi-ta-empty-state) {
        min-height: calc(100dvh - 5rem);
    }

    .fi-page.fi-resource-list-records-page:has(.fi-ta-empty-state) .fi-page-header-main-ctn {
        display: flex;
        min-height: inherit;
        flex-direction: column;
    }

    .fi-page.fi-resource-list-records-page:has(.fi-ta-empty-state) .fi-page-main,
    .fi-page.fi-resource-list-records-page:has(.fi-ta-empty-state) .fi-page-content {
        display: flex;
        min-height: 0;
        flex: 1 1 auto;
        flex-direction: column;
    }

    .fi-page.fi-resource-list-records-page .fi-ta-ctn:has(.fi-ta-empty-state) {
        min-height: calc(100vh - 8rem);
        min-height: calc(100dvh - 8rem);
        flex: 1 1 auto;
        flex-direction: column;
    }

    .fi-page.fi-resource-list-records-page .fi-ta-main:has(.fi-ta-empty-state),
    .fi-page.fi-resource-list-records-page .fi-ta-content-ctn:has(.fi-ta-empty-state) {
        display: flex;
        min-height: 0;
        flex: 1 1 auto;
        flex-direction: column;
    }

    .fi-page.fi-resource-list-records-page .fi-ta-empty-state {
        display: flex;
        min-height: 16rem;
        flex: 1 1 auto;
        align-items: center;
        justify-content: center;
    }

    @media (max-width: 1100px) {
        .ughi-dashboard-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ughi-dashboard-stat-grid-organization { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 640px) {
        .ughi-dashboard-shell { margin: -.5rem -.5rem -1rem; padding: .5rem .5rem 1.5rem; }
        .ughi-dashboard-hero { padding: 1.5rem; border-radius: 1.15rem; }
        .ughi-dashboard-stat-grid { grid-template-columns: 1fr; }
        .ughi-dashboard-stat-grid-organization { grid-template-columns: 1fr; }
        .ughi-dashboard-stat-grid-donations { grid-template-columns: 1fr; }
        .ughi-dashboard-section-heading { align-items: start; flex-direction: column; }
        .ughi-stat-card { min-height: 8.5rem; }
    }

    @media (max-width: 1024px) {
        .fi-sidebar {
            box-shadow: 8px 0 25px rgba(6, 46, 43, .2);
        }
    }
</style>
