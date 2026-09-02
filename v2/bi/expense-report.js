(function () {
    'use strict';

    var report = null;
    var currentPage = 1;
    var optionsLoaded = false;
    var trendChart = null;
    var initialStartDate = document.getElementById('expenseStartDate').value;
    var initialEndDate = document.getElementById('expenseEndDate').value;

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (character) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character];
        });
    }

    function formatNumber(value, decimals) {
        return Number(value || 0).toLocaleString('en-US', {
            minimumFractionDigits: decimals || 0,
            maximumFractionDigits: decimals == null ? 0 : decimals
        });
    }

    function formatCompact(value) {
        var number = Number(value || 0);
        var absolute = Math.abs(number);
        if (absolute >= 1000000000) { return (number / 1000000000).toFixed(1) + 'B'; }
        if (absolute >= 1000000) { return (number / 1000000).toFixed(1) + 'M'; }
        if (absolute >= 1000) { return (number / 1000).toFixed(1) + 'K'; }
        return formatNumber(number, 0);
    }

    function formatAmount(value) {
        var currency = report && report.metadata ? report.metadata.default_currency : 'PKR';
        return currency + ' ' + Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function formatPercent(value) {
        return Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) + '%';
    }

    function setAlert(message, type) {
        var alert = document.getElementById('expenseAlert');
        if (!message) {
            alert.style.display = 'none';
            alert.textContent = '';
            return;
        }
        alert.className = 'alert expense-alert alert-' + (type || 'warning');
        alert.textContent = message;
        alert.style.display = 'block';
    }

    function setLoading(loading) {
        document.getElementById('expenseLoading').style.display = loading ? 'flex' : 'none';
        if (loading && !report) {
            document.getElementById('expenseReportContent').style.display = 'none';
        }
        document.getElementById('expenseExport').disabled = loading;
    }

    function selectedValue(id) {
        var element = document.getElementById(id);
        return element ? element.value.trim() : '';
    }

    function requestPayload() {
        var payload = {
            dateRange: { start: selectedValue('expenseStartDate'), end: selectedValue('expenseEndDate') },
            page: currentPage,
            pageSize: 50,
            sort: 'date',
            direction: 'desc'
        };
        var fields = {
            category: 'expenseCategory',
            costCenter: 'expenseCostCenter',
            status: 'expenseStatus',
            currency: 'expenseCurrency',
            search: 'expenseSearch'
        };
        Object.keys(fields).forEach(function (key) {
            var value = selectedValue(fields[key]);
            if (value) { payload[key] = value; }
        });
        return payload;
    }

    function validateDates(payload) {
        if (!payload.dateRange.start || !payload.dateRange.end || payload.dateRange.start > payload.dateRange.end) {
            setAlert('Choose a valid date range before updating the report.', 'warning');
            return false;
        }
        return true;
    }

    function loadReport(scrollToTop) {
        var payload = requestPayload();
        if (!validateDates(payload)) { return; }
        setLoading(true);
        setAlert('', 'warning');
        fetch(window.SAHAMID_BI.expenseReportUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(function (response) {
            return response.json().then(function (body) {
                if (!response.ok || !body.ok) {
                    var error = body.error || {};
                    throw new Error(error.message || 'The expense report could not be loaded.');
                }
                return body.data;
            });
        }).then(function (data) {
            report = data;
            renderReport();
            setLoading(false);
            document.getElementById('expenseReportContent').style.display = 'block';
            if (scrollToTop) {
                document.getElementById('expenseReport').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }).catch(function (error) {
            setLoading(false);
            setAlert(error.message, 'danger');
        });
    }

    function populateSelect(id, options, placeholder) {
        var select = document.getElementById(id);
        var current = select.value;
        var html = '<option value="">' + escapeHtml(placeholder) + '</option>';
        (options || []).forEach(function (option) {
            html += '<option value="' + escapeHtml(option.value) + '">' + escapeHtml(option.label) + '</option>';
        });
        select.innerHTML = html;
        select.value = current;
    }

    function populateOptions() {
        if (optionsLoaded || !report.options) { return; }
        populateSelect('expenseCategory', report.options.categories, 'All categories');
        populateSelect('expenseCostCenter', report.options.cost_centers, 'All cost centres');
        populateSelect('expenseStatus', report.options.statuses, 'All statuses');
        populateSelect('expenseCurrency', report.options.currencies, 'All currencies');
        optionsLoaded = true;
    }

    function renderSummary() {
        var summary = report.summary;
        document.getElementById('expenseNetSpend').textContent = formatAmount(summary.net_total);
        var change = document.getElementById('expenseNetChange');
        if (summary.change_percent == null) {
            change.textContent = 'No comparable prior-period base';
            change.className = '';
        } else {
            change.textContent = (summary.change_amount >= 0 ? '↑ ' : '↓ ') + formatPercent(Math.abs(summary.change_percent)) + ' vs preceding period';
            change.className = summary.change_amount >= 0 ? 'expense-delta-up' : 'expense-delta-down';
        }
        document.getElementById('expensePnlSpend').textContent = formatAmount(summary.pnl_total);
        document.getElementById('expensePnlShare').textContent = summary.net_total ? formatPercent((summary.pnl_total / summary.net_total) * 100) + ' of net spend' : 'Operating and direct costs';
        document.getElementById('expenseBalanceSpend').textContent = formatAmount(summary.balance_sheet_total);
        document.getElementById('expenseActionSpend').textContent = formatAmount(summary.action_required_total);
        document.getElementById('expenseActionDetail').textContent = formatAmount(summary.pending_authorization_total) + ' awaiting approval · ' + formatAmount(summary.authorized_unposted_total) + ' unposted';
        document.getElementById('expenseTransactions').textContent = formatNumber(summary.transaction_count);
        document.getElementById('expenseAverage').textContent = formatAmount(summary.average_transaction) + ' average';
        document.getElementById('expenseReceiptCoverage').textContent = formatPercent(summary.receipt_coverage_percent);
        document.getElementById('expenseReceiptDetail').textContent = formatNumber(summary.missing_receipt_count) + ' without evidence';
    }

    function renderInsights() {
        var container = document.getElementById('expenseInsights');
        container.innerHTML = (report.insights || []).map(function (insight) {
            return '<article class="expense-insight expense-insight-' + escapeHtml(insight.tone) + '"><strong>' + escapeHtml(insight.title) + '</strong><span>' + escapeHtml(insight.detail) + '</span></article>';
        }).join('');
    }

    function chartColors(count) {
        var palette = ['#19a974', '#2f7ea5', '#e09f3e', '#7d6b91', '#d46a4c', '#4ca1a3', '#607d8b', '#8ba84a', '#a45d83', '#c28d36', '#708090', '#a85d5d'];
        return palette.slice(0, count);
    }

    function renderTrend() {
        var rows = report.breakdowns.monthly || [];
        var canvas = document.getElementById('expenseTrendChart');
        if (trendChart && typeof trendChart.destroy === 'function') { trendChart.destroy(); }
        if (typeof window.Chart === 'undefined') {
            canvas.parentNode.innerHTML = '<div class="expense-empty">The chart library is unavailable. Monthly values are included in the Excel export.</div>';
            return;
        }
        trendChart = new window.Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: rows.map(function (row) { return row.period; }),
                datasets: [{
                    label: 'Net spend',
                    data: rows.map(function (row) { return Number(row.total || 0); }),
                    borderColor: '#19a974',
                    backgroundColor: 'rgba(25,169,116,0.10)',
                    borderWidth: 2,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#19a974',
                    pointRadius: rows.length > 18 ? 1 : 3,
                    pointHoverRadius: 5,
                    fill: true,
                    lineTension: 0.24
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                tooltips: { callbacks: { label: function (item) { return formatAmount(item.yLabel); } } },
                scales: {
                    xAxes: [{ gridLines: { display: false }, ticks: { fontColor: '#7c8c98', maxTicksLimit: 12 } }],
                    yAxes: [{ gridLines: { color: '#edf1f4' }, ticks: { beginAtZero: true, fontColor: '#7c8c98', callback: function (value) { return formatCompact(value); } } }]
                }
            }
        });
        document.getElementById('expenseTrendCaption').textContent = rows.length + (rows.length === 1 ? ' month' : ' months');
    }

    function renderBars(containerId, rows, labelKey, limit, filterName) {
        var container = document.getElementById(containerId);
        var visible = (rows || []).slice(0, limit || 8);
        var max = visible.reduce(function (value, row) { return Math.max(value, Math.abs(Number(row.total || 0))); }, 0);
        if (!visible.length) {
            container.innerHTML = '<div class="expense-empty">No values match this view.</div>';
            return;
        }
        container.innerHTML = visible.map(function (row) {
            var label = row[labelKey] || 'Unassigned';
            var width = max ? Math.max(1.5, (Math.abs(Number(row.total || 0)) / max) * 100) : 0;
            var filter = filterName ? ' data-filter-name="' + escapeHtml(filterName) + '" data-filter-value="' + escapeHtml(filterName === 'category' ? row.category : '') + '"' : '';
            return '<div class="expense-bar-row"' + filter + '><div class="expense-bar-label"><strong title="' + escapeHtml(label) + '">' + escapeHtml(label) + '</strong><span>' + escapeHtml(formatAmount(row.total)) + '</span></div><div class="expense-bar-track"><div class="expense-bar-fill" style="width:' + width.toFixed(1) + '%"></div></div></div>';
        }).join('');
    }

    function changeBadge(value) {
        if (value == null) { return '<span class="expense-change expense-change-neutral">New</span>'; }
        var direction = value > 0.05 ? 'up' : (value < -0.05 ? 'down' : 'neutral');
        var prefix = value > 0 ? '+' : '';
        return '<span class="expense-change expense-change-' + direction + '">' + prefix + escapeHtml(formatPercent(value)) + '</span>';
    }

    function renderCategoryTable() {
        var rows = report.breakdowns.categories || [];
        document.getElementById('expenseCategoryTable').innerHTML = rows.length ? rows.map(function (row) {
            return '<tr><td><span class="expense-category-name">' + escapeHtml(row.category) + '</span><span class="expense-subtext">' + formatNumber(row.expense_code_count) + ' codes</span></td><td class="text-right">' + escapeHtml(formatAmount(row.total)) + '</td><td class="text-right">' + escapeHtml(formatPercent(row.share_percent)) + '</td><td class="text-right">' + formatNumber(row.transaction_count) + '</td><td class="text-right">' + changeBadge(row.change_percent) + '</td></tr>';
        }).join('') : '<tr><td colspan="5" class="expense-empty">No category totals are available.</td></tr>';
    }

    function statusLabel(status) {
        return { posted: 'Posted to GL', authorized_unposted: 'Authorized, not posted', pending_authorization: 'Pending authorization' }[status] || String(status || '').replace(/_/g, ' ');
    }

    function renderControls() {
        var statuses = report.breakdowns.statuses || [];
        document.getElementById('expenseStatusList').innerHTML = statuses.length ? statuses.map(function (row) {
            return '<div class="expense-status-row"><span class="expense-status-dot expense-status-' + escapeHtml(row.workflow_status) + '"></span><div class="expense-status-copy"><strong>' + escapeHtml(statusLabel(row.workflow_status)) + '</strong><small>' + formatNumber(row.transaction_count) + ' transactions · ' + escapeHtml(formatPercent(row.share_percent)) + '</small></div><span class="expense-status-amount">' + escapeHtml(formatAmount(row.total)) + '</span></div>';
        }).join('') : '<div class="expense-empty">No workflow activity.</div>';
        var summary = report.summary;
        var quality = [
            { value: summary.missing_receipt_count, label: 'Missing receipts' },
            { value: summary.unclassified_count, label: 'Unmapped rows' },
            { value: summary.foreign_currency_count, label: 'FX rows' }
        ];
        document.getElementById('expenseQuality').innerHTML = quality.map(function (item) {
            return '<div class="expense-quality-item"><strong>' + formatNumber(item.value) + '</strong><span>' + escapeHtml(item.label) + '</span></div>';
        }).join('');
    }

    function renderExpenseCodes() {
        var rows = report.breakdowns.expense_codes || [];
        var visible = rows.slice(0, 150);
        document.getElementById('expenseCodeTable').innerHTML = visible.length ? visible.map(function (row) {
            return '<tr><td><span class="expense-category-name">' + escapeHtml(row.category) + '</span></td><td>' + escapeHtml(row.codeexpense) + '</td><td>' + escapeHtml(row.description) + '</td><td>' + escapeHtml(row.glaccount || '—') + '<span class="expense-subtext">' + escapeHtml(row.accountname || 'Unmapped') + '</span></td><td>' + escapeHtml(row.account_group || 'Unmapped') + '</td><td>' + escapeHtml(row.spend_class) + '</td><td class="text-right">' + escapeHtml(formatAmount(row.total)) + '</td><td class="text-right">' + escapeHtml(formatPercent(row.share_percent)) + '</td><td class="text-right">' + formatNumber(row.transaction_count) + '</td></tr>';
        }).join('') : '<tr><td colspan="9" class="expense-empty">No expense codes match this view.</td></tr>';
        document.getElementById('expenseCodeCaption').textContent = visible.length === rows.length ? formatNumber(rows.length) + ' active codes' : 'Top ' + formatNumber(visible.length) + ' of ' + formatNumber(rows.length) + ' active codes · export for all';
    }

    function renderTransactions() {
        var data = report.transactions;
        var rows = data.rows || [];
        document.getElementById('expenseTransactionTable').innerHTML = rows.length ? rows.map(function (row) {
            var original = row.currency !== report.metadata.default_currency ? '<span class="expense-subtext">' + escapeHtml(row.currency + ' ' + formatNumber(row.original_amount, 2)) + '</span>' : '';
            return '<tr><td>' + escapeHtml(row.date) + '<span class="expense-subtext">#' + formatNumber(row.counterindex) + '</span></td><td><span class="expense-category-name">' + escapeHtml(row.category) + '</span><span class="expense-subtext">' + escapeHtml(row.codeexpense + ' · ' + row.description) + '</span></td><td>' + escapeHtml(row.owner) + '<span class="expense-subtext">' + escapeHtml(row.cost_center + ' · ' + row.tabcode) + '</span></td><td><span class="expense-workflow expense-workflow-' + escapeHtml(row.workflow_status) + '">' + escapeHtml(statusLabel(row.workflow_status)) + '</span></td><td class="expense-note-cell" title="' + escapeHtml(row.notes) + '">' + escapeHtml(row.notes || '—') + '</td><td>' + (row.has_receipt ? '<span class="expense-evidence-ok"><i class="fa fa-check-circle"></i> Available</span>' : '<span class="expense-evidence-missing"><i class="fa fa-exclamation-circle"></i> Missing</span>') + '</td><td class="text-right ' + (row.entry_kind === 'credit' ? 'expense-credit' : '') + '"><strong>' + escapeHtml(formatAmount(row.functional_amount)) + '</strong>' + original + '</td></tr>';
        }).join('') : '<tr><td colspan="7" class="expense-empty">No transactions match this view.</td></tr>';
        var first = data.total_rows ? ((data.page - 1) * data.page_size) + 1 : 0;
        var last = Math.min(data.total_rows, data.page * data.page_size);
        document.getElementById('expenseTransactionCaption').textContent = formatNumber(first) + '–' + formatNumber(last) + ' of ' + formatNumber(data.total_rows);
        document.getElementById('expensePageLabel').textContent = 'Page ' + formatNumber(data.page) + ' of ' + formatNumber(data.total_pages);
        document.getElementById('expensePreviousPage').disabled = data.page <= 1;
        document.getElementById('expenseNextPage').disabled = data.page >= data.total_pages;
    }

    function renderMethodology() {
        var metadata = report.metadata;
        document.getElementById('expenseMethodology').textContent = metadata.amount_definition + ' ' + metadata.currency_method + ' Access: ' + metadata.access_scope + '.';
        document.getElementById('expenseUpdatedAt').textContent = 'Updated ' + metadata.generated_at_utc + ' UTC · ' + metadata.elapsed_ms + ' ms';
    }

    function renderReport() {
        populateOptions();
        renderSummary();
        renderInsights();
        renderTrend();
        renderBars('expenseCategoryBars', report.breakdowns.categories, 'category', 8, 'category');
        renderCategoryTable();
        renderControls();
        renderBars('expenseOwnerBars', report.breakdowns.owners, 'owner', 8);
        renderBars('expenseCenterBars', report.breakdowns.cost_centers, 'cost_center', 8);
        renderExpenseCodes();
        renderTransactions();
        renderMethodology();
    }

    function exportUrl() {
        var payload = requestPayload();
        var params = [];
        params.push('startDate=' + encodeURIComponent(payload.dateRange.start));
        params.push('endDate=' + encodeURIComponent(payload.dateRange.end));
        ['category', 'costCenter', 'status', 'currency', 'search'].forEach(function (key) {
            if (payload[key]) { params.push(encodeURIComponent(key) + '=' + encodeURIComponent(payload[key])); }
        });
        params.push('page=1&pageSize=50&sort=date&direction=desc');
        return window.SAHAMID_BI.expenseExportUrl + '?' + params.join('&');
    }

    document.getElementById('expenseFilters').addEventListener('submit', function (event) {
        event.preventDefault();
        currentPage = 1;
        loadReport(false);
    });

    document.getElementById('expenseReset').addEventListener('click', function () {
        document.getElementById('expenseStartDate').value = initialStartDate;
        document.getElementById('expenseEndDate').value = initialEndDate;
        ['expenseCategory', 'expenseCostCenter', 'expenseStatus', 'expenseCurrency', 'expenseSearch'].forEach(function (id) { document.getElementById(id).value = ''; });
        currentPage = 1;
        loadReport(false);
    });

    document.getElementById('expenseExport').addEventListener('click', function () {
        var payload = requestPayload();
        if (validateDates(payload)) { window.location.href = exportUrl(); }
    });

    document.getElementById('expensePreviousPage').addEventListener('click', function () {
        if (currentPage > 1) { currentPage--; loadReport(false); }
    });

    document.getElementById('expenseNextPage').addEventListener('click', function () {
        if (report && currentPage < report.transactions.total_pages) { currentPage++; loadReport(false); }
    });

    document.getElementById('expenseCategoryBars').addEventListener('click', function (event) {
        var row = event.target.closest ? event.target.closest('[data-filter-name="category"]') : null;
        if (!row) { return; }
        document.getElementById('expenseCategory').value = row.getAttribute('data-filter-value');
        currentPage = 1;
        loadReport(false);
    });

    loadReport(false);
}());
