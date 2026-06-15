@extends('layouts.app')
@section('title', 'Dashboard')

@section('styles')
<style>
  .dashboard-page {
    --ops-success: color-mix(in srgb, var(--nk-success) 82%, var(--txt));
    --ops-success-soft: color-mix(in srgb, var(--nk-success) 10%, var(--surface));
    --ops-warning: color-mix(in srgb, var(--nk-warning) 86%, var(--txt));
    --ops-warning-soft: color-mix(in srgb, var(--nk-warning) 10%, var(--surface));
    --ops-danger: color-mix(in srgb, var(--nk-danger) 82%, var(--txt));
    --ops-danger-soft: color-mix(in srgb, var(--nk-danger) 10%, var(--surface));
  }

  .ops-shell {
    display: flex;
    flex-direction: column;
    gap: 18px;
  }

  .ops-hero {
    position: relative;
    overflow: hidden;
    border: 1px solid var(--border);
    border-radius: 28px;
    background:
      radial-gradient(circle at top right, rgba(91, 99, 211, .18), transparent 26%),
      radial-gradient(circle at bottom left, rgba(22, 163, 74, .12), transparent 28%),
      linear-gradient(135deg, color-mix(in srgb, var(--surface) 95%, white), color-mix(in srgb, var(--surface) 90%, #eef3ff));
    box-shadow: 0 22px 52px rgba(15, 23, 42, .08);
    padding: 22px;
    transform-style: preserve-3d;
  }

  .ops-hero::after {
    content: "";
    position: absolute;
    inset: auto -20% -55% auto;
    width: 280px;
    height: 280px;
    background: radial-gradient(circle, rgba(91, 99, 211, .12), transparent 70%);
    filter: blur(8px);
    pointer-events: none;
  }

  .ops-hero::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
      linear-gradient(115deg, transparent 0%, rgba(255,255,255,.34) 18%, transparent 38%),
      repeating-linear-gradient(90deg, transparent 0 36px, rgba(255,255,255,.035) 36px 37px);
    opacity: .72;
    pointer-events: none;
  }

  .ops-hero-top {
    position: relative;
    display: grid;
    grid-template-columns: minmax(0, 1.05fr) 320px;
    align-items: center;
    gap: 18px;
    margin-bottom: 18px;
    z-index: 1;
  }

  .ops-hero-left {
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .ops-hero-copy {
    max-width: 760px;
  }

  .ops-hero-title {
    margin: 0;
    font-size: 2.15rem;
    line-height: .98;
    letter-spacing: -.04em;
    color: var(--txt);
    font-weight: 850;
  }

  .ops-live-strip {
    display: flex;
    flex-wrap: wrap;
    gap: .55rem;
  }

  .ops-live-chip {
    display: inline-flex;
    align-items: center;
    gap: .42rem;
    padding: .46rem .75rem;
    border-radius: 999px;
    font-size: .73rem;
    font-weight: 700;
    border: 1px solid color-mix(in srgb, var(--blue) 12%, var(--border));
    background: rgba(255,255,255,.58);
    backdrop-filter: blur(14px);
    color: var(--txt);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.5);
  }

  .ops-hero-metrics {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
  }

  .ops-hero-metric {
    padding: 13px 14px;
    border-radius: 18px;
    border: 1px solid color-mix(in srgb, var(--blue) 12%, var(--border));
    background: linear-gradient(180deg, rgba(255,255,255,.72), rgba(255,255,255,.5));
    box-shadow: inset 0 1px 0 rgba(255,255,255,.5), 0 10px 24px rgba(15, 23, 42, .04);
    min-height: 96px;
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
  }

  .ops-hero-metric:hover {
    transform: translateY(-2px) rotateX(4deg);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.5), 0 16px 28px rgba(15, 23, 42, .07);
    border-color: color-mix(in srgb, var(--blue) 22%, var(--border));
  }

  .ops-hero-metric-label {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--txt-3);
    margin-bottom: 8px;
  }

  .ops-hero-metric-value {
    font-size: 1.55rem;
    line-height: 1;
    font-weight: 800;
    letter-spacing: -.04em;
    color: var(--txt);
  }

  .ops-hero-metric-note {
    font-size: .76rem;
    color: var(--txt-3);
    margin-top: 6px;
  }

  .ops-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    padding: 4px 0 2px;
  }

  .ops-eyebrow {
    font-size: .7rem;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--txt-3);
    margin-bottom: 8px;
  }

  .ops-title {
    margin: 0;
    font-size: 1.7rem;
    line-height: 1.08;
    letter-spacing: -.03em;
    color: var(--txt);
    font-weight: 650;
  }

  .ops-subtitle {
    margin-top: 8px;
    font-size: .88rem;
    color: var(--txt-3);
    max-width: 760px;
  }

  .ops-head-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    flex-wrap: wrap;
  }

  .ops-radar {
    position: relative;
    min-height: 220px;
    border-radius: 24px;
    border: 1px solid color-mix(in srgb, var(--blue) 16%, var(--border));
    background:
      radial-gradient(circle at center, rgba(91,99,211,.1), transparent 58%),
      linear-gradient(180deg, rgba(255,255,255,.52), rgba(255,255,255,.26));
    overflow: hidden;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.45);
  }

  .ops-radar-grid,
  .ops-radar-ring,
  .ops-radar-core,
  .ops-radar-sweep,
  .ops-radar-node {
    position: absolute;
  }

  .ops-radar-grid {
    inset: 0;
    background:
      linear-gradient(rgba(91,99,211,.045) 1px, transparent 1px),
      linear-gradient(90deg, rgba(91,99,211,.045) 1px, transparent 1px);
    background-size: 28px 28px;
    mask-image: radial-gradient(circle at center, black 20%, transparent 85%);
  }

  .ops-radar-ring {
    inset: 50%;
    border-radius: 999px;
    border: 1px solid rgba(91,99,211,.14);
    transform: translate(-50%, -50%);
  }

  .ops-radar-ring.r1 { width: 82px; height: 82px; }
  .ops-radar-ring.r2 { width: 140px; height: 140px; }
  .ops-radar-ring.r3 { width: 202px; height: 202px; }

  .ops-radar-core {
    inset: 50%;
    width: 20px;
    height: 20px;
    transform: translate(-50%, -50%);
    border-radius: 999px;
    background: linear-gradient(180deg, #5b63d3, #343b94);
    box-shadow: 0 0 0 10px rgba(91,99,211,.08), 0 0 28px rgba(91,99,211,.35);
    z-index: 2;
  }

  .ops-radar-sweep {
    inset: 50%;
    width: 210px;
    height: 210px;
    transform: translate(-50%, -50%);
    border-radius: 999px;
    background: conic-gradient(from 0deg, rgba(16,185,129,.28), transparent 22%, transparent 100%);
    filter: blur(2px);
    animation: ops-radar-spin 7s linear infinite;
    opacity: .9;
  }

  .ops-radar-node {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: #16a34a;
    box-shadow: 0 0 0 6px rgba(34,197,94,.12), 0 0 18px rgba(34,197,94,.3);
    animation: ops-pulse 2.4s ease-in-out infinite;
    z-index: 3;
  }

  .ops-radar-node.n1 { top: 22%; left: 62%; animation-delay: .1s; }
  .ops-radar-node.n2 { top: 60%; left: 24%; animation-delay: .6s; }
  .ops-radar-node.n3 { top: 67%; left: 72%; animation-delay: 1.2s; }

  .ops-radar-chip {
    position: absolute;
    display: inline-flex;
    flex-direction: column;
    gap: .18rem;
    min-width: 112px;
    padding: .6rem .72rem;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,.45);
    background: rgba(255,255,255,.72);
    backdrop-filter: blur(16px);
    box-shadow: 0 14px 30px rgba(15,23,42,.08);
    z-index: 4;
    transform: translateZ(22px);
  }

  .ops-radar-chip .label {
    font-size: .64rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--txt-3);
  }

  .ops-radar-chip .value {
    font-size: 1rem;
    font-weight: 800;
    line-height: 1.05;
    color: var(--txt);
  }

  .ops-radar-chip .note {
    font-size: .68rem;
    color: var(--txt-3);
  }

  .ops-radar-chip.c1 { top: 18px; right: 18px; }
  .ops-radar-chip.c2 { bottom: 18px; left: 18px; }
  .ops-radar-chip.c3 { bottom: 30px; right: 26px; }

  .ops-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0 12px;
    min-height: 34px;
    border-radius: 999px;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--txt-2);
    font-size: .78rem;
    font-weight: 500;
  }

  .ops-kpis {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
  }

  .ops-kpi {
    border: 1px solid var(--border);
    background: var(--surface);
    border-radius: 18px;
    padding: 17px 18px;
    min-height: 126px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    box-shadow: 0 12px 28px rgba(15, 23, 42, .045);
    transition: transform .22s ease, box-shadow .22s ease;
  }

  .ops-kpi:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 34px rgba(15, 23, 42, .07);
  }

  .ops-kpi-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
  }

  .ops-kpi-label {
    font-size: .73rem;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--txt-3);
  }

  .ops-kpi-value {
    margin-top: 6px;
    font-size: 1.72rem;
    line-height: 1;
    letter-spacing: -.04em;
    color: var(--txt);
    font-weight: 700;
  }

  .ops-kpi-meta {
    font-size: .82rem;
    color: var(--txt-2);
  }

  .ops-kpi-icon {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--surface-2);
    border: 1px solid var(--border);
    color: var(--txt-2);
    font-size: 1rem;
    flex-shrink: 0;
  }

  .ops-kpi::before {
    content: "";
    width: 44px;
    height: 4px;
    border-radius: 999px;
    background: color-mix(in srgb, var(--blue) 30%, var(--border));
  }

  .ops-kpi:nth-child(2)::before { background: color-mix(in srgb, var(--green) 30%, var(--border)); }
  .ops-kpi:nth-child(3)::before { background: color-mix(in srgb, var(--orange, #f97316) 32%, var(--border)); }
  .ops-kpi:nth-child(4)::before { background: color-mix(in srgb, var(--nk-danger, #e11d48) 32%, var(--border)); }

  .ops-kpi:hover .ops-kpi-icon i {
    transform: scale(1.15) rotate(-4deg);
  }
  .ops-kpi .ops-kpi-icon i {
    transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  .ops-kpi-icon i,
  .ops-pill i,
  .ops-quick-link i {
    font-family: 'boxicons' !important;
    font-style: normal !important;
    font-weight: 400 !important;
    font-variant: normal !important;
    text-transform: none !important;
    line-height: 1 !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    font-size: 1rem !important;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }

  .ops-panel {
    border: 1px solid var(--border);
    background: var(--surface);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 12px 30px rgba(15, 23, 42, .045);
  }

  .ops-panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 18px;
    border-bottom: 1px solid var(--border);
  }

  .ops-panel-title {
    margin: 0;
    font-size: .97rem;
    font-weight: 620;
    letter-spacing: -.02em;
    color: var(--txt);
  }

  .ops-panel-subtitle {
    margin-top: 4px;
    font-size: .78rem;
    color: var(--txt-3);
  }

  .ops-panel-body {
    padding: 18px;
  }

  .ops-panel-body.compact {
    padding-top: 16px;
  }

  .ops-network-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
  }

  .ops-network-card {
    padding: 15px 16px;
    border-radius: 14px;
    border: 1px solid var(--border);
    background: var(--surface-2);
    min-height: 108px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 12px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.4);
    transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
  }

  .ops-network-card:hover {
    transform: translateY(-2px);
    border-color: color-mix(in srgb, var(--blue) 20%, var(--border));
    box-shadow: 0 16px 28px rgba(15,23,42,.06);
  }

  .ops-network-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }

  .ops-network-label {
    font-size: .74rem;
    color: var(--txt-3);
    text-transform: uppercase;
    letter-spacing: .06em;
    font-weight: 600;
  }

  .ops-network-value {
    font-size: 1.34rem;
    font-weight: 700;
    letter-spacing: -.04em;
    color: var(--txt);
  }

  .ops-network-value .muted {
    color: var(--txt-3);
  }

  .ops-network-foot {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: .76rem;
    color: var(--txt-2);
  }

  @keyframes pulse-ring {
    0% { box-shadow: 0 0 0 0 color-mix(in srgb, var(--ops-success) 50%, transparent); }
    70% { box-shadow: 0 0 0 8px color-mix(in srgb, var(--ops-success) 0%, transparent); }
    100% { box-shadow: 0 0 0 0 color-mix(in srgb, var(--ops-success) 0%, transparent); }
  }

  .ops-dot {
    width: 7px;
    height: 7px;
    border-radius: 999px;
    display: inline-block;
    background: var(--ops-success);
    animation: pulse-ring 2.5s infinite cubic-bezier(0.215, 0.61, 0.355, 1);
  }

  .ops-analytics {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
    gap: 18px;
  }

  .ops-stack {
    display: grid;
    gap: 18px;
  }

  .ops-chart {
    height: 320px;
  }

  .ops-mini-chart {
    height: 280px;
  }

  .ops-table-wrap {
    overflow-x: auto;
  }

  .ops-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 640px;
  }

  .ops-table th {
    font-size: .7rem;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--txt-3);
    padding: 0 0 10px;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
  }

  .ops-table td {
    padding: 12px 0;
    border-bottom: 1px solid var(--border);
    font-size: .84rem;
    color: var(--txt-2);
    vertical-align: middle;
  }

  .ops-table tr:last-child td {
    border-bottom: none;
  }

  .ops-primary-text {
    color: var(--txt);
    font-weight: 560;
  }

  .ops-secondary-text {
    color: var(--txt-3);
    font-size: .76rem;
    margin-top: 4px;
  }

  .ops-status {
    display: inline-flex;
    align-items: center;
    min-height: 26px;
    padding: 0 10px;
    border-radius: 999px;
    font-size: .73rem;
    font-weight: 600;
    border: 1px solid var(--border);
    background: var(--surface-2);
    color: var(--txt-2);
  }

  .ops-status.status-active {
    color: var(--ops-success);
    border-color: color-mix(in srgb, var(--ops-success) 24%, var(--border));
    background: var(--ops-success-soft);
  }

  .ops-status.status-suspended,
  .ops-status.status-provisioning {
    color: var(--ops-warning);
    border-color: color-mix(in srgb, var(--ops-warning) 24%, var(--border));
    background: var(--ops-warning-soft);
  }

  .ops-status.status-failed,
  .ops-status.status-unpaid {
    color: var(--ops-danger);
    border-color: color-mix(in srgb, var(--ops-danger) 24%, var(--border));
    background: var(--ops-danger-soft);
  }

  .ops-list {
    display: grid;
    gap: 10px;
  }

  .ops-list-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    border: 1px solid var(--border);
    background: var(--surface-2);
    border-radius: 14px;
  }

  .ops-list-rank {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    border: 1px solid var(--border);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .76rem;
    color: var(--txt-3);
    background: var(--surface);
    flex-shrink: 0;
  }

  .ops-list-main {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    flex: 1;
  }

  .ops-list-copy {
    min-width: 0;
  }

  .ops-list-title {
    font-size: .85rem;
    font-weight: 560;
    color: var(--txt);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .ops-list-subtitle {
    margin-top: 3px;
    font-size: .75rem;
    color: var(--txt-3);
  }

  .ops-list-value {
    font-size: .86rem;
    font-weight: 650;
    color: var(--txt);
    flex-shrink: 0;
  }

  .ops-quick-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
  }

  .ops-quick-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 13px 14px;
    border: 1px solid var(--border);
    background: var(--surface-2);
    border-radius: 16px;
    color: var(--txt);
    text-decoration: none;
    transition: border-color .15s ease, transform .15s ease, box-shadow .15s ease;
  }

  .ops-quick-link:hover {
    color: var(--txt);
    border-color: color-mix(in srgb, var(--blue) 26%, var(--border));
    transform: translateY(-1px);
    box-shadow: 0 10px 20px rgba(91, 99, 211, .08);
  }

  .ops-quick-label {
    font-size: .84rem;
    font-weight: 560;
  }

  .ops-action-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    height: 34px;
    border-radius: 12px;
    border: 1px solid color-mix(in srgb, var(--blue) 18%, var(--border));
    background: rgba(91,99,211,.08);
    color: var(--blue);
  }

  .ops-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 220px;
    border: 1px dashed var(--border);
    border-radius: 16px;
    background: var(--surface-2);
    color: var(--txt-3);
    font-size: .84rem;
  }

  .ops-section-intro {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
  }

  .ops-section-kicker {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--txt-3);
  }

  .ops-section-hint {
    font-size: .78rem;
    color: var(--txt-3);
  }

  @keyframes ops-radar-spin {
    from { transform: translate(-50%, -50%) rotate(0deg); }
    to { transform: translate(-50%, -50%) rotate(360deg); }
  }

  @keyframes ops-pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.16); opacity: .72; }
  }

  @media (max-width: 1199.98px) {
    .ops-hero-top {
      grid-template-columns: 1fr;
    }

    .ops-hero-metrics {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .ops-kpis,
    .ops-network-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .ops-analytics {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 767.98px) {
    .dashboard-page {
      margin: -1.5rem;
      padding: 16px;
      overflow-x: hidden;
      width: 100vw;
      max-width: 100vw;
      box-sizing: border-box;
    }
    
    .ops-radar-chip.c1 { top: 12px; right: 12px; }
    .ops-radar-chip.c2 { bottom: 12px; left: 12px; }
    .ops-radar-chip.c3 { bottom: 20px; right: 16px; }

    .ops-head {
      flex-direction: column;
      align-items: stretch;
    }

    .ops-kpis,
    .ops-network-grid,
    .ops-quick-grid {
      grid-template-columns: 1fr;
    }

    .ops-panel-head,
    .ops-panel-body {
      padding: 14px;
    }

    .ops-hero {
      padding: 14px;
      border-radius: 20px;
    }

    .ops-hero-title {
      font-size: 1.55rem;
    }

    .ops-hero-metrics {
      grid-template-columns: 1fr;
    }

    .ops-radar {
      min-height: 190px;
    }

    .ops-radar-chip {
      min-width: 92px;
      padding: .48rem .58rem;
    }

    .ops-chart,
    .ops-mini-chart {
      height: 260px;
    }
  }

  /* Dark Mode Overrides */
  html[data-theme="dark"] .ops-hero {
    background:
      radial-gradient(circle at top right, rgba(91, 99, 211, .15), transparent 26%),
      radial-gradient(circle at bottom left, rgba(22, 163, 74, .1), transparent 28%),
      var(--surface-2);
  }

  html[data-theme="dark"] .ops-hero::before {
    display: none;
  }

  html[data-theme="dark"] .ops-hero-metric {
    background: var(--surface);
    border-color: var(--border);
    box-shadow: 0 10px 24px rgba(0, 0, 0, .2);
  }

  html[data-theme="dark"] .ops-hero-metric:hover {
    background: var(--surface-2);
    border-color: color-mix(in srgb, var(--blue) 22%, var(--border));
    box-shadow: 0 16px 28px rgba(0, 0, 0, .3);
  }

  html[data-theme="dark"] .ops-radar {
    background: radial-gradient(circle at center, rgba(91,99,211,.15), transparent 58%), var(--surface-2);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.05);
  }

  /* Advanced Glassmorphism & Spotlight Glow */
  html[data-theme="dark"] .ops-glow-card {
    background: linear-gradient(145deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.06);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.05), 0 10px 30px rgba(0,0,0,0.3);
    position: relative;
    overflow: hidden;
  }

  html[data-theme="dark"] .ops-glow-card::after {
    content: "";
    position: absolute;
    inset: 0;
    background: radial-gradient(600px circle at var(--mouse-x, 0) var(--mouse-y, 0), rgba(255,255,255,0.08), transparent 40%);
    opacity: 0;
    transition: opacity 0.4s ease;
    pointer-events: none;
    z-index: 0;
  }

  html[data-theme="dark"] .ops-glow-card:hover::after {
    opacity: 1;
  }

  html[data-theme="dark"] .ops-kpi:hover { border-color: rgba(255,255,255,0.15); box-shadow: 0 16px 40px rgba(0,0,0,0.5); }
  
  /* Ensure content stays above the glow */
  .ops-hero-metric-label, .ops-hero-metric-value, .ops-hero-metric-note,
  .ops-kpi-top, .ops-kpi-meta {
    position: relative;
    z-index: 1;
  }

  .ops-glow-card {
    position: relative;
    overflow: hidden;
  }

  .ops-sparkline {
    position: absolute;
    bottom: -5px;
    left: 0;
    right: 0;
    height: 60px;
    opacity: 0.35;
    z-index: 0;
    pointer-events: none;
  }

  /* Advanced Mesh Gradient & Light Mode Glow */
  html:not([data-theme="dark"]) .dashboard-page::before {
    content: "";
    position: fixed;
    top: 0; left: 0; width: 100vw; height: 100vh;
    background: 
      radial-gradient(800px circle at 15% 30%, rgba(91,99,211,0.04), transparent 100%),
      radial-gradient(800px circle at 85% 20%, rgba(22,163,74,0.03), transparent 100%),
      radial-gradient(800px circle at 50% 80%, rgba(249,115,22,0.03), transparent 100%);
    z-index: -1; pointer-events: none;
  }

  html:not([data-theme="dark"]) .ops-glow-card {
    background: linear-gradient(145deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.6) 100%);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(0,0,0,0.04);
    box-shadow: 0 10px 30px rgba(0,0,0,0.02), inset 0 1px 0 rgba(255,255,255,1);
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
  }
  
  html:not([data-theme="dark"]) .ops-glow-card::after {
    content: "";
    position: absolute;
    inset: 0;
    background: radial-gradient(500px circle at var(--mouse-x, 0) var(--mouse-y, 0), rgba(91,99,211,0.04), transparent 40%);
    opacity: 0;
    transition: opacity 0.4s ease;
    pointer-events: none;
    z-index: 0;
  }

  html:not([data-theme="dark"]) .ops-glow-card:hover::after { opacity: 1; }
  
  html:not([data-theme="dark"]) .ops-glow-card:hover {
    box-shadow: 0 18px 40px rgba(0,0,0,0.05), inset 0 1px 0 rgba(255,255,255,1);
    border-color: rgba(91,99,211,0.15);
    transform: translateY(-2px);
  }

  html[data-theme="dark"] .ops-radar-chip {
    background: rgba(30, 30, 35, .72);
    border-color: rgba(255, 255, 255, .08);
    box-shadow: 0 14px 30px rgba(0, 0, 0, .3);
  }

  html[data-theme="dark"] .ops-live-chip {
    background: rgba(30, 30, 35, .58);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .05);
  }
</style>
@endsection

@section('content')
@php
  $labels = $chartData['labels'] ?? [];
  $revenueSeries = $chartData['revenue'] ?? [];
  $growthSeries = $chartData['growth'] ?? [];
  $areaNames = $areaStats->take(6)->pluck('name')->values();
  $areaCounts = $areaStats->take(6)->pluck('customers_count')->values();
  $activeRate = ($stats['total_customers'] ?? 0) > 0 ? round((($stats['active_customers'] ?? 0) / max(1, $stats['total_customers'])) * 100) : 0;
@endphp

<div class="ops-shell dashboard-page">
  <section class="ops-hero">
    <div class="ops-hero-top">
      <div class="ops-hero-left">
        <div class="ops-hero-copy">
          <div class="ops-eyebrow">Ringkasan Operasional</div>
          <h1 class="ops-hero-title">Dashboard</h1>
        </div>
        <div class="ops-live-strip">
          <span class="ops-live-chip"><i class='bx bx-time-five'></i> Auto-refresh 30 dtk</span>
          <span class="ops-live-chip"><i class='bx bx-pulse'></i> Live monitoring aktif</span>
          <span class="ops-live-chip"><i class='bx bx-check-shield'></i> {{ $activeRate }}% layanan aktif</span>
        </div>
        <div class="ops-head-actions">
          <button class="ms-btn-secondary" type="button" onclick="location.reload()">
            <i class='bx bx-refresh'></i>
            Refresh
          </button>
        </div>
      </div>
      <div class="ops-radar">
        <div class="ops-radar-grid"></div>
        <div class="ops-radar-ring r1"></div>
        <div class="ops-radar-ring r2"></div>
        <div class="ops-radar-ring r3"></div>
        <div class="ops-radar-sweep"></div>
        <div class="ops-radar-core"></div>
        <div class="ops-radar-node n1"></div>
        <div class="ops-radar-node n2"></div>
        <div class="ops-radar-node n3"></div>
        <div class="ops-radar-chip c1">
          <span class="label">Pending</span>
          <span class="value">{{ number_format($stats['pending_payments'] ?? 0) }}</span>
          <span class="note">menunggu review</span>
        </div>
        <div class="ops-radar-chip c2">
          <span class="label">Area</span>
          <span class="value">{{ number_format($stats['total_areas'] ?? 0) }}</span>
          <span class="note">router aktif</span>
        </div>
        <div class="ops-radar-chip c3">
          <span class="label">Approve</span>
          <span class="value">{{ number_format($stats['approved_this_month'] ?? 0) }}</span>
          <span class="note">bulan ini</span>
        </div>
      </div>
    </div>
    <div class="ops-hero-metrics">
      <div class="ops-hero-metric ops-glow-card">
        <div class="ops-hero-metric-label">Pelanggan aktif</div>
        <div class="ops-hero-metric-value">{{ number_format($stats['active_customers'] ?? 0) }}</div>
        <div class="ops-hero-metric-note">{{ $activeRate }}% dari total layanan</div>
      </div>
      <div class="ops-hero-metric ops-glow-card">
        <div class="ops-hero-metric-label">Pending review</div>
        <div class="ops-hero-metric-value">{{ number_format($stats['pending_payments'] ?? 0) }}</div>
        <div class="ops-hero-metric-note">approval pembayaran</div>
      </div>
      <div class="ops-hero-metric ops-glow-card">
        <div class="ops-hero-metric-label">MRR bulanan</div>
        <div class="ops-hero-metric-value">Rp {{ number_format($stats['mrr'] ?? 0, 0, ',', '.') }}</div>
        <div class="ops-hero-metric-note">Perkiraan pendapatan langganan</div>
      </div>
    </div>
  </section>

  <div class="ops-kpis">
    {{-- Total Pelanggan --}}
    <div class="ops-kpi ops-glow-card">
      <div id="spark-kpi-1" class="ops-sparkline"></div>
      <div class="ops-kpi-top">
        <div>
          <div class="ops-kpi-label">Total Pelanggan</div>
          <div class="ops-kpi-value">{{ number_format($stats['total_customers'] ?? 0) }}</div>
        </div>
        <div class="ops-kpi-icon" style="background:rgba(91,99,211,.1);border-color:rgba(91,99,211,.2);color:#5b63d3;">
          <i class='bx bx-user'></i>
        </div>
      </div>
      <div class="ops-kpi-meta">{{ number_format($stats['active_customers'] ?? 0) }} aktif di {{ number_format($stats['total_areas'] ?? 0) }} area</div>
    </div>

    {{-- Tingkat Aktif --}}
    @php $rateColor = $activeRate >= 90 ? 'rgba(22,163,74,.1)' : ($activeRate >= 70 ? 'rgba(217,119,6,.1)' : 'rgba(220,38,38,.1)');
         $rateBorder = $activeRate >= 90 ? 'rgba(22,163,74,.25)' : ($activeRate >= 70 ? 'rgba(217,119,6,.25)' : 'rgba(220,38,38,.25)');
         $rateText = $activeRate >= 90 ? '#16a34a' : ($activeRate >= 70 ? '#d97706' : '#dc2626'); @endphp
    <div class="ops-kpi ops-glow-card">
      <div id="spark-kpi-2" class="ops-sparkline"></div>
      <div class="ops-kpi-top">
        <div>
          <div class="ops-kpi-label">Area Layanan</div>
          <div class="ops-kpi-value">{{ number_format($stats['total_areas'] ?? 0) }}</div>
        </div>
        <div class="ops-kpi-icon" style="background:{{ $rateColor }};border-color:{{ $rateBorder }};color:{{ $rateText }};">
          <i class='bx bx-map-alt'></i>
        </div>
      </div>
      <div class="ops-kpi-meta">Tersebar di router dan area aktif</div>
    </div>

    {{-- Pembayaran Pending --}}
    @php $pending = $stats['pending_payments'] ?? 0;
         $pendingBg = $pending > 0 ? 'rgba(217,119,6,.1)' : 'rgba(22,163,74,.1)';
         $pendingBorder = $pending > 0 ? 'rgba(217,119,6,.25)' : 'rgba(22,163,74,.25)';
         $pendingText = $pending > 0 ? '#d97706' : '#16a34a'; @endphp
    <div class="ops-kpi ops-glow-card">
      <div id="spark-kpi-3" class="ops-sparkline"></div>
      <div class="ops-kpi-top">
        <div>
          <div class="ops-kpi-label">Approve Bulan Ini</div>
          <div class="ops-kpi-value">{{ number_format($stats['approved_this_month'] ?? 0) }}</div>
        </div>
        <div class="ops-kpi-icon" style="background:{{ $pendingBg }};border-color:{{ $pendingBorder }};color:{{ $pendingText }};">
          <i class='bx bx-badge-check'></i>
        </div>
      </div>
      <div class="ops-kpi-meta">
        @if(($stats['monthly_revenue'] ?? 0) > 0)
          <span style="color:#16a34a;">Rp {{ number_format($stats['monthly_revenue'] ?? 0, 0, ',', '.') }} revenue tercatat</span>
        @else
          Belum ada revenue tercatat bulan ini
        @endif
      </div>
    </div>

    {{-- MRR / Pendapatan --}}
    <div class="ops-kpi ops-glow-card">
      <div id="spark-kpi-4" class="ops-sparkline"></div>
      <div class="ops-kpi-top">
        <div>
          <div class="ops-kpi-label">Pelanggan Diisolir</div>
          <div class="ops-kpi-value">{{ number_format($stats['suspended_customers'] ?? 0) }}</div>
        </div>
        <div class="ops-kpi-icon" style="background:color-mix(in srgb, var(--nk-danger, #e11d48) 12%, transparent);border-color:color-mix(in srgb, var(--nk-danger, #e11d48) 25%, transparent);color:var(--nk-danger, #e11d48);">
          <i class='bx bx-block'></i>
        </div>
      </div>
      <div class="ops-kpi-meta">Butuh follow up atau reaktivasi</div>
    </div>
  </div>

  <section class="ops-panel">
    <div class="ops-panel-head">
      <div>
        <h2 class="ops-panel-title">Status jaringan</h2>
        <div class="ops-panel-subtitle">Ringkasan cepat status OLT, area, dan pelanggan aktif.</div>
      </div>
      <span class="ops-pill" style="color:var(--ops-success);border-color:color-mix(in srgb,var(--ops-success) 22%,var(--border));"><i class='bx bx-pulse'></i> Live Monitoring</span>
    </div>
    <div class="ops-panel-body compact">
      <div class="ops-network-grid">
        <div class="ops-network-card">
          <div class="ops-network-top">
            <div>
              <div class="ops-network-label">ONT Online</div>
              <div class="ops-network-value"><span id="live-ont-online">—</span> <span class="muted">/ <span id="live-ont-total">—</span></span></div>
            </div>
            <div class="ops-kpi-icon"><i class='bx bx-wifi'></i></div>
          </div>
          <div class="ops-network-foot"><span class="ops-dot"></span> Ringkasan status ONT dari inventaris OLT</div>
        </div>

        <div class="ops-network-card">
          <div class="ops-network-top">
            <div>
              <div class="ops-network-label">Total Area</div>
              <div class="ops-network-value">{{ number_format($stats['total_areas'] ?? 0) }}</div>
            </div>
            <div class="ops-kpi-icon"><i class='bx bx-map-alt'></i></div>
          </div>
          <div class="ops-network-foot"><span class="ops-dot"></span> Wilayah layanan yang sudah terdaftar</div>
        </div>

        <div class="ops-network-card">
          <div class="ops-network-top">
            <div>
              <div class="ops-network-label">Pending Review</div>
              <div class="ops-network-value"><span id="live-unpaid">{{ number_format($stats['pending_payments'] ?? 0) }}</span></div>
            </div>
            <div class="ops-kpi-icon"><i class='bx bx-calendar-exclamation'></i></div>
          </div>
          <div class="ops-network-foot">
            <span class="ops-dot" style="background:#fbbf24;box-shadow:0 0 0 3px rgba(251,191,36,.14);"></span>
            <span id="live-overdue-wrap" style="{{ ($stats['pending_payments'] ?? 0) > 0 ? 'display:inline;' : 'display:none;' }}"><span id="live-overdue">{{ number_format($stats['pending_payments'] ?? 0) }}</span> menunggu review</span>
            <span id="live-overdue-empty" style="{{ ($stats['pending_payments'] ?? 0) > 0 ? 'display:none;' : 'display:inline;' }}">Semua pembayaran telah diproses</span>
          </div>
        </div>

        <div class="ops-network-card">
          <div class="ops-network-top">
            <div>
              <div class="ops-network-label">Pelanggan aktif</div>
              <div class="ops-network-value"><span id="live-active">{{ number_format($stats['active_customers'] ?? 0) }}</span> <span class="muted">/ <span id="live-total-cust">{{ number_format($stats['total_customers'] ?? 0) }}</span></span></div>
            </div>
            <div class="ops-kpi-icon"><i class='bx bx-user-check'></i></div>
          </div>
          <div class="ops-network-foot"><span class="ops-dot"></span> Jumlah layanan aktif saat ini</div>
        </div>
      </div>
    </div>
  </section>

  <div class="ops-analytics">
    <section class="ops-panel">
      <div class="ops-panel-head">
        <div>
          <h2 class="ops-panel-title">Tren pendapatan</h2>
          <div class="ops-panel-subtitle">Tagihan terbayar dalam 6 bulan terakhir.</div>
        </div>
      </div>
      <div class="ops-panel-body">
        @if(array_sum($revenueSeries) > 0)
          <div id="revenueChart" class="ops-chart"></div>
        @else
          <div class="ops-empty" style="flex-direction:column;gap:8px;min-height:320px;">
            <i class='bx bx-bar-chart-alt-2' style="font-size:2rem;color:var(--txt-3);"></i>
            <div style="font-weight:600;color:var(--txt-2);">Belum ada pendapatan bulan ini</div>
            <div style="font-size:.78rem;color:var(--txt-3);">Data akan muncul setelah tagihan berstatus <em>paid</em>.</div>
          </div>
        @endif
      </div>
    </section>

    <div class="ops-stack">
      <section class="ops-panel">
        <div class="ops-panel-head">
          <div>
            <h2 class="ops-panel-title">Pertumbuhan pelanggan</h2>
            <div class="ops-panel-subtitle">Pertumbuhan kumulatif pelanggan per bulan.</div>
          </div>
        </div>
        <div class="ops-panel-body">
          <div id="growthChart" class="ops-mini-chart"></div>
        </div>
      </section>

      <section class="ops-panel">
        <div class="ops-panel-head">
          <div>
            <h2 class="ops-panel-title">Distribusi area</h2>
            <div class="ops-panel-subtitle">Area teratas berdasarkan jumlah pelanggan.</div>
          </div>
        </div>
        <div class="ops-panel-body">
          @if($areaCounts->sum() > 0)
            <div id="areaChart" class="ops-mini-chart"></div>
          @else
            <div class="ops-empty">Belum ada data area untuk divisualisasikan.</div>
          @endif
        </div>
      </section>
    </div>
  </div>

  <div class="ops-analytics">
    <section class="ops-panel">
      <div class="ops-panel-head">
        <div>
          <h2 class="ops-panel-title">Pelanggan terbaru</h2>
          <div class="ops-panel-subtitle">Pelanggan terbaru yang masuk ke sistem.</div>
        </div>
      </div>
      <div class="ops-panel-body">
        <div class="ops-table-wrap">
          <table class="ops-table">
            <thead>
              <tr>
                <th>Pelanggan</th>
                <th>Area</th>
                <th>Paket</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentCustomers as $customer)
                <tr>
                  <td>
                    <div class="ops-primary-text">{{ $customer->name }}</div>
                    <div class="ops-secondary-text">{{ $customer->pppoe_user ?: 'Tanpa PPPoE' }}</div>
                  </td>
                  <td>{{ $customer->area->name ?? 'Tidak Ditetapkan' }}</td>
                  <td>{{ $customer->package->name ?? 'Tanpa paket' }}</td>
                  <td>
                    @php
                      $statusLabel = ['active'=>'Aktif','suspended'=>'Nonaktif','provisioning'=>'Proses','failed'=>'Gagal','unpaid'=>'Belum Bayar'][$customer->status] ?? ucfirst($customer->status);
                    @endphp
                    <span class="ops-status status-{{ $customer->status }}">{{ $statusLabel }}</span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4">
                    <div class="ops-empty" style="min-height:180px;">Belum ada pelanggan terbaru.</div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <div class="ops-stack">
      <section class="ops-panel">
        <div class="ops-panel-head">
          <div>
            <h2 class="ops-panel-title">Aksi cepat</h2>
            <div class="ops-panel-subtitle">Akses cepat ke area kerja inti.</div>
          </div>
        </div>
        <div class="ops-panel-body">
          <div class="ops-quick-grid">
            <a href="{{ route('admin.customers.index') }}" class="ops-quick-link">
              <span class="ops-quick-label">Pelanggan</span>
              <span class="ops-action-badge"><i class='bx bx-right-arrow-alt'></i></span>
            </a>
            <a href="{{ route('admin.pppoe.index') }}" class="ops-quick-link">
              <span class="ops-quick-label">PPPoE</span>
              <span class="ops-action-badge"><i class='bx bx-right-arrow-alt'></i></span>
            </a>
            <a href="{{ route('admin.olts.index') }}" class="ops-quick-link">
              <span class="ops-quick-label">OLT & ONT</span>
              <span class="ops-action-badge"><i class='bx bx-right-arrow-alt'></i></span>
            </a>
            <a href="{{ route('admin.payments.review') }}" class="ops-quick-link">
              <span class="ops-quick-label">Pembayaran</span>
              <span class="ops-action-badge"><i class='bx bx-right-arrow-alt'></i></span>
            </a>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  (function () {
    const labels = @json($labels);
    const revenueSeries = @json($revenueSeries);
    const growthSeries = @json($growthSeries);
    const areaLabels = @json($areaNames);
    const areaSeries = @json($areaCounts);

    function chartPalette() {
      const dark = document.documentElement.getAttribute('data-theme') === 'dark';
      return {
        dark,
        primary: dark ? '#5e6ad2' : '#5b63d3',
        primarySoft: dark ? 'rgba(94,106,210,0.2)' : 'rgba(91,99,211,0.16)',
        success: dark ? '#4ade80' : '#16a34a',
        warning: dark ? '#fbbf24' : '#d97706',
        danger: dark ? '#fb7185' : '#e11d48',
        text: dark ? '#f4f4f5' : '#18181b',
        muted: dark ? '#71717a' : '#71717a',
        border: dark ? 'rgba(255,255,255,0.08)' : '#e7e7ec',
        fill: dark ? '#111113' : '#ffffff'
      };
    }

    function initCharts() {
      if (typeof ApexCharts === 'undefined') return;
      const palette = chartPalette();

      const revenueEl = document.querySelector('#revenueChart');
      if (revenueEl) {
        revenueEl.innerHTML = '';
        new ApexCharts(revenueEl, {
          chart: {
            type: 'area',
            height: 320,
            toolbar: { show: false },
            background: 'transparent'
          },
          series: [{
            name: 'Pendapatan',
            data: revenueSeries
          }],
          xaxis: {
            categories: labels,
            labels: { style: { colors: palette.muted, fontSize: '12px' } },
            axisBorder: { show: false },
            axisTicks: { show: false }
          },
          yaxis: {
            labels: {
              style: { colors: [palette.muted], fontSize: '12px' },
              formatter: function (value) {
                if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                if (value >= 1000) return 'Rp ' + Math.round(value / 1000) + 'k';
                return 'Rp ' + value;
              }
            }
          },
          dataLabels: { enabled: false },
          stroke: {
            curve: 'smooth',
            width: palette.dark ? 2.2 : 3.0
          },
          colors: [palette.primary],
          grid: {
            borderColor: palette.border,
            strokeDashArray: 4
          },
          fill: {
            type: 'gradient',
            gradient: {
              shadeIntensity: 1,
              opacityFrom: palette.dark ? 0.22 : 0.45,
              opacityTo: 0.02,
              stops: [0, 100]
            }
          },
          tooltip: {
            theme: palette.dark ? 'dark' : 'light',
            y: {
              formatter: function (value) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0);
              }
            }
          },
          legend: { show: false }
        }).render();
      }

      const growthEl = document.querySelector('#growthChart');
      if (growthEl) {
        growthEl.innerHTML = '';
        new ApexCharts(growthEl, {
          chart: {
            type: 'area',
            height: 280,
            toolbar: { show: false },
            background: 'transparent'
          },
          series: [{
            name: 'Pelanggan',
            data: growthSeries
          }],
          xaxis: {
            categories: labels,
            labels: { style: { colors: labels.map(() => palette.muted), fontSize: '12px' } },
            axisBorder: { show: false },
            axisTicks: { show: false }
          },
          yaxis: {
            labels: { style: { colors: [palette.muted], fontSize: '12px' } }
          },
          stroke: {
            curve: 'smooth',
            width: palette.dark ? 2.2 : 3.0
          },
          markers: {
            size: 3,
            strokeWidth: 0,
            colors: [palette.primary]
          },
          colors: [palette.primary],
          dataLabels: { enabled: false },
          fill: {
            type: 'gradient',
            gradient: {
              shadeIntensity: 1,
              opacityFrom: palette.dark ? 0.22 : 0.45,
              opacityTo: 0.02,
              stops: [0, 100]
            }
          },
          grid: {
            borderColor: palette.border,
            strokeDashArray: 4
          },
          tooltip: {
            theme: palette.dark ? 'dark' : 'light'
          },
          legend: { show: false }
        }).render();
      }

      const areaEl = document.querySelector('#areaChart');
      if (areaEl && areaSeries.length) {
        areaEl.innerHTML = '';
        new ApexCharts(areaEl, {
          chart: {
            type: 'donut',
            height: 280,
            background: 'transparent'
          },
          series: areaSeries,
          labels: areaLabels,
          colors: [
            palette.primary,
            '#7c83e6',
            '#8b92eb',
            '#9da3ef',
            '#b0b5f4',
            '#c4c8f7'
          ],
          stroke: {
            width: 0
          },
          legend: {
            position: 'bottom',
            fontSize: '12px',
            labels: { colors: palette.muted }
          },
          dataLabels: {
            enabled: false
          },
          plotOptions: {
            pie: {
              donut: {
                size: '72%',
                labels: {
                  show: true,
                  value: {
                    color: palette.text
                  },
                  total: {
                    show: true,
                    label: 'Total',
                    color: palette.muted
                  }
                }
              }
            }
          },
          tooltip: {
            theme: palette.dark ? 'dark' : 'light'
          }
        }).render();
      }
    }

    function initSparklines() {
      if (typeof ApexCharts === 'undefined') return;
      const palette = chartPalette();
      const sparkData = @json($sparkline ?? []);

      const commonOptions = {
        chart: { type: 'area', height: 60, sparkline: { enabled: true }, animations: { enabled: false } },
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
        tooltip: { fixed: { enabled: false }, x: { show: false }, y: { title: { formatter: function () { return '' } } }, marker: { show: false } }
      };

      if (sparkData.customers && document.querySelector('#spark-kpi-1')) {
        new ApexCharts(document.querySelector('#spark-kpi-1'), { ...commonOptions, series: [{ data: sparkData.customers }], colors: [palette.primary] }).render();
      }
      if (sparkData.active && document.querySelector('#spark-kpi-2')) {
        new ApexCharts(document.querySelector('#spark-kpi-2'), { ...commonOptions, series: [{ data: sparkData.active }], colors: [palette.success] }).render();
      }
      if (sparkData.revenue && document.querySelector('#spark-kpi-3')) {
        new ApexCharts(document.querySelector('#spark-kpi-3'), { ...commonOptions, series: [{ data: sparkData.revenue }], colors: [palette.warning] }).render();
      }
      if (sparkData.payments && document.querySelector('#spark-kpi-4')) {
        new ApexCharts(document.querySelector('#spark-kpi-4'), { ...commonOptions, series: [{ data: sparkData.payments }], colors: [palette.danger] }).render();
      }
    }

    function initGlowEffect() {
      document.querySelectorAll('.ops-panel, .ops-network-card').forEach(function(el) {
        el.classList.add('ops-glow-card');
      });
      document.querySelectorAll('.ops-glow-card').forEach(function(card) {
        card.addEventListener('mousemove', function(e) {
          const rect = card.getBoundingClientRect();
          const x = e.clientX - rect.left;
          const y = e.clientY - rect.top;
          card.style.setProperty('--mouse-x', x + 'px');
          card.style.setProperty('--mouse-y', y + 'px');
        });
      });
    }

    function fetchLiveStatus() {
      fetch(@json(route('admin.api.dashboard-live')))
        .then(function (response) { return response.json(); })
        .then(function (data) {
          if (data.ont_online !== undefined) {
            document.getElementById('live-ont-online').textContent = data.ont_online;
            document.getElementById('live-ont-total').textContent = data.ont_total;
          }
          if (data.pending_payments !== undefined) {
            document.getElementById('live-unpaid').textContent = data.pending_payments;
          }
          if (data.pending_payments !== undefined) {
            const wrap = document.getElementById('live-overdue-wrap');
            const empty = document.getElementById('live-overdue-empty');
            document.getElementById('live-overdue').textContent = data.pending_payments;
            if (data.pending_payments > 0) {
              wrap.style.display = 'inline';
              empty.style.display = 'none';
            } else {
              wrap.style.display = 'none';
              empty.style.display = 'inline';
            }
          }
          if (data.active_customers !== undefined) {
            document.getElementById('live-active').textContent = data.active_customers;
            document.getElementById('live-total-cust').textContent = data.total_customers;
          }
        })
        .catch(function () {});
    }

    document.addEventListener('DOMContentLoaded', function () {
      initCharts();
      initSparklines();
      initGlowEffect();
      fetchLiveStatus();
      setInterval(fetchLiveStatus, 30000);
    });

    window.addEventListener('storage', function (event) {
      if (event.key === 'nk_theme') {
        initCharts();
        initSparklines();
      }
    });
  })();
</script>
@endsection
