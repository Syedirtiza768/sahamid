(function () {
    'use strict';
    var config = window.SAHAMID_DIRECTORS || {};
    var root = document.getElementById('directorsRoot');
    if (!root) { return; }
    var overview = null, exploration = null, controller = null, requestNumber = 0;
    var fiscalStart = 1, state = readState(), risks = [], periodDays = null, comparisonDays = null;
    var $ = function (id) { return document.getElementById(id); };
    var esc = function (value) { return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
    var own = function (o, key) { return Object.prototype.hasOwnProperty.call(o || {}, key); };
    var isNumber = function (v) { return v !== null && v !== undefined && v !== '' && isFinite(Number(v)); };
    var number = function (value, decimals) { return isNumber(value) ? Number(value).toLocaleString(undefined, { maximumFractionDigits: decimals == null ? 2 : decimals }) : '—'; };
    var shortNumber = function (v) {
        if (!isNumber(v)) { return '—'; }
        var a = Math.abs(Number(v)), sign = Number(v) < 0 ? '−' : '';
        if (a >= 1e9) { return sign + number(a / 1e9, 2) + 'bn'; }
        if (a >= 1e6) { return sign + number(a / 1e6, 2) + 'm'; }
        if (a >= 1e3) { return sign + number(a / 1e3, 1) + 'k'; }
        return number(v, 2);
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
    function tag(metric) {
        var status = metric.available === false ? 'unavailable' : (metric.status || 'actual');
        var label = status === 'actual' ? 'Actual source' : status === 'estimated' ? 'Estimated' : status === 'unavailable' ? 'Unavailable' : status;
        return '<span class="dir-tag dir-tag-' + esc(status.replace(/[^a-z]/g, '')) + '">' + esc(label) + '</span>';
    }
    function changeText(metric) {
        if (metric.available === false) { return esc(metric.error || 'Source unavailable'); }
        if (isSnapshot(metric)) { return 'Live balance · historical comparison unavailable'; }
        if (state.comparison === 'none') { return 'Comparison disabled'; }
        if (metric.comparison_complete === false && (Number(metric.invalid_amounts) > 0 || Number(metric.comparison_invalid_amounts) > 0)) { return 'Comparison incomplete · review excluded amounts'; }
        if (!isNumber(metric.previous)) { return 'Comparison unavailable'; }
        if (!isNumber(metric.delta)) { return 'Comparison unavailable'; }
        var delta = Number(metric.delta);
        var text = (delta > 0 ? '+' : '') + number(delta);
        return esc(text) + (isNumber(metric.percent) ? ' <span>(' + esc((Number(metric.percent) > 0 ? '+' : '') + number(metric.percent, 1)) + '%)</span>' : ' <span>· no percentage baseline</span>');
    }
    function readState() {
        var p = new URLSearchParams(window.location.search), filters = {};
        try { var parsed = JSON.parse(p.get('filters') || '{}'); if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) { Object.keys(parsed).forEach(function (key) { if (/^[a-z_]+$/.test(key) && typeof parsed[key] === 'string') { filters[key] = parsed[key]; } }); } } catch (ignore) {}
        var view = p.get('view');
        return {
            view: ['overview', 'explore', 'coverage'].indexOf(view) >= 0 ? view : 'overview',
            preset: p.get('preset') || (p.has('start') ? 'custom' : 'ytd'), start: p.get('start') || '', end: p.get('end') || '',
            comparison: p.get('comparison') || 'previous', comparison_start: p.get('comparison_start') || '', comparison_end: p.get('comparison_end') || '',
            dataset: p.get('dataset') || '', group_by: p.get('group_by') || '', search: p.get('search') || '',
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
    function alertMessage(message) { $('dirAlert').textContent = message || ''; $('dirAlert').hidden = !message; }
    function loading(active) { root.classList.toggle('is-loading', active); root.setAttribute('aria-busy', active ? 'true' : 'false'); $('dirRefresh').disabled = active; $('dirExportSummary').disabled = active || !overview; $('dirExport').disabled = active || !exploration || !exploration.details.total; if (active) { $('dirStatus').textContent = 'Loading live source data…'; } }
    function parameters(action) {
        var p = new URLSearchParams();
        p.set('action', action);
        ['preset', 'start', 'end', 'comparison', 'comparison_start', 'comparison_end'].forEach(function (key) { if (state[key]) { p.set(key, state[key]); } });
        if (action === 'explore' || action === 'export') {
            ['dataset', 'group_by', 'search', 'sort', 'direction', 'page', 'page_size'].forEach(function (key) { if (state[key] !== '') { p.set(key, state[key]); } });
            if (Object.keys(state.filters).length) { p.set('filters', JSON.stringify(state.filters)); }
        }
        return p;
    }
    function api(action, signal) {
        return fetch(config.apiUrl + '?' + parameters(action).toString(), { credentials: 'same-origin', cache: 'no-store', signal: signal, headers: { 'Accept': 'application/json' } }).then(function (response) {
            return response.json().catch(function () { throw new Error('The report server did not return a readable response. Refresh or sign in again.'); }).then(function (payload) {
                if (!response.ok || !payload.ok) { throw new Error(payload.error && payload.error.message ? payload.error.message : 'This report could not be loaded.'); }
                return payload.data;
            });
        });
    }
    function useMetadata(metadata) {
        metadata = metadata || {};
        var period = metadata.period || {};
        if (period.start) { state.start = period.start; }
        if (period.end) { state.end = period.end; }
        if (period.comparison_start) { state.comparison_start = period.comparison_start; }
        if (period.comparison_end) { state.comparison_end = period.comparison_end; }
        if (period.comparison === 'none') { state.comparison_start = ''; state.comparison_end = ''; }
        if (period.fiscal_start_month) { fiscalStart = Number(period.fiscal_start_month); }
        if (period.today) { config.today = period.today; }
        periodDays = period.days; comparisonDays = period.comparison_days;
        $('dirCompany').textContent = metadata.company || 'Current company';
        $('dirScope').textContent = typeof metadata.scope === 'string' ? metadata.scope : 'Authorized company · ' + (metadata.currency || 'source units');
        $('dirFreshness').textContent = 'Live source · ' + (metadata.generated_at_utc ? metadata.generated_at_utc + ' UTC' : 'just refreshed');
        $('dirStatus').textContent = 'Live source refreshed' + (isNumber(metadata.server_ms) ? ' · ' + number(metadata.server_ms, 0) + ' ms server time' : '') + ' · ' + (state.view === 'explore' ? 'Investigation filters applied' : 'Company overview');
        syncControls();
    }
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
        var dimensions = dimsFor(metricById(state.dataset));
        $('dirChips').innerHTML = state.view === 'explore' ? Object.keys(state.filters).map(function (key) { return '<button class="dir-chip" type="button" data-remove-filter="' + esc(key) + '" title="Remove this filter">' + esc(dimensions[key] || key) + ': ' + esc(state.filters[key]) + ' ×</button>'; }).join('') : '';
        ['overview', 'explore', 'coverage'].forEach(function (view) { document.querySelector('[data-view="' + view + '"]').classList.toggle('is-active', state.view === view); $('dir' + view.charAt(0).toUpperCase() + view.slice(1)).hidden = state.view !== view; });
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
    function renderOverview(data) {
        overview = data;
        var groups = {};
        data.metrics.forEach(function (metric) { var group = sectionFor(metric); if (!groups[group]) { groups[group] = []; } groups[group].push(metric); });
        $('dirMetricGroups').innerHTML = Object.keys(groups).map(function (group) {
            return '<section><h3 class="dir-group-heading">' + esc(group) + '</h3><div class="dir-card-grid">' + groups[group].map(function (m) {
                return '<button type="button" class="dir-metric" data-metric="' + esc(m.id) + '"' + (!canInvestigate(m) ? ' disabled' : '') + ' title="' + esc(m.definition || m.title) + '"><span class="dir-metric-title">' + esc(m.title) + (Number(m.invalid_amounts) > 0 ? ' · known subtotal' : '') + '</span><strong class="dir-metric-value" title="' + esc(number(m.value) + ' ' + unit(m)) + '">' + (m.available === false ? '—' : esc(shortNumber(m.value))) + '<span class="dir-unit">' + esc(unit(m)) + '</span></strong><span class="dir-metric-change">' + changeText(m) + '</span><span class="dir-metric-bottom">' + tag(m) + '<span>' + esc(Number(m.invalid_amounts) > 0 ? number(m.invalid_amounts, 0) + ' excluded amounts' : basis(m)) + '</span></span></button>';
            }).join('') + '</div></section>';
        }).join('') || '<div class="dir-placeholder">No report measures are available in this company scope.</div>';
        var available = data.metrics.filter(function (m) { return m.available !== false; }).length;
        $('dirCoverageStatus').textContent = available + ' of ' + data.metrics.length + ' measures available';
        risks = data.risks || [];
        $('dirRisks').innerHTML = risks.length ? risks.map(function (risk, index) { return '<article class="dir-risk"><h3>' + esc(risk.title) + '</h3><p>' + esc(risk.message) + '</p>' + (risk.dataset && canInvestigate(metricById(risk.dataset)) ? '<button type="button" data-risk="' + index + '">Investigate records →</button>' : '') + '</article>'; }).join('') : '<div class="dir-placeholder">No configured exception thresholds were triggered. Review the reporting limitations before interpreting this as an absence of business risk.</div>';
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
    function renderTrend(data, metric) {
        var current = data.trend || [], previous = data.comparison_trend || [];
        if (isSnapshot(metric)) {
            $('dirTrend').innerHTML = '<div class="dir-snapshot-note">This is a live snapshot of the current records.<br>Historical balances require retained snapshots or a validated reconstruction. The selected date range does not turn current allocations or quantities into past balances.</div>';
            $('dirTrendTable').innerHTML = ''; return;
        }
        if (!current.length && !previous.length) { $('dirTrend').innerHTML = '<div class="dir-placeholder">No activity was returned for these periods.</div>'; $('dirTrendTable').innerHTML = ''; return; }
        var length = Math.max(current.length, previous.length), values = [];
        current.concat(previous).forEach(function (r) { if (isNumber(r.value)) { values.push(Number(r.value)); } });
        var maximum = Math.max.apply(null, [0].concat(values)), minimum = Math.min.apply(null, [0].concat(values));
        if (maximum === minimum) { maximum = minimum + 1; }
        var width = 650, height = 255, left = 55, right = 10, top = 22, bottom = 40, plotHeight = height - top - bottom, plotWidth = width - left - right;
        var y = function (v) { return top + (maximum - v) / (maximum - minimum) * plotHeight; }, zero = y(0), segment = plotWidth / length;
        var svg = '<svg viewBox="0 0 ' + width + ' ' + height + '" role="img" aria-label="Monthly source values. Blue is current; gray is comparison. Select a month to investigate."><title>Current and comparison monthly values</title>';
        for (var g = 0; g <= 4; g++) { var tick = minimum + (maximum - minimum) * g / 4, ty = y(tick); svg += '<line class="dir-chart-grid" x1="' + left + '" x2="' + (width - right) + '" y1="' + ty + '" y2="' + ty + '"></line><text class="dir-chart-label" x="' + (left - 8) + '" y="' + (ty + 3) + '" text-anchor="end">' + esc(shortNumber(tick)) + '</text>'; }
        var rows = [];
        for (var i = 0; i < length; i++) {
            var c = current[i], p = previous[i], period = c ? trendPeriod(c) : '', cp = p ? trendPeriod(p) : '', center = left + segment * (i + .5), barWidth = Math.min(18, segment * .28);
            var tip = (period ? showDate(period) : 'No current month') + ': ' + (c ? number(c.value) : 'unavailable') + '; comparison ' + (cp ? showDate(cp) : 'unavailable') + ': ' + (p ? number(p.value) : 'unavailable');
            svg += '<g class="dir-chart-month"' + (period ? ' tabindex="0" role="button" data-month="' + esc(period) + '" aria-label="' + esc(tip + '. Investigate this month') + '"' : '') + '><title>' + esc(tip) + '</title>';
            if (p && isNumber(p.value)) { var py = y(Number(p.value)); svg += '<rect class="dir-chart-comparison" x="' + (center - barWidth - 2) + '" y="' + Math.min(py, zero) + '" width="' + barWidth + '" height="' + Math.abs(zero - py) + '" rx="2"></rect>'; }
            if (c && isNumber(c.value)) { var cy = y(Number(c.value)); svg += '<rect class="dir-chart-current" x="' + (center + 2) + '" y="' + Math.min(cy, zero) + '" width="' + barWidth + '" height="' + Math.abs(zero - cy) + '" rx="2"></rect>'; }
            svg += '<rect x="' + (center - segment * .45) + '" y="' + top + '" width="' + (segment * .9) + '" height="' + plotHeight + '" fill="transparent"></rect>';
            if (i % Math.max(1, Math.ceil(length / 8)) === 0 || length < 10) { svg += '<text class="dir-chart-label" x="' + center + '" y="' + (height - 18) + '" text-anchor="middle">' + esc(monthLabel(period || cp)) + '</text>'; }
            svg += '</g>';
            rows.push('<tr><td>' + (period ? '<button type="button" class="dir-chip" data-month="' + esc(period) + '">' + esc(monthLabel(period)) + '</button>' : 'Unavailable') + '</td><td class="dir-number">' + (c ? esc(number(c.value)) : '—') + '</td><td>' + esc(cp ? monthLabel(cp) : '—') + '</td><td class="dir-number">' + (p ? esc(number(p.value)) : '—') + '</td></tr>');
        }
        $('dirTrend').innerHTML = svg + '</svg>';
        $('dirTrendTable').innerHTML = '<table class="dir-table"><thead><tr><th>Month</th><th class="dir-number">Current</th><th>Comparison month</th><th class="dir-number">Comparison</th></tr></thead><tbody>' + rows.join('') + '</tbody></table>';
    }
    function renderBreakdown(data, metric) {
        var rows = (data.breakdown || []).slice(), compare = state.comparison !== 'none' && !isSnapshot(metric);
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
        }).join('') + '</tr>'; }).join('') : '<tr><td class="dir-empty-cell" colspan="' + columns.length + '">No source records match this investigation. Remove filters or choose another period.</td></tr>';
        var total = Number(details.total || 0), page = Number(details.page || state.page), size = Number(details.page_size || state.page_size);
        state.page = page; state.page_size = size;
        $('dirPageStatus').textContent = total ? number((page - 1) * size + 1, 0) + '–' + number(Math.min(page * size, total), 0) + ' of ' + number(total, 0) + ' records' : '0 matching records';
        $('dirPrevious').disabled = page <= 1; $('dirNext').disabled = page * size >= total;
        $('dirRecordContext').textContent = 'The same ' + (isSnapshot(metric) ? 'current snapshot' : 'period') + ', source definition, access rules and investigation filters apply to this table and its export.' + (Number((data.summary || {}).invalid_amounts) > 0 ? ' Monetary totals are known subtotals; unavailable amounts remain visible as —.' : '');
        $('dirExport').disabled = total === 0;
    }
    function changeView(view) { state.view = view; syncControls(); writeState(false); if (view === 'explore' && state.dataset && (!exploration || exploration.metric.id !== state.dataset)) { load(false); } }
    function investigate(id, filters, groupBy) {
        var m = metricById(id); if (!canInvestigate(m)) { return; }
        if (state.dataset !== id) { state.filters = {}; state.group_by = ''; state.search = ''; }
        state.dataset = id; state.view = 'explore'; state.page = 1;
        if (filters) { Object.keys(filters).forEach(function (key) { if (own(dimsFor(m), key)) { state.filters[key] = String(filters[key]); } }); }
        if (groupBy && own(dimsFor(m), groupBy)) { state.group_by = groupBy; }
        renderDimensions(); syncControls(); writeState(false); load(false);
    }
    function load(withOverview) {
        if (controller) { controller.abort(); }
        controller = new AbortController(); var signal = controller.signal, run = ++requestNumber;
        loading(true); alertMessage('');
        var task = withOverview || !overview ? api('overview', signal).then(function (data) { if (run !== requestNumber) { return; } renderOverview(data); useMetadata(data.metadata); }) : Promise.resolve();
        task.then(function () {
            if (run !== requestNumber) { return; }
            if (state.view === 'explore' && state.dataset) {
                if (!canInvestigate(metricById(state.dataset))) { throw new Error('This measure is unavailable or outside your current access. Choose an available measure from the overview.'); }
                return api('explore', signal).then(function (data) { if (run !== requestNumber) { return; } renderExploration(data); useMetadata(data.metadata); });
            }
            if (state.view === 'explore') { $('dirInvestigationEmpty').hidden = false; $('dirInvestigation').hidden = true; }
        }).then(function () {
            if (run !== requestNumber) { return; }
            loading(false); syncControls(); writeState(true);
        }).catch(function (error) {
            if (error.name === 'AbortError' || run !== requestNumber) { return; }
            loading(false); alertMessage(error.message); $('dirStatus').textContent = 'Refresh failed · displayed data may be from the previous refresh';
            $('dirExport').disabled = true; $('dirExportSummary').disabled = true;
            if (state.view === 'explore') { $('dirInvestigation').hidden = true; }
            if (!overview) { $('dirMetricGroups').innerHTML = '<div class="dir-placeholder">The dashboard could not load. Use Refresh to retry after the source or access issue is resolved.</div>'; $('dirRisks').innerHTML = ''; }
        });
    }
    function applyPeriod(event) {
        event.preventDefault();
        state.preset = $('dirPreset').value; state.start = $('dirStart').value; state.end = $('dirEnd').value;
        state.comparison = $('dirComparison').value; state.comparison_start = $('dirComparisonStart').value; state.comparison_end = $('dirComparisonEnd').value;
        if (!validDate(state.start) || !validDate(state.end) || state.start > state.end) { alertMessage('Choose valid reporting dates, with From no later than To.'); return; }
        if (state.comparison === 'custom' && (!validDate(state.comparison_start) || !validDate(state.comparison_end) || state.comparison_start > state.comparison_end)) { alertMessage('Choose valid custom comparison dates.'); return; }
        state.page = 1; exploration = null; writeState(false); load(true);
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
        var d = dateUTC(period), last = iso(new Date(Date.UTC(d.getUTCFullYear(), d.getUTCMonth() + 1, 0)));
        state.start = period > state.start ? period : state.start; state.end = last < state.end ? last : state.end;
        state.preset = 'custom'; state.page = 1; syncControls(); writeState(false); load(true);
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
    $('dirPreset').addEventListener('change', previewPreset);
    ['dirStart', 'dirEnd'].forEach(function (id) { $(id).addEventListener('change', function () { $('dirPreset').value = 'custom'; }); });
    $('dirComparison').addEventListener('change', function () { var custom = this.value === 'custom'; $('dirCustomStartWrap').hidden = !custom; $('dirCustomEndWrap').hidden = !custom; $('dirComparisonStart').required = custom; $('dirComparisonEnd').required = custom; });
    $('dirRefresh').addEventListener('click', function () { load(true); });
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
        var button = event.target.closest('[data-view],[data-metric],[data-risk],[data-remove-filter],[data-month],[data-breakdown],[data-sort]');
        if (!button || !root.contains(button) || button.disabled) { return; }
        if (button.hasAttribute('data-view')) { changeView(button.getAttribute('data-view')); }
        if (button.hasAttribute('data-metric')) { investigate(button.getAttribute('data-metric')); }
        if (button.hasAttribute('data-risk')) { var risk = risks[Number(button.getAttribute('data-risk'))]; if (risk) { investigate(risk.dataset, risk.filters, risk.group_by); } }
        if (button.hasAttribute('data-remove-filter')) { delete state.filters[button.getAttribute('data-remove-filter')]; state.page = 1; syncControls(); writeState(false); load(false); }
        if (button.hasAttribute('data-month')) { chooseMonth(button.getAttribute('data-month')); }
        if (button.hasAttribute('data-breakdown')) { var row = ($('dirBreakdown')._rows || [])[Number(button.getAttribute('data-breakdown'))]; if (row && state.group_by) { state.filters[state.group_by] = String(row.key == null ? '' : row.key); state.page = 1; syncControls(); writeState(false); load(false); } }
        if (button.hasAttribute('data-sort')) { var sort = button.getAttribute('data-sort'); state.direction = state.sort === sort && state.direction === 'desc' ? 'asc' : 'desc'; state.sort = sort; state.page = 1; writeState(false); load(false); }
    });
    root.addEventListener('keydown', function (event) { if ((event.key === 'Enter' || event.key === ' ') && event.target.matches('g[data-month]')) { event.preventDefault(); chooseMonth(event.target.getAttribute('data-month')); } });
    window.addEventListener('popstate', function () { state = readState(); exploration = null; syncControls(); load(true); });
    syncControls(); load(true);
}());
