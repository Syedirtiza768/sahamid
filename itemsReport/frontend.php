<?php
$PathPrefix = "";
include("includes/session.inc");

if (!isset($_SESSION['UsersRealName'])) {
    header("Location: " . $RootPath . "/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Inventory Dashboard — <?php echo stripslashes($_SESSION['CompanyRecord']['coyname']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta charset="UTF-8">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Core CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
    /* ═══════════════════════════════════════
       DESIGN TOKENS
       ═══════════════════════════════════════ */
    :root {
        /* Primary palette */
        --color-primary:        #1a56db;
        --color-primary-light:  #e1effe;
        --color-primary-hover:  #1648b8;
        --color-primary-50:     #eff6ff;

        /* Neutral palette */
        --color-gray-50:   #f9fafb;
        --color-gray-100:  #f3f4f6;
        --color-gray-200:  #e5e7eb;
        --color-gray-300:  #d1d5db;
        --color-gray-400:  #9ca3af;
        --color-gray-500:  #6b7280;
        --color-gray-600:  #4b5563;
        --color-gray-700:  #374151;
        --color-gray-800:  #1f2937;
        --color-gray-900:  #111827;

        /* Semantic colors */
        --color-success:    #059669;
        --color-success-bg: #ecfdf5;
        --color-warning:    #d97706;
        --color-warning-bg: #fffbeb;
        --color-danger:     #dc2626;
        --color-danger-bg:  #fef2f2;
        --color-info:       #0891b2;
        --color-info-bg:    #ecfeff;

        /* Typography */
        --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        --text-xs:   0.75rem;
        --text-sm:   0.8125rem;
        --text-base: 0.875rem;
        --text-lg:   1rem;
        --text-xl:   1.125rem;
        --text-2xl:  1.25rem;
        --text-3xl:  1.5rem;

        /* Spacing (4px grid) */
        --space-1: 4px;
        --space-2: 8px;
        --space-3: 12px;
        --space-4: 16px;
        --space-5: 20px;
        --space-6: 24px;
        --space-8: 32px;
        --space-10: 40px;
        --space-12: 48px;

        /* Shadows */
        --shadow-xs:  0 1px 2px rgba(0,0,0,0.05);
        --shadow-sm:  0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
        --shadow-md:  0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        --shadow-lg:  0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);

        /* Borders */
        --radius-sm:  6px;
        --radius-md:  8px;
        --radius-lg:  12px;
        --radius-xl:  16px;
        --radius-full: 9999px;

        /* Transition */
        --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
        --transition-base: 200ms cubic-bezier(0.4, 0, 0.2, 1);
    }


    /* ═══════════════════════════════════════
       RESET & BASE
       ═══════════════════════════════════════ */
    *, *::before, *::after { box-sizing: border-box; }

    body {
        font-family: var(--font-sans);
        font-size: var(--text-base);
        color: var(--color-gray-800);
        background: var(--color-gray-50);
        line-height: 1.5;
        -webkit-font-smoothing: antialiased;
        margin: 0;
        padding: 0;
    }

    ::selection {
        background: var(--color-primary-light);
        color: var(--color-primary);
    }


    /* ═══════════════════════════════════════
       TOP BAR
       ═══════════════════════════════════════ */
    .topbar {
        background: #fff;
        border-bottom: 1px solid var(--color-gray-200);
        padding: var(--space-3) var(--space-6);
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: var(--shadow-xs);
    }
    .topbar-inner {
        max-width: 1600px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--space-4);
    }
    .topbar-brand {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        text-decoration: none;
        color: var(--color-gray-900);
        flex-shrink: 0;
    }
    .topbar-brand img {
        width: 36px;
        height: 36px;
        border-radius: var(--radius-sm);
        object-fit: contain;
    }
    .topbar-brand-text {
        font-weight: 700;
        font-size: var(--text-lg);
        letter-spacing: -0.02em;
        white-space: nowrap;
    }
    .topbar-title {
        font-size: var(--text-sm);
        font-weight: 600;
        color: var(--color-gray-500);
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .topbar-actions {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        flex-shrink: 0;
    }
    .topbar-user {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-1) var(--space-3);
        background: var(--color-gray-100);
        border-radius: var(--radius-full);
        font-size: var(--text-sm);
        font-weight: 500;
        color: var(--color-gray-700);
    }
    .topbar-user .avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--color-primary);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--text-xs);
        font-weight: 700;
    }
    .topbar-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: var(--space-2) var(--space-3);
        font-size: var(--text-sm);
        font-weight: 500;
        color: var(--color-gray-600);
        text-decoration: none;
        border-radius: var(--radius-sm);
        transition: all var(--transition-fast);
        border: 1px solid transparent;
    }
    .topbar-link:hover {
        background: var(--color-gray-100);
        color: var(--color-gray-900);
        text-decoration: none;
    }
    .topbar-divider {
        width: 1px;
        height: 20px;
        background: var(--color-gray-200);
    }


    /* ═══════════════════════════════════════
       PAGE SHELL
       ═══════════════════════════════════════ */
    .page-shell {
        max-width: 1600px;
        margin: 0 auto;
        padding: var(--space-6);
    }
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: var(--space-6);
        flex-wrap: wrap;
        gap: var(--space-4);
    }
    .page-header h1 {
        font-size: var(--text-2xl);
        font-weight: 700;
        color: var(--color-gray-900);
        margin: 0;
        letter-spacing: -0.02em;
    }
    .page-header-sub {
        font-size: var(--text-sm);
        color: var(--color-gray-500);
        margin-top: 2px;
    }


    /* ═══════════════════════════════════════
       STAT CARDS
       ═══════════════════════════════════════ */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: var(--space-4);
        margin-bottom: var(--space-6);
    }
    @media (max-width: 1024px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .stats-grid { grid-template-columns: 1fr; }
    }
    .stat-card {
        background: #fff;
        border: 1px solid var(--color-gray-200);
        border-radius: var(--radius-lg);
        padding: var(--space-5);
        transition: all var(--transition-base);
        position: relative;
        overflow: hidden;
    }
    .stat-card:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--color-gray-300);
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
    }
    .stat-card--success::before { background: var(--color-success); }
    .stat-card--warning::before { background: var(--color-warning); }
    .stat-card--danger::before  { background: var(--color-danger); }
    .stat-card--dark::before    { background: var(--color-gray-800); }

    .stat-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: var(--space-3);
    }
    .stat-card-label {
        font-size: var(--text-sm);
        font-weight: 500;
        color: var(--color-gray-500);
    }
    .stat-card-icon {
        width: 36px;
        height: 36px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--text-base);
    }
    .stat-card--success .stat-card-icon { background: var(--color-success-bg); color: var(--color-success); }
    .stat-card--warning .stat-card-icon { background: var(--color-warning-bg); color: var(--color-warning); }
    .stat-card--danger .stat-card-icon  { background: var(--color-danger-bg);  color: var(--color-danger); }
    .stat-card--dark .stat-card-icon    { background: var(--color-gray-100);   color: var(--color-gray-700); }

    .stat-card-value {
        font-size: var(--text-3xl);
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: -0.02em;
        font-variant-numeric: tabular-nums;
    }
    .stat-card--success .stat-card-value { color: var(--color-success); }
    .stat-card--warning .stat-card-value { color: var(--color-warning); }
    .stat-card--danger .stat-card-value  { color: var(--color-danger); }
    .stat-card--dark .stat-card-value    { color: var(--color-gray-800); }

    .stat-card-sum {
        font-size: var(--text-sm);
        font-weight: 500;
        color: var(--color-gray-500);
        margin-top: var(--space-1);
        font-variant-numeric: tabular-nums;
    }

    /* Pricing card variants */
    .stat-card--indigo::before  { background: #6366f1; }
    .stat-card--indigo .stat-card-icon  { background: #eef2ff; color: #6366f1; }
    .stat-card--indigo .stat-card-value { color: #4338ca; }

    .stat-card--teal::before    { background: #0d9488; }
    .stat-card--teal .stat-card-icon    { background: #f0fdfa; color: #0d9488; }
    .stat-card--teal .stat-card-value   { color: #0d9488; }

    .stat-card--violet::before  { background: #7c3aed; }
    .stat-card--violet .stat-card-icon  { background: #f5f3ff; color: #7c3aed; }
    .stat-card--violet .stat-card-value { color: #7c3aed; }

    .stat-card-metric-label {
        font-size: var(--text-xs);
        font-weight: 600;
        color: var(--color-gray-400);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-top: var(--space-1);
    }
    .stat-card-pricing-breakdown {
        font-size: var(--text-sm);
        color: var(--color-gray-600);
        margin-top: var(--space-3);
        line-height: 1.55;
    }
    .stat-card-pricing-breakdown > div { font-variant-numeric: tabular-nums; }

    /* 3-column grid variant */
    .stats-grid--3 {
        grid-template-columns: repeat(3, 1fr);
    }
    @media (max-width: 900px) {
        .stats-grid--3 { grid-template-columns: 1fr; }
    }

    /* Section label */
    .stats-section-label {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        font-size: var(--text-xs);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--color-gray-400);
        margin-bottom: var(--space-3);
    }
    .stats-section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--color-gray-200);
    }


    /* ═══════════════════════════════════════
       TOOLBAR (Filters + Actions)
       ═══════════════════════════════════════ */
    .toolbar {
        background: #fff;
        border: 1px solid var(--color-gray-200);
        border-radius: var(--radius-lg);
        padding: var(--space-4) var(--space-5);
        margin-bottom: var(--space-4);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: var(--space-3);
    }
    .toolbar-group {
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }
    .toolbar-label {
        font-size: var(--text-sm);
        font-weight: 600;
        color: var(--color-gray-500);
        margin-right: var(--space-2);
        white-space: nowrap;
    }

    /* Filter pills */
    .pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: var(--space-2) var(--space-4);
        font-family: var(--font-sans);
        font-size: var(--text-sm);
        font-weight: 500;
        color: var(--color-gray-600);
        background: var(--color-gray-100);
        border: 1px solid var(--color-gray-200);
        border-radius: var(--radius-full);
        cursor: pointer;
        transition: all var(--transition-fast);
        line-height: 1;
        user-select: none;
    }
    .pill:hover {
        background: var(--color-gray-200);
        color: var(--color-gray-800);
    }
    .pill:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(26,86,219,0.12);
    }
    .pill.active {
        background: var(--color-primary);
        color: #fff;
        border-color: var(--color-primary);
        box-shadow: 0 1px 3px rgba(26,86,219,0.3);
    }
    .pill .pill-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        font-size: var(--text-xs);
        font-weight: 600;
        border-radius: var(--radius-full);
        background: rgba(0,0,0,0.08);
        line-height: 1;
    }
    .pill.active .pill-count {
        background: rgba(255,255,255,0.25);
    }
    .pill.loading {
        pointer-events: none;
        opacity: 0.6;
    }


    /* ═══════════════════════════════════════
       BUTTONS
       ═══════════════════════════════════════ */
    .btn-primary-custom {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: var(--space-2) var(--space-4);
        font-family: var(--font-sans);
        font-size: var(--text-sm);
        font-weight: 600;
        color: #fff;
        background: var(--color-primary);
        border: 1px solid var(--color-primary);
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: all var(--transition-fast);
        text-decoration: none;
        line-height: 1.4;
    }
    .btn-primary-custom:hover {
        background: var(--color-primary-hover);
        color: #fff;
        text-decoration: none;
        box-shadow: var(--shadow-sm);
    }
    .btn-secondary-custom {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: var(--space-2) var(--space-4);
        font-family: var(--font-sans);
        font-size: var(--text-sm);
        font-weight: 500;
        color: var(--color-gray-700);
        background: #fff;
        border: 1px solid var(--color-gray-300);
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: all var(--transition-fast);
        text-decoration: none;
        line-height: 1.4;
    }
    .btn-secondary-custom:hover {
        background: var(--color-gray-50);
        border-color: var(--color-gray-400);
        color: var(--color-gray-900);
        text-decoration: none;
        box-shadow: var(--shadow-xs);
    }


    /* ═══════════════════════════════════════
       TABLE PANEL
       ═══════════════════════════════════════ */
    .table-panel {
        background: #fff;
        border: 1px solid var(--color-gray-200);
        border-radius: var(--radius-lg);
        overflow: hidden;
        position: relative;
    }
    .table-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--space-4) var(--space-5);
        border-bottom: 1px solid var(--color-gray-200);
        flex-wrap: wrap;
        gap: var(--space-3);
    }
    .table-panel-title {
        font-size: var(--text-lg);
        font-weight: 600;
        color: var(--color-gray-900);
    }

    /* Loading overlay */
    .loading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,0.96);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 50;
        border-radius: var(--radius-lg);
    }
    .loading-content {
        text-align: center;
    }
    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 3px solid var(--color-gray-200);
        border-top-color: var(--color-primary);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin: 0 auto var(--space-4);
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    .loading-text {
        font-size: var(--text-base);
        font-weight: 600;
        color: var(--color-gray-700);
    }
    .loading-sub {
        font-size: var(--text-sm);
        color: var(--color-gray-400);
        margin-top: var(--space-1);
    }

    .filter-loading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,0.9);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 51;
    }


    /* ═══════════════════════════════════════
       DATA TABLE OVERRIDES
       ═══════════════════════════════════════ */
    .dataTables_wrapper {
        padding: 0;
    }
    .dataTables_wrapper .row:first-child {
        padding: var(--space-4) var(--space-5) 0;
    }
    .dataTables_wrapper .row:last-child {
        padding: var(--space-3) var(--space-5) var(--space-4);
    }
    .dataTables_wrapper .dataTables_length label {
        font-family: var(--font-sans);
        font-size: var(--text-sm);
        color: var(--color-gray-600);
    }
    .dataTables_wrapper .dataTables_length select {
        font-family: var(--font-sans);
        font-size: var(--text-sm);
        border-radius: var(--radius-sm);
        border: 1px solid var(--color-gray-300);
        padding: var(--space-1) var(--space-6) var(--space-1) var(--space-2);
    }
    .dataTables_wrapper .dataTables_filter label {
        font-family: var(--font-sans);
        font-size: var(--text-sm);
        color: var(--color-gray-600);
    }
    .dataTables_wrapper .dataTables_filter input {
        font-family: var(--font-sans);
        font-size: var(--text-sm);
        border-radius: var(--radius-full);
        border: 1px solid var(--color-gray-300);
        padding: var(--space-2) var(--space-4);
        width: 280px;
        transition: all var(--transition-fast);
        outline: none;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(26,86,219,0.12);
    }
    .dataTables_wrapper .dataTables_info {
        font-size: var(--text-sm);
        color: var(--color-gray-500);
        padding-top: 0 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        font-family: var(--font-sans) !important;
        font-size: var(--text-sm) !important;
        border-radius: var(--radius-sm) !important;
        padding: 4px 10px !important;
        margin: 0 2px !important;
        border: 1px solid var(--color-gray-200) !important;
        color: var(--color-gray-700) !important;
        background: #fff !important;
        transition: all var(--transition-fast);
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: var(--color-gray-100) !important;
        border-color: var(--color-gray-300) !important;
        color: var(--color-gray-900) !important;
        box-shadow: none !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--color-primary) !important;
        border-color: var(--color-primary) !important;
        color: #fff !important;
        font-weight: 600;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: var(--color-primary-hover) !important;
        border-color: var(--color-primary-hover) !important;
        color: #fff !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.4;
    }

    /* Hide default DT buttons — we use custom ones */
    .dataTables_wrapper .dt-buttons { display: none !important; }

    /* Table itself */
    #datatable {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0 !important;
    }
    #datatable thead th {
        background: var(--color-gray-50);
        color: var(--color-gray-600);
        font-size: var(--text-xs);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: var(--space-3) var(--space-3) !important;
        border-bottom: 2px solid var(--color-gray-200) !important;
        border-top: none !important;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
        text-align: left;
    }

    #datatable tbody td {
        padding: var(--space-3) var(--space-3) !important;
        border-bottom: 1px solid var(--color-gray-100) !important;
        border-top: none !important;
        font-size: var(--text-sm);
        vertical-align: middle;
        color: var(--color-gray-700);
        text-align: left;
        transition: background var(--transition-fast);
    }
    #datatable tbody tr:hover td {
        background: var(--color-primary-50);
    }
    #datatable tbody tr:last-child td {
        border-bottom: none !important;
    }

    /* Remove default Bootstrap table borders */
    #datatable.table-bordered td,
    #datatable.table-bordered th {
        border-left: none !important;
        border-right: none !important;
    }

    /* Column-specific styles */
    .col-stockid a {
        color: var(--color-primary);
        font-weight: 600;
        text-decoration: none;
        transition: color var(--transition-fast);
    }
    .col-stockid a:hover {
        color: var(--color-primary-hover);
        text-decoration: underline;
    }
    .col-numeric {
        font-variant-numeric: tabular-nums;
        text-align: right !important;
    }
    .col-center {
        text-align: center !important;
    }

    /* Quantity badge */
    .qty-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        padding: 2px 8px;
        font-size: var(--text-sm);
        font-weight: 600;
        border-radius: var(--radius-full);
        line-height: 1.6;
    }
    .qty-badge--positive {
        background: var(--color-success-bg);
        color: var(--color-success);
    }
    .qty-badge--zero {
        background: var(--color-danger-bg);
        color: var(--color-danger);
    }

    /* Status badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        font-size: var(--text-xs);
        font-weight: 600;
        border-radius: var(--radius-full);
        white-space: nowrap;
    }
    .status-badge--fast    { background: var(--color-success-bg); color: var(--color-success); }
    .status-badge--slow    { background: var(--color-warning-bg); color: var(--color-warning); }
    .status-badge--dead    { background: var(--color-danger-bg);  color: var(--color-danger); }
    .status-badge--extreme { background: var(--color-gray-100);   color: var(--color-gray-600); }


    /* ═══════════════════════════════════════
       INLINE INPUTS
       ═══════════════════════════════════════ */
    .inline-input {
        width: 96px;
        padding: 5px 8px;
        font-family: var(--font-sans);
        font-size: var(--text-sm);
        font-variant-numeric: tabular-nums;
        color: var(--color-gray-800);
        background: var(--color-gray-50);
        border: 1px solid var(--color-gray-300);
        border-radius: var(--radius-sm);
        text-align: right;
        transition: all var(--transition-fast);
        outline: none;
    }
    .inline-input:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(26,86,219,0.12);
        background: #fff;
    }
    .inline-input.edited {
        border-color: var(--color-success);
        background: var(--color-success-bg);
    }
    .inline-input::-webkit-inner-spin-button {
        opacity: 0;
    }
    .inline-input:hover::-webkit-inner-spin-button {
        opacity: 1;
    }


    /* ═══════════════════════════════════════
       IMPORT PANEL (collapsible)
       ═══════════════════════════════════════ */
    .import-panel {
        background: #fff;
        border: 1px solid var(--color-gray-200);
        border-radius: var(--radius-lg);
        margin-bottom: var(--space-4);
        overflow: hidden;
    }
    .import-panel-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--space-4) var(--space-5);
        cursor: pointer;
        user-select: none;
        transition: background var(--transition-fast);
    }
    .import-panel-toggle:hover {
        background: var(--color-gray-50);
    }
    .import-panel-title {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        font-size: var(--text-base);
        font-weight: 600;
        color: var(--color-gray-800);
    }
    .import-panel-toggle .chevron {
        transition: transform var(--transition-base);
        color: var(--color-gray-400);
    }
    .import-panel-toggle.collapsed .chevron {
        transform: rotate(-90deg);
    }
    .import-panel-body {
        padding: 0 var(--space-5) var(--space-5);
    }
    .import-hint {
        display: flex;
        align-items: flex-start;
        gap: var(--space-3);
        padding: var(--space-3) var(--space-4);
        background: var(--color-info-bg);
        border-radius: var(--radius-md);
        font-size: var(--text-sm);
        color: var(--color-gray-700);
        line-height: 1.6;
    }
    .import-hint i {
        color: var(--color-info);
        margin-top: 3px;
        flex-shrink: 0;
    }


    /* ═══════════════════════════════════════
       MODAL OVERRIDES
       ═══════════════════════════════════════ */
    .modal-content {
        border: none;
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-lg);
        overflow: hidden;
    }
    .modal-header {
        border-bottom: 1px solid var(--color-gray-200);
        padding: var(--space-5) var(--space-6);
        background: #fff;
    }
    .modal-header .modal-title {
        font-family: var(--font-sans);
        font-size: var(--text-xl);
        font-weight: 600;
        color: var(--color-gray-900);
    }
    .modal-body {
        padding: var(--space-5) var(--space-6);
    }
    .modal-footer {
        border-top: 1px solid var(--color-gray-200);
        padding: var(--space-4) var(--space-6);
    }
    .modal-header .close {
        font-size: 1.25rem;
        opacity: 0.5;
        transition: opacity var(--transition-fast);
    }
    .modal-header .close:hover {
        opacity: 1;
    }

    /* Format preview table in modal */
    .format-table th,
    .format-table td {
        font-size: var(--text-xs) !important;
        padding: 6px 8px !important;
    }
    .format-table .col-highlight {
        background: var(--color-success-bg);
        color: var(--color-success);
        font-weight: 700;
    }

    #importPreview table {
        font-size: var(--text-sm);
    }

    /* Upload progress */
    .upload-progress-bar {
        background: var(--color-primary-50);
        border: 1px solid var(--color-primary-light);
        border-radius: var(--radius-md);
        padding: var(--space-4);
    }
    .progress {
        height: 8px;
        border-radius: var(--radius-full);
        background: var(--color-gray-200);
        overflow: hidden;
    }
    .progress-bar {
        background: var(--color-primary);
        border-radius: var(--radius-full);
        transition: width var(--transition-base);
        font-size: 0;
        line-height: 8px;
    }
    .timer-display {
        font-family: 'SF Mono', 'Fira Code', 'Consolas', monospace;
        font-size: var(--text-sm);
        font-weight: 600;
        color: var(--color-primary);
    }

    .file-info-bar {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-2) var(--space-3);
        background: var(--color-gray-100);
        border-radius: var(--radius-sm);
        font-size: var(--text-sm);
        color: var(--color-gray-600);
    }

    .upload-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--space-3);
        margin-top: var(--space-3);
    }
    .upload-stat {
        text-align: center;
        padding: var(--space-2);
        background: rgba(255,255,255,0.6);
        border-radius: var(--radius-sm);
    }
    .upload-stat-val  { font-size: var(--text-lg); font-weight: 700; color: var(--color-gray-800); }
    .upload-stat-label { font-size: var(--text-xs); color: var(--color-gray-500); margin-top: 2px; }


    /* ═══════════════════════════════════════
       TOAST NOTIFICATIONS
       ═══════════════════════════════════════ */
    .toast-container {
        position: fixed;
        top: 70px;
        right: var(--space-5);
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: var(--space-2);
        pointer-events: none;
    }
    .toast-item {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        padding: var(--space-3) var(--space-4);
        background: #fff;
        border: 1px solid var(--color-gray-200);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-lg);
        font-size: var(--text-sm);
        font-weight: 500;
        color: var(--color-gray-700);
        max-width: 380px;
        pointer-events: auto;
        animation: toastIn var(--transition-base) forwards;
        border-left: 3px solid var(--color-gray-300);
    }
    .toast-item--success { border-left-color: var(--color-success); }
    .toast-item--error   { border-left-color: var(--color-danger); }
    .toast-item--info    { border-left-color: var(--color-primary); }

    .toast-item .toast-icon { flex-shrink: 0; font-size: var(--text-base); }
    .toast-item--success .toast-icon { color: var(--color-success); }
    .toast-item--error   .toast-icon { color: var(--color-danger); }
    .toast-item--info    .toast-icon { color: var(--color-primary); }

    .toast-item.leaving { animation: toastOut var(--transition-base) forwards; }
    @keyframes toastIn {
        from { opacity: 0; transform: translateX(20px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes toastOut {
        from { opacity: 1; transform: translateX(0); }
        to   { opacity: 0; transform: translateX(20px); }
    }


    /* ═══════════════════════════════════════
       MISC
       ═══════════════════════════════════════ */
    .text-tabular { font-variant-numeric: tabular-nums; }

    .custom-file-label {
        font-family: var(--font-sans);
        font-size: var(--text-sm);
        border-radius: var(--radius-sm);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .custom-file-input:lang(en)~.custom-file-label::after { content: "Browse"; }

    .page-footer {
        text-align: center;
        padding: var(--space-8) 0 var(--space-6);
        font-size: var(--text-xs);
        color: var(--color-gray-400);
    }
    .page-footer strong { color: var(--color-gray-500); }

    .kbd-hint {
        position: fixed;
        bottom: var(--space-5);
        right: var(--space-5);
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: var(--color-gray-800);
        color: var(--color-gray-300);
        border-radius: var(--radius-full);
        font-size: var(--text-xs);
        opacity: 0.35;
        transition: opacity var(--transition-base);
        z-index: 50;
    }
    .kbd-hint:hover { opacity: 0.9; }
    .kbd-hint kbd {
        display: inline-block;
        padding: 1px 5px;
        background: var(--color-gray-600);
        border-radius: 3px;
        font-family: var(--font-sans);
        font-size: 10px;
        font-weight: 600;
        color: #fff;
    }

    .result-number {
        font-size: 2.5rem;
        font-weight: 700;
        letter-spacing: -0.03em;
        font-variant-numeric: tabular-nums;
    }
    .result-number--success { color: var(--color-success); }
    .result-number--error   { color: var(--color-danger); }

    /* Responsive */
    @media (max-width: 768px) {
        .topbar-title { display: none; }
        .topbar-user span:not(.avatar) { display: none; }
        .toolbar { flex-direction: column; align-items: stretch; }
        .toolbar-group { flex-wrap: wrap; }
        .page-shell { padding: var(--space-4); }
        .dataTables_wrapper .dataTables_filter input { width: 180px; }
    }
    </style>
</head>

<body>

<!-- ════════════════════════════════════════
     TOP BAR
     ════════════════════════════════════════ -->
<header class="topbar">
    <div class="topbar-inner">
        <div class="topbar-brand">
            <img src="includes/ERP.png" alt="Logo">
            <span class="topbar-brand-text">S A Hamid &amp; Co</span>
        </div>

        <span class="topbar-title">Inventory Price List</span>

        <div class="topbar-actions">
            <div class="topbar-user">
                <span class="avatar"><?php echo strtoupper(substr($_SESSION['UsersRealName'], 0, 1)); ?></span>
                <span><?php echo htmlspecialchars($_SESSION['UsersRealName']); ?></span>
            </div>
            <span class="topbar-divider"></span>
            <a href="<?php echo $RootPath; ?>/index.php" class="topbar-link">
                <i class="fas fa-th-large"></i> Menu
            </a>
            <a href="<?php echo $RootPath; ?>/Logout.php" class="topbar-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</header>


<!-- ════════════════════════════════════════
     PAGE SHELL
     ════════════════════════════════════════ -->
<main class="page-shell">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1>Inventory Dashboard</h1>
            <div class="page-header-sub">Real-time pricing, stock levels, and inventory health</div>
        </div>
    </div>

    <!-- Stock Movement Cards -->
    <div class="stats-section-label">Stock Movement</div>
    <div class="stats-grid">
        <div class="stat-card stat-card--success">
            <div class="stat-card-header">
                <span class="stat-card-label">Fast Moving</span>
                <span class="stat-card-icon"><i class="fas fa-arrow-trend-up"></i></span>
            </div>
            <div class="stat-card-value" id="runningCount">&mdash;</div>
            <div class="stat-card-sum">Rs. <span id="runningSum">0.00</span></div>
        </div>
        <div class="stat-card stat-card--warning">
            <div class="stat-card-header">
                <span class="stat-card-label">Slow Moving</span>
                <span class="stat-card-icon"><i class="fas fa-clock-rotate-left"></i></span>
            </div>
            <div class="stat-card-value" id="slowCount">&mdash;</div>
            <div class="stat-card-sum">Rs. <span id="slowSum">0.00</span></div>
        </div>
        <div class="stat-card stat-card--danger">
            <div class="stat-card-header">
                <span class="stat-card-label">Dead Stock</span>
                <span class="stat-card-icon"><i class="fas fa-triangle-exclamation"></i></span>
            </div>
            <div class="stat-card-value" id="deadCount">&mdash;</div>
            <div class="stat-card-sum">Rs. <span id="deadSum">0.00</span></div>
        </div>
        <div class="stat-card stat-card--dark">
            <div class="stat-card-header">
                <span class="stat-card-label">Extremely Dead</span>
                <span class="stat-card-icon"><i class="fas fa-ban"></i></span>
            </div>
            <div class="stat-card-value" id="extremelyDeadCount">&mdash;</div>
            <div class="stat-card-sum">Rs. <span id="extremelyDeadSum">0.00</span></div>
        </div>
    </div>

    <!-- Pricing Coverage Cards -->
    <div class="stats-section-label" style="margin-top:var(--space-5)">Pricing Coverage</div>
    <div class="stats-grid stats-grid--3">

        <!-- No Price Attached -->
        <div class="stat-card stat-card--indigo">
            <div class="stat-card-header">
                <span class="stat-card-label">No Price Attached</span>
                <span class="stat-card-icon"><i class="fas fa-circle-exclamation"></i></span>
            </div>
            <div class="stat-card-value" id="priceZeroTotal">&mdash;</div>
            <div class="stat-card-metric-label">Total SKUs</div>
            <div class="stat-card-pricing-breakdown">
                <div>On stock: <span id="priceZeroOnStock">0</span> · Rs. <span id="priceZeroOnStockValue">0.00</span></div>
                <div>Not on stock: <span id="priceZeroNotOnStock">0</span></div>
            </div>
        </div>

        <!-- Manually Adjusted -->
        <div class="stat-card stat-card--teal">
            <div class="stat-card-header">
                <span class="stat-card-label">Manually Adjusted</span>
                <span class="stat-card-icon"><i class="fas fa-sliders"></i></span>
            </div>
            <div class="stat-card-value" id="priceAdjTotal">&mdash;</div>
            <div class="stat-card-metric-label">Total SKUs</div>
            <div class="stat-card-pricing-breakdown">
                <div>On stock: <span id="priceAdjOnStock">0</span> · Rs. <span id="priceAdjOnStockValue">0.00</span></div>
                <div>Not on stock: <span id="priceAdjNotOnStock">0</span></div>
            </div>
        </div>

        <!-- Original Prices as per MPIW -->
        <div class="stat-card stat-card--violet">
            <div class="stat-card-header">
                <span class="stat-card-label">Original Prices as per MPIW</span>
                <span class="stat-card-icon"><i class="fas fa-receipt"></i></span>
            </div>
            <div class="stat-card-value" id="priceOrigTotal">&mdash;</div>
            <div class="stat-card-metric-label">Total SKUs</div>
            <div class="stat-card-pricing-breakdown">
                <div>On stock: <span id="priceOrigOnStock">0</span> · Rs. <span id="priceOrigOnStockValue">0.00</span></div>
                <div>Not on stock: <span id="priceOrigNotOnStock">0</span></div>
            </div>
        </div>

    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <div class="toolbar-group">
            <span class="toolbar-label"><i class="fas fa-filter" style="margin-right:4px"></i> Stock</span>
            <button class="pill active qty-filter-btn" data-filter="non-zero">
                In Stock <span class="pill-count" id="nonZeroCount">&mdash;</span>
            </button>
            <button class="pill qty-filter-btn" data-filter="zero">
                Out of Stock <span class="pill-count" id="zeroCount">&mdash;</span>
            </button>
            <button class="pill qty-filter-btn" data-filter="both">
                All <span class="pill-count" id="bothCount">&mdash;</span>
            </button>
        </div>
        <div class="toolbar-group">
            <button class="btn-primary-custom" id="btnDownloadCSV">
                <i class="fas fa-download"></i> Export CSV
            </button>
            <button class="btn-secondary-custom" id="btnDownloadAll">
                <i class="fas fa-file-arrow-down"></i> Export All
            </button>
        </div>
    </div>

    <!-- Import Panel -->
    <div class="import-panel">
        <div class="import-panel-toggle collapsed" data-toggle="collapse" data-target="#importPanelBody" aria-expanded="false">
            <span class="import-panel-title">
                <i class="fas fa-file-import"></i> Import Prices from CSV
            </span>
            <i class="fas fa-chevron-down chevron"></i>
        </div>
        <div class="collapse" id="importPanelBody">
            <div class="import-panel-body">
                <div class="import-hint mb-3">
                    <i class="fas fa-circle-info"></i>
                    <span>Upload the CSV file you downloaded using <strong>Export CSV</strong> or <strong>Export All</strong>. Only the <strong>Adjust Unit Price</strong> and <strong>Landing Factor</strong> columns will be processed.</span>
                </div>
                <button type="button" class="btn-primary-custom" data-toggle="modal" data-target="#importModal">
                    <i class="fas fa-upload"></i> Upload CSV File
                </button>
            </div>
        </div>
    </div>

    <!-- Data Table Panel -->
    <div class="table-panel">
        <div class="table-panel-header">
            <span class="table-panel-title">Price List</span>
            <div id="customizationsArea" style="display:flex;align-items:center;gap:8px"></div>
        </div>

        <div class="loading-overlay" id="loadingOverlay">
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-text">Loading inventory data</div>
                <div class="loading-sub">This may take a minute for large catalogs</div>
            </div>
        </div>

        <div class="filter-loading-overlay" id="filterLoadingOverlay">
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-text" id="filterLoadingText">Applying filter&hellip;</div>
            </div>
        </div>

        <table class="table" id="datatable" style="width:100%">
            <thead>
                <tr>
                    <th>Stock ID</th>
                    <th>Brand</th>
                    <th>Category</th>
                    <th>Model #</th>
                    <th>Part #</th>
                    <th class="col-center">Qty</th>
                    <th class="col-numeric">Total Price</th>
                    <th class="col-numeric">Unit Price</th>
                    <th class="col-center">Adjust Price</th>
                    <th class="col-center">Factor</th>
                    <th class="col-numeric">Adj. Price</th>
                    <th class="col-numeric">Qty &times; Adj.</th>
                    <th class="col-numeric">List Price</th>
                    <th class="col-center">Status</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

</main>


<!-- ════════════════════════════════════════
     IMPORT MODAL
     ════════════════════════════════════════ -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-import" style="color:var(--color-primary);margin-right:8px"></i>Upload CSV</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="import-hint mb-4">
                    <i class="fas fa-circle-info"></i>
                    <span>Upload the CSV exactly as downloaded. The system reads <strong>Stock ID</strong>, <strong>Adjust Unit Price</strong>, and <strong>Landing Factor</strong> columns.</span>
                </div>

                <div class="mb-4" style="overflow-x:auto;">
                    <table class="table table-sm table-bordered format-table mb-0" style="font-size:11px">
                        <thead style="background:var(--color-gray-50)">
                            <tr>
                                <th>Stock ID</th><th>Brand</th><th>Category</th><th>Model #</th><th>Part #</th><th>Qty</th><th>Total Price</th><th>Unit Price</th>
                                <th class="col-highlight">Adjust Price</th>
                                <th class="col-highlight">Landing Factor</th>
                                <th>Adj. Price</th><th>Qty &times; Adj</th><th>List Price</th><th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>ABC123</td><td>Brand</td><td>Cat</td><td>MDL</td><td>PRT</td><td>10</td><td>5,000</td><td>500</td>
                                <td class="col-highlight">550.00</td>
                                <td class="col-highlight">1.20</td>
                                <td>660</td><td>6,600</td><td>700</td><td>FAST</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="form-group">
                    <label for="csvFile" style="font-weight:600;font-size:var(--text-sm)">Select CSV file</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="csvFile" accept=".csv, text/csv">
                        <label class="custom-file-label" for="csvFile">Choose file&hellip;</label>
                    </div>
                </div>

                <div id="fileInfo" class="file-info-bar mt-2" style="display:none">
                    <i class="fas fa-file-csv" style="color:var(--color-success)"></i>
                    <span id="fileName"></span> &mdash;
                    <span id="fileSize"></span> &mdash;
                    <span id="fileRows"></span> rows
                </div>

                <div id="uploadProgress" style="display:none" class="upload-progress-bar mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span style="font-size:var(--text-sm);font-weight:600;color:var(--color-gray-700)"><i class="fas fa-spinner fa-spin" style="margin-right:6px"></i>Processing&hellip;</span>
                        <span class="timer-display" id="timer">00:00</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width:0%" id="uploadProgressBar"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small id="uploadStatus" style="font-size:var(--text-xs);color:var(--color-gray-500)">Initializing&hellip;</small>
                        <small id="cancelImport" style="font-size:var(--text-xs);color:var(--color-danger);cursor:pointer;font-weight:500"><i class="fas fa-xmark" style="margin-right:3px"></i>Cancel</small>
                    </div>
                    <div id="uploadStats" class="upload-stats" style="display:none">
                        <div class="upload-stat">
                            <div class="upload-stat-val" id="processedRows">0</div>
                            <div class="upload-stat-label">Processed</div>
                        </div>
                        <div class="upload-stat">
                            <div class="upload-stat-val" id="successRows" style="color:var(--color-success)">0</div>
                            <div class="upload-stat-label">Updated</div>
                        </div>
                        <div class="upload-stat">
                            <div class="upload-stat-val" id="errorRows" style="color:var(--color-danger)">0</div>
                            <div class="upload-stat-label">Errors</div>
                        </div>
                    </div>
                </div>

                <div id="importPreview" style="display:none" class="mt-3">
                    <h6 style="font-weight:600;font-size:var(--text-sm);color:var(--color-gray-700);margin-bottom:var(--space-3)">Preview of changes</h6>
                    <div style="max-height:280px;overflow-y:auto;border:1px solid var(--color-gray-200);border-radius:var(--radius-md)">
                        <table class="table table-sm table-bordered mb-0" id="previewTable" style="font-size:var(--text-sm)">
                            <thead style="background:var(--color-gray-50);position:sticky;top:0;z-index:2">
                                <tr>
                                    <th>Stock ID</th>
                                    <th>Current Price</th>
                                    <th>New Price</th>
                                    <th style="text-align:center">Change</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary-custom" data-dismiss="modal"><i class="fas fa-xmark"></i> Cancel</button>
                <button type="button" class="btn-primary-custom" id="processImport" disabled><i class="fas fa-play"></i> Update Prices</button>
            </div>
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════
     IMPORT RESULTS MODAL
     ════════════════════════════════════════ -->
<div class="modal fade" id="importResultsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-circle-check" style="color:var(--color-success);margin-right:8px"></i>Import Complete</h5>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <div style="display:flex;justify-content:center;gap:var(--space-8);margin:var(--space-4) 0 var(--space-6)">
                    <div>
                        <div class="result-number result-number--success" id="successCount">0</div>
                        <div style="font-size:var(--text-sm);color:var(--color-gray-500);margin-top:2px">Updated</div>
                    </div>
                    <div>
                        <div class="result-number result-number--error" id="errorCount">0</div>
                        <div style="font-size:var(--text-sm);color:var(--color-gray-500);margin-top:2px">Failed</div>
                    </div>
                </div>
                <div id="errorDetails" style="display:none;text-align:left">
                    <h6 style="font-weight:600;font-size:var(--text-sm);color:var(--color-danger);margin-bottom:var(--space-2)">Error Details</h6>
                    <div style="max-height:180px;overflow-y:auto;border:1px solid var(--color-gray-200);border-radius:var(--radius-md)">
                        <table class="table table-sm table-bordered mb-0" style="font-size:var(--text-sm)">
                            <thead style="background:var(--color-gray-50)"><tr><th>Stock ID</th><th>Error</th></tr></thead>
                            <tbody id="errorList"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-primary-custom" data-dismiss="modal"><i class="fas fa-check"></i> Done</button>
            </div>
        </div>
    </div>
</div>


<!-- Toast container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Keyboard hint -->
<div class="kbd-hint"><kbd>Ctrl</kbd>+<kbd>I</kbd> to import</div>

<!-- Footer -->
<footer class="page-footer">
    Powered by <strong>Compresol Technologies</strong>
</footer>


<!-- ════════════════════════════════════════
     SCRIPTS
     ════════════════════════════════════════ -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    var allData = [];
    var allDataMap = {};
    var currentFilter = 'non-zero';
    var currentStatusFilter = 'all';
    var isCalculatingPrices = false;

    var customUnitPrices = {};
    var landingFactors = {};
    var originalPrices = {};
    var originalFactors = {};
    var autoSaveTimeout = null;

    // Import state
    var importStartTime = null;
    var importTimerInterval = null;
    var importCancelled = false;
    var importResults = { success: [], errors: [] };

    // ── Utility functions ──────────────────────────
    function numberFormat(number) {
        if (number === null || number === undefined || isNaN(number)) return '0.00';
        return parseFloat(number).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    function formatTime(seconds) {
        var mins = Math.floor(seconds / 60);
        var secs = Math.floor(seconds % 60);
        return (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
    }

    function calculateTotalQty(row) {
        if (row && row.total_qty !== undefined && row.total_qty !== null) {
            return parseFloat(row.total_qty) || 0;
        }
        return (parseFloat(row.qtyHO) || 0) +
            (parseFloat(row.qtyMT) || 0) +
            (parseFloat(row.qtySR) || 0) +
            (parseFloat(row.qtyOS) || 0) +
            (parseFloat(row.qtyVSR) || 0) +
            (parseFloat(row.qtyWS) || 0);
    }

    function getActivityDate(row) {
        return row.latest_outward_date || row.latest_trandate || null;
    }

    function getPriceCoverageLabel(row) {
        var status = row.price_status || '';
        if (status === 'MISSING_COST') return 'Missing cost for on-hand quantity';
        if (status === 'PARTIAL_COST_COVERAGE') {
            return 'Partial cost coverage: ' + numberFormat(row.unpriced_quantity || 0) + ' units unpriced';
        }
        if (row.price_source === 'BPITEM_FALLBACK') return 'BP item fallback price';
        return '';
    }

    function hasDisplayOverride(stockId) {
        return customUnitPrices[stockId] !== undefined || landingFactors[stockId] !== undefined;
    }

    // Valuation from API row only (matches igp_parchi / index.php; comparable to crosssection2 DB pricing)
    function getServerEffectiveUnitPrice(row) {
        var up = parseFloat(row.weighted_unit_price) || 0;
        var adj = parseFloat(row.adjust_unit_price) || 0;
        return up > 0 ? up : adj;
    }
    function getServerLandingFactor(row) {
        return parseFloat(row.landing_factor) || 1;
    }
    function getServerItemValue(row) {
        var totalQty = calculateTotalQty(row);
        if (totalQty > 0 && row.total_bpitems_price !== undefined) {
            return parseFloat(row.total_bpitems_price) || 0;
        }
        return totalQty * getServerEffectiveUnitPrice(row) * getServerLandingFactor(row);
    }
    // Pending input overrides (table columns only, not card totals)
    function getDisplayEffectiveUnitPrice(row, sid) {
        if (customUnitPrices[sid] !== undefined) {
            return parseFloat(customUnitPrices[sid]) || 0;
        }
        var up = parseFloat(row.weighted_unit_price) || 0;
        var ap = parseFloat(row.adjust_unit_price) || 0;
        return up > 0 ? up : (parseFloat(ap) || 0);
    }
    function getDisplayLandingFactor(row, sid) {
        return landingFactors[sid] !== undefined
            ? landingFactors[sid]
            : (parseFloat(row.landing_factor) || 1);
    }
    function getDisplayAdjustedUnitPrice(row, sid) {
        if (!hasDisplayOverride(sid)) {
            var totalQty = calculateTotalQty(row);
            return totalQty > 0
                ? (parseFloat(row.total_bpitems_price) || 0) / totalQty
                : 0;
        }
        return getDisplayEffectiveUnitPrice(row, sid) * getDisplayLandingFactor(row, sid);
    }
    function getDisplayItemValue(row, sid) {
        if (!hasDisplayOverride(sid)) {
            return parseFloat(row.total_bpitems_price) || 0;
        }
        return calculateTotalQty(row) * getDisplayAdjustedUnitPrice(row, sid);
    }

    function syncItemFieldFromServer(stockId, field, value) {
        var item = allDataMap[stockId];
        if (!item) return;
        if (field === 'adjust_unit_price') {
            item.adjust_unit_price = parseFloat(value) || 0;
            if (item.adjust_unit_price > 0) item.has_manual_price = true;
        } else if (field === 'landing_factor') {
            item.landing_factor = parseFloat(value) || 1;
        }
        updateStatusStatistics(allData);
    }

    function getStockStatus(dateString) {
        if (!dateString) return { status: 'EXTREMELY DEAD', cssClass: 'status-badge--extreme', icon: 'fa-ban', days: null };
        var today = new Date();
        var transactionDate = new Date(dateString);
        if (isNaN(transactionDate.getTime())) {
            return { status: 'EXTREMELY DEAD', cssClass: 'status-badge--extreme', icon: 'fa-ban', days: null };
        }
        var diffDays = Math.max(0, Math.floor((today - transactionDate) / (1000 * 60 * 60 * 24)));

        if (diffDays <= 180) return { status: 'FAST MOVING', cssClass: 'status-badge--fast', icon: 'fa-arrow-trend-up', days: diffDays };
        if (diffDays <= 360) return { status: 'SLOW MOVING', cssClass: 'status-badge--slow', icon: 'fa-clock-rotate-left', days: diffDays };
        if (diffDays <= 1000) return { status: 'DEAD STOCK', cssClass: 'status-badge--dead', icon: 'fa-triangle-exclamation', days: diffDays };
        return { status: 'EXTREMELY DEAD', cssClass: 'status-badge--extreme', icon: 'fa-ban', days: diffDays };
    }

    // ── Toast notifications ────────────────────────
    function showNotification(message, type) {
        type = type || 'info';
        var iconMap = { success: 'fa-circle-check', error: 'fa-circle-xmark', info: 'fa-circle-info' };
        var icon = iconMap[type] || iconMap.info;

        var $toast = $('<div class="toast-item toast-item--' + type + '">' +
            '<i class="fas ' + icon + ' toast-icon"></i>' +
            '<span>' + message + '</span></div>');

        $('#toastContainer').append($toast);

        setTimeout(function() {
            $toast.addClass('leaving');
            setTimeout(function() { $toast.remove(); }, 200);
        }, 3500);
    }

    // ── Row index map for O(1) lookups ─────────────
    var rowIndexMap = {};
    function rebuildRowIndexMap() {
        rowIndexMap = {};
        datatable.rows().every(function(idx) {
            var d = this.data();
            if (d) rowIndexMap[d.stockid] = idx;
        });
    }

    // ── Row calculation update ─────────────────────
    function updateRowCalculations(stockId) {
        var idx = rowIndexMap[stockId];
        if (idx === undefined) return;
        var row = datatable.row(idx);
        var rowNode = row.node();
        if (!rowNode) return;
        var rowData = row.data();
        var adjustedPrice = getDisplayAdjustedUnitPrice(rowData, stockId);
        var qtyTimesAdjustedPrice = getDisplayItemValue(rowData, stockId);

        $(rowNode).find('td:eq(10)').html('<span class="text-tabular">' + numberFormat(adjustedPrice) + '</span>');
        $(rowNode).find('td:eq(11)').html('<span class="text-tabular" style="font-weight:600;color:var(--color-gray-900)">' + numberFormat(qtyTimesAdjustedPrice) + '</span>');
    }

    // ── Auto-save ──────────────────────────────────
    function autoSaveToDatabase(stockId, field, value, isBatch) {
        if (isBatch) {
            $.ajax({
                type: 'POST', url: 'save_parchino.php',
                data: { action: 'save_single', stockid: stockId, field: field, value: value },
                dataType: 'json',
                success: function(r) {
                    if (r.status === 'success') {
                        if (field === 'adjust_unit_price') { originalPrices[stockId] = value; syncItemFieldFromServer(stockId, field, value); }
                        else if (field === 'landing_factor') { originalFactors[stockId] = value; syncItemFieldFromServer(stockId, field, value); }
                    }
                },
                error: function(x, s, e) { console.error('Auto-save error for ' + stockId + ':', e); }
            });
        } else {
            if (autoSaveTimeout) clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(function() {
                $.ajax({
                    type: 'POST', url: 'save_parchino.php',
                    data: { action: 'save_single', stockid: stockId, field: field, value: value },
                    dataType: 'json',
                    success: function(r) {
                        if (r.status === 'success') {
                            if (field === 'adjust_unit_price') { originalPrices[stockId] = value; syncItemFieldFromServer(stockId, field, value); showNotification('Saved price for ' + stockId, 'success'); }
                            else if (field === 'landing_factor') { originalFactors[stockId] = value; syncItemFieldFromServer(stockId, field, value); showNotification('Saved factor for ' + stockId, 'success'); }
                        } else {
                            showNotification('Failed to save: ' + r.message, 'error');
                        }
                    },
                    error: function(x, s, e) { showNotification('Auto-save error: ' + e, 'error'); }
                });
            }, 1000);
        }
    }

    // ── Load saved customizations ──────────────────
    function loadSavedCustomizations() {
        $.ajax({
            type: 'GET', url: 'save_parchino.php', data: { action: 'get' }, dataType: 'json',
            success: function(r) {
                if (r.status === 'success' && r.data) {
                    var keys = Object.keys(r.data);
                    for (var k = 0; k < keys.length; k++) {
                        var stockId = keys[k];
                        var d = r.data[stockId];
                        if (d.adjust_unit_price !== 0) { customUnitPrices[stockId] = d.adjust_unit_price; originalPrices[stockId] = d.adjust_unit_price; }
                        if (d.landing_factor !== 1) { landingFactors[stockId] = d.landing_factor; originalFactors[stockId] = d.landing_factor; }
                    }
                    applyFilters();
                    updateStatusStatistics(allData);
                    showNotification('Loaded ' + keys.length + ' saved customizations', 'info');
                }
            },
            error: function() { console.error('Failed to load saved customizations'); }
        });
    }

    // ── Statistics (single-pass) ─────────────────────
    var statsDebounceTimer = null;
    function updateStatusStatistics(data) {
        // Debounce rapid calls (e.g. during import)
        if (statsDebounceTimer) clearTimeout(statsDebounceTimer);
        statsDebounceTimer = setTimeout(function() { _computeStats(data); }, 50);
    }
    function _computeStats(data) {
        var running = 0, slow = 0, dead = 0, extremelyDead = 0;
        var runningSum = 0, slowSum = 0, deadSum = 0, extremelyDeadSum = 0;
        var priceZero = 0, priceZeroOnStock = 0, priceZeroNotOnStock = 0, priceZeroOnStockValue = 0;
        var priceAdj = 0, priceAdjOnStock = 0, priceAdjNotOnStock = 0, priceAdjOnStockValue = 0;
        var priceOrig = 0, priceOrigOnStock = 0, priceOrigNotOnStock = 0, priceOrigOnStockValue = 0;
        var today = Date.now();

        for (var i = 0, len = data.length; i < len; i++) {
            var item = data[i];
            var sid = item.stockid;
            var totalQty = calculateTotalQty(item);

            // Shared price computation — DB row only (same source as index.php / crosssection2 igp_parchi)
            var effectivePrice = getServerEffectiveUnitPrice(item);
            var itemValue = getServerItemValue(item);

            // Stock movement cards (in-stock only)
            if (totalQty > 0) {
                var d = getActivityDate(item);
                var movementTimestamp = d ? new Date(d).getTime() : NaN;
                var diffDays = isNaN(movementTimestamp)
                    ? 99999
                    : Math.max(0, Math.floor((today - movementTimestamp) / 86400000));
                if (diffDays <= 180) { running++; runningSum += itemValue; }
                else if (diffDays <= 360) { slow++; slowSum += itemValue; }
                else if (diffDays <= 1000) { dead++; deadSum += itemValue; }
                else { extremelyDead++; extremelyDeadSum += itemValue; }
            }

            // Pricing coverage cards (has_manual_price from index.php / igp_parchi)
            var pHasManual = !!item.has_manual_price;
            var hasAnyPrice = effectivePrice > 0;

            if (!hasAnyPrice) {
                priceZero++;
                if (totalQty > 0) { priceZeroOnStock++; priceZeroOnStockValue += itemValue; }
                else { priceZeroNotOnStock++; }
            } else if (pHasManual) {
                priceAdj++;
                if (totalQty > 0) { priceAdjOnStock++; priceAdjOnStockValue += itemValue; }
                else { priceAdjNotOnStock++; }
            } else {
                priceOrig++;
                if (totalQty > 0) { priceOrigOnStock++; priceOrigOnStockValue += itemValue; }
                else { priceOrigNotOnStock++; }
            }
        }

        $('#runningCount').text(running);
        $('#slowCount').text(slow);
        $('#deadCount').text(dead);
        $('#extremelyDeadCount').text(extremelyDead);
        $('#runningSum').text(numberFormat(runningSum));
        $('#slowSum').text(numberFormat(slowSum));
        $('#deadSum').text(numberFormat(deadSum));
        $('#extremelyDeadSum').text(numberFormat(extremelyDeadSum));
        $('#priceZeroTotal').text(priceZero);
        $('#priceZeroOnStock').text(priceZeroOnStock);
        $('#priceZeroOnStockValue').text(numberFormat(priceZeroOnStockValue));
        $('#priceZeroNotOnStock').text(priceZeroNotOnStock);
        $('#priceAdjTotal').text(priceAdj);
        $('#priceAdjOnStock').text(priceAdjOnStock);
        $('#priceAdjOnStockValue').text(numberFormat(priceAdjOnStockValue));
        $('#priceAdjNotOnStock').text(priceAdjNotOnStock);
        $('#priceOrigTotal').text(priceOrig);
        $('#priceOrigOnStock').text(priceOrigOnStock);
        $('#priceOrigOnStockValue').text(numberFormat(priceOrigOnStockValue));
        $('#priceOrigNotOnStock').text(priceOrigNotOnStock);
    }

    // ── CSV Export ──────────────────────────────────
    function exportToCSV(dt, filteredOnly) {
        try {
            var data = filteredOnly ? dt.rows({ search: 'applied' }).data() : dt.rows().data();
            var headers = ['Stock ID','Brand','Category','Model #','Part #','Qty','Total Price','Unit Price','Adjust Unit Price','Landing Factor','Adjusted Price After Multiplication','Qty × Adjusted Price','List Price','Stock Status','Cost Coverage','Price Status','Price Source','Latest Outward Date'];
            var csvContent = headers.join(',') + '\n';

            for (var i = 0; i < data.length; i++) {
                var row = data[i];
                var rd = [];
                rd.push('"' + (row.stockid || '') + '"');
                rd.push('"' + (row.manufacturers_name || '') + '"');
                rd.push('"' + (row.categorydescription || '') + '"');
                rd.push('"' + (row.mnfCode || '') + '"');
                rd.push('"' + (row.mnfpno || '') + '"');
                var totalQty = calculateTotalQty(row);
                rd.push(totalQty);
                rd.push(parseFloat(row.total_bpitems_price || 0).toFixed(2));
                rd.push(parseFloat(row.weighted_unit_price || 0).toFixed(2));
                var apDb = parseFloat(row.adjust_unit_price) || 0;
                rd.push(apDb > 0 ? apDb.toFixed(2) : '');
                var fct = getServerLandingFactor(row);
                rd.push(parseFloat(fct).toFixed(2));
                var adj = getDisplayAdjustedUnitPrice(row, row.stockid);
                rd.push(adj.toFixed(2));
                rd.push(getDisplayItemValue(row, row.stockid).toFixed(2));
                rd.push(parseFloat(row.materialcost || 0).toFixed(2));
                var st = getStockStatus(getActivityDate(row));
                rd.push('"' + st.status + '"');
                rd.push((parseFloat(row.price_coverage_percent || 0)).toFixed(2) + '%');
                rd.push('"' + (row.price_status || 'MISSING_COST') + '"');
                rd.push('"' + (row.price_source || 'NONE') + '"');
                rd.push('"' + (getActivityDate(row) || '') + '"');
                csvContent += rd.join(',') + '\n';
            }

            var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            var link = document.createElement('a');
            var url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', (filteredOnly ? 'inventory_filtered_' : 'inventory_all_') + new Date().toISOString().slice(0,10) + '.csv');
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            showNotification('CSV downloaded — ' + data.length + ' records', 'success');
        } catch (error) {
            showNotification('Export error: ' + error.message, 'error');
        }
    }

    // ── DataTable ──────────────────────────────────
    var datatable = $('#datatable').DataTable({
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [],
        deferRender: true,
        lengthMenu: [[10, 25, 50, 100, -1], ["10", "25", "50", "100", "All"]],
        pageLength: 25,
        order: [[0, 'asc']],
        language: {
            search: "",
            searchPlaceholder: "Search products, brands, categories\u2026",
            lengthMenu: "Show _MENU_",
            info: "Showing _START_\u2013_END_ of _TOTAL_",
            infoEmpty: "No records",
            zeroRecords: "No matching records found"
        },
        columns: [
            {
                data: "stockid",
                className: "col-stockid",
                render: function(data, type) {
                    if (type === 'display') {
                        var safe = $('<span>').text(data).html();
                        return '<a href="../SelectProduct.php?Select=' + encodeURIComponent(data) + '" target="_blank">' + safe + '</a>';
                    }
                    return data;
                }
            },
            { data: "manufacturers_name", render: function(d) { return d || ''; } },
            { data: "categorydescription", className: "text-muted", render: function(d) { return d || ''; } },
            {
                data: "mnfCode",
                render: function(d, type) {
                    if (type === 'display') return d || '<span style="color:var(--color-gray-300)">\u2014</span>';
                    return d || '';
                }
            },
            {
                data: "mnfpno",
                render: function(d, type) {
                    if (type === 'display') return d || '<span style="color:var(--color-gray-300)">\u2014</span>';
                    return d || '';
                }
            },
            {
                data: null, className: "col-center",
                render: function(data, type, row) {
                    var q = calculateTotalQty(row);
                    if (type === 'display') return q > 0 ? '<span class="qty-badge qty-badge--positive">' + q + '</span>' : '<span class="qty-badge qty-badge--zero">0</span>';
                    return q || 0;
                }
            },
            {
                data: "total_bpitems_price", className: "col-numeric text-tabular",
                render: function(d, type, row) {
                    var value = type === 'display' ? numberFormat(d || 0) : (d || 0);
                    var label = type === 'display' ? getPriceCoverageLabel(row) : '';
                    return label
                        ? value + ' <span class="price-warning" title="' + label + '" aria-label="' + label + '">&#9888;</span>'
                        : value;
                }
            },
            {
                data: "weighted_unit_price", className: "col-numeric text-tabular",
                render: function(d, type) { return type === 'display' ? numberFormat(d || 0) : (d || 0); }
            },
            {
                data: null, className: "col-center",
                render: function(data, type, row) {
                    var sid = row.stockid;
                    var fromDb = parseFloat(row.adjust_unit_price) || 0;
                    var cp = customUnitPrices[sid] !== undefined ? customUnitPrices[sid] : (fromDb > 0 ? fromDb : '');
                    var op = parseFloat(row.weighted_unit_price) || 0;
                    if (type === 'display') {
                        return '<input type="number" class="inline-input unit-price-input' +
                            (cp !== '' && cp !== op ? ' edited' : '') +
                            '" data-stockid="' + sid + '" data-original="' + op +
                            '" value="' + cp + '" min="0" step="0.01" placeholder="\u2014">';
                    }
                    return cp !== '' ? cp : '';
                }
            },
            {
                data: null, className: "col-center",
                render: function(data, type, row) {
                    var sid = row.stockid;
                    var f = landingFactors[sid] !== undefined
                        ? landingFactors[sid]
                        : (parseFloat(row.landing_factor) || 1);
                    if (type === 'display') {
                        return '<input type="number" class="inline-input landing-factor-input' +
                            (f !== 1 ? ' edited' : '') +
                            '" data-stockid="' + sid + '" value="' + f + '" min="0.01" step="0.01">';
                    }
                    return f;
                }
            },
            {
                data: null, className: "col-numeric text-tabular",
                render: function(data, type, row) {
                    var sid = row.stockid;
                    var val = getDisplayAdjustedUnitPrice(row, sid);
                    return type === 'display' ? numberFormat(val) : (val || 0);
                }
            },
            {
                data: null, className: "col-numeric text-tabular",
                render: function(data, type, row) {
                    var sid = row.stockid;
                    var val = getDisplayItemValue(row, sid);
                    return type === 'display' ? '<strong>' + numberFormat(val) + '</strong>' : (val || 0);
                }
            },
            {
                data: "materialcost", className: "col-numeric text-tabular",
                render: function(d, type) { return type === 'display' ? numberFormat(d || 0) : (d || 0); }
            },
            {
                data: null, className: "col-center",
                render: function(data, type, row) {
                    var d = getActivityDate(row);
                    var s = getStockStatus(d);
                    if (type === 'sort') return d ? ((new Date()) - (new Date(d))) : 999999;
                    if (type === 'display') {
                        var title = d ? 'Latest outward: ' + d : 'No outward movement recorded';
                        return '<span class="status-badge ' + s.cssClass + '" title="' + title + '"><i class="fas ' + s.icon + '"></i> ' + s.status + '</span>';
                    }
                    return s.status;
                }
            }
        ],
        initComplete: function() { loadData(); }
    });

    // ── Input handlers ─────────────────────────────
    $('#datatable tbody').on('input', '.unit-price-input', function() {
        var $input = $(this);
        var stockId = $input.data('stockid');
        var originalPrice = parseFloat($input.data('original')) || 0;
        var inputValue = $input.val();
        var newPrice = inputValue === '' ? '' : (parseFloat(inputValue) || 0);

        if (newPrice === '') { delete customUnitPrices[stockId]; $input.removeClass('edited'); }
        else if (newPrice !== originalPrice) { customUnitPrices[stockId] = newPrice; $input.addClass('edited'); }
        else { delete customUnitPrices[stockId]; $input.removeClass('edited'); }

        updateRowCalculations(stockId);
        updateCustomizationsCount();
    });

    $('#datatable tbody').on('blur', '.unit-price-input', function() {
        var $input = $(this);
        var stockId = $input.data('stockid');
        var val = $input.val();
        if (val === '') { autoSaveToDatabase(stockId, 'adjust_unit_price', 0, false); }
        else {
            var n = parseFloat(val) || 0;
            if (n < 0) { $input.val(0); n = 0; }
            autoSaveToDatabase(stockId, 'adjust_unit_price', n, false);
        }
        updateRowCalculations(stockId);
    });

    $('#datatable tbody').on('input', '.landing-factor-input', function() {
        var $input = $(this);
        var stockId = $input.data('stockid');
        var nf = parseFloat($input.val()) || 1;
        if (nf < 0.01) { $input.val(0.01); landingFactors[stockId] = 0.01; }
        else { landingFactors[stockId] = nf; }
        $input.toggleClass('edited', nf !== 1);
        updateRowCalculations(stockId);
        updateCustomizationsCount();
    });

    $('#datatable tbody').on('blur', '.landing-factor-input', function() {
        var $input = $(this);
        var stockId = $input.data('stockid');
        var v = parseFloat($input.val()) || 1;
        if (v < 0.01) { $input.val(0.01); v = 0.01; }
        autoSaveToDatabase(stockId, 'landing_factor', v, false);
        updateRowCalculations(stockId);
    });

    // ── Customizations badge ───────────────────────
    function updateCustomizationsCount() {
        var cp = Object.keys(customUnitPrices).length;
        var fc = Object.keys(landingFactors).length;
        var $area = $('#customizationsArea');
        $area.empty();
        if (cp > 0 || fc > 0) {
            $area.html('<span style="font-size:var(--text-sm);font-weight:500;color:var(--color-gray-500)">' +
                '<i class="fas fa-tag" style="margin-right:4px;color:var(--color-primary)"></i>' +
                cp + ' custom price' + (cp !== 1 ? 's' : '') + ', ' +
                fc + ' factor' + (fc !== 1 ? 's' : '') + '</span>');
        }
    }

    // ── Filter handlers ────────────────────────────
    $('.qty-filter-btn').on('click', function() {
        var filter = $(this).data('filter');
        showFilterLoading(getFilterText(filter));
        $('.qty-filter-btn').addClass('loading');
        var self = this;
        setTimeout(function() {
            $('.qty-filter-btn').removeClass('active');
            $(self).addClass('active');
            currentFilter = filter;
            applyFilters();
            $('.qty-filter-btn').removeClass('loading');
        }, 200);
    });

    function getFilterText(filter) {
        if (filter === 'non-zero') return 'Filtering in-stock items\u2026';
        if (filter === 'zero') return 'Filtering out-of-stock items\u2026';
        if (filter === 'both') return 'Showing all items\u2026';
        return 'Applying filter\u2026';
    }

    function showFilterLoading(msg) {
        $('#filterLoadingText').text(msg);
        $('#filterLoadingOverlay').fadeIn(200);
    }
    function hideFilterLoading() { $('#filterLoadingOverlay').fadeOut(200); }

    function applyFilters() {
        var filteredData = allData;
        if (currentFilter === 'non-zero') filteredData = filteredData.filter(function(i) { return calculateTotalQty(i) > 0; });
        else if (currentFilter === 'zero') filteredData = filteredData.filter(function(i) { return calculateTotalQty(i) === 0; });
        datatable.clear();
        datatable.rows.add(filteredData).draw();
        rebuildRowIndexMap();
        updateFilterCounts();
        hideFilterLoading();
    }

    // ── Data loading ───────────────────────────────
    function loadData() {
        isCalculatingPrices = true;
        $('#loadingOverlay').fadeIn(200);
        $.ajax({
            type: 'GET', url: 'index.php', dataType: 'json',
            success: function(response) {
                isCalculatingPrices = false;
                if (response.status === 'success' && response.data) {
                    allData = response.data;
                    customUnitPrices = {};
                    landingFactors = {};
                    originalPrices = {};
                    originalFactors = {};
                    // Build lookup map for fast stock ID searches
                    allDataMap = {};
                    for (var mi = 0; mi < allData.length; mi++) {
                        allDataMap[allData[mi].stockid] = allData[mi];
                    }
                    updateStatusStatistics(allData);
                    applyFilters();
                    var wp = allData.filter(function(i) { return i.total_bpitems_price > 0; }).length;
                    showNotification('Loaded ' + response.count + ' products (' + wp + ' with price data)', 'success');
                    loadSavedCustomizations();
                    updateCustomizationsCount();
                    $('#loadingOverlay').fadeOut(200);
                } else {
                    showNotification('Error: ' + (response.error || 'Unable to load data'), 'error');
                    $('#loadingOverlay').fadeOut(200);
                }
            },
            error: function(xhr, status) {
                isCalculatingPrices = false;
                $('#loadingOverlay').fadeOut(200);
                showNotification(status === 'timeout' ? 'Request timed out' : 'Failed to load data', 'error');
            }
        });
    }

    function updateFilterCounts() {
        var nz = 0, z = 0;
        for (var i = 0; i < allData.length; i++) {
            if (calculateTotalQty(allData[i]) > 0) nz++; else z++;
        }
        $('#nonZeroCount').text(nz);
        $('#zeroCount').text(z);
        $('#bothCount').text(allData.length);
    }

    // ── Export buttons ─────────────────────────────
    $('#btnDownloadCSV').on('click', function() {
        showNotification('Preparing filtered CSV\u2026', 'info');
        setTimeout(function() { exportToCSV(datatable, true); }, 100);
    });
    $('#btnDownloadAll').on('click', function() {
        showNotification('Preparing full CSV\u2026', 'info');
        setTimeout(function() { exportToCSV(datatable, false); }, 100);
    });

    // ── Import collapse toggle ─────────────────────
    $('#importPanelBody').on('show.bs.collapse', function() {
        $(this).closest('.import-panel').find('.import-panel-toggle').removeClass('collapsed');
    });
    $('#importPanelBody').on('hide.bs.collapse', function() {
        $(this).closest('.import-panel').find('.import-panel-toggle').addClass('collapsed');
    });

    // ── Keyboard shortcut ──────────────────────────
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && e.key === 'i') { e.preventDefault(); $('#importModal').modal('show'); }
    });

    // ════════════════════════════════════════════════
    //  IMPORT FUNCTIONALITY
    // ════════════════════════════════════════════════

    $('#csvFile').on('change', function() {
        var file = this.files[0];
        if (!file) {
            $('#processImport').prop('disabled', true);
            $('#importPreview').hide();
            $('#fileInfo').hide();
            return;
        }
        $(this).next('.custom-file-label').html(file.name);
        $('#fileName').text(file.name);
        $('#fileSize').text((file.size / 1024).toFixed(2) + ' KB');
        $('#fileInfo').show();
        $('#processImport').prop('disabled', false);
        parseCSVFile(file);
    });

    $('#cancelImport').on('click', function() {
        if (confirm('Cancel import?')) {
            importCancelled = true;
            resetImportDisplay();
            showNotification('Import cancelled', 'error');
        }
    });

    function resetImportDisplay() {
        $('#uploadProgress').hide();
        $('#importPreview').show();
        $('#processImport').prop('disabled', false);
        $('#csvFile').prop('disabled', false);
        $('#uploadStats').hide();
        $('#timer').text('00:00');
        $('#uploadProgressBar').css('width', '0%');
        $('#uploadStatus').text('Processing\u2026');
        if (importTimerInterval) { clearInterval(importTimerInterval); importTimerInterval = null; }
    }

    function parseCSVFile(file) {
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(e) {
            var content = e.target.result;
            var lines = content.split('\n');
            var dataRows = lines.filter(function(l, idx) { return idx > 0 && l.trim() && !l.startsWith('#'); }).length;
            $('#fileRows').text(dataRows);
            $('#previewTable tbody').empty();

            var headers = lines[0].split(',').map(function(h) { return h.trim().replace(/["']/g, ''); });
            var stockIdIndex = -1, adjustPriceIndex = -1, landingFactorIndex = -1;
            for (var h = 0; h < headers.length; h++) {
                var hl = headers[h].toLowerCase();
                if (stockIdIndex === -1 && (hl.indexOf('stock id') !== -1 || hl === 'stockid' || hl === 'stock_id')) stockIdIndex = h;
                if (adjustPriceIndex === -1 && (hl.indexOf('adjust unit price') !== -1 || hl.indexOf('adjust') !== -1 || hl === 'adjust price')) adjustPriceIndex = h;
                if (landingFactorIndex === -1 && (hl.indexOf('landing factor') !== -1 || hl === 'landing_factor')) landingFactorIndex = h;
            }

            if (stockIdIndex === -1) {
                showNotification('Could not find "Stock ID" column', 'error');
                $('#importPreview').hide();
                $('#processImport').prop('disabled', true);
                return;
            }

            $('#previewTable tbody').append('<tr style="background:var(--color-info-bg)"><td colspan="5" style="text-align:center;font-size:var(--text-xs);font-weight:600;color:var(--color-gray-600)">Found: Stock ID \u2713 ' + (adjustPriceIndex !== -1 ? '| Adjust Price \u2713 ' : '| Adjust Price \u2717 ') + (landingFactorIndex !== -1 ? '| Landing Factor \u2713' : '| Landing Factor \u2717') + '</td></tr>');

            var changedCount = 0, factorChangedCount = 0, invalidCount = 0;
            var previewData = [];

            for (var i = 1; i < Math.min(lines.length, 21); i++) {
                var line = lines[i].trim();
                if (!line || line.startsWith('#')) continue;
                var values = parseCSVLine(line);
                if (values.length <= Math.max(stockIdIndex, adjustPriceIndex, landingFactorIndex)) continue;

                var stockId = (values[stockIdIndex] || '').trim().replace(/["']/g, '');
                var newPrice = '';
                if (adjustPriceIndex !== -1 && values[adjustPriceIndex]) {
                    var ps = values[adjustPriceIndex].trim().replace(/["']/g, '');
                    if (ps) { var pv = parseFloat(ps.replace(/[^0-9.-]/g, '')); if (!isNaN(pv)) newPrice = pv; }
                }
                var newFactor = '';
                if (landingFactorIndex !== -1 && values[landingFactorIndex]) {
                    var fs = values[landingFactorIndex].trim().replace(/["']/g, '');
                    if (fs) { var fv = parseFloat(fs.replace(/[^0-9.-]/g, '')); if (!isNaN(fv)) newFactor = fv; }
                }

                var rowStatus = '', statusStyle = '', currentPrice = '', currentFactor = '', changeIcon = '', changes = [];
                if (!stockId) {
                    rowStatus = 'Missing Stock ID'; statusStyle = 'color:var(--color-danger)'; invalidCount++;
                } else {
                    var stockItem = allDataMap[stockId] || null;
                    if (stockItem) {
                        currentPrice = parseFloat(stockItem.adjust_unit_price) || 0;
                        if (newPrice !== '' && parseFloat(newPrice) !== parseFloat(currentPrice)) { changes.push('Price'); changedCount++; }
                        currentFactor = parseFloat(stockItem.landing_factor) || 1;
                        if (newFactor !== '' && parseFloat(newFactor) !== parseFloat(currentFactor)) { changes.push('Factor'); factorChangedCount++; }
                        if (changes.length > 0) { rowStatus = 'Will update: ' + changes.join(' + '); statusStyle = 'color:var(--color-success);font-weight:600'; changeIcon = '\u2713'; }
                        else { rowStatus = 'No changes'; statusStyle = 'color:var(--color-gray-400)'; changeIcon = '\u2014'; }
                    } else { rowStatus = 'Not found'; statusStyle = 'color:var(--color-danger)'; invalidCount++; }
                }
                previewData.push({ stockId: stockId || '-', currentPrice: currentPrice !== '' ? numberFormat(currentPrice) : '-', newPrice: newPrice !== '' ? numberFormat(newPrice) : '\u2014', currentFactor: currentFactor !== '' ? parseFloat(currentFactor).toFixed(2) : '1.00', newFactor: newFactor !== '' ? parseFloat(newFactor).toFixed(2) : '\u2014', changeIcon: changeIcon, status: rowStatus, statusStyle: statusStyle });
            }

            $('#previewTable tbody').append('<tr style="background:var(--color-warning-bg)"><td colspan="5" style="text-align:center;font-size:var(--text-xs);font-weight:600;color:var(--color-gray-600)">' + changedCount + ' price changes, ' + factorChangedCount + ' factor changes, ' + invalidCount + ' errors (first 20 rows)</td></tr>');

            for (var p = 0; p < previewData.length; p++) {
                var pi = previewData[p];
                $('#previewTable tbody').append('<tr><td>' + pi.stockId + '</td><td>' + pi.currentPrice + '<br><small style="color:var(--color-gray-400)">Factor: ' + pi.currentFactor + '</small></td><td>' + pi.newPrice + '<br><small style="color:var(--color-gray-400)">Factor: ' + pi.newFactor + '</small></td><td style="text-align:center">' + pi.changeIcon + '</td><td style="' + pi.statusStyle + '">' + pi.status + '</td></tr>');
            }

            if (lines.length - 1 > 20) {
                $('#previewTable tbody').append('<tr><td colspan="5" style="text-align:center;color:var(--color-gray-400);font-size:var(--text-sm)">\u2026 ' + (lines.length - 1 - 20) + ' more rows</td></tr>');
            }

            $('#importPreview').show();
            $('#importModal').data('csvData', { stockIdIndex: stockIdIndex, priceIndex: adjustPriceIndex, factorIndex: landingFactorIndex, lines: lines, totalRows: lines.length - 1 });
            $('#processImport').prop('disabled', changedCount === 0 && factorChangedCount === 0);
            if (changedCount === 0 && factorChangedCount === 0 && lines.length > 1) showNotification('No changes detected in file', 'info');
        };
        reader.readAsText(file);
    }

    function parseCSVLine(line) {
        var result = [], current = '', inQuotes = false;
        for (var i = 0; i < line.length; i++) {
            var ch = line.charAt(i);
            if (ch === '"') inQuotes = !inQuotes;
            else if (ch === ',' && !inQuotes) { result.push(current); current = ''; }
            else current += ch;
        }
        result.push(current);
        return result;
    }

    function startImportTimer() {
        importStartTime = Date.now();
        importTimerInterval = setInterval(function() {
            if (importCancelled) return;
            $('#timer').text(formatTime((Date.now() - importStartTime) / 1000));
        }, 100);
    }

    function updateImportProgress(processed, total, success, errors) {
        var percent = Math.round((processed / total) * 100);
        $('#uploadProgressBar').css('width', percent + '%');
        $('#uploadStatus').html('Processed ' + processed + ' of ' + total);
        $('#processedRows').text(processed);
        $('#successRows').text(success);
        $('#errorRows').text(errors);
        $('#uploadStats').show();
    }

    // ── Process import (batched) ──────────────────
    $('#processImport').on('click', function() {
        var csvData = $('#importModal').data('csvData');
        if (!csvData) return;

        var stockIdIndex = csvData.stockIdIndex;
        var priceIndex = csvData.priceIndex;
        var factorIndex = csvData.factorIndex;
        var lines = csvData.lines;
        var totalRows = csvData.totalRows;

        importCancelled = false;
        importResults = { success: [], errors: [] };

        $('#processImport').prop('disabled', true);
        $('#csvFile').prop('disabled', true);
        $('#importPreview').hide();
        $('#uploadProgress').show();
        startImportTimer();

        // Collect all changes first (no AJAX yet)
        var batchPrices = {};
        var batchFactors = {};
        var processed = 0;

        for (var index = 1; index < lines.length; index++) {
            var line = lines[index].trim();
            if (!line || line.startsWith('#')) continue;

            var values = parseCSVLine(line);
            var stockId = (values[stockIdIndex] || '').trim().replace(/["']/g, '');
            if (!stockId) continue;

            var newPrice = null;
            if (priceIndex !== -1 && values[priceIndex]) {
                var ps = values[priceIndex].trim().replace(/["']/g, '');
                if (ps) { var pv = parseFloat(ps.replace(/[^0-9.-]/g, '')); if (!isNaN(pv)) newPrice = pv; }
            }
            var newFactor = null;
            if (factorIndex !== -1 && values[factorIndex]) {
                var fs = values[factorIndex].trim().replace(/["']/g, '');
                if (fs) { var fv = parseFloat(fs.replace(/[^0-9.-]/g, '')); if (!isNaN(fv)) newFactor = fv; }
            }

            if (newPrice !== null) {
                var item = allDataMap[stockId];
                var cp = item ? (parseFloat(item.adjust_unit_price) || 0) : 0;
                if (parseFloat(newPrice) !== parseFloat(cp)) {
                    batchPrices[stockId] = newPrice;
                    customUnitPrices[stockId] = newPrice;
                }
            }
            if (newFactor !== null) {
                var itemF = allDataMap[stockId];
                var cf = itemF ? (parseFloat(itemF.landing_factor) || 1) : 1;
                if (parseFloat(newFactor) !== parseFloat(cf)) {
                    batchFactors[stockId] = newFactor;
                    landingFactors[stockId] = newFactor;
                }
            }
            processed++;
        }

        var totalChanges = Object.keys(batchPrices).length + Object.keys(batchFactors).length;
        if (totalChanges === 0) {
            if (importTimerInterval) { clearInterval(importTimerInterval); importTimerInterval = null; }
            resetImportDisplay();
            showNotification('No changes to import', 'info');
            return;
        }

        updateImportProgress(processed, totalRows, 0, 0);
        $('#uploadStatus').html('Saving ' + totalChanges + ' changes to database\u2026');

        // Single batch AJAX call using save_all endpoint
        $.ajax({
            type: 'POST',
            url: 'save_parchino.php',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'save_all', prices: batchPrices, factors: batchFactors }),
            dataType: 'json',
            timeout: 120000,
            success: function(r) {
                if (importTimerInterval) { clearInterval(importTimerInterval); importTimerInterval = null; }
                var sc = r.success_count || 0;
                var ec = r.error_count || 0;

                // Update original tracking
                var pKeys = Object.keys(batchPrices);
                for (var pi = 0; pi < pKeys.length; pi++) originalPrices[pKeys[pi]] = batchPrices[pKeys[pi]];
                var fKeys = Object.keys(batchFactors);
                for (var fi = 0; fi < fKeys.length; fi++) originalFactors[fKeys[fi]] = batchFactors[fKeys[fi]];

                updateImportProgress(processed, totalRows, sc, ec);
                $('#importModal').modal('hide');
                resetImportDisplay();
                $('#successCount').text(sc);
                $('#errorCount').text(ec);
                if (ec > 0 && r.errors) {
                    $('#errorDetails').show();
                    $('#errorList').empty();
                    for (var ei = 0; ei < r.errors.length; ei++) {
                        $('#errorList').append('<tr><td colspan="2">' + r.errors[ei] + '</td></tr>');
                    }
                } else { $('#errorDetails').hide(); }
                $('#importResultsModal').modal('show');
                loadData();
            },
            error: function(xhr, status, error) {
                if (importTimerInterval) { clearInterval(importTimerInterval); importTimerInterval = null; }
                resetImportDisplay();
                showNotification('Import failed: ' + (status === 'timeout' ? 'Request timed out' : error), 'error');
            }
        });
    });
});
</script>
</body>
</html>
