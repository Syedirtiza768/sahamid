(function () {
    'use strict';
    var config = window.SAHAMID_DIRECTORS || {};
    var root = document.getElementById('directorsRoot');
    if (!root) { return; }
    var overview = null, exploration = null, controller = null, requestNumber = 0;
    var fiscalStart = 1, state = readState(), risks = [], healthItems = [], periodDays = null, comparisonDays = null;
    var $ = function (id) { return document.getElementById(id); };
    var esc = function (value) { return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
    var own = function (o, key) { return Object.prototype.hasOwnProperty.call(o || {}, key); };
    var isNumber = function (v) { return v !== null && v !== undefined && v !== '' && isFinite(Number(v)); };
    var number = function (value, decimals) { return isNumber(value) ? Number(value).toLocaleString(undefined, { maximumFractionDigits: decimals == null ? 2 : decimals }) : '—'; };
    var shortNumber = function (v) {
        if (!isNumber(v)) { return '—'; }
        var a = Math.abs(Number(v)), sign = Number(v) < 0 ? '−' : '';
        if (a >= 1e7) { return sign + number(a / 1e7, 2) + ' Cr'; }
        if (a >= 1e5) { return sign + number(a / 1e5, 2) + ' Lakh'; }
        return number(v, a >= 1000 ? 0 : 2);
    };
    function validDate(v) { return /^\d{4}-\d{2}-\d{2}$/.test(v || '') && !isNaN(new Date(v + 'T00:00:00Z').getTime()); }
    function dateUTC(value) { return new Date(value + 'T00:00:00Z'); }
    function iso(date) { return date.toISOString().slice(0, 10); }
    function dayBefore(value) { var d = dateUTC(value); d.setUTCDate(d.getUTCDate() - 1); return iso(d); }
    function showDate(value) { return validDate((value || '').slice(0, 10)) ? dateUTC(value.slice(0, 10)).toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'UTC' }) : String(value || '—'); }
    function monthLabel(value) { return validDate((value || '').slice(0, 10)) ? dateUTC(value.slice(0, 10)).toLocaleDateString(undefined, { month: 'short', year: '2-digit', timeZone: 'UTC' }) : String(value || '—'); }
    function unit(metric) { return metric.unit === 'base' ? ((overview && overview.metadata.currency) || 'Base currency') : (metric.unit || ''); }
    function isSnapshot(metric) { return metric.kind === 'snapshot'; }
    function canInvestigate(metric) { return metric && (metric.available !== false || metric.can_investigate === true); }
    function dimsFor(metric) { return metric && metric.dimensions && !Array.isArray(metric.dimensions) ? metric.dimensions : {}; }
    function basis(metric) { return isSnapshot(metric) ? 'Current snapshot' : (metric.kind === 'budget' ? (metric.status === 'actual' ? 'Accounting-period actual' : 'Recorded budget') : 'Selected period'); }
    var plainTitles = { receivables: 'Receivables (net customer ledger)', payables: 'Payables (net supplier ledger)', cash_movement: 'Bank movement',
        pipeline: 'Open enquiry value (as entered)', inventory: 'Stock value (at standard cost)', purchase_commitments: 'Unreceived market-slip purchases',
        legacy_po_commitments: 'Unreceived purchase orders', procurement_pipeline: 'Planned procurement', stock_exceptions: 'Stock positions to review',
        pending_deliveries: 'Pending DC List (value)', gl_result: 'Net profit', gl_revenue: 'Revenue in the ledger (GL)' };
    function plainTitle(metric) { return plainTitles[metric.id] || metric.title; }
    var comparisonLabels = { yoy: 'vs same period last year', previous: 'vs previous equal period', mom: 'vs previous month', qoq: 'vs previous quarter', custom: 'vs comparison period' };
    function tag(metric) {
        var status = metric.available === false ? 'unavailable' : (metric.status || 'actual');
        var label = status === 'actual' ? 'Actual' : status === 'estimated' ? 'Estimated' : status === 'unavailable' ? 'Unavailable' : status;
        return '<span class="dir-tag dir-tag-' + esc(status.replace(/[^a-z]/g, '')) + '">' + esc(label) + '</span>';
    }
    function changeText(metric) {
        if (metric.available === false) { return esc(metric.error || 'Source unavailable'); }
        if (isSnapshot(metric)) { return 'Live balance · historical comparison unavailable'; }
        if (state.comparison === 'none') { return 'Comparison disabled'; }
        if (metric.comparison_complete === false && (Number(metric.invalid_amounts) > 0 || Number(metric.comparison_invalid_amounts) > 0)) { return 'Comparison incomplete · review excluded amounts'; }
        if (!isNumber(metric.previous)) { return 'Comparison unavailable'; }
        if (!isNumber(metric.delta)) { return 'Comparison unavailable'; }
        var delta = Number(metric.delta), points = metric.unit === '%', inDays = metric.unit === 'days';
        var good = metric.polarity ? ((delta > 0) === (Number(metric.polarity) > 0)) : null;
        var cls = delta === 0 || good === null ? 'dir-change-neutral' : (good ? 'dir-change-good' : 'dir-change-bad');
        var arrow = delta > 0 ? '▲ ' : (delta < 0 ? '▼ ' : '');
        var text = points ? (delta > 0 ? '+' : '') + number(delta, 1) + ' pts' : inDays ? (delta > 0 ? '+' : '') + number(delta, 0) + ' days' : (delta > 0 ? '+' : '') + shortNumber(delta);
        var percent = (points || inDays) ? '' : (isNumber(metric.percent) ? (Math.abs(Number(metric.percent)) > 1000 ? ' <span>(not meaningful: very small base)</span>' : ' <span>(' + esc((Number(metric.percent) > 0 ? '+' : '') + number(metric.percent, 1)) + '%)</span>') : ' <span>· no percentage baseline</span>');
        return '<span class="' + cls + '">' + arrow + esc(text) + '</span>' + percent + ' <small>' + esc(comparisonLabels[state.comparison] || 'vs comparison period') + '</small>';
    }
    function readState() {
        var p = new URLSearchParams(window.location.search), filters = {};
        try { var parsed = JSON.parse(p.get('filters') || '{}'); if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) { Object.keys(parsed).forEach(function (key) { if (/^[a-z_]+$/.test(key) && typeof parsed[key] === 'string') { filters[key] = parsed[key]; } }); } } catch (ignore) {}
        var view = p.get('view');
        return {
            view: ['overview', 'explore', 'coverage'].indexOf(view) >= 0 ? view : 'overview',
            preset: p.get('preset') || (p.has('start') ? 'custom' : 'ytd'), start: p.get('start') || '', end: p.get('end') || '',
            comparison: p.get('comparison') || 'yoy', comparison_start: p.get('comparison_start') || '', comparison_end: p.get('comparison_end') || '',
            dataset: p.get('dataset') || '', month: validDate(p.get('month') || '') ? p.get('month') : '', group_by: p.get('group_by') || '', search: p.get('search') || '',
            sort: p.get('sort') || 'date', direction: p.get('direction') === 'asc' ? 'asc' : 'desc', page: Math.max(1, parseInt(p.get('page'), 10) || 1),
            page_size: [25, 50, 100].indexOf(Number(p.get('page_size'))) >= 0 ? Number(p.get('page_size')) : 50, filters: filters
        };
    }
    function writeState(replace) {
        var p = new URLSearchParams();
        Object.keys(state).forEach(function (key) {
            var value = state[key];
            if (key === 'filters') { if (Object.keys(value).length) { p.set(key, JSON.stringify(value)); } }
            else if (value !== '' && value !== null && value !== undefined) { p.set(key, String(value)); }
        });
        window.history[replace ? 'replaceState' : 'pushState']({}, '', window.location.pathname + '?' + p.toString());
    }
    function metricById(id) { return overview && overview.metrics.filter(function (m) { return m.id === id; })[0]; }
    function alertMessage(message, retry) {
        $('dirAlert').textContent = message || ''; $('dirAlert').hidden = !message;
        if (message && retry) { var again = document.createElement('button'); again.type = 'button'; again.className = 'dir-link'; again.setAttribute('data-retry', '1'); again.textContent = 'Try again'; $('dirAlert').appendChild(again); }
    }
    function errorPanel(title, message) { return '<div class="dir-error" role="alert"><strong>' + esc(title) + '</strong>' + esc(message) + '<br><button type="button" class="dir-button" data-retry="1"><i class="fa fa-refresh" aria-hidden="true"></i> Try again</button></div>'; }
    function loading(active) { root.classList.toggle('is-loading', active); root.setAttribute('aria-busy', active ? 'true' : 'false'); $('dirRefresh').disabled = active; $('dirExportSummary').disabled = active || !overview; $('dirExport').disabled = active || !exploration || !exploration.details.total; if (active) { $('dirStatus').textContent = 'Loading live source data…'; } }
    function parameters(action) {
        var p = new URLSearchParams();
        p.set('action', action);
        ['preset', 'start', 'end', 'comparison', 'comparison_start', 'comparison_end'].forEach(function (key) { if (state[key]) { p.set(key, state[key]); } });
        if ((action === 'explore' || action === 'export') && state.month) {
            var monthStart = state.month, monthLast = iso(new Date(Date.UTC(dateUTC(state.month).getUTCFullYear(), dateUTC(state.month).getUTCMonth() + 1, 0)));
            p.set('preset', 'custom'); p.set('start', monthStart < state.start ? state.start : monthStart); p.set('end', monthLast > state.end ? state.end : monthLast);
            p.delete('comparison_start'); p.delete('comparison_end');
            p.set('comparison', state.comparison === 'custom' ? 'yoy' : state.comparison);
        }
        if (action === 'explore' || action === 'export') {
            ['dataset', 'group_by', 'search', 'sort', 'direction', 'page', 'page_size'].forEach(function (key) { if (state[key] !== '') { p.set(key, state[key]); } });
            if (Object.keys(state.filters).length) { p.set('filters', JSON.stringify(state.filters)); }
        }
        return p;
    }
    function api(action, signal, extra) {
        var query = parameters(action);
        if (extra) { Object.keys(extra).forEach(function (key) { query.set(key, extra[key]); }); }
        return fetch(config.apiUrl + '?' + query.toString(), { credentials: 'same-origin', cache: 'no-store', signal: signal, headers: { 'Accept': 'application/json' } }).then(function (response) {
            return response.json().catch(function () { throw new Error('The report server did not return a readable response. Refresh or sign in again.'); }).then(function (payload) {
                if (!response.ok || !payload.ok) { throw new Error(payload.error && payload.error.message ? payload.error.message : 'This report could not be loaded.'); }
                return payload.data;
            });
        });
    }
    var PARTS = ['finance', 'sales', 'operations'], partsPending = 0, forceRefresh = false;
    /** Combine the slices received so far into the same shape as one whole overview (repeats are collapsed). */
    function mergeParts(collected) {
        var byId = {}, order = [], seen = {}, attention = [], health = [], risks = [], hasAttention = false, meta = null, budget = null, limitations = null, hit = true, age = 0, ms = 0, any = false;
        function once(list, item, key) { var k = key(item); if (!own(seen, k)) { seen[k] = true; list.push(item); } }
        PARTS.forEach(function (part) {
            var d = collected[part]; if (!d) { return; }
            any = true; meta = meta || d.metadata; limitations = limitations || d.limitations; budget = budget || d.budget_comparison;
            if (d.metadata) { ms = Math.max(ms, Number(d.metadata.server_ms) || 0); hit = hit && !!(d.metadata.cache && d.metadata.cache.hit); age = Math.max(age, d.metadata.cache ? Number(d.metadata.cache.age_seconds) || 0 : 0); }
            (d.metrics || []).forEach(function (m) { if (!own(byId, m.id)) { order.push(m.id); } byId[m.id] = m; });
            if (d.attention) { hasAttention = true; d.attention.forEach(function (item) { once(attention, item, function (i) { return 'a|' + (i.id || i.title); }); }); }
            (d.data_health || []).forEach(function (item) { once(health, item, function (i) { return 'h|' + i.title; }); });
            (d.risks || []).forEach(function (item) { once(risks, item, function (i) { return 'r|' + i.title; }); });
        });
        if (!any) { return null; }
        attention.sort(function (a, b) { return Number(b.amount) - Number(a.amount); });
        var metadata = meta ? JSON.parse(JSON.stringify(meta)) : {};
        metadata.server_ms = ms; metadata.cache = { hit: hit, age_seconds: age };
        return { metadata: metadata, metrics: order.map(function (id) { return byId[id]; }), attention: hasAttention ? attention.slice(0, 8) : undefined,
            data_health: health, risks: risks, budget_comparison: budget, limitations: limitations || [] };
    }
    function applyMerged(collected) { var merged = mergeParts(collected); if (merged) { renderOverview(merged); useMetadata(merged.metadata); } }
    function useMetadata(metadata, keepPeriod) {
        metadata = metadata || {};
        var period = metadata.period || {};
        if (!keepPeriod) {
            if (period.start) { state.start = period.start; }
            if (period.end) { state.end = period.end; }
            if (period.comparison_start) { state.comparison_start = period.comparison_start; }
            if (period.comparison_end) { state.comparison_end = period.comparison_end; }
            if (period.comparison === 'none') { state.comparison_start = ''; state.comparison_end = ''; }
        }
        if (period.fiscal_start_month) { fiscalStart = Number(period.fiscal_start_month); }
        if (period.today) { config.today = period.today; }
        periodDays = period.days; comparisonDays = period.comparison_days;
        $('dirCompany').textContent = metadata.company || 'Current company';
        $('dirScope').textContent = typeof metadata.scope === 'string' ? metadata.scope : 'Authorized company · ' + (metadata.currency || 'source units');
        $('dirFreshness').textContent = (metadata.cache && metadata.cache.hit ? 'Saved result' : 'Live source') + ' · ' + (metadata.generated_local ? 'updated ' + metadata.generated_local + (metadata.time_zone ? ' (' + metadata.time_zone + ')' : '') : (metadata.generated_at_utc ? metadata.generated_at_utc + ' UTC' : 'just refreshed')) + (metadata.cache && metadata.cache.hit ? ' · ' + (Number(metadata.cache.age_seconds) >= 60 ? Math.round(Number(metadata.cache.age_seconds) / 60) + ' min old; Refresh for the latest' : 'moments old') : '');
        $('dirStatus').textContent = 'Live source refreshed' + (isNumber(metadata.server_ms) ? ' · ' + number(metadata.server_ms, 0) + ' ms server time' : '') + ' · ' + (state.view === 'explore' ? 'Investigation filters applied' : 'Company overview');
        syncControls();
    }
    function updateChip() {
        var select = $('dirPreset'), option = select.options[select.selectedIndex];
        var label = state.preset === 'custom' ? 'Custom dates' : (option ? option.text : '');
        var range = state.start && state.end ? showDate(state.start) + ' – ' + showDate(state.end) : 'Resolving dates';
        var compare = state.comparison === 'none' ? 'no comparison' : (comparisonLabels[state.comparison] || 'vs comparison period');
        $('dirPeriodChipText').textContent = label + ' · ' + range + ' · ' + compare;
    }
    function closePopover() { $('dirFilters').hidden = true; $('dirPeriodChip').setAttribute('aria-expanded', 'false'); }
    function submitFilters() { $('dirFilters').dispatchEvent(new Event('submit', { cancelable: true })); }
    function syncControls() {
        ['preset', 'start', 'end', 'comparison', 'comparison_start', 'comparison_end'].forEach(function (key) { var id = { preset: 'dirPreset', start: 'dirStart', end: 'dirEnd', comparison: 'dirComparison', comparison_start: 'dirComparisonStart', comparison_end: 'dirComparisonEnd' }[key]; $(id).value = state[key] || ''; });
        $('dirCustomStartWrap').hidden = state.comparison !== 'custom';
        $('dirCustomEndWrap').hidden = state.comparison !== 'custom';
        $('dirComparisonStart').required = state.comparison === 'custom'; $('dirComparisonEnd').required = state.comparison === 'custom';
        $('dirSearch').value = state.search; $('dirPageSize').value = state.page_size;
        if (state.dataset) { $('dirDataset').value = state.dataset; }
        var current = state.start && state.end ? showDate(state.start) + ' – ' + showDate(state.end) : 'Resolving dates';
        var comparison = state.comparison === 'none' ? 'No comparison' : (state.comparison_start && state.comparison_end ? 'Compared with ' + showDate(state.comparison_start) + ' – ' + showDate(state.comparison_end) : 'Resolving comparison');
        $('dirPeriodContext').textContent = current + ' · ' + comparison + (isNumber(periodDays) ? ' · ' + periodDays + ' days' + (isNumber(comparisonDays) ? ' versus ' + comparisonDays + ' days' : '') : '') + ' · Fiscal year begins ' + new Date(Date.UTC(2000, fiscalStart - 1, 1)).toLocaleString(undefined, { month: 'long', timeZone: 'UTC' }) + '. ' + (state.view === 'explore' ? 'Investigation filters apply to the selected measure.' : 'Overview covers the authorized company. Current snapshots use today’s balances.');
        updateChip();
        var dimensions = dimsFor(metricById(state.dataset));
        $('dirChips').innerHTML = (state.view === 'explore' && state.month ? '<button class="dir-chip" type="button" data-remove-month="1" title="Back to the whole period">Month: ' + esc(monthLabel(state.month)) + ' ×</button>' : '') + (state.view === 'explore' ? Object.keys(state.filters).map(function (key) { return '<button class="dir-chip" type="button" data-remove-filter="' + esc(key) + '" title="Remove this filter">' + esc(dimensions[key] || key) + ': ' + esc(state.filters[key]) + ' ×</button>'; }).join('') : '');
        ['overview', 'explore', 'coverage'].forEach(function (view) { var tab = document.querySelector('[data-view="' + view + '"]'); tab.setAttribute('aria-selected', state.view === view ? 'true' : 'false'); tab.tabIndex = state.view === view ? 0 : -1; document.querySelector('[data-view="' + view + '"]').classList.toggle('is-active', state.view === view); $('dir' + view.charAt(0).toUpperCase() + view.slice(1)).hidden = state.view !== view; });
    }
    function sectionFor(metric) {
        if (metric.section) { return metric.section; }
        var id = metric.id;
        if (/^gl_/.test(id)) { return 'Financial performance'; }
        if (/cash|bank/.test(id)) { return 'Cash & liquidity'; }
        if (/receiv|payable|overdue/.test(id)) { return 'Working capital'; }
        if (/invoice|sale|shop/.test(id)) { return 'Sales & customers'; }
        if (/purchas|mpi|procure|commit/.test(id)) { return 'Purchasing & suppliers'; }
        if (/stock|invent|reorder/.test(id)) { return 'Inventory'; }
        if (/expense|claim/.test(id)) { return 'Expense control'; }
        return 'Commercial & operational performance';
    }
    var headlineIds = ['sales_revenue', 'gross_margin_pct', 'gl_result', 'cash_movement', 'receivables', 'payables'];
    function tileValue(m) {
        if (m.available === false) { return '—'; }
        if (m.unit === '%') { return number(m.value, 1) + '%'; }
        if (m.unit === 'count' || m.unit === 'days') { return number(m.value, 0); }
        return shortNumber(m.value);
    }
    function tileHtml(m, large) {
        var sub = '';
        if (m.components && m.available !== false) { sub = '<span class="dir-metric-sub">' + m.components.map(function (c) { return esc(c.title) + ' ' + esc(shortNumber(c.value)); }).join(' · ') + '</span>'; }
        else if (isNumber(m.overdue) && Number(m.overdue) > 0 && m.available !== false) { sub = '<span class="dir-metric-sub">Past-due balances ' + esc(shortNumber(m.overdue)) + ' ' + esc(unit(m)) + ', before credits and unapplied receipts</span>'; }
        return '<button type="button" class="dir-metric' + (large ? ' dir-metric-large' : '') + '" data-metric="' + esc(m.id) + '"' + (!canInvestigate(m) ? ' disabled' : '') + ' title="' + esc(m.definition || m.title) + '"><span class="dir-metric-title">' + esc(plainTitle(m)) + (Number(m.invalid_amounts) > 0 ? ' · known subtotal' : '') + '</span><strong class="dir-metric-value" title="' + esc(number(m.value) + ' ' + unit(m)) + '">' + esc(tileValue(m)) + (m.unit === '%' ? '' : '<span class="dir-unit">' + esc(unit(m)) + '</span>') + '</strong><span class="dir-metric-change">' + changeText(m) + '</span>' + sub + '<span class="dir-metric-bottom">' + tag(m) + '<span>' + esc(Number(m.invalid_amounts) > 0 ? number(m.invalid_amounts, 0) + ' excluded amounts' : basis(m)) + '</span></span></button>';
    }
    function renderOverview(data) {
        overview = data;
        var groups = {}, unavailable = [], skeleton = '<div class="dir-skel dir-skel-tile" aria-hidden="true"></div>';
        $('dirHeadline').innerHTML = headlineIds.map(function (id) { var m = metricById(id); return m ? tileHtml(m, true) : (partsPending > 0 ? skeleton : ''); }).join('') || '<div class="dir-placeholder">No headline measures are available in this company scope.</div>';
        data.metrics.forEach(function (metric) {
            if (headlineIds.indexOf(metric.id) >= 0) { return; }
            if (metric.available === false && !canInvestigate(metric)) { unavailable.push(metric); return; }
            var group = sectionFor(metric); if (!groups[group]) { groups[group] = []; } groups[group].push(metric);
        });
        $('dirMetricGroups').innerHTML = Object.keys(groups).map(function (group) {
            return '<section><h3 class="dir-group-heading">' + esc(group) + '</h3><div class="dir-card-grid">' + groups[group].map(function (m) { return tileHtml(m, false); }).join('') + '</div></section>';
        }).join('') + (partsPending > 0 ? '<div class="dir-card-grid">' + skeleton + skeleton + '</div>' : '') + (unavailable.length ? '<section><h3 class="dir-group-heading">Not available for this selection</h3><ul class="dir-unavailable">' + unavailable.map(function (m) { return '<li><strong>' + esc(plainTitle(m)) + '</strong> — ' + esc(m.error || 'Source unavailable') + '</li>'; }).join('') + '</ul></section>' : '') || '<div class="dir-placeholder">No report measures are available in this company scope.</div>';
        var available = data.metrics.filter(function (m) { return m.available !== false; }).length;
        $('dirCoverageStatus').textContent = partsPending > 0 ? 'Loading measures…' : available + ' of ' + data.metrics.length + ' measures available';
        risks = data.attention || data.risks || [];
        var attentionSkeleton = partsPending > 0 ? '<div class="dir-skel dir-skel-card" aria-hidden="true"></div>' : '';
        $('dirRisks').innerHTML = (risks.length ? risks.map(function (risk, index) { return '<article class="dir-risk dir-risk-' + esc(risk.severity || 'medium') + '"><h3>' + esc(risk.title) + '</h3>' + (isNumber(risk.amount) ? '<strong class="dir-risk-amount">' + esc(shortNumber(risk.amount)) + ' <span class="dir-unit">' + esc(risk.unit || '') + '</span></strong>' : '') + '<p>' + esc(risk.message) + '</p>' + (risk.dataset && canInvestigate(metricById(risk.dataset)) ? '<button type="button" data-risk="' + index + '">See the records →</button>' : '') + '</article>'; }).join('') : (partsPending > 0 ? '' : '<div class="dir-placeholder">Nothing crossed the attention thresholds for this selection. This does not rule out risks the data cannot show; see Definitions &amp; coverage.</div>')) + attentionSkeleton;
        var health = data.attention ? (data.data_health || []) : [];
        healthItems = health;
        $('dirDataHealth').hidden = !health.length;
        $('dirHealthCount').textContent = health.length + (health.length === 1 ? ' item' : ' items');
        $('dirHealthList').innerHTML = health.map(function (item, index) { return '<li><strong>' + esc(item.title) + '</strong> — ' + esc(item.message) + (item.dataset && canInvestigate(metricById(item.dataset)) ? ' <button type="button" class="dir-link" data-health="' + index + '">See the records</button>' : '') + '</li>'; }).join('');
        var selected = state.dataset;
        $('dirDataset').innerHTML = '<option value="">Choose a measure</option>' + data.metrics.map(function (m) { return '<option value="' + esc(m.id) + '"' + (!canInvestigate(m) ? ' disabled' : '') + '>' + esc(m.title) + (m.available === false ? ' — unavailable' : '') + '</option>'; }).join('');
        if (selected && metricById(selected)) { $('dirDataset').value = selected; }
        renderCoverage(data);
        var budget = data.budget_comparison;
        $('dirBudgetComparison').hidden = !budget;
        if (budget) {
            $('dirBudgetComparison').innerHTML = '<div class="dir-panel-heading"><div><h3>Recorded budget versus actual</h3><p>Same complete accounting periods · P&amp;L accounts · ' + esc(data.metadata.currency || 'base currency') + '</p></div><span class="dir-tag dir-tag-budget">' + esc(budget.status || 'Recorded, unversioned') + '</span></div><div class="dir-budget-values"><div><span>Recorded budget</span><strong>' + esc(number(budget.budget)) + '</strong></div><div><span>Accounting-period actual</span><strong>' + esc(number(budget.actual)) + '</strong></div><div><span>Actual minus budget</span><strong>' + esc(number(budget.delta)) + '</strong><small>' + (isNumber(budget.percent) ? esc(number(budget.percent, 1)) + '%' : 'Percentage baseline unavailable') + '</small></div></div><p class="dir-budget-caveat">' + esc(budget.caveat || '') + '</p>';
        }
        renderDimensions();
    }
    var businessQuestions = {
        gl_result: 'What result is recorded in the general ledger?', gl_revenue: 'How is recorded revenue changing?', gl_cost_of_sales: 'What cost of sales has been posted?', gl_expenses: 'How are operating expenses moving?', gl_budget: 'What budget has been recorded?',
        cash_movement: 'Where is cash moving in or out?', receivables: 'What do customers currently owe?', payables: 'What do we currently owe suppliers?', invoice_value: 'Where is invoice value generated?', inventory: 'Where is capital held in stock?', salescases: 'Which opportunities need attention?'
    };
    function renderCoverage(data) {
        var limitations = data.limitations || [];
        $('dirLimitations').innerHTML = limitations.map(function (text) { return '<li>' + esc(typeof text === 'string' ? text : (text.message || text.title || JSON.stringify(text))) + '</li>'; }).join('') || '<li>No additional limitations returned. Read the definition and basis for every measure.</li>';
        if (data.budget_comparison) {
            var b = data.budget_comparison;
            $('dirLimitations').innerHTML += '<li><strong>Recorded budget versus accounting-period actual:</strong> Budget ' + esc(number(b.budget)) + ', actual ' + esc(number(b.actual)) + ', variance ' + esc(number(b.delta)) + (isNumber(b.percent) ? ' (' + esc(number(b.percent, 1)) + '%)' : '') + '. ' + esc(b.status || '') + '. ' + esc(b.caveat || '') + '</li>';
        }
        $('dirDefinitions').innerHTML = data.metrics.map(function (m) { var dimensions = dimsFor(m); return '<tr><td><button class="dir-definition-title" type="button" data-metric="' + esc(m.id) + '"' + (!canInvestigate(m) ? ' disabled' : '') + '>' + esc(m.title) + '</button><small>' + esc(m.question || businessQuestions[m.id] || 'How is this area performing, and which records explain it?') + '</small></td><td>' + tag(m) + '<small>' + esc(basis(m)) + ' · ' + esc(unit(m)) + '</small><small>' + esc(m.source || '') + '</small></td><td>' + esc(m.definition || '') + '<small>' + esc(m.caveat || '') + (m.error ? ' ' + esc(m.error) : '') + '</small></td><td>' + esc(Object.keys(dimensions).map(function (key) { return dimensions[key]; }).join(', ') || 'Source records') + '</td></tr>'; }).join('');
    }
    function renderDimensions() {
        var dimensions = dimsFor(metricById(state.dataset));
        var keys = Object.keys(dimensions);
        if (keys.indexOf(state.group_by) < 0) { state.group_by = keys[0] || ''; }
        $('dirGroup').innerHTML = keys.length ? keys.map(function (key) { return '<option value="' + esc(key) + '">' + esc(dimensions[key]) + '</option>'; }).join('') : '<option value="">No supported breakdown</option>';
        $('dirGroup').value = state.group_by;
    }
    function renderExploration(data) {
        exploration = data;
        var m = data.metric || metricById(state.dataset), summary = data.summary || {}, dimensions = data.dimensions || dimsFor(m);
        $('dirInvestigationEmpty').hidden = true; $('dirInvestigation').hidden = false;
        $('dirExploreTitle').textContent = m.title;
        $('dirExploreSubtitle').textContent = basis(m) + ' · ' + unit(m) + ' · ' + number(summary.rows, 0) + ' supporting rows';
        var compare = isSnapshot(m) ? 'Historical comparison unavailable' : state.comparison === 'none' ? 'Comparison disabled' : 'Comparison period';
        var kpis = [
            [Number(summary.invalid_amounts) > 0 ? 'Known subtotal' : 'Selected value', summary.value, unit(m)],
            [compare, isSnapshot(m) || state.comparison === 'none' ? null : summary.previous, isSnapshot(m) ? 'Current allocation / quantity state' : unit(m)],
            ['Change', isSnapshot(m) || state.comparison === 'none' ? null : summary.delta, isNumber(summary.percent) ? number(summary.percent, 1) + '%' : 'Percentage baseline unavailable'],
            ['Supporting records', summary.rows, (summary.invalid_amounts ? number(summary.invalid_amounts, 0) + ' amounts excluded · see source quality' : 'At the source grain')]
        ];
        $('dirInvestigationKpis').innerHTML = kpis.map(function (k) { return '<div class="dir-small-kpi"><span>' + esc(k[0]) + '</span><strong title="' + esc(number(k[1])) + '">' + esc(shortNumber(k[1])) + '</strong><small>' + esc(k[2]) + '</small></div>'; }).join('');
        var extra = [];
        if (isNumber(summary.overdue)) { extra.push('Overdue amount: ' + number(summary.overdue) + ' ' + unit(m)); }
        if (state.dataset === 'cash_movement' && isNumber(summary.inflow)) { extra.push('Inflows: ' + number(summary.inflow)); }
        if (state.dataset === 'cash_movement' && isNumber(summary.outflow)) { extra.push('Outflows: ' + number(summary.outflow)); }
        if (Number(summary.invalid_amounts) > 0) { extra.push(number(summary.invalid_amounts, 0) + ' rows have unavailable amounts; totals exclude them'); }
        if (Number(summary.unknown_due) > 0) { extra.push(number(summary.unknown_due, 0) + ' rows have unknown due dates'); }
        if (Number(summary.estimated_due) > 0) { extra.push(number(summary.estimated_due, 0) + ' rows use estimated due dates'); }
        if (Number(summary.quality_issues) > 0) { extra.push(number(summary.quality_issues, 0) + ' source quality exceptions'); }
        var firstDate = summary.first_record_date || m.first_record_date, lastDate = summary.last_record_date || m.last_record_date;
        if (firstDate && lastDate) { extra.push('Selected source records span ' + showDate(firstDate) + ' – ' + showDate(lastDate)); }
        $('dirDefinition').innerHTML = '<strong>Definition:</strong> ' + esc(m.definition || '') + '<br><strong>Basis:</strong> ' + esc(basis(m)) + '. ' + esc(m.caveat || '') + (extra.length ? '<br>' + esc(extra.join(' · ')) : '');
        root.querySelector('.dir-chart-legend').hidden = isSnapshot(m);
        $('dirTrendTitle').textContent = isSnapshot(m) ? 'Current snapshot' : 'Monthly trend & comparison';
        $('dirBreakdownTitle').textContent = (dimensions[state.group_by] || 'Source') + (state.comparison !== 'none' && !isSnapshot(m) ? ' · contributors to movement' : ' · concentration');
        renderTrend(data, m);
        renderBreakdown(data, m);
        renderRows(data, m, dimensions);
    }
    function trendPeriod(row) { return String(row.period || row.period_start || '').slice(0, 10); }
    var trendTips = [];
    function monthLastDay(first) { var d = dateUTC(first); return iso(new Date(Date.UTC(d.getUTCFullYear(), d.getUTCMonth() + 1, 0))); }
    function partialMonth(first, rangeStart, rangeEnd) { return validDate(first) && validDate(rangeStart || '') && validDate(rangeEnd || '') && (first < rangeStart || monthLastDay(first) > rangeEnd); }
    function renderTrend(data, metric) {
        var current = data.trend || [], previous = data.comparison_trend || [], per = (data.metadata || {}).period || {};
        if (isSnapshot(metric)) {
            $('dirTrend').innerHTML = '<div class="dir-snapshot-note">This is a live snapshot of the current records.<br>Historical balances require retained snapshots or a validated reconstruction. The selected date range does not turn current allocations or quantities into past balances.</div>';
            $('dirTrendTable').innerHTML = ''; return;
        }
        if (!current.length && !previous.length) { $('dirTrend').innerHTML = '<div class="dir-placeholder">No activity was returned for these periods.</div>'; $('dirTrendTable').innerHTML = ''; return; }
        var length = Math.max(current.length, previous.length), values = [], anyPartial = false, unitText = unit(metric);
        current.concat(previous).forEach(function (r) { if (isNumber(r.value)) { values.push(Number(r.value)); } });
        var maximum = Math.max.apply(null, [0].concat(values)), minimum = Math.min.apply(null, [0].concat(values));
        if (maximum === minimum) { maximum = minimum + 1; }
        var width = 650, height = 265, left = 62, right = 10, top = 22, bottom = 44, plotHeight = height - top - bottom, plotWidth = width - left - right;
        var y = function (v) { return top + (maximum - v) / (maximum - minimum) * plotHeight; }, zero = y(0), segment = plotWidth / length;
        var svg = '<svg viewBox="0 0 ' + width + ' ' + height + '" role="img" aria-label="Monthly source values. Blue is current; gray is comparison. Hatched bars are partial months. Select a month to investigate."><title>Current and comparison monthly values</title><defs><pattern id="dirHatch" width="6" height="6" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><rect width="6" height="6" fill="#dbe7f5"/><rect width="2.5" height="6" fill="#3574ba"/></pattern><pattern id="dirHatchComparison" width="6" height="6" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><rect width="6" height="6" fill="#eef3f9"/><rect width="2.5" height="6" fill="#aebfd3"/></pattern></defs>';
        for (var g = 0; g <= 4; g++) { var tick = minimum + (maximum - minimum) * g / 4, ty = y(tick); svg += '<line class="dir-chart-grid" x1="' + left + '" x2="' + (width - right) + '" y1="' + ty + '" y2="' + ty + '"></line><text class="dir-chart-label" x="' + (left - 8) + '" y="' + (ty + 4) + '" text-anchor="end">' + esc(shortNumber(tick)) + '</text>'; }
        var rows = []; trendTips = [];
        for (var i = 0; i < length; i++) {
            var c = current[i], p = previous[i], period = c ? trendPeriod(c) : '', cp = p ? trendPeriod(p) : '', center = left + segment * (i + .5), barWidth = Math.min(20, segment * .3);
            var cPartial = c && partialMonth(period, per.start || state.start, per.end || state.end), pPartial = p && partialMonth(cp, per.comparison_start || state.comparison_start, per.comparison_end || state.comparison_end);
            anyPartial = anyPartial || cPartial || pPartial;
            var tip = '<strong>' + esc(period ? monthLabel(period) : 'No current month') + (cPartial ? ' (partial month)' : '') + '</strong><br>Current: ' + (c && isNumber(c.value) ? esc(number(c.value)) + ' ' + esc(unitText) : 'unavailable');
            if (p) {
                tip += '<br>Comparison, ' + esc(monthLabel(cp)) + (pPartial ? ' (partial month)' : '') + ': ' + (isNumber(p.value) ? esc(number(p.value)) + ' ' + esc(unitText) : 'unavailable');
                if (c && isNumber(c.value) && isNumber(p.value)) { var d = Number(c.value) - Number(p.value); tip += '<br>Change: ' + esc((d > 0 ? '+' : '') + number(d)) + (Number(p.value) !== 0 ? ' (' + esc((d > 0 ? '+' : '') + number(d / Math.abs(Number(p.value)) * 100, 1)) + '%)' : ' (no percentage baseline)'); }
            }
            trendTips.push(tip);
            var plain = tip.replace(/<br>/g, '. ').replace(/<[^>]+>/g, '');
            svg += '<g class="dir-chart-month" data-tip="' + i + '"' + (period ? ' tabindex="0" role="button" data-month="' + esc(period) + '" aria-label="' + esc(plain + '. Investigate this month') + '"' : '') + '>';
            if (p && isNumber(p.value)) { var py = y(Number(p.value)); svg += '<rect class="dir-chart-comparison' + (pPartial ? ' dir-chart-partial-comparison' : '') + '"' + (pPartial ? ' fill="url(#dirHatchComparison)"' : '') + ' x="' + (center - barWidth - 2) + '" y="' + Math.min(py, zero) + '" width="' + barWidth + '" height="' + Math.abs(zero - py) + '" rx="2"></rect>'; }
            if (c && isNumber(c.value)) { var cy = y(Number(c.value)); svg += '<rect class="dir-chart-current' + (cPartial ? ' dir-chart-partial' : '') + '"' + (cPartial ? ' fill="url(#dirHatch)"' : '') + ' x="' + (center + 2) + '" y="' + Math.min(cy, zero) + '" width="' + barWidth + '" height="' + Math.abs(zero - cy) + '" rx="2"></rect>'; }
            svg += '<rect x="' + (center - segment * .45) + '" y="' + top + '" width="' + (segment * .9) + '" height="' + plotHeight + '" fill="transparent"></rect>';
            if (i % Math.max(1, Math.ceil(length / 8)) === 0 || length < 10) { svg += '<text class="dir-chart-label" x="' + center + '" y="' + (height - 20) + '" text-anchor="middle">' + esc(monthLabel(period || cp)) + (cPartial ? '*' : '') + '</text>'; }
            svg += '</g>';
            rows.push('<tr><td>' + (period ? '<button type="button" class="dir-chip" data-month="' + esc(period) + '">' + esc(monthLabel(period)) + '</button>' : 'Unavailable') + '</td><td class="dir-number">' + (c ? esc(number(c.value)) : '—') + '</td><td>' + esc(cp ? monthLabel(cp) : '—') + '</td><td class="dir-number">' + (p ? esc(number(p.value)) : '—') + '</td></tr>');
        }
        $('dirTrend').innerHTML = svg + '</svg>' + (anyPartial ? '<p class="dir-chart-note">* Hatched bars are partial months: the period does not cover the whole calendar month, so they are not comparable with a full month.</p>' : '') + '<div id="dirTip" class="dir-tip" role="tooltip" hidden></div>';
        $('dirTrendTable').innerHTML = '<table class="dir-table"><thead><tr><th>Month</th><th class="dir-number">Current</th><th>Comparison month</th><th class="dir-number">Comparison</th></tr></thead><tbody>' + rows.join('') + '</tbody></table>';
    }
    function showTip(target, x, y) {
        var tip = $('dirTip'), box = $('dirTrend').getBoundingClientRect(); if (!tip || !target) { return; }
        tip.innerHTML = trendTips[Number(target.getAttribute('data-tip'))] || ''; tip.hidden = false;
        tip.style.left = Math.max(0, Math.min(box.width - tip.offsetWidth, x - box.left + 12)) + 'px'; tip.style.top = Math.max(0, y - box.top - tip.offsetHeight - 10) + 'px';
    }
    function hideTip() { var tip = $('dirTip'); if (tip) { tip.hidden = true; } }
    function signed(v) { return (Number(v) > 0 ? '+' : '') + shortNumber(v); }
    function renderContributors(rows, metric, summary) {
        var inc = rows.filter(function (r) { return Number(r.delta) > 0; }).sort(function (a, b) { return b.delta - a.delta; });
        var dec = rows.filter(function (r) { return Number(r.delta) < 0; }).sort(function (a, b) { return a.delta - b.delta; });
        var max = Math.max.apply(null, [1].concat(inc.concat(dec).map(function (r) { return Math.abs(Number(r.delta)); }))), shown = [], total = Number(summary.delta);
        function side(list, title, up) {
            var top = list.slice(0, 10), rest = list.slice(10), restSum = rest.reduce(function (s, r) { return s + Number(r.delta); }, 0);
            var good = metric.polarity ? (up === (Number(metric.polarity) > 0)) : null, cls = good === null ? 'is-neutral' : (good ? 'is-good' : 'is-bad');
            return '<div class="dir-contrib-col"><h4>' + title + ' <small>' + list.length + '</small></h4>' + (top.length ? top.map(function (r) {
                var index = shown.length; shown.push(r); var label = r.label || r.key || 'Unassigned', share = total !== 0 ? Number(r.delta) / total * 100 : null;
                return '<button type="button" class="dir-breakdown-row" data-breakdown="' + index + '"' + (r.key === null ? ' disabled' : '') + ' aria-label="' + (r.key === null ? 'Aggregate of ' : 'Filter ') + esc(label) + '"><span class="dir-breakdown-row-head"><span class="dir-breakdown-label">' + esc(label) + '</span><strong class="dir-contrib-delta ' + cls + '" title="' + esc(number(r.delta)) + '">' + esc(signed(r.delta)) + '</strong></span><span class="dir-breakdown-bar"><i class="' + cls + '" style="width:' + Math.max(2, Math.min(100, Math.abs(Number(r.delta)) / max * 100)) + '%"></i></span><small>Now ' + esc(shortNumber(r.value)) + (share !== null ? ' · ' + esc(number(share, 1)) + '% of the total change' : '') + ' · ' + esc(number(r.rows, 0)) + ' rows</small></button>';
            }).join('') : '<p class="dir-muted">None</p>') + (rest.length ? '<div class="dir-contrib-other"><span>Other (' + rest.length + ')</span><strong>' + esc(signed(restSum)) + '</strong></div>' : '') + '</div>';
        }
        var html = '<div class="dir-contrib">' + side(inc, 'Increases', true) + side(dec, 'Decreases', false) + '</div>';
        var sum = rows.reduce(function (s, r) { return s + Number(r.delta); }, 0), gap = total - sum;
        $('dirBreakdown').innerHTML = html + '<div class="dir-contrib-total">Total change ' + esc(signed(total)) + ' = increases ' + esc(signed(inc.reduce(function (s, r) { return s + Number(r.delta); }, 0))) + ' + decreases ' + esc(signed(dec.reduce(function (s, r) { return s + Number(r.delta); }, 0))) + (Math.abs(gap) > Math.max(0.01, Math.abs(total) * 1e-6) ? ' · ' + esc(signed(gap)) + ' is not attributed to a group' : '') + '. Choose a group to filter the records.</div>';
        $('dirBreakdown')._rows = shown;
    }
    function renderBreakdown(data, metric) {
        var rows = (data.breakdown || []).slice(), compare = state.comparison !== 'none' && !isSnapshot(metric);
        if (compare && rows.length && rows.every(function (r) { return isNumber(r.delta); }) && isNumber((data.summary || {}).delta)) { return renderContributors(rows, metric, data.summary); }
        rows.sort(function (a, b) { return compare && isNumber(a.delta) && isNumber(b.delta) ? Math.abs(Number(b.delta)) - Math.abs(Number(a.delta)) : Math.abs(Number(b.value) || 0) - Math.abs(Number(a.value) || 0); });
        var max = Math.max.apply(null, [1].concat(rows.map(function (r) { return Math.abs(Number(r.value) || 0); })));
        var rendered = rows.slice(0, 15);
        $('dirBreakdown').innerHTML = rendered.length ? rendered.map(function (r, i) { var filtered = own(state.filters, state.group_by) && state.filters[state.group_by] === String(r.key); return '<button type="button" class="dir-breakdown-row" data-breakdown="' + i + '"' + (r.key === null ? ' disabled' : '') + ' aria-label="' + (r.key === null ? 'Aggregate of ' : 'Filter ') + esc(r.label || r.key || 'Unassigned') + '"><span class="dir-breakdown-row-head"><span class="dir-breakdown-label">' + esc(r.label || r.key || 'Unassigned') + (filtered ? ' ✓' : '') + '</span><strong title="' + esc(number(r.value)) + '">' + esc(shortNumber(r.value)) + '</strong></span><span class="dir-breakdown-bar"><i style="width:' + Math.max(0, Math.min(100, Math.abs(Number(r.value) || 0) / max * 100)) + '%"></i></span><small>' + esc(number(r.rows, 0)) + ' rows' + (compare ? ' · Δ ' + esc(isNumber(r.delta) ? (Number(r.delta) > 0 ? '+' : '') + number(r.delta) : '—') + (isNumber(r.percent) ? ' (' + esc(number(r.percent, 1)) + '%)' : ' · no percentage baseline') : '') + '</small></button>'; }).join('') + '<div class="dir-breakdown-summary">' + (compare ? 'Ranked by absolute movement when comparison data is available. ' : 'Ranked by absolute value. ') + 'Showing ' + rendered.length + ' of ' + rows.length + ' returned groups.</div>' : '<div class="dir-placeholder">No groups were returned for this investigation.</div>';
        $('dirBreakdown')._rows = rendered;
    }
    function safeRecordLink(url) {
        if (!url) { return ''; }
        try { var result = new URL(url, window.location.href); if (result.origin !== window.location.origin || result.pathname.indexOf(config.rootUrl || '/') !== 0) { return ''; } result.searchParams.set('directors_return', window.location.pathname + window.location.search); return result.href; } catch (ignore) { return ''; }
    }
    function renderRows(data, metric, dimensions) {
        var details = data.details || {}, rows = details.rows || [], dimKeys = Object.keys(dimensions).filter(function (key) { return ['date', 'reference', 'amount', 'status', 'quantity'].indexOf(key) < 0; });
        var columns = [{ key: 'date', label: 'Date', sort: true }, { key: 'reference', label: 'Reference / source record', sort: true }];
        dimKeys.forEach(function (key) { columns.push({ key: key, label: dimensions[key] }); });
        if (rows.some(function (r) { return own(r, 'due_date'); })) { columns.push({ key: 'due_date', label: 'Due date' }); }
        columns.push({ key: 'status', label: 'Status' }, { key: 'quantity', label: 'Quantity', numeric: true }, { key: 'amount', label: 'Amount · ' + unit(metric), numeric: true, sort: true });
        $('dirRecords').querySelector('thead').innerHTML = '<tr>' + columns.map(function (c) { return '<th' + (c.numeric ? ' class="dir-number"' : '') + '>' + (c.sort ? '<button type="button" data-sort="' + c.key + '">' + esc(c.label) + ' ' + (state.sort === c.key ? (state.direction === 'asc' ? '↑' : '↓') : '↕') + '</button>' : esc(c.label)) + '</th>'; }).join('') + '</tr>';
        $('dirRecords').querySelector('tbody').innerHTML = rows.length ? rows.map(function (row) { return '<tr>' + columns.map(function (c) {
            var value = row[c.key], content = c.numeric ? esc(number(value)) : esc(value == null || value === '' ? '—' : value);
            if (row[c.key + '_label']) { content = esc(row[c.key + '_label']); }
            if (c.key === 'date' || c.key === 'due_date') { content = esc(showDate(value)); }
            if (c.key === 'reference') { var link = safeRecordLink(row.source_url); if (link) { content = '<a target="_blank" rel="noopener" href="' + esc(link) + '" aria-label="Open source record ' + esc(value || row.id) + ' in a separate tab">' + esc(value || row.id) + ' ↗</a>'; } }
            return '<td' + (c.numeric ? ' class="dir-number"' : '') + '>' + content + '</td>';
        }).join('') + '</tr>'; }).join('') : '<tr><td class="dir-empty-cell" colspan="' + columns.length + '">No source records match this investigation. ' + (Object.keys(state.filters).length || state.search || state.month ? 'Remove a filter chip above' + (state.search ? ', clear the search' : '') + ' or choose another period.' : 'Choose another period.') + '</td></tr>';
        var total = Number(details.total || 0), page = Number(details.page || state.page), size = Number(details.page_size || state.page_size);
        state.page = page; state.page_size = size;
        $('dirPageStatus').textContent = total ? number((page - 1) * size + 1, 0) + '–' + number(Math.min(page * size, total), 0) + ' of ' + number(total, 0) + ' records' : '0 matching records';
        $('dirPrevious').disabled = page <= 1; $('dirNext').disabled = page * size >= total;
        $('dirRecordContext').textContent = 'The same ' + (isSnapshot(metric) ? 'current snapshot' : 'period') + ', source definition, access rules and investigation filters apply to this table and its export.' + (Number((data.summary || {}).invalid_amounts) > 0 ? ' Monetary totals are known subtotals; unavailable amounts remain visible as —.' : '');
        $('dirExport').disabled = total === 0;
    }
    function changeView(view) { state.view = view; syncControls(); writeState(false); if (view === 'explore' && state.dataset && (!exploration || exploration.metric.id !== state.dataset)) { load(false); } }
    function investigate(id, filters, groupBy) {
        var m = metricById(id); if (m && m.components && m.components.length) { id = m.components[0].id; m = metricById(id); }
        if (!canInvestigate(m)) { return; }
        if (state.dataset !== id) { state.filters = {}; state.group_by = ''; state.search = ''; state.month = ''; }
        state.dataset = id; state.view = 'explore'; state.page = 1;
        if (filters) { Object.keys(filters).forEach(function (key) { if (own(dimsFor(m), key)) { state.filters[key] = String(filters[key]); } }); }
        if (groupBy && own(dimsFor(m), groupBy)) { state.group_by = groupBy; }
        renderDimensions(); syncControls(); writeState(false); load(false);
    }
    function load(withOverview) {
        if (controller) { controller.abort(); }
        controller = new AbortController(); var signal = controller.signal, run = ++requestNumber;
        var bypass = forceRefresh; forceRefresh = false;
        var extra = bypass ? { refresh: '1' } : {};
        loading(true); alertMessage('');
        var needOverview = withOverview || !overview, wantExplore = state.view === 'explore' && !!state.dataset, collected = {}, failures = [];
        partsPending = needOverview ? PARTS.length : 0;
        // The three slices and the investigation are independent requests, so the wait is the slowest one, not their sum.
        var tasks = needOverview ? PARTS.map(function (part) {
            return api('overview', signal, Object.assign({ part: part }, extra)).then(function (data) {
                if (run !== requestNumber) { return; }
                collected[part] = data; partsPending--; applyMerged(collected);
            }, function (error) {
                if (error.name === 'AbortError') { throw error; }
                partsPending--; failures.push({ part: part, error: error });
            });
        }) : [];
        if (wantExplore) {
            tasks.push(api('explore', signal, extra).then(function (data) { if (run !== requestNumber) { return; } renderExploration(data); useMetadata(data.metadata, !!state.month); }));
        }
        Promise.all(tasks).then(function () {
            if (run !== requestNumber) { return; }
            if (needOverview && failures.length === PARTS.length) { throw failures[0].error; }
            if (needOverview) { partsPending = 0; applyMerged(collected); }
            if (wantExplore && metricById(state.dataset) && !canInvestigate(metricById(state.dataset))) { throw new Error('This measure is unavailable or outside your current access. Choose an available measure from the overview.'); }
            if (state.view === 'explore' && !state.dataset) { $('dirInvestigationEmpty').hidden = false; $('dirInvestigation').hidden = true; }
            loading(false); syncControls(); writeState(true);
            if (failures.length) { alertMessage('Some measures could not be loaded (' + failures.map(function (f) { return f.part; }).join(', ') + '). The rest are shown. ' + failures[0].error.message, true); }
        }).catch(function (error) {
            if (error.name === 'AbortError' || run !== requestNumber) { return; }
            partsPending = 0;
            loading(false); alertMessage(overview ? error.message : '', true); $('dirStatus').textContent = 'Refresh failed · displayed data may be from the previous refresh';
            $('dirExport').disabled = true; $('dirExportSummary').disabled = true;
            if (state.view === 'explore') { $('dirInvestigation').hidden = true; $('dirInvestigationEmpty').hidden = false; $('dirInvestigationEmpty').innerHTML = errorPanel('This investigation could not be loaded', 'Nothing from an earlier load is shown, so you never read figures that do not match the selection.'); }
            if (!overview) { $('dirCoverageStatus').textContent = 'Not loaded'; $('dirHeadline').innerHTML = errorPanel('The dashboard could not load', error.message); $('dirMetricGroups').innerHTML = ''; $('dirRisks').innerHTML = ''; }
        });
    }
    function applyPeriod(event) {
        event.preventDefault();
        state.preset = $('dirPreset').value; state.start = $('dirStart').value; state.end = $('dirEnd').value;
        state.comparison = $('dirComparison').value; state.comparison_start = $('dirComparisonStart').value; state.comparison_end = $('dirComparisonEnd').value;
        if (!validDate(state.start) || !validDate(state.end) || state.start > state.end) { alertMessage('Choose valid reporting dates, with From no later than To.'); return; }
        if (state.comparison === 'custom' && (!validDate(state.comparison_start) || !validDate(state.comparison_end) || state.comparison_start > state.comparison_end)) { alertMessage('Choose valid custom comparison dates.'); return; }
        state.page = 1; state.month = ''; exploration = null; closePopover(); writeState(false); load(true);
    }
    function previewPreset() {
        var preset = $('dirPreset').value; if (preset === 'custom') { return; }
        var today = dateUTC(config.today), y = today.getUTCFullYear(), m = today.getUTCMonth(), start, end = iso(today);
        var fy = m + 1 >= fiscalStart ? y : y - 1, fiscal = new Date(Date.UTC(fy, fiscalStart - 1, 1));
        if (preset === 'ytd') { start = iso(fiscal); }
        if (preset === 'month') { start = iso(new Date(Date.UTC(y, m, 1))); }
        if (preset === 'last_month') { start = iso(new Date(Date.UTC(y, m - 1, 1))); end = iso(new Date(Date.UTC(y, m, 0))); }
        if (preset === 'quarter' || preset === 'last_quarter') {
            var monthsFromFiscal = ((m + 1 - fiscalStart) + 12) % 12, quarterStart = new Date(Date.UTC(y, m - monthsFromFiscal % 3, 1));
            if (preset === 'last_quarter') { end = dayBefore(iso(quarterStart)); quarterStart.setUTCMonth(quarterStart.getUTCMonth() - 3); }
            start = iso(quarterStart);
        }
        if (preset === 'last_year') { end = dayBefore(iso(fiscal)); fiscal.setUTCFullYear(fiscal.getUTCFullYear() - 1); start = iso(fiscal); }
        if (start) { $('dirStart').value = start; $('dirEnd').value = end; }
    }
    function chooseMonth(period) {
        if (!validDate(period)) { return; }
        state.month = period; state.page = 1; syncControls(); writeState(false); load(false);
    }
    function exportReport(action, button) {
        var previousText = button.textContent; button.disabled = true; button.textContent = 'Preparing workbook…'; alertMessage('');
        fetch(config.apiUrl + '?' + parameters(action).toString(), { credentials: 'same-origin', cache: 'no-store' }).then(function (response) {
            var contentType = response.headers.get('Content-Type') || '';
            if (!response.ok || contentType.indexOf('json') >= 0) { return response.json().then(function (payload) { throw new Error(payload.error && payload.error.message ? payload.error.message : 'Export could not be prepared.'); }); }
            if (contentType.indexOf('spreadsheetml') < 0 && contentType.indexOf('octet-stream') < 0) { throw new Error('The server did not return a workbook. Sign in again or retry the export.'); }
            return response.blob();
        }).then(function (blob) {
            var url = URL.createObjectURL(blob), link = document.createElement('a'); link.href = url; link.download = 'directors-' + (action === 'export' ? state.dataset : 'overview') + '-' + state.start + '-' + state.end + '.xlsx'; document.body.appendChild(link); link.click(); link.remove(); window.setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
            $('dirStatus').textContent = action === 'export' ? 'Filtered source-record workbook downloaded.' : 'Company overview workbook downloaded for the applied period.';
        }).catch(function (error) { alertMessage(error.message); }).then(function () { button.disabled = false; button.textContent = previousText; });
    }
    $('dirFilters').addEventListener('submit', applyPeriod);
    $('dirPreset').addEventListener('change', function () { previewPreset(); if (this.value !== 'custom') { submitFilters(); } });
    ['dirStart', 'dirEnd'].forEach(function (id) { $(id).addEventListener('change', function () { $('dirPreset').value = 'custom'; }); });
    $('dirComparison').addEventListener('change', function () { var custom = this.value === 'custom'; $('dirCustomStartWrap').hidden = !custom; $('dirCustomEndWrap').hidden = !custom; $('dirComparisonStart').required = custom; $('dirComparisonEnd').required = custom; if (!custom) { submitFilters(); } });
    $('dirPeriodChip').addEventListener('click', function (event) { event.stopPropagation(); var open = $('dirFilters').hidden; $('dirFilters').hidden = !open; this.setAttribute('aria-expanded', open ? 'true' : 'false'); if (open) { $('dirPreset').focus(); } });
    document.addEventListener('click', function (event) { if (!$('dirFilters').hidden && !$('dirFilters').contains(event.target) && event.target !== $('dirPeriodChip')) { closePopover(); } });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && !$('dirFilters').hidden) { closePopover(); $('dirPeriodChip').focus(); } });
    $('dirRefresh').addEventListener('click', function () { forceRefresh = true; load(true); });
    $('dirBack').addEventListener('click', function () { changeView('overview'); });
    $('dirExportSummary').addEventListener('click', function () { exportReport('export_summary', this); });
    $('dirExport').addEventListener('click', function () { if (state.dataset) { exportReport('export', this); } });
    $('dirDataset').addEventListener('change', function () { if (this.value) { investigate(this.value); } });
    $('dirExploreApply').addEventListener('click', function () { state.group_by = $('dirGroup').value; state.search = $('dirSearch').value.trim(); state.page = 1; writeState(false); load(false); });
    $('dirSearch').addEventListener('keydown', function (event) { if (event.key === 'Enter') { event.preventDefault(); $('dirExploreApply').click(); } });
    $('dirGroup').addEventListener('change', function () { state.group_by = this.value; state.page = 1; writeState(false); load(false); });
    $('dirPageSize').addEventListener('change', function () { state.page_size = Number(this.value); state.page = 1; writeState(false); load(false); });
    $('dirPrevious').addEventListener('click', function () { if (state.page > 1) { state.page--; writeState(false); load(false); } });
    $('dirNext').addEventListener('click', function () { state.page++; writeState(false); load(false); });
    root.addEventListener('click', function (event) {
        var button = event.target.closest('[data-view],[data-metric],[data-risk],[data-health],[data-remove-month],[data-retry],[data-remove-filter],[data-month],[data-breakdown],[data-sort]');
        if (!button || !root.contains(button) || button.disabled) { return; }
        if (button.hasAttribute('data-view')) { changeView(button.getAttribute('data-view')); }
        if (button.hasAttribute('data-metric')) { investigate(button.getAttribute('data-metric')); }
        if (button.hasAttribute('data-health')) { var item = healthItems[Number(button.getAttribute('data-health'))]; if (item) { investigate(item.dataset, item.filters, item.group_by); } }
        if (button.hasAttribute('data-risk')) { var risk = risks[Number(button.getAttribute('data-risk'))]; if (risk) { investigate(risk.dataset, risk.filters, risk.group_by); } }
        if (button.hasAttribute('data-remove-filter')) { delete state.filters[button.getAttribute('data-remove-filter')]; state.page = 1; syncControls(); writeState(false); load(false); }
        if (button.hasAttribute('data-retry')) { load(true); return; }
        if (button.hasAttribute('data-remove-month')) { state.month = ''; state.page = 1; syncControls(); writeState(false); load(false); }
        if (button.hasAttribute('data-month')) { chooseMonth(button.getAttribute('data-month')); }
        if (button.hasAttribute('data-breakdown')) { var row = ($('dirBreakdown')._rows || [])[Number(button.getAttribute('data-breakdown'))]; if (row && state.group_by) { state.filters[state.group_by] = String(row.key == null ? '' : row.key); state.page = 1; syncControls(); writeState(false); load(false); } }
        if (button.hasAttribute('data-sort')) { var sort = button.getAttribute('data-sort'); state.direction = state.sort === sort && state.direction === 'desc' ? 'asc' : 'desc'; state.sort = sort; state.page = 1; writeState(false); load(false); }
    });
    root.addEventListener('keydown', function (event) { if ((event.key === 'Enter' || event.key === ' ') && event.target.matches('g[data-month]')) { event.preventDefault(); chooseMonth(event.target.getAttribute('data-month')); } });
    document.querySelector('.dir-tabs').addEventListener('keydown', function (event) {
        var order = ['overview', 'explore', 'coverage'], at = order.indexOf(state.view), next = event.key === 'ArrowRight' ? at + 1 : event.key === 'ArrowLeft' ? at - 1 : event.key === 'Home' ? 0 : event.key === 'End' ? order.length - 1 : null;
        if (next === null) { return; }
        event.preventDefault(); next = (next + order.length) % order.length; changeView(order[next]); document.querySelector('[data-view="' + order[next] + '"]').focus();
    });
    $('dirTrend').addEventListener('mousemove', function (event) { var t = event.target.closest('.dir-chart-month'); if (t) { showTip(t, event.clientX, event.clientY); } else { hideTip(); } });
    $('dirTrend').addEventListener('mouseleave', hideTip);
    $('dirTrend').addEventListener('focusin', function (event) { var t = event.target.closest('.dir-chart-month'); if (t) { var r = t.getBoundingClientRect(); showTip(t, r.left + r.width / 2, r.top); } });
    $('dirTrend').addEventListener('focusout', hideTip);
    window.addEventListener('popstate', function () { state = readState(); exploration = null; syncControls(); load(true); });
    syncControls(); load(true);
}());
