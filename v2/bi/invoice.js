(function (window, document) {
    'use strict';

    var config = window.SAHAMID_INVOICE_BI || {};
    var root = document.getElementById('invoiceReportRoot');
    if (!root) {
        return;
    }

    var charts = {};
    var state = {
        preset: 'ytd',
        start: config.defaultStart || '',
        end: config.defaultEnd || '',
        salesperson: '',
        search: '',
        page: 1,
        pageSize: 50,
        sort: 'date',
        direction: 'asc'
    };

    function byId(id) { return document.getElementById(id); }

    function pad(value) { return value < 10 ? '0' + value : String(value); }

    function isoDate(date) {
        return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
    }

    function startOfWeek(date) {
        var result = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        var day = result.getDay();
        result.setDate(result.getDate() + (day === 0 ? -6 : 1 - day));
        return result;
    }

    function applyPreset(value) {
        var today = new Date();
        var rangeStart = null;
        var rangeEnd = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        var monthStart;
        var quarterStart;
        var weekStart;

        if (value === 'today') {
            rangeStart = rangeEnd;
        } else if (value === 'yesterday') {
            rangeStart = new Date(rangeEnd);
            rangeStart.setDate(rangeStart.getDate() - 1);
            rangeEnd = new Date(rangeStart);
        } else if (value === 'week') {
            rangeStart = startOfWeek(today);
        } else if (value === 'last_week') {
            weekStart = startOfWeek(today);
            rangeEnd = new Date(weekStart);
            rangeEnd.setDate(rangeEnd.getDate() - 1);
            rangeStart = new Date(rangeEnd);
            rangeStart.setDate(rangeStart.getDate() - 6);
        } else if (value === 'month') {
            rangeStart = new Date(today.getFullYear(), today.getMonth(), 1);
        } else if (value === 'last_month') {
            monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
            rangeEnd = new Date(monthStart);
            rangeEnd.setDate(rangeEnd.getDate() - 1);
            rangeStart = new Date(rangeEnd.getFullYear(), rangeEnd.getMonth(), 1);
        } else if (value === 'quarter') {
            quarterStart = Math.floor(today.getMonth() / 3) * 3;
            rangeStart = new Date(today.getFullYear(), quarterStart, 1);
        } else if (value === 'last_quarter') {
            quarterStart = Math.floor(today.getMonth() / 3) * 3;
            rangeStart = new Date(today.getFullYear(), quarterStart - 3, 1);
            rangeEnd = new Date(today.getFullYear(), quarterStart, 0);
        } else if (value === 'last_7' || value === 'last_30' || value === 'last_90') {
            var days = value === 'last_7' ? 6 : (value === 'last_30' ? 29 : 89);
            rangeStart = new Date(rangeEnd);
            rangeStart.setDate(rangeStart.getDate() - days);
        } else if (value === 'year') {
            rangeStart = new Date(today.getFullYear(), 0, 1);
        } else if (value === 'last_year') {
            rangeStart = new Date(today.getFullYear() - 1, 0, 1);
            rangeEnd = new Date(today.getFullYear() - 1, 11, 31);
        } else if (value === 'all') {
            rangeStart = new Date(1000, 0, 1);
        } else {
            rangeStart = new Date(today.getFullYear(), 0, 1);
        }
        state.start = rangeStart ? isoDate(rangeStart) : '';
        state.end = rangeEnd ? isoDate(rangeEnd) : '';
    }

    function escapeHtml(value) {
        return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, function (character) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[character];
        });
    }

    function formatNumber(value) {
        return Number(value || 0).toLocaleString('en-US', {maximumFractionDigits: 2});
    }

    function formatMoney(value) {
        return 'PKR ' + formatNumber(value);
    }

    function showAlert(message, type) {
        var alert = byId('invoiceAlert');
        alert.className = 'alert invoice-alert alert-' + (type || 'info');
        alert.innerHTML = message;
        alert.style.display = 'block';
    }

    function hideAlert() { byId('invoiceAlert').style.display = 'none'; }

    function syncControls() {
        byId('invoiceDatePreset').value = state.preset;
        byId('invoiceStart').value = state.start;
        byId('invoiceEnd').value = state.end;
        byId('invoiceSalesperson').value = state.salesperson;
        byId('invoiceSearch').value = state.search;
    }

    function syncUrl() {
        var url = new URL(window.location.href);
        url.searchParams.set('preset', state.preset);
        url.searchParams.set('start', state.start);
        url.searchParams.set('end', state.end);
        if (state.salesperson) { url.searchParams.set('salesperson', state.salesperson); } else { url.searchParams.delete('salesperson'); }
        if (state.search) { url.searchParams.set('search', state.search); } else { url.searchParams.delete('search'); }
        window.history.replaceState({}, '', url.toString());
    }

    function renderChips() {
        var output = [];
        output.push('<span class="invoice-filter-chip">Date: ' + escapeHtml(state.start || 'All') + ' → ' + escapeHtml(state.end || 'All') + '</span>');
        if (state.salesperson) { output.push('<span class="invoice-filter-chip">Salesperson: ' + escapeHtml(state.salesperson) + '</span>'); }
        if (state.search) { output.push('<span class="invoice-filter-chip">Search: ' + escapeHtml(state.search) + '</span>'); }
        byId('invoiceFilterChips').innerHTML = output.join('');
        byId('invoiceAppliedPeriod').textContent = (state.start || 'All') + ' → ' + (state.end || 'All');
    }

    function savedViewsKey() {
        return 'sahamid.bi.saved-views.invoice-value';
    }

    function readSavedViews() {
        try {
            return JSON.parse(window.localStorage.getItem(savedViewsKey()) || '[]');
        } catch (error) {
            return [];
        }
    }

    function renderSavedViews() {
        var select = byId('invoiceSavedView');
        if (!select) { return; }
        select.innerHTML = '<option value="">Saved views</option>' + readSavedViews().map(function (view, index) {
            return '<option value="' + index + '">' + escapeHtml(view.name) + '</option>';
        }).join('');
    }

    function saveView() {
        var name = window.prompt('Name this private saved view:', 'My invoice value view');
        if (!name) { return; }
        var views = readSavedViews();
        views.push({name: name, state: {preset: state.preset, start: state.start, end: state.end, salesperson: state.salesperson, search: state.search}, savedAt: new Date().toISOString()});
        window.localStorage.setItem(savedViewsKey(), JSON.stringify(views.slice(-10)));
        renderSavedViews();
        showAlert('Saved “' + escapeHtml(name) + '” in this browser. It contains filter state only and is private to this device.', 'success');
    }

    function restoreSavedView(index) {
        var view = readSavedViews()[Number(index)];
        if (!view || !view.state) { return; }
        state.preset = view.state.preset || 'custom';
        state.start = view.state.start || '';
        state.end = view.state.end || '';
        state.salesperson = view.state.salesperson || '';
        state.search = view.state.search || '';
        state.page = 1;
        syncControls();
        syncUrl();
        loadReport();
    }

    function queryUrl(action, extra) {
        var params = {
            action: action,
            start: state.start,
            end: state.end,
            salesperson: state.salesperson,
            search: state.search
        };
        var key;
        if (extra) {
            for (key in extra) {
                if (Object.prototype.hasOwnProperty.call(extra, key)) { params[key] = extra[key]; }
            }
        }
        var query = [];
        for (key in params) {
            if (Object.prototype.hasOwnProperty.call(params, key) && params[key] !== '') {
                query.push(encodeURIComponent(key) + '=' + encodeURIComponent(params[key]));
            }
        }
        return config.apiUrl + '?' + query.join('&');
    }

    function request(action, extra) {
        return window.fetch(queryUrl(action, extra), {credentials: 'same-origin', headers: {'Accept': 'application/json'}}).then(function (response) {
            return response.json().then(function (payload) {
                if (!response.ok || !payload.ok) {
                    var message = payload.error && payload.error.message ? payload.error.message : 'The invoice report request failed.';
                    throw new Error(message);
                }
                return payload.data;
            });
        });
    }

    function setChartEmpty(canvasId, emptyId, visible) {
        byId(canvasId).style.display = visible ? 'block' : 'none';
        byId(emptyId).style.display = visible ? 'none' : 'block';
    }

    function destroyChart(name) {
        if (charts[name] && typeof charts[name].destroy === 'function') { charts[name].destroy(); }
        charts[name] = null;
    }

    function makeChart(name, canvasId, rows) {
        var canvas = byId(canvasId);
        if (!window.Chart || !rows.length) { return false; }
        destroyChart(name);
        charts[name] = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: rows.map(function (row) { return row.period_start; }),
                datasets: [
                    {label: 'Invoice value', data: rows.map(function (row) { return row.total_value; }), backgroundColor: 'rgba(29,121,200,.72)', borderColor: '#1d79c8', borderWidth: 1},
                    {label: 'Invoices', data: rows.map(function (row) { return row.invoice_count; }), type: 'line', borderColor: '#7a4bb7', backgroundColor: 'rgba(122,75,183,.12)', fill: false, lineTension: .25, yAxisID: 'count'}
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {position: 'bottom', labels: {boxWidth: 12, fontSize: 10}},
                scales: {
                    yAxes: [
                        {id: 'value', position: 'left', ticks: {beginAtZero: true, callback: function (value) { return 'PKR ' + Number(value).toLocaleString('en-US'); }}, gridLines: {color: '#edf1f5'}},
                        {id: 'count', position: 'right', ticks: {beginAtZero: true}, gridLines: {display: false}}
                    ],
                    xAxes: [{gridLines: {display: false}}]
                },
                tooltips: {callbacks: {label: function (tooltipItem, chartData) { var dataset = chartData.datasets[tooltipItem.datasetIndex]; return dataset.label + ': ' + (dataset.label === 'Invoice value' ? formatMoney(tooltipItem.yLabel) : formatNumber(tooltipItem.yLabel)); }}}
            }
        });
        return true;
    }

    function renderTrend(rows) {
        rows = rows || [];
        setChartEmpty('invoiceTrendChart', 'invoiceTrendEmpty', rows.length > 0);
        setChartEmpty('invoiceTrendChartLarge', 'invoiceTrendLargeEmpty', rows.length > 0);
        byId('invoiceTrendStatus').textContent = rows.length ? rows.length + ' month' + (rows.length === 1 ? '' : 's') : 'No matching months';
        byId('invoiceTrendFallback').innerHTML = rows.length && !window.Chart ? '<table><thead><tr><th>Month</th><th>Value</th><th>Invoices</th></tr></thead><tbody>' + rows.map(function (row) { return '<tr><td>' + escapeHtml(row.period_start) + '</td><td>' + formatMoney(row.total_value) + '</td><td>' + formatNumber(row.invoice_count) + '</td></tr>'; }).join('') + '</tbody></table>' : '';
        destroyChart('trend');
        destroyChart('trendLarge');
        if (rows.length && window.Chart) {
            makeChart('trend', 'invoiceTrendChart', rows);
            makeChart('trendLarge', 'invoiceTrendChartLarge', rows);
        }
    }

    function renderSummary(data) {
        var summary = data.summary || {};
        byId('invoiceKpiValue').textContent = formatMoney(summary.total_value);
        byId('invoiceKpiCount').textContent = formatNumber(summary.invoice_count);
        byId('invoiceKpiAverage').textContent = formatMoney(summary.average_invoice_value);
        byId('invoiceKpiRows').textContent = formatNumber(summary.detail_option_rows);
        byId('invoiceSubtitle').textContent = 'Showing ' + (summary.first_invoice_date || state.start) + ' through ' + (summary.last_invoice_date || state.end) + ' · ' + formatNumber(summary.invoice_count) + ' distinct invoices.';
        byId('invoiceReportStatus').textContent = 'Loaded';
        byId('invoiceContext').textContent = state.salesperson ? 'Salesperson ' + state.salesperson + ' · live source' : 'All authorized salespeople · live source';
        byId('invoiceScopeText').textContent = state.salesperson ? 'Salesperson ' + state.salesperson : 'All authorized salespeople or ERP session scope';
        byId('invoiceFreshness').textContent = data.metadata && data.metadata.freshness ? 'Live as of ' + data.metadata.freshness.as_of_utc + ' UTC' : 'Live source query';
    }

    function renderDetails(data) {
        var rows = data.rows || [];
        var pagination = data.pagination || {};
        var body = byId('invoiceDetailTable').querySelector('tbody');
        byId('invoiceDetailStatus').textContent = formatNumber(pagination.total_rows || 0) + ' matching detail rows';
        byId('invoicePageStatus').textContent = 'Page ' + (pagination.page || 1) + ' of ' + (pagination.total_pages || 1) + ' · ' + formatNumber(pagination.total_rows || 0) + ' rows';
        byId('invoicePrevPage').disabled = (pagination.page || 1) <= 1;
        byId('invoiceNextPage').disabled = (pagination.page || 1) >= (pagination.total_pages || 1);
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="11" class="invoice-empty-cell">No invoice option detail matches the selected controls.</td></tr>';
            return;
        }
        body.innerHTML = rows.map(function (row) {
            return '<tr><td>' + escapeHtml(row.invoicesdate) + '</td><td><button type="button" class="invoice-drill-link" data-invoice-no="' + escapeHtml(row.invoiceno) + '">' + escapeHtml(row.invoiceno) + '</button></td><td>' + escapeHtml(row.salescaseref) + '</td><td>' + escapeHtml(row.salesperson) + '</td><td><code>' + escapeHtml(row.stkcode) + '</code></td><td>' + escapeHtml(row.narrative) + '</td><td class="text-right">' + formatMoney(row.unitprice) + '</td><td class="text-right">' + formatNumber(Number(row.discountpercent) * 100) + '%</td><td class="text-right">' + formatNumber(row.quantity) + '</td><td class="text-right">' + formatNumber(row.option_quantity) + '</td><td class="text-right"><strong>' + formatMoney(row.line_value) + '</strong></td></tr>';
        }).join('');
    }

    function loadDetails() {
        byId('invoiceDetailStatus').textContent = 'Loading…';
        return request('details', {page: state.page, pageSize: state.pageSize, sort: state.sort, direction: state.direction}).then(renderDetails);
    }

    function loadReport() {
        root.setAttribute('aria-busy', 'true');
        byId('invoiceReportStatus').textContent = 'Loading';
        hideAlert();
        renderChips();
        return Promise.all([request('summary'), request('trend'), request('details', {page: state.page, pageSize: state.pageSize, sort: state.sort, direction: state.direction})]).then(function (results) {
            renderSummary(results[0]);
            renderTrend(results[1].trend || []);
            renderDetails(results[2]);
            byId('invoiceLastRefreshed').textContent = new Date().toLocaleString();
        }).catch(function (error) {
            byId('invoiceReportStatus').textContent = 'Error';
            showAlert(escapeHtml(error.message || 'The invoice report could not be loaded.'), 'danger');
        }).then(function () {
            root.setAttribute('aria-busy', 'false');
        });
    }

    function restoreState() {
        var params = new URLSearchParams(window.location.search);
        var preset = params.get('preset') || 'ytd';
        state.preset = preset;
        state.start = params.has('start') ? (params.get('start') || '') : state.start;
        state.end = params.has('end') ? (params.get('end') || '') : state.end;
        if (preset === 'all' && !params.has('start')) { applyPreset('all'); }
        state.salesperson = params.get('salesperson') || '';
        state.search = params.get('search') || '';
        syncControls();
        syncUrl();
        renderChips();
    }

    byId('invoiceDatePreset').addEventListener('change', function () { state.preset = this.value; if (this.value !== 'custom') { applyPreset(this.value); } syncControls(); });
    byId('invoiceStart').addEventListener('change', function () { state.preset = 'custom'; state.start = this.value; byId('invoiceDatePreset').value = 'custom'; });
    byId('invoiceEnd').addEventListener('change', function () { state.preset = 'custom'; state.end = this.value; byId('invoiceDatePreset').value = 'custom'; });
    byId('invoiceApply').addEventListener('click', function () {
        state.start = byId('invoiceStart').value;
        state.end = byId('invoiceEnd').value;
        state.salesperson = byId('invoiceSalesperson').value.trim();
        state.search = byId('invoiceSearch').value.trim();
        state.page = 1;
        if (!state.start || !state.end) { showAlert('Choose both a start and end date. Use “All available dates” to include the full available range.', 'warning'); return; }
        if (state.start > state.end) { showAlert('The start date cannot be after the end date.', 'warning'); return; }
        syncUrl();
        loadReport();
    });
    byId('invoiceReset').addEventListener('click', function () { state.preset = 'ytd'; state.salesperson = ''; state.search = ''; applyPreset('ytd'); syncControls(); state.page = 1; syncUrl(); loadReport(); });
    byId('invoiceRefresh').addEventListener('click', function () { loadReport(); });
    byId('invoiceExport').addEventListener('click', function () { window.location.href = queryUrl('export'); });
    byId('invoiceSaveView').addEventListener('click', saveView);
    byId('invoiceSavedView').addEventListener('change', function () { if (this.value !== '') { restoreSavedView(this.value); this.value = ''; } });
    byId('invoicePageSize').addEventListener('change', function () { state.pageSize = Number(this.value); state.page = 1; loadDetails(); });
    byId('invoicePrevPage').addEventListener('click', function () { if (state.page > 1) { state.page--; loadDetails(); } });
    byId('invoiceNextPage').addEventListener('click', function () { state.page++; loadDetails(); });
    byId('invoiceDetailTable').querySelector('thead').addEventListener('click', function (event) {
        var header = event.target.closest('[data-invoice-sort]');
        if (!header) { return; }
        var selected = header.getAttribute('data-invoice-sort');
        if (state.sort === selected) { state.direction = state.direction === 'asc' ? 'desc' : 'asc'; } else { state.sort = selected; state.direction = 'asc'; }
        state.page = 1;
        loadDetails();
    });
    byId('invoiceDetailTable').querySelector('tbody').addEventListener('click', function (event) {
        var button = event.target.closest('[data-invoice-no]');
        if (!button) { return; }
        state.search = button.getAttribute('data-invoice-no');
        state.page = 1;
        byId('invoiceSearch').value = state.search;
        syncUrl();
        renderChips();
        var detailsTab = document.querySelector('[data-invoice-tab="details"]');
        if (detailsTab) { detailsTab.click(); }
        loadReport();
    });
    Array.prototype.forEach.call(document.querySelectorAll('[data-invoice-tab]'), function (tab) {
        tab.addEventListener('click', function () {
            var selected = this.getAttribute('data-invoice-tab');
            Array.prototype.forEach.call(document.querySelectorAll('[data-invoice-tab]'), function (item) { item.classList.toggle('is-active', item === tab); });
            Array.prototype.forEach.call(document.querySelectorAll('[data-invoice-view]'), function (view) { var visible = view.getAttribute('data-invoice-view') === selected; view.classList.toggle('is-active', visible); if (visible) { view.removeAttribute('hidden'); } else { view.setAttribute('hidden', 'hidden'); } });
            if (window.history && window.history.replaceState) { window.history.replaceState({}, '', window.location.pathname + window.location.search + '#' + selected); }
        });
    });

    restoreState();
    renderSavedViews();
    loadReport();
})(window, document);
