(function (window, document) {
    'use strict';

    var config = window.SAHAMID_SUPPLIER_BI || {};
    var root = document.getElementById('supplierReportRoot');
    if (!root || !config.apiUrl) { return; }

    var state = {
        preset: 'ytd', start: config.defaultStart || '', end: config.defaultEnd || '', search: '',
        supplierIds: [], supplierTypes: [], paymentTerms: [], transactionTypes: [],
        status: 'all', aging: 'all', attention: 'all', dueFrom: '', dueTo: '', minOutstanding: '', maxOutstanding: '',
        portfolioPage: 1, portfolioPageSize: 50, portfolioSort: 'supplier', portfolioDirection: 'asc',
        detailPage: 1, detailPageSize: 50, detailSort: 'date', detailDirection: 'desc'
    };
    var lookups = {suppliers: [], supplierMap: {}, supplierTypes: [], paymentTerms: [], transactionTypes: []};
    var charts = {ageing: null, trend: null};
    var lookupsLoaded = false;
    var requestVersion = 0;
    var activeCurrency = '';
    var hiddenColumns = loadJson('sahamid.bi.supplier-columns', {portfolio: [], details: []});
    var compactRows = window.localStorage.getItem('sahamid.bi.supplier-density') === 'compact';

    function byId(id) { return document.getElementById(id); }
    function escapeHtml(value) { return String(value == null ? '' : value).replace(/[&<>"']/g, function (character) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[character]; }); }
    function decodeText(value) { var element = document.createElement('textarea'); element.innerHTML = String(value == null ? '' : value); return element.value; }
    function number(value) { return Number(value || 0); }
    function formatNumber(value) { return number(value).toLocaleString('en-US', {maximumFractionDigits: 2}); }
    function formatMoney(value, currency) { return (currency ? escapeHtml(currency) + ' ' : '') + formatNumber(value); }
    function formatDate(value) { return value && value !== '0000-00-00' ? String(value).substring(0, 10) : '—'; }
    function plural(value, singular, pluralWord) { return formatNumber(value) + ' ' + (number(value) === 1 ? singular : (pluralWord || singular + 's')); }
    function pad(value) { return value < 10 ? '0' + value : String(value); }
    function isoDate(date) { return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()); }
    function startOfWeek(date) { var result = new Date(date.getFullYear(), date.getMonth(), date.getDate()); var day = result.getDay(); result.setDate(result.getDate() + (day === 0 ? -6 : 1 - day)); return result; }
    function loadJson(key, fallback) { try { var value = JSON.parse(window.localStorage.getItem(key) || 'null'); return value || fallback; } catch (error) { return fallback; } }
    function unique(values) { var seen = {}; return (values || []).map(String).filter(function (value) { if (!value || seen[value]) { return false; } seen[value] = true; return true; }); }
    function parseList(value) { return unique(String(value || '').split(',').map(function (item) { return item.trim(); })); }

    function applyPreset(value) {
        var today = new Date();
        var end = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        var start = null;
        var monthStart;
        var quarterStart;
        if (value === 'today') { start = end; }
        else if (value === 'yesterday') { start = new Date(end); start.setDate(start.getDate() - 1); end = new Date(start); }
        else if (value === 'this_week') { start = startOfWeek(today); }
        else if (value === 'last_week') { start = startOfWeek(today); end = new Date(start); end.setDate(end.getDate() - 1); start = new Date(end); start.setDate(start.getDate() - 6); }
        else if (value === 'this_month' || value === 'month') { start = new Date(today.getFullYear(), today.getMonth(), 1); }
        else if (value === 'last_month') { monthStart = new Date(today.getFullYear(), today.getMonth(), 1); end = new Date(monthStart); end.setDate(end.getDate() - 1); start = new Date(end.getFullYear(), end.getMonth(), 1); }
        else if (value === 'this_quarter') { quarterStart = Math.floor(today.getMonth() / 3) * 3; start = new Date(today.getFullYear(), quarterStart, 1); }
        else if (value === 'last_quarter') { quarterStart = Math.floor(today.getMonth() / 3) * 3; start = new Date(today.getFullYear(), quarterStart - 3, 1); end = new Date(today.getFullYear(), quarterStart, 0); }
        else if (value === 'last_7' || value === 'last_30' || value === 'last_90' || value === 'quarter') { start = new Date(end); start.setDate(start.getDate() - (value === 'last_7' ? 6 : (value === 'last_30' ? 29 : 89))); }
        else if (value === 'this_year' || value === 'ytd') { start = new Date(today.getFullYear(), 0, 1); }
        else if (value === 'last_year') { start = new Date(today.getFullYear() - 1, 0, 1); end = new Date(today.getFullYear() - 1, 11, 31); }
        else if (value === 'all') { state.start = ''; state.end = isoDate(end); return; }
        else { start = new Date(today.getFullYear(), 0, 1); }
        state.start = start ? isoDate(start) : '';
        state.end = end ? isoDate(end) : '';
    }

    function showAlert(message, type) { var alert = byId('supplierAlert'); alert.className = 'alert supplier-alert alert-' + (type || 'info'); alert.textContent = message; alert.style.display = 'block'; }
    function hideAlert() { byId('supplierAlert').style.display = 'none'; }
    function supplierName(id) { var row = lookups.supplierMap[String(id)]; return row ? decodeText(row.suppname) : String(id); }
    function selectedLabel(rows, valueField, labelField, selected) { var map = {}; (rows || []).forEach(function (row) { map[String(row[valueField])] = decodeText(row[labelField]); }); return (selected || []).map(function (value) { return map[String(value)] || String(value); }); }
    function syncFilterGroup(containerId, stateKey) { var selected = state[stateKey].map(String); byId(containerId).querySelectorAll('input[data-array-key]').forEach(function (input) { input.checked = selected.indexOf(String(input.value)) !== -1; }); }
    function advancedFilterCount() { return (state.supplierTypes.length ? 1 : 0) + (state.paymentTerms.length ? 1 : 0) + (state.transactionTypes.length ? 1 : 0) + (state.attention !== 'all' ? 1 : 0) + (state.dueFrom ? 1 : 0) + (state.dueTo ? 1 : 0) + (state.minOutstanding !== '' ? 1 : 0) + (state.maxOutstanding !== '' ? 1 : 0); }
    function syncControls() {
        byId('supplierDatePreset').value = state.preset;
        byId('supplierStart').value = state.start;
        byId('supplierEnd').value = state.end;
        byId('supplierSearch').value = state.search;
        byId('supplierStatus').value = state.status;
        byId('supplierAging').value = state.aging;
        byId('supplierAttention').value = state.attention;
        byId('supplierDueFrom').value = state.dueFrom;
        byId('supplierDueTo').value = state.dueTo;
        byId('supplierMinOutstanding').value = state.minOutstanding;
        byId('supplierMaxOutstanding').value = state.maxOutstanding;
        byId('supplierGroupBy').value = state.portfolioSort;
        if (lookupsLoaded) {
            syncFilterGroup('supplierTypeOptions', 'supplierTypes');
            syncFilterGroup('supplierPaymentTermOptions', 'paymentTerms');
            syncFilterGroup('supplierTransactionTypeOptions', 'transactionTypes');
            renderSupplierPicker();
        }
        var count = advancedFilterCount();
        byId('supplierAdvancedCount').textContent = count;
        if (count) { document.querySelector('.supplier-advanced-fields').removeAttribute('hidden'); byId('supplierAdvancedToggle').setAttribute('aria-expanded', 'true'); }
        renderQuickViews();
        renderChips();
    }

    function syncUrl() {
        if (!window.history || !window.history.replaceState) { return; }
        var url = new URL(window.location.href);
        var scalar = {preset:state.preset, start:state.start, end:state.end, search:state.search, status:state.status, aging:state.aging, attention:state.attention, dueFrom:state.dueFrom, dueTo:state.dueTo, minOutstanding:state.minOutstanding, maxOutstanding:state.maxOutstanding};
        Object.keys(scalar).forEach(function (key) { if (scalar[key] !== '' && scalar[key] !== null) { url.searchParams.set(key, scalar[key]); } else { url.searchParams.delete(key); } });
        [['supplierIds',state.supplierIds],['supplierTypes',state.supplierTypes],['paymentTerms',state.paymentTerms],['transactionTypes',state.transactionTypes]].forEach(function (entry) { if (entry[1].length) { url.searchParams.set(entry[0], entry[1].join(',')); } else { url.searchParams.delete(entry[0]); } });
        url.searchParams.delete('currency');
        url.searchParams.delete('supplierId');
        window.history.replaceState({}, '', url.toString());
    }

    function ageLabel(bucket) { return {current:'Current / not due',due:'Due now',overdue1:'Overdue threshold 1',overdue2:'Overdue threshold 2+',credits:'Credits / overpayments',undated:'No due date',settled:'Settled'}[bucket] || bucket || 'Other'; }
    function attentionLabel(value) { return {missing_due:'Missing due date',on_hold:'Open and on hold',zero_unsettled:'Zero balance, not settled',unapplied_credit:'Unapplied credit / overpayment'}[value] || value; }
    function chip(label, key) { return '<span class="supplier-filter-chip">' + escapeHtml(label) + (key ? '<button type="button" data-clear-filter="' + escapeHtml(key) + '" aria-label="Remove ' + escapeHtml(label) + '">&times;</button>' : '') + '</span>'; }
    function renderChips() {
        var chips = [chip((state.start || 'All history') + ' → ' + (state.end || 'Current'), '')];
        if (state.supplierIds.length) { chips.push(chip(state.supplierIds.length + ' suppliers selected', 'supplierIds')); }
        if (state.search) { chips.push(chip('Search: ' + state.search, 'search')); }
        if (state.status !== 'all') { chips.push(chip('Status: ' + state.status, 'status')); }
        if (state.aging !== 'all') { chips.push(chip('Ageing: ' + ageLabel(state.aging), 'aging')); }
        if (state.attention !== 'all') { chips.push(chip('Control: ' + attentionLabel(state.attention), 'attention')); }
        if (state.supplierTypes.length) { chips.push(chip('Supplier types: ' + selectedLabel(lookups.supplierTypes, 'typeid', 'typename', state.supplierTypes).join(', '), 'supplierTypes')); }
        if (state.paymentTerms.length) { chips.push(chip('Terms: ' + selectedLabel(lookups.paymentTerms, 'termsindicator', 'terms', state.paymentTerms).join(', '), 'paymentTerms')); }
        if (state.transactionTypes.length) { chips.push(chip('Transactions: ' + selectedLabel(lookups.transactionTypes, 'typeid', 'typename', state.transactionTypes).join(', '), 'transactionTypes')); }
        if (state.dueFrom || state.dueTo) { chips.push(chip('Due: ' + (state.dueFrom || 'Any') + ' → ' + (state.dueTo || 'Any'), 'dueDates')); }
        if (state.minOutstanding !== '' || state.maxOutstanding !== '') { chips.push(chip('Outstanding: ' + (state.minOutstanding || 'Any') + ' → ' + (state.maxOutstanding || 'Any'), 'amounts')); }
        byId('supplierFilterChips').innerHTML = chips.join('');
        byId('supplierAppliedPeriod').textContent = (state.start || 'All history') + ' → ' + (state.end || 'Current');
    }

    function queryUrl(action, extra) {
        var params = {action:action,preset:state.preset,start:state.start,end:state.end,search:state.search,supplierIds:state.supplierIds.join(','),supplierTypes:state.supplierTypes.join(','),paymentTerms:state.paymentTerms.join(','),transactionTypes:state.transactionTypes.join(','),status:state.status,aging:state.aging,attention:state.attention,dueFrom:state.dueFrom,dueTo:state.dueTo,minOutstanding:state.minOutstanding,maxOutstanding:state.maxOutstanding};
        Object.keys(extra || {}).forEach(function (key) { params[key] = extra[key]; });
        return config.apiUrl + '?' + Object.keys(params).filter(function (key) { return params[key] !== '' && params[key] !== null && params[key] !== undefined; }).map(function (key) { return encodeURIComponent(key) + '=' + encodeURIComponent(params[key]); }).join('&');
    }
    function request(action, extra) {
        return window.fetch(queryUrl(action, extra), {credentials:'same-origin', headers:{'Accept':'application/json'}}).then(function (response) {
            return response.text().then(function (raw) { var payload; try { payload = JSON.parse(raw); } catch (error) { throw new Error('The supplier report API returned an unexpected response. Refresh your ERP session and try again.'); } if (!response.ok || !payload.ok) { throw new Error(payload.error && payload.error.message ? payload.error.message : 'The supplier report request failed.'); } return payload.data; });
        });
    }
    function currentCurrency(data) { var rows = data && data.currency_breakdown ? data.currency_breakdown : []; return rows.length === 1 ? rows[0].currency : ''; }
    function moneyForSummary(value, data) { var summary = data.summary || {}; return summary.currency_count > 1 ? formatNumber(value) + ' mixed' : formatMoney(value, currentCurrency(data)); }
    function destroyChart(name) { if (charts[name] && typeof charts[name].destroy === 'function') { charts[name].destroy(); } charts[name] = null; }
    function setChartEmpty(canvasId, emptyId, visible) { byId(canvasId).style.display = visible ? 'block' : 'none'; byId(emptyId).style.display = visible ? 'none' : 'block'; }
    function makeChart(name, canvasId, type, data, options) { destroyChart(name); if (!window.Chart || !data.labels.length) { return false; } charts[name] = new Chart(byId(canvasId).getContext('2d'), {type:type,data:data,options:options}); return true; }
    function chartOptions(moneyAxis) { return {responsive:true,maintainAspectRatio:false,legend:{position:'bottom',labels:{boxWidth:12,fontSize:10}},scales:{yAxes:[{ticks:{beginAtZero:true,callback:function(value){return moneyAxis?formatNumber(value):value;}},gridLines:{color:'#edf1f5'}}],xAxes:[{gridLines:{display:false}}]},tooltips:{callbacks:{label:function(item,chartData){var dataset=chartData.datasets[item.datasetIndex];return dataset.label+': '+(moneyAxis?formatMoney(item.yLabel,activeCurrency):formatNumber(item.yLabel));}}}}; }

    function renderSummary(data) {
        var summary = data.summary || {};
        var overdue = number(summary.overdue_1) + number(summary.overdue_2);
        activeCurrency = currentCurrency(data);
        byId('supplierKpiBalance').textContent = moneyForSummary(summary.net_balance, data);
        byId('supplierKpiOpen').textContent = moneyForSummary(summary.open_payables, data);
        byId('supplierKpiDue').textContent = moneyForSummary(summary.due_now, data);
        byId('supplierKpiOverdue').textContent = moneyForSummary(overdue, data);
        byId('supplierKpiPaid').textContent = moneyForSummary(summary.paid_allocated, data);
        byId('supplierKpiSuppliers').textContent = formatNumber(summary.open_supplier_count);
        byId('supplierSummarySubtitle').textContent = 'As of ' + formatDate(data.metadata && data.metadata.as_of_date) + ' · activity ' + (state.start || 'all history') + ' to ' + (state.end || 'current') + ' · ' + plural(summary.supplier_count, 'supplier') + ' in scope.';
        byId('supplierContext').textContent = (summary.currency_count > 1 ? summary.currency_count + ' currencies' : activeCurrency || 'Active company') + ' · live source · read-only';
        byId('supplierAsOfDate').textContent = formatDate(data.metadata && data.metadata.as_of_date);
        var thresholds = (data.metadata || {}).aging_thresholds || {};
        byId('supplierThresholds').textContent = 'Due now until ' + formatNumber(thresholds.overdue_1_days) + ' days overdue; next bucket until ' + formatNumber(thresholds.overdue_2_days) + ' days.';
        byId('supplierFreshness').textContent = data.metadata && data.metadata.freshness ? 'Live as of ' + data.metadata.freshness.as_of_utc + ' UTC' : 'Live source query';
        byId('supplierActivityPaid').textContent = moneyForSummary(summary.payment_activity, data);
        byId('supplierActivityMeta').textContent = moneyForSummary(summary.purchase_activity, data) + ' purchases · ' + moneyForSummary(summary.allocated_activity, data) + ' allocated in the selected period.';
        byId('supplierMissingDue').textContent = formatNumber(summary.missing_due_date);
        byId('supplierOnHold').textContent = formatNumber(summary.on_hold_open_bill);
        byId('supplierZeroUnsettled').textContent = formatNumber(summary.zero_balance_unsettled);
        var hasIssue = number(summary.missing_due_date) || number(summary.on_hold_open_bill) || number(summary.zero_balance_unsettled);
        byId('supplierQualityBadge').textContent = hasIssue ? 'Review needed' : 'No flagged controls';
        byId('supplierQualityBadge').className = 'supplier-quality-badge ' + (hasIssue ? 'is-warning' : 'is-good');
        byId('supplierNavScope').innerHTML = '<i class="fa fa-filter"></i> ' + formatNumber(summary.supplier_count) + ' suppliers · ' + formatNumber(summary.open_bill_count) + ' open bills';
        renderInsight(data);
        renderExceptions(data);
    }
    function renderInsight(data) {
        var summary = data.summary || {};
        var overdue = number(summary.overdue_1) + number(summary.overdue_2);
        var headline = overdue > 0 ? moneyForSummary(overdue, data) + ' requires overdue follow-up' : 'No overdue exposure in the selected scope';
        var body = overdue > 0 ? formatNumber(summary.overdue_supplier_count) + ' supplier accounts have balances at or beyond the first overdue threshold. Use portfolio and transaction evidence to follow up.' : 'Review current balances, payment activity, and portfolio concentration for upcoming obligations.';
        if (summary.currency_count > 1) { body += ' Multiple currencies are in scope and remain separated in Currency reconciliation.'; }
        byId('supplierInsightHeadline').textContent = headline;
        byId('supplierInsightBody').textContent = body;
    }
    function renderExceptions(data) {
        var summary = data.summary || {};
        var items = [];
        if (number(summary.missing_due_date)) { items.push(formatNumber(summary.missing_due_date) + ' open bills have no usable due date'); }
        if (number(summary.on_hold_open_bill)) { items.push(formatNumber(summary.on_hold_open_bill) + ' open bills are on hold'); }
        if (number(summary.unapplied_credits)) { items.push(moneyForSummary(summary.unapplied_credits, data) + ' credits / overpayments remain unapplied'); }
        if (number(summary.zero_balance_unsettled)) { items.push(formatNumber(summary.zero_balance_unsettled) + ' zero-balance transactions are not marked settled'); }
        if (!items.length) { items.push('No deterministic control exceptions were found for the selected scope.'); }
        byId('supplierExceptionHeadline').textContent = items[0].indexOf('No deterministic') === -1 ? 'Follow-up items found' : 'Ledger controls look clear';
        byId('supplierExceptionBody').textContent = 'These source-data and payment-control signals do not alter the net AP balance.';
        byId('supplierExceptionList').innerHTML = items.map(function (item) { return '<li>' + escapeHtml(item) + '</li>'; }).join('');
    }

    function renderAgeing(data) {
        var rows = data.ageing || [];
        byId('supplierAgeingTable').querySelector('tbody').innerHTML = rows.length ? rows.map(function (row) { return '<tr class="supplier-click-row" data-aging-bucket="' + escapeHtml(row.bucket) + '" title="Filter to ' + escapeHtml(ageLabel(row.bucket)) + '"><td>' + escapeHtml(ageLabel(row.bucket)) + '</td><td class="text-right">' + formatNumber(row.supplier_count) + '</td><td class="text-right">' + formatMoney(row.amount, activeCurrency) + '</td></tr>'; }).join('') : '<tr><td colspan="3" class="supplier-empty-cell">No ageing data for the selected controls.</td></tr>';
        byId('supplierAgeingStatus').textContent = rows.length + ' buckets · interactive';
        var chartRows = rows.filter(function (row) { return ['current','due','overdue1','overdue2'].indexOf(row.bucket) !== -1 && number(row.amount) > 0; });
        setChartEmpty('supplierAgeingChart', 'supplierAgeingEmpty', chartRows.length > 0);
        byId('supplierAgeingFallback').innerHTML = chartRows.length && !window.Chart ? '<table><thead><tr><th>Bucket</th><th>Amount</th></tr></thead><tbody>' + chartRows.map(function (row) { return '<tr data-aging-bucket="' + escapeHtml(row.bucket) + '"><td>' + escapeHtml(ageLabel(row.bucket)) + '</td><td>' + formatMoney(row.amount, activeCurrency) + '</td></tr>'; }).join('') + '</tbody></table>' : '';
        destroyChart('ageing');
        if (chartRows.length && window.Chart) {
            makeChart('ageing', 'supplierAgeingChart', 'doughnut', {labels:chartRows.map(function(row){return ageLabel(row.bucket);}),datasets:[{data:chartRows.map(function(row){return row.amount;}),backgroundColor:['#2e9d72','#e6a52f','#d76b45','#ba4966']}]}, {responsive:true,maintainAspectRatio:false,legend:{position:'bottom'},onClick:function(event,elements){if(elements.length){applyAging(chartRows[elements[0]._index].bucket,true);}}});
        }
    }
    function renderTrend(data) {
        var rows = data.trend || [];
        byId('supplierTrendStatus').textContent = rows.length + ' month' + (rows.length === 1 ? '' : 's') + ' · interactive';
        setChartEmpty('supplierTrendChart', 'supplierTrendEmpty', rows.length > 0);
        byId('supplierTrendFallback').innerHTML = rows.length && !window.Chart ? '<table><thead><tr><th>Month</th><th>Purchases</th><th>Payments / credits</th><th>Allocated</th></tr></thead><tbody>' + rows.map(function (row) { return '<tr data-trend-month="' + escapeHtml(row.period_start) + '"><td>' + escapeHtml(row.period_start) + '</td><td>' + formatMoney(row.purchases, activeCurrency) + '</td><td>' + formatMoney(row.payments, activeCurrency) + '</td><td>' + formatMoney(row.allocated, activeCurrency) + '</td></tr>'; }).join('') + '</tbody></table>' : '';
        destroyChart('trend');
        if (rows.length && window.Chart) {
            var options = chartOptions(true);
            options.onClick = function (event, elements) { if (elements.length) { drillMonth(rows[elements[0]._index].period_start); } };
            makeChart('trend','supplierTrendChart','bar',{labels:rows.map(function(row){return row.period_start;}),datasets:[{label:'Purchases',data:rows.map(function(row){return row.purchases;}),backgroundColor:'rgba(37,125,201,.68)'},{label:'Payments / credits',data:rows.map(function(row){return row.payments;}),backgroundColor:'rgba(46,157,114,.68)'},{label:'Allocated',data:rows.map(function(row){return row.allocated;}),type:'line',borderColor:'#d98c27',backgroundColor:'rgba(217,140,39,.12)',fill:false,lineTension:.2}]},options);
        }
    }
    function renderCurrency(data) {
        var rows = data.currency_breakdown || [];
        byId('supplierCurrencyTable').querySelector('tbody').innerHTML = rows.length ? rows.map(function (row) { return '<tr><td><strong>' + escapeHtml(row.currency || '—') + '</strong></td><td class="text-right">' + formatMoney(row.net_balance,row.currency) + '</td><td class="text-right">' + formatMoney(row.open_payables,row.currency) + '</td><td class="text-right">' + formatMoney(row.overdue,row.currency) + '</td><td class="text-right">' + formatMoney(row.paid_allocated,row.currency) + '</td></tr>'; }).join('') : '<tr><td colspan="5" class="supplier-empty-cell">No currency totals are available.</td></tr>';
    }

    function renderPortfolio(data) {
        var rows = data.rows || [];
        var pagination = data.pagination || {};
        byId('supplierPortfolioStatus').textContent = formatNumber(pagination.total_rows || 0) + ' matching suppliers';
        byId('supplierPortfolioPageStatus').textContent = 'Page ' + (pagination.page || 1) + ' of ' + (pagination.total_pages || 1) + ' · ' + formatNumber(pagination.total_rows || 0) + ' suppliers';
        byId('supplierPortfolioPrev').disabled = (pagination.page || 1) <= 1;
        byId('supplierPortfolioNext').disabled = (pagination.page || 1) >= (pagination.total_pages || 1);
        var body = byId('supplierPortfolioTable').querySelector('tbody');
        if (!rows.length) { body.innerHTML = '<tr><td colspan="11" class="supplier-empty-cell">No suppliers match the selected controls.</td></tr>'; applyColumnVisibility('portfolio'); return; }
        body.innerHTML = rows.map(function (row) {
            var overdue = number(row.overdue_1) + number(row.overdue_2);
            var contact = [decodeText(row.primary_contact),decodeText(row.primary_contact_email),decodeText(row.payment_terms)].filter(Boolean).join(' · ');
            return '<tr class="' + (overdue > 0 ? 'supplier-risk-row' : '') + '"><td data-column="supplier"><button type="button" class="supplier-link" data-supplier-id="' + escapeHtml(row.supplierid) + '">' + escapeHtml(row.supplierid) + '</button><br><span class="supplier-name">' + escapeHtml(decodeText(row.suppname)) + '</span><small>' + escapeHtml(decodeText(row.supplier_type || '')) + ' · <button type="button" class="supplier-inline-action" data-open-transactions="' + escapeHtml(row.supplierid) + '">View transactions</button></small></td><td data-column="contact">' + escapeHtml(contact || 'No contact / terms recorded') + '<br><small>' + escapeHtml(row.telephone || '') + '</small></td><td data-column="currency">' + escapeHtml(row.currcode || '—') + '</td><td data-column="net" class="text-right"><strong>' + formatMoney(row.net_balance,row.currcode) + '</strong></td><td data-column="paid" class="text-right">' + formatMoney(row.paid_allocated,row.currcode) + '</td><td data-column="open" class="text-right">' + formatMoney(row.open_payables,row.currcode) + '</td><td data-column="current" class="text-right">' + formatMoney(row.current_amount,row.currcode) + '</td><td data-column="due" class="text-right">' + formatMoney(row.due_now,row.currcode) + '</td><td data-column="overdue" class="text-right ' + (overdue > 0 ? 'supplier-negative' : '') + '">' + formatMoney(overdue,row.currcode) + '</td><td data-column="bills" class="text-right">' + formatNumber(row.open_bill_count) + '</td><td data-column="last_payment">' + formatDate(row.last_payment_date || row.lastpaiddate) + '<br><small>' + formatMoney(row.lastpaid,row.currcode) + '</small></td></tr>';
        }).join('');
        applyColumnVisibility('portfolio');
    }

    function renderDetails(data) {
        var rows = data.rows || [];
        var pagination = data.pagination || {};
        byId('supplierDetailStatus').textContent = formatNumber(pagination.total_rows || 0) + ' matching transactions';
        byId('supplierDetailPageStatus').textContent = 'Page ' + (pagination.page || 1) + ' of ' + (pagination.total_pages || 1) + ' · ' + formatNumber(pagination.total_rows || 0) + ' rows';
        byId('supplierDetailPrev').disabled = (pagination.page || 1) <= 1;
        byId('supplierDetailNext').disabled = (pagination.page || 1) >= (pagination.total_pages || 1);
        var body = byId('supplierDetailTable').querySelector('tbody');
        if (!rows.length) { body.innerHTML = '<tr><td colspan="12" class="supplier-empty-cell">No supplier transactions match the selected controls.</td></tr>'; applyColumnVisibility('details'); return; }
        body.innerHTML = rows.map(function (row) {
            var status = number(row.outstanding) > .009 ? (number(row.hold) ? 'On hold' : 'Open') : (number(row.outstanding) < -.009 ? 'Credit' : 'Settled');
            var days = row.days_overdue === null || row.days_overdue === '' ? '—' : formatNumber(row.days_overdue);
            var dueSource = row.due_date_source || 'Due date unavailable';
            return '<tr><td data-column="date">' + escapeHtml(formatDate(row.trandate)) + '</td><td data-column="supplier"><button type="button" class="supplier-link" data-supplier-id="' + escapeHtml(row.supplierid) + '">' + escapeHtml(row.supplierid) + '</button><br><span class="supplier-name">' + escapeHtml(decodeText(row.suppname)) + '</span></td><td data-column="type">' + escapeHtml(decodeText(row.transaction_type || 'Transaction')) + '<br><small>' + escapeHtml(row.transno || '') + ' · ' + escapeHtml(row.suppreference || '') + '</small></td><td data-column="due_date" title="' + escapeHtml(dueSource) + '">' + escapeHtml(formatDate(row.due_date)) + '<br><small class="supplier-muted">' + escapeHtml(dueSource) + '</small></td><td data-column="days" class="text-right ' + (number(row.days_overdue) > 0 ? 'supplier-negative' : '') + '">' + days + '</td><td data-column="age"><span class="supplier-age-badge age-' + escapeHtml(row.age_bucket || 'other') + '">' + escapeHtml(ageLabel(row.age_bucket)) + '</span></td><td data-column="total" class="text-right">' + formatMoney(row.total_amount,row.currcode) + '</td><td data-column="paid" class="text-right">' + formatMoney(row.paid_amount,row.currcode) + '</td><td data-column="payment" class="text-right">' + formatMoney(row.payment_amount,row.currcode) + '</td><td data-column="outstanding" class="text-right"><strong>' + formatMoney(row.outstanding,row.currcode) + '</strong></td><td data-column="status"><span class="supplier-status-badge status-' + status.toLowerCase().replace(/[^a-z]+/g,'-') + '">' + escapeHtml(status) + '</span></td><td data-column="comments" title="' + escapeHtml(row.transtext || '') + '">' + escapeHtml(row.transtext || '—') + '</td></tr>';
        }).join('');
        applyColumnVisibility('details');
    }

    function renderFilterGroup(containerId, rows, stateKey, valueField, labelField) {
        byId(containerId).innerHTML = rows.length ? rows.map(function (row) { var value = String(row[valueField]); return '<label><input type="checkbox" data-array-key="' + stateKey + '" value="' + escapeHtml(value) + '"> <span>' + escapeHtml(decodeText(row[labelField] || value)) + '</span></label>'; }).join('') : '<span>No values available</span>';
    }
    function loadLookups() {
        if (lookupsLoaded) { return Promise.resolve(); }
        return request('lookups').then(function (data) {
            lookups.suppliers = data.suppliers || [];
            lookups.supplierTypes = data.supplier_types || [];
            lookups.paymentTerms = data.payment_terms || [];
            lookups.transactionTypes = data.transaction_types || [];
            lookups.suppliers.forEach(function (row) { lookups.supplierMap[String(row.supplierid)] = row; });
            renderFilterGroup('supplierTypeOptions',lookups.supplierTypes,'supplierTypes','typeid','typename');
            renderFilterGroup('supplierPaymentTermOptions',lookups.paymentTerms,'paymentTerms','termsindicator','terms');
            renderFilterGroup('supplierTransactionTypeOptions',lookups.transactionTypes,'transactionTypes','typeid','typename');
            lookupsLoaded = true;
            syncControls();
        });
    }

    function filteredSuppliers() {
        var query = byId('supplierPickerSearch').value.trim().toLowerCase();
        return lookups.suppliers.filter(function (row) { return !query || (String(row.supplierid) + ' ' + decodeText(row.suppname) + ' ' + decodeText(row.supplier_type) + ' ' + decodeText(row.payment_terms)).toLowerCase().indexOf(query) !== -1; });
    }
    function renderSupplierPicker() {
        var selected = state.supplierIds.map(String);
        var matches = filteredSuppliers();
        var visible = matches.slice(0, 300);
        byId('supplierPickerList').innerHTML = visible.length ? visible.map(function (row) { var id = String(row.supplierid); return '<label class="supplier-picker-option"><input type="checkbox" data-supplier-pick="' + escapeHtml(id) + '" ' + (selected.indexOf(id) !== -1 ? 'checked' : '') + '><span><strong>' + escapeHtml(decodeText(row.suppname)) + '</strong><small>' + escapeHtml(id) + ' · ' + escapeHtml(decodeText(row.supplier_type || 'Unclassified')) + ' · ' + escapeHtml(row.currcode || '—') + '</small></span></label>'; }).join('') : '<div class="supplier-picker-empty">No suppliers match this search.</div>';
        byId('supplierPickerMatchCount').textContent = matches.length > 300 ? 'Showing 300 of ' + formatNumber(matches.length) : plural(matches.length,'match');
        byId('supplierPickerSummary').textContent = selected.length ? plural(selected.length,'supplier') + ' selected' : 'All suppliers';
        var selectedRows = selected.map(function (id) { return {id:id,name:supplierName(id)}; });
        byId('supplierSelectedList').innerHTML = selectedRows.slice(0,12).map(function (row) { return '<span>' + escapeHtml(row.name) + '<button type="button" data-remove-supplier="' + escapeHtml(row.id) + '" aria-label="Remove ' + escapeHtml(row.name) + '">&times;</button></span>'; }).join('') + (selectedRows.length > 12 ? '<em>+' + (selectedRows.length - 12) + ' more</em>' : '');
        var summary = byId('supplierSelectionSummary');
        summary.hidden = !selected.length;
        byId('supplierSelectionHeadline').textContent = selected.length === 1 ? 'Focused supplier relationship' : 'Focused supplier portfolio · ' + selected.length + ' suppliers';
        byId('supplierSelectionNames').textContent = selectedRows.slice(0,5).map(function(row){return row.name;}).join(' · ') + (selectedRows.length > 5 ? ' · +' + (selectedRows.length - 5) + ' more' : '');
    }
    function setSupplierSelected(id, checked) {
        id = String(id);
        var selected = state.supplierIds.map(String);
        if (checked && selected.indexOf(id) === -1) { if (selected.length >= 100) { showAlert('You can select up to 100 suppliers at a time.','warning'); return; } selected.push(id); }
        if (!checked) { selected = selected.filter(function(value){return value !== id;}); }
        state.supplierIds = selected;
        renderSupplierPicker();
        renderChips();
    }
    function focusSupplier(id, target) { state.supplierIds = [String(id)]; state.portfolioPage = 1; state.detailPage = 1; syncControls(); syncUrl(); loadReport().then(function(){navigateTo(target || 'portfolio');}); }

    function loadReport() {
        var version = ++requestVersion;
        root.setAttribute('aria-busy','true');
        root.classList.add('is-loading');
        byId('supplierReportStatus').textContent = 'Loading';
        byId('supplierNavScope').innerHTML = '<i class="fa fa-circle-o-notch fa-spin"></i> Refreshing portfolio';
        hideAlert();
        renderChips();
        return Promise.all([
            request('summary'),request('ageing'),request('trend'),
            request('suppliers',{page:state.portfolioPage,pageSize:state.portfolioPageSize,sort:state.portfolioSort,direction:state.portfolioDirection}),
            request('details',{page:state.detailPage,pageSize:state.detailPageSize,sort:state.detailSort,direction:state.detailDirection})
        ]).then(function (results) {
            if (version !== requestVersion) { return; }
            renderSummary(results[0]);renderAgeing(results[1]);renderTrend(results[2]);renderCurrency(results[0]);renderPortfolio(results[3]);renderDetails(results[4]);
            byId('supplierLastRefreshed').textContent = new Date().toLocaleString();
            byId('supplierFooterAsOf').textContent = state.end || 'Current';
            byId('supplierReportStatus').textContent = 'Loaded';
        }).catch(function (error) {
            if (version !== requestVersion) { return; }
            byId('supplierReportStatus').textContent = 'Error';
            byId('supplierNavScope').innerHTML = '<i class="fa fa-exclamation-triangle"></i> Report needs attention';
            showAlert(error.message || 'The supplier report could not be loaded.','danger');
        }).then(function () { if (version === requestVersion) { root.setAttribute('aria-busy','false');root.classList.remove('is-loading'); } });
    }

    function serializableState() { return {preset:state.preset,start:state.start,end:state.end,search:state.search,supplierIds:state.supplierIds.slice(),supplierTypes:state.supplierTypes.slice(),paymentTerms:state.paymentTerms.slice(),transactionTypes:state.transactionTypes.slice(),status:state.status,aging:state.aging,attention:state.attention,dueFrom:state.dueFrom,dueTo:state.dueTo,minOutstanding:state.minOutstanding,maxOutstanding:state.maxOutstanding,portfolioSort:state.portfolioSort,portfolioDirection:state.portfolioDirection}; }
    function savedViews() { return loadJson('sahamid.bi.saved-views.supplier-relationship',[]); }
    function renderSavedViews() { byId('supplierSavedView').innerHTML = '<option value="">Saved views</option>' + savedViews().map(function(view,index){return '<option value="' + index + '">' + escapeHtml(view.name) + '</option>';}).join(''); }
    function saveView() { var name = window.prompt('Name this private saved view:','My supplier relationship view'); if (!name) { return; } var views = savedViews(); views.push({name:name,state:serializableState(),savedAt:new Date().toISOString()}); window.localStorage.setItem('sahamid.bi.saved-views.supplier-relationship',JSON.stringify(views.slice(-15))); renderSavedViews(); showAlert('Saved “' + name + '” privately in this browser.','success'); }
    function restoreState() {
        var params = new URLSearchParams(window.location.search);
        state.preset = params.get('preset') || 'ytd';
        if (params.has('start')) { state.start = params.get('start') || ''; } else { applyPreset(state.preset); }
        if (params.has('end')) { state.end = params.get('end') || ''; }
        state.search = params.get('search') || '';
        state.supplierIds = parseList(params.get('supplierIds') || params.get('supplierId') || '');
        state.supplierTypes = parseList(params.get('supplierTypes'));
        state.paymentTerms = parseList(params.get('paymentTerms'));
        state.transactionTypes = parseList(params.get('transactionTypes'));
        state.status = params.get('status') || 'all';
        state.aging = params.get('aging') || 'all';
        state.attention = params.get('attention') || 'all';
        state.dueFrom = params.get('dueFrom') || '';
        state.dueTo = params.get('dueTo') || '';
        state.minOutstanding = params.get('minOutstanding') || '';
        state.maxOutstanding = params.get('maxOutstanding') || '';
        syncUrl();
    }
    function applyControls() {
        state.start = byId('supplierStart').value;
        state.end = byId('supplierEnd').value;
        state.search = byId('supplierSearch').value.trim();
        state.status = byId('supplierStatus').value;
        state.aging = byId('supplierAging').value;
        state.attention = byId('supplierAttention').value;
        state.dueFrom = byId('supplierDueFrom').value;
        state.dueTo = byId('supplierDueTo').value;
        state.minOutstanding = byId('supplierMinOutstanding').value.trim();
        state.maxOutstanding = byId('supplierMaxOutstanding').value.trim();
        state.preset = byId('supplierDatePreset').value === 'all' ? 'all' : 'custom';
        state.portfolioSort = byId('supplierGroupBy').value;
        state.portfolioDirection = state.portfolioSort === 'supplier' ? 'asc' : 'desc';
        state.portfolioPage = 1;
        state.detailPage = 1;
        if (!state.end || (!state.start && state.preset !== 'all')) { showAlert('Choose an activity date range, or select All available dates.','warning'); return; }
        if (state.start && state.start > state.end) { showAlert('The activity start date cannot be after the as-of date.','warning'); return; }
        if (state.dueFrom && state.dueTo && state.dueFrom > state.dueTo) { showAlert('The due-date start cannot be after the due-date end.','warning'); return; }
        if (state.minOutstanding !== '' && state.maxOutstanding !== '' && Number(state.minOutstanding) > Number(state.maxOutstanding)) { showAlert('Minimum outstanding cannot exceed maximum outstanding.','warning'); return; }
        syncControls();syncUrl();loadReport();
    }

    function renderQuickViews() {
        var active = 'custom';
        if (state.status === 'all' && state.aging === 'all' && state.attention === 'all') { active = 'all'; }
        else if (state.status === 'outstanding' && state.aging === 'all' && state.attention === 'all') { active = 'outstanding'; }
        else if (state.status === 'overdue' && state.aging === 'all' && state.attention === 'all') { active = 'overdue'; }
        else if (state.aging === 'credits' && state.attention === 'all') { active = 'credits'; }
        else if (state.attention === 'on_hold') { active = 'on_hold'; }
        else if (state.attention === 'missing_due') { active = 'missing_due'; }
        document.querySelectorAll('[data-quick-view]').forEach(function(button){button.classList.toggle('is-active',button.getAttribute('data-quick-view') === active);});
    }
    function applyQuickView(view, shouldNavigate) {
        state.status = 'all';state.aging = 'all';state.attention = 'all';
        if (view === 'outstanding') { state.status = 'outstanding'; }
        else if (view === 'overdue') { state.status = 'overdue'; }
        else if (view === 'credits') { state.aging = 'credits'; }
        else if (view === 'on_hold' || view === 'missing_due' || view === 'zero_unsettled') { state.attention = view; }
        else if (view === 'settled') { state.status = 'settled'; }
        else if (view === 'due') { state.aging = 'due'; }
        state.portfolioPage = 1;state.detailPage = 1;
        syncControls();syncUrl();loadReport().then(function(){if(shouldNavigate){navigateTo(view === 'on_hold' || view === 'missing_due' || view === 'zero_unsettled' ? 'controls' : 'transactions');}});
    }
    function applyAging(bucket, shouldNavigate) { state.status='all';state.attention='all';state.aging=bucket;state.portfolioPage=1;state.detailPage=1;syncControls();syncUrl();loadReport().then(function(){if(shouldNavigate){navigateTo('transactions');}}); }
    function drillMonth(period) { var parts=String(period).substring(0,10).split('-');if(parts.length!==3){return;}var end=new Date(Number(parts[0]),Number(parts[1]),0);state.preset='custom';state.start=parts[0]+'-'+parts[1]+'-01';state.end=isoDate(end);state.detailPage=1;syncControls();syncUrl();loadReport().then(function(){navigateTo('transactions');}); }

    function navigateTo(id) {
        var section = byId(id);
        if (!section) { return; }
        document.querySelectorAll('.supplier-section-nav a').forEach(function(item){item.classList.toggle('is-active',item.getAttribute('href') === '#' + id);});
        if (window.history && window.history.replaceState) { var url=new URL(window.location.href);url.hash=id;window.history.replaceState({},'',url.toString()); }
        section.scrollIntoView({behavior:'smooth',block:'start'});
    }
    function setupScrollSpy() {
        if (!window.IntersectionObserver) { return; }
        var observer = new IntersectionObserver(function(entries){entries.filter(function(entry){return entry.isIntersecting;}).sort(function(a,b){return b.intersectionRatio-a.intersectionRatio;}).slice(0,1).forEach(function(entry){document.querySelectorAll('.supplier-section-nav a').forEach(function(link){link.classList.toggle('is-active',link.getAttribute('href') === '#' + entry.target.id);});});},{rootMargin:'-90px 0px -62% 0px',threshold:[.05,.25,.5]});
        document.querySelectorAll('.supplier-report-section').forEach(function(section){observer.observe(section);});
    }
    function applyColumnVisibility(tableName) {
        var tableId = tableName === 'portfolio' ? 'supplierPortfolioTable' : 'supplierDetailTable';
        var hidden = hiddenColumns[tableName] || [];
        byId(tableId).querySelectorAll('[data-column]').forEach(function(cell){cell.style.display=hidden.indexOf(cell.getAttribute('data-column')) !== -1?'none':'';});
        var menu = document.querySelector('[data-column-menu="' + tableName + '"]');
        if (menu) { menu.querySelectorAll('[data-table-column]').forEach(function(checkbox){checkbox.checked=hidden.indexOf(checkbox.getAttribute('data-table-column')) === -1;}); }
    }
    function setColumnVisibility(tableName,column,visible) { var hidden=hiddenColumns[tableName]||[];hidden=hidden.filter(function(value){return value!==column;});if(!visible){hidden.push(column);}hiddenColumns[tableName]=hidden;window.localStorage.setItem('sahamid.bi.supplier-columns',JSON.stringify(hiddenColumns));applyColumnVisibility(tableName); }
    function syncDensity() { root.classList.toggle('is-compact',compactRows);byId('supplierDensityToggle').innerHTML=compactRows?'<i class="fa fa-expand"></i> Comfortable rows':'<i class="fa fa-compress"></i> Compact rows'; }

    document.querySelectorAll('[data-period]').forEach(function(button){button.addEventListener('click',function(){state.preset=this.getAttribute('data-period')==='quarter'?'last_90':this.getAttribute('data-period');applyPreset(state.preset);syncControls();syncUrl();loadReport();});});
    byId('supplierDatePreset').addEventListener('change',function(){state.preset=this.value;if(this.value!=='custom'){applyPreset(this.value);syncControls();syncUrl();loadReport();}});
    byId('supplierStart').addEventListener('change',function(){state.preset='custom';byId('supplierDatePreset').value='custom';});
    byId('supplierEnd').addEventListener('change',function(){state.preset='custom';byId('supplierDatePreset').value='custom';});
    byId('supplierApply').addEventListener('click',applyControls);
    byId('supplierSearch').addEventListener('keydown',function(event){if(event.key==='Enter'){applyControls();}});
    byId('supplierRefresh').addEventListener('click',loadReport);
    byId('supplierExport').addEventListener('click',function(){window.location.href=queryUrl('export');});
    byId('supplierSaveView').addEventListener('click',saveView);
    byId('supplierSavedView').addEventListener('change',function(){var view=savedViews()[Number(this.value)];if(!view||!view.state){return;}Object.keys(view.state).forEach(function(key){if(Object.prototype.hasOwnProperty.call(state,key)){state[key]=Array.isArray(state[key])?unique(view.state[key]||[]):view.state[key];}});state.portfolioPage=1;state.detailPage=1;syncControls();syncUrl();loadReport();this.value='';});
    byId('supplierReset').addEventListener('click',function(){state.preset='ytd';applyPreset('ytd');state.search='';state.supplierIds=[];state.supplierTypes=[];state.paymentTerms=[];state.transactionTypes=[];state.status='all';state.aging='all';state.attention='all';state.dueFrom='';state.dueTo='';state.minOutstanding='';state.maxOutstanding='';state.portfolioSort='supplier';state.portfolioDirection='asc';state.portfolioPage=1;state.detailPage=1;syncControls();syncUrl();loadReport();});
    byId('supplierAdvancedToggle').addEventListener('click',function(){var fields=document.querySelector('.supplier-advanced-fields');var hidden=fields.hasAttribute('hidden');if(hidden){fields.removeAttribute('hidden');}else{fields.setAttribute('hidden','hidden');}this.setAttribute('aria-expanded',hidden?'true':'false');});

    byId('supplierPickerToggle').addEventListener('click',function(){var menu=byId('supplierPickerMenu');var opening=menu.hasAttribute('hidden');if(opening){menu.removeAttribute('hidden');byId('supplierPickerSearch').focus();}else{menu.setAttribute('hidden','hidden');}this.setAttribute('aria-expanded',opening?'true':'false');});
    byId('supplierPickerSearch').addEventListener('input',renderSupplierPicker);
    byId('supplierSelectVisible').addEventListener('click',function(){var ids=filteredSuppliers().slice(0,100).map(function(row){return String(row.supplierid);});state.supplierIds=unique(state.supplierIds.concat(ids)).slice(0,100);renderSupplierPicker();renderChips();});
    byId('supplierClearSelection').addEventListener('click',function(){state.supplierIds=[];renderSupplierPicker();renderChips();});
    byId('supplierApplySelection').addEventListener('click',function(){state.portfolioPage=1;state.detailPage=1;byId('supplierPickerMenu').setAttribute('hidden','hidden');byId('supplierPickerToggle').setAttribute('aria-expanded','false');syncUrl();loadReport();});
    byId('supplierSelectionClear').addEventListener('click',function(){state.supplierIds=[];state.portfolioPage=1;state.detailPage=1;syncControls();syncUrl();loadReport();});
    byId('supplierPickerList').addEventListener('change',function(event){var input=event.target.closest('[data-supplier-pick]');if(input){setSupplierSelected(input.getAttribute('data-supplier-pick'),input.checked);}});
    byId('supplierSelectedList').addEventListener('click',function(event){var button=event.target.closest('[data-remove-supplier]');if(button){setSupplierSelected(button.getAttribute('data-remove-supplier'),false);}});

    root.addEventListener('change',function(event){
        var input=event.target.closest('[data-array-key]');
        if(input){var key=input.getAttribute('data-array-key');var values=state[key].map(String);if(input.checked&&values.indexOf(String(input.value))===-1){values.push(String(input.value));}if(!input.checked){values=values.filter(function(value){return value!==String(input.value);});}state[key]=values;renderChips();byId('supplierAdvancedCount').textContent=advancedFilterCount();}
        var column=event.target.closest('[data-table-column]');
        if(column){var menu=column.closest('[data-column-menu]');setColumnVisibility(menu.getAttribute('data-column-menu'),column.getAttribute('data-table-column'),column.checked);}
    });
    root.addEventListener('click',function(event){
        var clear=event.target.closest('[data-clear-filter]');
        if(clear){var key=clear.getAttribute('data-clear-filter');if(key==='dueDates'){state.dueFrom='';state.dueTo='';}else if(key==='amounts'){state.minOutstanding='';state.maxOutstanding='';}else if(Array.isArray(state[key])){state[key]=[];}else if(key==='status'||key==='aging'||key==='attention'){state[key]='all';}else{state[key]='';}state.portfolioPage=1;state.detailPage=1;syncControls();syncUrl();loadReport();return;}
        var quick=event.target.closest('[data-quick-view]');if(quick){applyQuickView(quick.getAttribute('data-quick-view'),true);return;}
        var kpi=event.target.closest('[data-kpi-view]');if(kpi){applyQuickView(kpi.getAttribute('data-kpi-view'),true);return;}
        var navigation=event.target.closest('[data-navigate]');if(navigation){navigateTo(navigation.getAttribute('data-navigate'));return;}
        var supplier=event.target.closest('[data-supplier-id]');if(supplier){focusSupplier(supplier.getAttribute('data-supplier-id'),'portfolio');return;}
        var transaction=event.target.closest('[data-open-transactions]');if(transaction){focusSupplier(transaction.getAttribute('data-open-transactions'),'transactions');return;}
        var aging=event.target.closest('[data-aging-bucket]');if(aging){applyAging(aging.getAttribute('data-aging-bucket'),true);return;}
        var month=event.target.closest('[data-trend-month]');if(month){drillMonth(month.getAttribute('data-trend-month'));return;}
        var columnToggle=event.target.closest('[data-column-toggle]');
        if(columnToggle){var name=columnToggle.getAttribute('data-column-toggle');var menu=document.querySelector('[data-column-menu="'+name+'"]');document.querySelectorAll('.supplier-column-menu').forEach(function(other){if(other!==menu){other.setAttribute('hidden','hidden');}});if(menu.hasAttribute('hidden')){menu.removeAttribute('hidden');}else{menu.setAttribute('hidden','hidden');}return;}
    });
    root.addEventListener('keydown',function(event){var action=event.target.closest('[data-kpi-view],[data-quick-view],[data-navigate]');if(action&&(event.key==='Enter'||event.key===' ')){event.preventDefault();action.click();}});
    document.addEventListener('click',function(event){if(!event.target.closest('#supplierMultiSelect')){byId('supplierPickerMenu').setAttribute('hidden','hidden');byId('supplierPickerToggle').setAttribute('aria-expanded','false');}if(!event.target.closest('.supplier-column-picker')){document.querySelectorAll('.supplier-column-menu').forEach(function(menu){menu.setAttribute('hidden','hidden');});}});

    byId('supplierPortfolioPageSize').addEventListener('change',function(){state.portfolioPageSize=Number(this.value);state.portfolioPage=1;loadReport();});
    byId('supplierPortfolioPrev').addEventListener('click',function(){if(state.portfolioPage>1){state.portfolioPage--;loadReport();}});
    byId('supplierPortfolioNext').addEventListener('click',function(){state.portfolioPage++;loadReport();});
    byId('supplierDetailPageSize').addEventListener('change',function(){state.detailPageSize=Number(this.value);state.detailPage=1;loadReport();});
    byId('supplierDetailPrev').addEventListener('click',function(){if(state.detailPage>1){state.detailPage--;loadReport();}});
    byId('supplierDetailNext').addEventListener('click',function(){state.detailPage++;loadReport();});
    byId('supplierPortfolioTable').querySelector('thead').addEventListener('click',function(event){var header=event.target.closest('[data-supplier-sort]');if(!header){return;}var selected=header.getAttribute('data-supplier-sort');if(state.portfolioSort===selected){state.portfolioDirection=state.portfolioDirection==='asc'?'desc':'asc';}else{state.portfolioSort=selected;state.portfolioDirection=selected==='supplier'?'asc':'desc';}state.portfolioPage=1;byId('supplierGroupBy').value=state.portfolioSort;loadReport();});
    byId('supplierDetailTable').querySelector('thead').addEventListener('click',function(event){var header=event.target.closest('[data-detail-sort]');if(!header){return;}var selected=header.getAttribute('data-detail-sort');if(state.detailSort===selected){state.detailDirection=state.detailDirection==='asc'?'desc':'asc';}else{state.detailSort=selected;state.detailDirection='desc';}state.detailPage=1;loadReport();});
    byId('supplierDensityToggle').addEventListener('click',function(){compactRows=!compactRows;window.localStorage.setItem('sahamid.bi.supplier-density',compactRows?'compact':'comfortable');syncDensity();});
    document.querySelectorAll('.supplier-section-nav a').forEach(function(tab){tab.addEventListener('click',function(event){event.preventDefault();navigateTo(this.getAttribute('href').substring(1));});});
    byId('supplierBackToTop').addEventListener('click',function(){byId('supplierSectionNav').scrollIntoView({behavior:'smooth',block:'start'});});
    window.addEventListener('scroll',function(){byId('supplierBackToTop').classList.toggle('is-visible',window.pageYOffset>700);});

    restoreState();
    renderSavedViews();
    syncDensity();
    applyColumnVisibility('portfolio');
    applyColumnVisibility('details');
    setupScrollSpy();
    loadLookups().then(loadReport).catch(function(error){showAlert(error.message||'The supplier report could not be initialized.','danger');root.setAttribute('aria-busy','false');root.classList.remove('is-loading');});
}(window, document));
