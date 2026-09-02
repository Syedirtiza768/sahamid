(function () {
    'use strict';

    var report = null;
    var currentPage = 1;
    var optionsLoaded = false;
    var trendChart = null;
    var charts = {};
    var activeTab = 'summary';
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
        document.getElementById('expenseExport').disabled = loading;
        if (loading && !report) {
            document.getElementById('expenseReportContent').style.display = 'none';
        }
    }

    function selectedValue(id) {
        var element = document.getElementById(id);
        return element ? element.value.trim() : '';
    }

    function requestPayload() {
        var payload = {
            dateRange: { start: selectedValue('expenseStartDate'), end: selectedValue('expenseEndDate') },
            includeLocalPurchases: document.getElementById('expenseIncludeLocalPurchases').checked,
            page: currentPage,
            pageSize: parseInt(selectedValue('expensePageSize') || '50', 10),
            sort: selectedValue('expenseSort') || 'date',
            direction: document.getElementById('expenseSortDirection').getAttribute('data-direction') || 'desc'
        };
        var fields = {
            category: 'expenseCategory',
            costCenter: 'expenseCostCenter',
            tabCode: 'expenseTabCode',
            userCode: 'expenseUser',
            expenseCode: 'expenseExpenseCode',
            glAccount: 'expenseGlAccount',
            accountGroup: 'expenseAccountGroup',
            section: 'expenseSection',
            spendClass: 'expenseSpendClass',
            status: 'expenseStatus',
            receipt: 'expenseReceipt',
            entryKind: 'expenseEntryKind',
            currency: 'expenseCurrency',
            search: 'expenseSearch',
            minAmount: 'expenseMinAmount',
            maxAmount: 'expenseMaxAmount'
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
        if (payload.minAmount && payload.maxAmount && Number(payload.minAmount) > Number(payload.maxAmount)) {
            setAlert('The minimum amount must not be greater than the maximum amount.', 'warning');
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
            document.getElementById('expenseReportContent').style.display = 'block';
            renderReport();
            setLoading(false);
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
        populateSelect('expenseTabCode', report.options.tabs, 'All tabs');
        populateSelect('expenseUser', report.options.users, 'All users');
        populateSelect('expenseExpenseCode', report.options.expense_codes, 'All expense codes');
        populateSelect('expenseGlAccount', report.options.gl_accounts, 'All GL accounts');
        populateSelect('expenseAccountGroup', report.options.account_groups, 'All GL groups');
        populateSelect('expenseSection', report.options.sections, 'All sections');
        populateSelect('expenseSpendClass', report.options.spend_classes, 'All spend classes');
        populateSelect('expenseStatus', report.options.statuses, 'All statuses');
        populateSelect('expenseReceipt', report.options.receipts, 'All evidence states');
        populateSelect('expenseEntryKind', report.options.entry_kinds, 'Expenses and credits');
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
        document.getElementById('expenseClassificationSummary').innerHTML =
            '<span><b>Net bridge</b> ' + escapeHtml(formatAmount(summary.gross_outflow)) + ' gross outflow − ' + escapeHtml(formatAmount(summary.credits)) + ' credits = ' + escapeHtml(formatAmount(summary.net_total)) + ' net spend</span>' +
            '<span><b>Classification</b> ' + escapeHtml(formatAmount(summary.pnl_total)) + ' P&amp;L · ' + escapeHtml(formatAmount(summary.balance_sheet_total)) + ' capital / advances · ' + escapeHtml(formatAmount(summary.unclassified_total)) + ' unclassified</span>';
        document.getElementById('expenseActionSpend').textContent = formatAmount(summary.action_required_total);
        document.getElementById('expenseActionDetail').textContent = formatAmount(summary.pending_authorization_total) + ' awaiting approval · ' + formatAmount(summary.authorized_unposted_total) + ' unposted';
        document.getElementById('expenseTransactions').textContent = formatNumber(summary.transaction_count);
        document.getElementById('expenseAverage').textContent = formatAmount(summary.average_transaction) + ' average';
        document.getElementById('expenseReceiptCoverage').textContent = formatPercent(summary.receipt_coverage_percent);
        document.getElementById('expenseReceiptDetail').textContent = formatNumber(summary.missing_receipt_count) + ' without evidence';
        document.getElementById('expenseResultSummary').textContent = formatNumber(summary.transaction_count) + ' matching transaction' + (summary.transaction_count === 1 ? '' : 's');
        document.getElementById('expenseResultScope').textContent = report.metadata.access_scope;
        document.getElementById('expenseTransactionTabCount').textContent = formatNumber(summary.transaction_count);
        var validation = report.validation || {};
        var validationStatus = document.getElementById('expenseValidationStatus');
        validationStatus.className = validation.status === 'passed' ? 'expense-validation-status is-passed' : 'expense-validation-status is-attention';
        var warningCount = (validation.warnings || []).length;
        validationStatus.textContent = validation.status === 'passed'
            ? 'Validated · ' + Number(validation.passed || 0) + ' checks' + (warningCount ? ' · ' + warningCount + ' data note' + (warningCount === 1 ? '' : 's') : '')
            : 'Review data checks · ' + Number(validation.failed || 0) + ' failed';
        validationStatus.title = (validation.warnings || []).join(' ');
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
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: { boxWidth: 10, fontColor: '#536b7d', fontSize: 10, padding: 12 }
                },
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
            return '<tr><td><span class="expense-category-name">' + escapeHtml(row.category) + '</span></td><td class="text-right">' + escapeHtml(formatAmount(row.total)) + '</td><td class="text-right">' + escapeHtml(formatPercent(row.share_percent)) + '</td><td class="text-right">' + formatNumber(row.transaction_count) + '</td><td class="text-right">' + formatNumber(row.expense_code_count) + '</td><td class="text-right">' + changeBadge(row.change_percent) + '</td></tr>';
        }).join('') : modernEmptyRow(6, 'No category totals are available.');
        document.getElementById('expenseCategoryCaption').textContent = rows.length + (rows.length === 1 ? ' complete category' : ' complete categories');
    }

    function statusLabel(status) {
        return { posted: 'Posted to GL', authorized_unposted: 'Authorized, not posted', pending_authorization: 'Pending authorization' }[status] || String(status || '').replace(/_/g, ' ');
    }

    function renderControls() {
        var statuses = report.breakdowns.statuses || [];
        document.getElementById('expenseStatusTable').innerHTML = statuses.length ? statuses.map(function (row) {
            return '<tr><td><span class="expense-status-dot expense-status-' + escapeHtml(row.workflow_status) + '"></span>' + escapeHtml(statusLabel(row.workflow_status)) + '</td><td class="text-right">' + escapeHtml(formatAmount(row.total)) + '</td><td class="text-right">' + escapeHtml(formatPercent(row.share_percent)) + '</td><td class="text-right">' + formatNumber(row.transaction_count) + '</td></tr>';
        }).join('') : modernEmptyRow(4, 'No workflow activity.');
        document.getElementById('expenseStatusCaption').textContent = statuses.length + (statuses.length === 1 ? ' status' : ' statuses');

        var centers = report.breakdowns.cost_centers || [];
        document.getElementById('expenseCenterTable').innerHTML = centers.length ? centers.map(function (row) {
            return '<tr><td>' + escapeHtml(row.cost_center || 'Unassigned') + '<span class="expense-subtext">' + escapeHtml(row.cost_center_code || 'No code') + '</span></td><td class="text-right">' + escapeHtml(formatAmount(row.total)) + '</td><td class="text-right">' + escapeHtml(formatPercent(row.share_percent)) + '</td><td class="text-right">' + formatNumber(row.transaction_count) + '</td></tr>';
        }).join('') : modernEmptyRow(4, 'No cost-centre totals are available.');
        document.getElementById('expenseCenterCaption').textContent = centers.length + (centers.length === 1 ? ' cost centre' : ' cost centres');
    }

    function renderExpenseCodes() {
        var rows = report.breakdowns.expense_codes || [];
        document.getElementById('expenseCodeTable').innerHTML = rows.length ? rows.map(function (row) {
            return '<tr><td>' + escapeHtml(row.category) + '</td><td>' + escapeHtml(row.codeexpense) + '</td><td>' + escapeHtml(row.description) + '</td><td>' + escapeHtml(row.glaccount || '—') + '<span class="expense-subtext">' + escapeHtml(row.accountname || 'Unmapped') + '</span></td><td>' + escapeHtml(row.account_group || 'Unmapped') + '</td><td>' + escapeHtml(row.sectionname || 'Unmapped') + '</td><td>' + escapeHtml(row.spend_class) + '</td><td class="text-right">' + escapeHtml(formatAmount(row.total)) + '</td><td class="text-right">' + escapeHtml(formatPercent(row.share_percent)) + '</td><td class="text-right">' + formatNumber(row.transaction_count) + '</td><td class="text-right">' + changeBadge(row.change_percent) + '</td></tr>';
        }).join('') : modernEmptyRow(11, 'No expense codes match this view.');
        document.getElementById('expenseCodeCaption').textContent = rows.length + (rows.length === 1 ? ' complete expense code' : ' complete expense codes');

        var groups = modernGroupRows(rows, 'account_group');
        document.getElementById('expenseGlTable').innerHTML = groups.length ? groups.map(function (row) {
            return '<tr><td>' + escapeHtml(row.label) + '</td><td class="text-right">' + escapeHtml(formatAmount(row.total)) + '</td><td class="text-right">' + escapeHtml(formatPercent(row.share_percent)) + '</td><td class="text-right">' + formatNumber(row.transaction_count) + '</td><td class="text-right">' + formatNumber(row.expense_code_count) + '</td></tr>';
        }).join('') : modernEmptyRow(5, 'No GL group totals are available.');
        document.getElementById('expenseGlCaption').textContent = groups.length + (groups.length === 1 ? ' complete GL group' : ' complete GL groups');
    }

    function renderCurrencyTable() {
        var rows = report.breakdowns.currencies || [];
        document.getElementById('expenseCurrencyTable').innerHTML = rows.length ? rows.map(function (row) {
            return '<tr><td>' + escapeHtml(row.currency) + '</td><td class="text-right">' + escapeHtml(Number(row.current_rate || 0).toLocaleString('en-US', { minimumFractionDigits: 4, maximumFractionDigits: 4 })) + '</td><td class="text-right">' + escapeHtml(formatNumber(row.original_total, 2)) + '</td><td class="text-right">' + escapeHtml(formatAmount(row.total)) + '</td><td class="text-right">' + formatNumber(row.transaction_count) + '</td></tr>';
        }).join('') : modernEmptyRow(5, 'No currency totals are available.');
        document.getElementById('expenseCurrencyCaption').textContent = rows.length + (rows.length === 1 ? ' currency' : ' currencies') + ' · PKR-only · no exchange-rate conversion';
    }

    function renderTabTables() {
        var rows = report.breakdowns.tabs || [];
        document.getElementById('expenseTabTable').innerHTML = rows.length ? rows.map(function (row) {
            return '<tr><td><strong>' + escapeHtml(row.tabcode || 'Unmapped') + '</strong><span class="expense-subtext">' + escapeHtml(row.usercode || 'No user code') + '</span></td><td>' + escapeHtml(row.owner || 'Unassigned') + '</td><td>' + escapeHtml(row.cost_center || 'Unassigned') + '</td><td class="text-right">' + escapeHtml(formatAmount(row.total)) + '</td><td class="text-right">' + escapeHtml(formatPercent(row.share_percent)) + '</td><td class="text-right">' + formatNumber(row.transaction_count) + '</td><td class="text-right">' + formatNumber(row.expense_code_count) + '</td><td class="text-right">' + escapeHtml(formatAmount(row.posted_total)) + '</td><td class="text-right">' + escapeHtml(formatAmount(row.pending_total)) + '</td><td class="text-right">' + escapeHtml(formatAmount(row.authorized_unposted_total)) + '</td><td class="text-right">' + formatNumber(row.missing_receipt_count) + '</td></tr>';
        }).join('') : modernEmptyRow(11, 'No expense tabs match the current filters.');
        document.getElementById('expenseTabCaption').textContent = rows.length + (rows.length === 1 ? ' complete tab' : ' complete tabs') + ' · fully filtered · ' + report.metadata.default_currency;
    }
    function renderUserTables() {
        var users = report.breakdowns.users || [];
        var userTable = document.getElementById('expenseUserTable');
        userTable.innerHTML = users.length ? users.map(function (row) {
            return '<tr><td><span class="expense-category-name">' + escapeHtml(row.owner) + '</span><span class="expense-subtext">' + escapeHtml(row.usercode || 'No user code') + '</span></td><td class="text-right">' + escapeHtml(formatAmount(row.total)) + '</td><td class="text-right">' + escapeHtml(formatPercent(row.share_percent)) + '</td><td class="text-right">' + formatNumber(row.transaction_count) + '</td><td class="text-right">' + formatNumber(row.tab_count) + '</td><td class="text-right">' + formatNumber(row.expense_code_count) + '</td><td class="text-right">' + escapeHtml(formatAmount(row.pnl_total)) + '</td><td class="text-right">' + escapeHtml(formatAmount(row.balance_sheet_total)) + '</td><td class="text-right">' + escapeHtml(formatAmount(row.unclassified_total)) + '</td><td class="text-right">' + escapeHtml(formatPercent(row.receipt_coverage_percent)) + '</td><td class="text-right">' + changeBadge(row.change_percent) + '</td></tr>';
        }).join('') : modernEmptyRow(11, 'No user totals match this view.');
        document.getElementById('expenseUserCaption').textContent = users.length + (users.length === 1 ? ' complete user' : ' complete users') + ' · consolidated across visible expense tabs';

        var details = report.breakdowns.user_expenses || [];
        document.getElementById('expenseUserExpenseTable').innerHTML = details.length ? details.map(function (row) {
            return '<tr><td><span class="expense-category-name">' + escapeHtml(row.owner) + '</span><span class="expense-subtext">' + escapeHtml(row.usercode || 'No user code') + '</span></td><td><span class="expense-category-name">' + escapeHtml(row.category) + '</span><span class="expense-subtext">' + escapeHtml(row.spend_class) + '</span></td><td>' + escapeHtml(row.codeexpense) + '</td><td>' + escapeHtml(row.description) + '</td><td>' + escapeHtml(row.glaccount || '—') + '<span class="expense-subtext">' + escapeHtml(row.account_group || 'Unmapped') + '</span></td><td class="text-right">' + escapeHtml(formatAmount(row.total)) + '</td><td class="text-right">' + escapeHtml(formatPercent(row.share_percent)) + '</td><td class="text-right">' + formatNumber(row.transaction_count) + '</td><td class="text-right">' + formatNumber(row.tab_count) + '</td><td class="text-right">' + changeBadge(row.change_percent) + '</td></tr>';
        }).join('') : modernEmptyRow(10, 'No user expense detail matches this view.');
        document.getElementById('expenseUserExpenseCaption').textContent = formatNumber(details.length) + (details.length === 1 ? ' complete user/code combination' : ' complete user/code combinations');
    }

    function renderTransactions() {
        var data = report.transactions;
        var rows = data.rows || [];
        document.getElementById('expenseTransactionTable').innerHTML = rows.length ? rows.map(function (row) {
            var original = row.currency !== report.metadata.default_currency ? '<span class="expense-subtext">' + escapeHtml(row.currency + ' ' + formatNumber(row.original_amount, 2)) + '</span>' : '';
            return '<tr><td>' + escapeHtml(row.date) + '<span class="expense-subtext">#' + formatNumber(row.counterindex) + '</span></td><td><span class="expense-category-name">' + escapeHtml(row.category) + '</span><span class="expense-subtext">' + escapeHtml(row.codeexpense + ' · ' + row.description) + '</span></td><td>' + escapeHtml(row.owner) + '<span class="expense-subtext">' + escapeHtml(row.usercode || 'No user code') + ' · ' + escapeHtml(row.cost_center) + ' · ' + escapeHtml(row.tabcode) + '</span></td><td>' + escapeHtml(row.glaccount || '—') + '<span class="expense-subtext">' + escapeHtml(row.account_group || 'Unmapped') + ' · ' + escapeHtml(row.sectionname || 'Unmapped') + '</span></td><td><span class="expense-workflow expense-workflow-' + escapeHtml(row.workflow_status) + '">' + escapeHtml(statusLabel(row.workflow_status)) + '</span></td><td class="expense-note-cell" title="' + escapeHtml(row.notes) + '">' + escapeHtml(row.notes || '—') + '</td><td>' + (row.has_receipt ? '<span class="expense-evidence-ok"><i class="fa fa-check-circle"></i> Available</span>' : '<span class="expense-evidence-missing"><i class="fa fa-exclamation-circle"></i> Missing</span>') + '</td><td class="text-right ' + (row.entry_kind === 'credit' ? 'expense-credit' : '') + '"><strong>' + escapeHtml(formatAmount(row.functional_amount)) + '</strong>' + original + '</td></tr>';
        }).join('') : '<tr><td colspan="8" class="expense-empty">No transactions match this view.</td></tr>';
        var first = data.total_rows ? ((data.page - 1) * data.page_size) + 1 : 0;
        var last = Math.min(data.total_rows, data.page * data.page_size);
        document.getElementById('expenseTransactionCaption').textContent = formatNumber(first) + '–' + formatNumber(last) + ' of ' + formatNumber(data.total_rows);
        document.getElementById('expensePageLabel').textContent = 'Page ' + formatNumber(data.page) + ' of ' + formatNumber(data.total_pages);
        document.getElementById('expensePreviousPage').disabled = data.page <= 1;
        document.getElementById('expenseNextPage').disabled = data.page >= data.total_pages;
        document.getElementById('expenseTransactionTabCount').textContent = formatNumber(data.total_rows);
    }

    function modernSelectedOptionLabel(id) {
        var select = document.getElementById(id);
        if (!select || !select.value || !select.options[select.selectedIndex]) { return ''; }
        return select.options[select.selectedIndex].text;
    }

    function renderModernFilterState() {
        var fields = {
            category: { id: 'expenseCategory', label: 'Category' },
            costCenter: { id: 'expenseCostCenter', label: 'Cost centre' },
            tabCode: { id: 'expenseTabCode', label: 'Tab' },
            userCode: { id: 'expenseUser', label: 'User' },
            expenseCode: { id: 'expenseExpenseCode', label: 'Expense code' },
            glAccount: { id: 'expenseGlAccount', label: 'GL account' },
            accountGroup: { id: 'expenseAccountGroup', label: 'GL group' },
            section: { id: 'expenseSection', label: 'Section' },
            spendClass: { id: 'expenseSpendClass', label: 'Spend class' },
            status: { id: 'expenseStatus', label: 'Workflow' },
            receipt: { id: 'expenseReceipt', label: 'Evidence' },
            entryKind: { id: 'expenseEntryKind', label: 'Entry type' },
            search: { id: 'expenseSearch', label: 'Search' },
            minAmount: { id: 'expenseMinAmount', label: 'Minimum' },
            maxAmount: { id: 'expenseMaxAmount', label: 'Maximum' }
        };
        var active = [];
        Object.keys(fields).forEach(function (key) {
            var value = selectedValue(fields[key].id);
            if (!value) { return; }
            var label = fields[key].id === 'expenseMinAmount' || fields[key].id === 'expenseMaxAmount' ? value : modernSelectedOptionLabel(fields[key].id) || value;
            active.push({ key: key, label: fields[key].label + ': ' + label });
        });
        if (!document.getElementById('expenseIncludeLocalPurchases').checked) {
            active.push({ key: 'includeLocalPurchases', label: 'Local purchases: excluded' });
        }
        document.getElementById('expenseFilterCount').textContent = active.length ? active.length + (active.length === 1 ? ' active filter' : ' active filters') : 'All filters available';
        document.getElementById('expenseFilterSummary').textContent = active.length ? active.length + (active.length === 1 ? ' filter is applied' : ' filters are applied') + ' to every view and export' : 'All expense data in the selected period';
        document.getElementById('expenseActiveFilters').innerHTML = active.length ? active.map(function (item) {
            return '<button type="button" class="expense-filter-chip" data-clear-filter="' + escapeHtml(item.key) + '">' + escapeHtml(item.label) + ' <i class="fa fa-times"></i></button>';
        }).join('') : '<span class="expense-no-filter-chip">No additional filters</span>';
    }

    function modernDestroyChart(canvasId) {
        if (charts[canvasId] && typeof charts[canvasId].destroy === 'function') { charts[canvasId].destroy(); }
        delete charts[canvasId];
    }

    function modernPrepareCanvas(canvasId) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) { return null; }
        canvas.style.display = 'block';
        var message = canvas.parentNode.querySelector('.expense-chart-message');
        if (message) { message.parentNode.removeChild(message); }
        return canvas;
    }

    function modernChartUnavailable(canvasId, message) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) { return; }
        canvas.style.display = 'none';
        var node = document.createElement('div');
        node.className = 'expense-chart-message';
        node.textContent = message;
        canvas.parentNode.appendChild(node);
    }

    function modernChartColors(count) {
        var palette = ['#168a65', '#2f7ea5', '#d98d2b', '#74608a', '#c65e49', '#3d9b9a', '#607d8b', '#8aa447', '#a0527c', '#bd7e28', '#6c7a89', '#a35757', '#4b7896', '#697d3b'];
        var colors = [];
        for (var i = 0; i < count; i++) { colors.push(palette[i % palette.length]); }
        return colors;
    }

    function modernLegend(legendId, items, note) {
        var legend = document.getElementById(legendId);
        if (!legend) { return; }
        legend.innerHTML = (items || []).map(function (item) {
            return '<span class="expense-legend-item"><i style="background:' + escapeHtml(item.color) + '"></i><span>' + escapeHtml(item.label) + '</span>' + (item.value == null ? '' : '<b>' + escapeHtml(item.value) + '</b>') + '</span>';
        }).join('') + (note ? '<span class="expense-legend-note">' + escapeHtml(note) + '</span>' : '');
    }

    function modernChartRows(rows, labelKey, limit) {
        var sorted = (rows || []).slice().sort(function (left, right) { return Number(right.total || 0) - Number(left.total || 0); });
        var visible = sorted.slice(0, limit);
        var remainder = sorted.slice(limit);
        if (remainder.length) {
            visible.push({
                chart_label: 'Other (' + remainder.length + ')',
                total: remainder.reduce(function (sum, row) { return sum + Number(row.total || 0); }, 0),
                transaction_count: remainder.reduce(function (sum, row) { return sum + Number(row.transaction_count || 0); }, 0)
            });
        }
        return { rows: visible, totalRows: sorted.length, shownRows: Math.min(sorted.length, limit), labelKey: labelKey };
    }

    function modernChartLabel(row, labelKey) {
        return row.chart_label || row[labelKey] || 'Unmapped';
    }

    function modernRenderDoughnut(canvasId, legendId, rows, labelKey, limit) {
        modernDestroyChart(canvasId);
        var canvas = modernPrepareCanvas(canvasId);
        if (!canvas) { return; }
        var prepared = modernChartRows(rows, labelKey, limit || 8);
        if (!prepared.rows.length) {
            modernChartUnavailable(canvasId, 'No values match the current filters.');
            modernLegend(legendId, [], 'No data in this filtered view.');
            return;
        }
        if (typeof window.Chart === 'undefined') {
            modernChartUnavailable(canvasId, 'Chart library unavailable. Use the complete table.');
            return;
        }
        var colors = modernChartColors(prepared.rows.length);
        var data = prepared.rows.map(function (row, index) {
            return { value: Math.abs(Number(row.total || 0)), color: colors[index], highlight: colors[index], label: modernChartLabel(row, labelKey) };
        });
        charts[canvasId] = new window.Chart(canvas.getContext('2d')).Doughnut(data, { responsive: true, percentageInnerCutout: 48 });
        modernLegend(legendId, prepared.rows.map(function (row, index) {
            return {
                label: modernChartLabel(row, labelKey),
                color: colors[index],
                value: formatAmount(row.total) + ' · ' + formatPercent(report.summary.net_total ? (Number(row.total || 0) / report.summary.net_total) * 100 : 0)
            };
        }), prepared.totalRows > prepared.shownRows ? 'Top values plus Other; the complete table is below.' : 'Net spend share of this filtered view.');
    }

    function modernRenderBar(canvasId, legendId, rows, labelKey, limit, captionId) {
        modernDestroyChart(canvasId);
        var canvas = modernPrepareCanvas(canvasId);
        if (!canvas) { return; }
        var prepared = modernChartRows(rows, labelKey, limit || 12);
        if (captionId) {
            document.getElementById(captionId).textContent = prepared.totalRows > prepared.shownRows ? 'Top ' + prepared.shownRows + ' of ' + prepared.totalRows : prepared.totalRows + ' values';
        }
        if (!prepared.rows.length) {
            modernChartUnavailable(canvasId, 'No values match the current filters.');
            modernLegend(legendId, [], 'No data in this filtered view.');
            return;
        }
        if (typeof window.Chart === 'undefined') {
            modernChartUnavailable(canvasId, 'Chart library unavailable. Use the complete table.');
            return;
        }
        charts[canvasId] = new window.Chart(canvas.getContext('2d')).Bar({
            labels: prepared.rows.map(function (row) { return modernChartLabel(row, labelKey); }),
            datasets: [{ label: 'Net spend', fillColor: '#168a65', strokeColor: '#168a65', highlightFill: '#31ad86', highlightStroke: '#31ad86', data: prepared.rows.map(function (row) { return Number(row.total || 0); }) }]
        }, { responsive: true, scaleLabel: '<%=value%>', scaleFontColor: '#718393', scaleFontSize: 10, barValueSpacing: 8 });
        modernLegend(legendId, [{ label: 'Net spend', color: '#168a65' }], prepared.totalRows > prepared.shownRows ? 'Top values plus Other; the complete table is below.' : 'All values in this filtered view.');
    }

    function modernRenderTrend() {
        var rows = report.breakdowns.monthly || [];
        var canvasId = 'expenseTrendChart';
        modernDestroyChart(canvasId);
        var canvas = modernPrepareCanvas(canvasId);
        if (!canvas) { return; }
        document.getElementById('expenseTrendCaption').textContent = rows.length + (rows.length === 1 ? ' month' : ' months');
        if (!rows.length) {
            modernChartUnavailable(canvasId, 'No monthly values match the current filters.');
            modernLegend('expenseTrendLegend', [], 'No data in this filtered period.');
            return;
        }
        if (typeof window.Chart === 'undefined') {
            modernChartUnavailable(canvasId, 'Chart library unavailable. Use the complete tables.');
            return;
        }
        charts[canvasId] = new window.Chart(canvas.getContext('2d')).Line({
            labels: rows.map(function (row) { return row.period; }),
            datasets: [
                { label: 'Net spend', fillColor: 'rgba(22,138,101,0.10)', strokeColor: '#168a65', pointColor: '#168a65', pointStrokeColor: '#fff', data: rows.map(function (row) { return Number(row.total || 0); }) },
                { label: 'Gross outflow', fillColor: 'rgba(47,126,165,0.02)', strokeColor: '#2f7ea5', pointColor: '#2f7ea5', pointStrokeColor: '#fff', data: rows.map(function (row) { return Number(row.gross_outflow || 0); }) },
                { label: 'Credits', fillColor: 'rgba(217,141,43,0.02)', strokeColor: '#d98d2b', pointColor: '#d98d2b', pointStrokeColor: '#fff', data: rows.map(function (row) { return Number(row.credits || 0); }) }
            ]
        }, { responsive: true, scaleLabel: '<%=value%>', scaleFontColor: '#718393', scaleFontSize: 10 });
        modernLegend('expenseTrendLegend', [{ label: 'Net spend', color: '#168a65' }, { label: 'Gross outflow', color: '#2f7ea5' }, { label: 'Credits', color: '#d98d2b' }], 'Amounts in ' + report.metadata.default_currency + ' · monthly claim date');
    }

    function modernGroupRows(rows, key) {
        var groups = {};
        (rows || []).forEach(function (row) {
            var label = String(row[key] || '').trim() || 'Unmapped';
            if (!groups[label]) { groups[label] = { label: label, total: 0, transaction_count: 0, expense_code_count: 0 }; }
            groups[label].total += Number(row.total || 0);
            groups[label].transaction_count += Number(row.transaction_count || 0);
            groups[label].expense_code_count += 1;
        });
        var output = Object.keys(groups).map(function (keyName) { return groups[keyName]; });
        output.sort(function (left, right) { return right.total - left.total; });
        output.forEach(function (row) { row.share_percent = report.summary.net_total ? (row.total / report.summary.net_total) * 100 : 0; });
        return output;
    }

    function modernEmptyRow(colspan, message) {
        return '<tr><td colspan="' + colspan + '" class="expense-empty">' + escapeHtml(message) + '</td></tr>';
    }

    function renderModernCharts() {
        modernRenderTrend();
        modernRenderDoughnut('expenseCategoryChart', 'expenseCategoryLegend', report.breakdowns.categories, 'category', 8);
        modernRenderDoughnut('expenseStatusChart', 'expenseStatusLegend', report.breakdowns.statuses, 'workflow_status', 6);
        modernRenderDoughnut('expenseOwnerChart', 'expenseOwnerLegend', report.breakdowns.users, 'owner', 8);
        modernRenderBar('expenseTabChart', 'expenseTabLegend', report.breakdowns.tabs, 'tabcode', 12, 'expenseTabChartCaption');
    }

    function renderChartsForTab(tabName) {
        if (!report) { return; }
        if (tabName === 'summary') {
            renderModernCharts();
            return;
        }
        if (tabName === 'users') {
            var users = report.breakdowns.users || [];
            var details = report.breakdowns.user_expenses || [];
            modernRenderBar('expenseUserChart', 'expenseUserLegend', users, 'owner', 12);
            modernRenderBar('expenseUserExpenseChart', 'expenseUserExpenseLegend', details.map(function (row) {
                return { label: row.owner + ' · ' + row.codeexpense, total: row.total, transaction_count: row.transaction_count };
            }), 'label', 12, 'expenseUserExpenseChartCaption');
            return;
        }
        if (tabName === 'accounting') {
            var expenseCodes = report.breakdowns.expense_codes || [];
            var groups = modernGroupRows(expenseCodes, 'account_group');
            modernRenderBar('expenseCodeChart', 'expenseCodeLegend', expenseCodes, 'codeexpense', 12);
            modernRenderBar('expenseGlChart', 'expenseGlLegend', groups, 'label', 10);
            modernRenderDoughnut('expenseAccountingCenterChart', 'expenseAccountingCenterLegend', report.breakdowns.cost_centers, 'cost_center', 8);
        }
    }

    function renderReport() {
        populateOptions();
        renderModernFilterState();
        renderSummary();
        renderInsights();
        renderCategoryTable();
        renderControls();
        renderUserTables();
        renderExpenseCodes();
        renderCurrencyTable();
        renderTabTables();
        renderTransactions();
        document.getElementById('expenseUpdatedAt').textContent = 'Updated ' + report.metadata.generated_at_utc + ' UTC · ' + report.metadata.elapsed_ms + ' ms';
        setActiveExpenseTab(activeTab);
    }

    function setActiveExpenseTab(tabName) {
        activeTab = tabName;
        var tabs = document.querySelectorAll('[data-expense-tab]');
        for (var i = 0; i < tabs.length; i++) {
            var selected = tabs[i].getAttribute('data-expense-tab') === tabName;
            tabs[i].className = selected ? 'expense-tab is-active' : 'expense-tab';
            tabs[i].setAttribute('aria-selected', selected ? 'true' : 'false');
        }
        var panes = document.querySelectorAll('.expense-tab-pane');
        for (var j = 0; j < panes.length; j++) {
            var paneName = panes[j].id.replace('expenseTab', '').toLowerCase();
            var visible = paneName === tabName;
            panes[j].className = visible ? 'expense-tab-pane is-active' : 'expense-tab-pane';
            panes[j].hidden = !visible;
        }
        renderChartsForTab(tabName);
    }

    function exportUrl() {
        var payload = requestPayload();
        var params = [];
        params.push('startDate=' + encodeURIComponent(payload.dateRange.start));
        params.push('endDate=' + encodeURIComponent(payload.dateRange.end));
        ['category', 'costCenter', 'tabCode', 'userCode', 'expenseCode', 'glAccount', 'accountGroup', 'section', 'spendClass', 'status', 'receipt', 'entryKind', 'currency', 'search', 'minAmount', 'maxAmount'].forEach(function (key) {
            if (payload[key]) { params.push(encodeURIComponent(key) + '=' + encodeURIComponent(payload[key])); }
        });
        params.push('includeLocalPurchases=' + (payload.includeLocalPurchases ? '1' : '0'));
        params.push('page=1&pageSize=50&sort=' + encodeURIComponent(payload.sort) + '&direction=' + encodeURIComponent(payload.direction));
        return window.SAHAMID_BI.expenseExportUrl + '?' + params.join('&');
    }

    function clearExpenseFilter(key) {
        if (key === 'includeLocalPurchases') {
            document.getElementById('expenseIncludeLocalPurchases').checked = true;
        } else {
            var ids = {
                category: 'expenseCategory', costCenter: 'expenseCostCenter', tabCode: 'expenseTabCode',
                userCode: 'expenseUser', expenseCode: 'expenseExpenseCode', glAccount: 'expenseGlAccount',
                accountGroup: 'expenseAccountGroup', section: 'expenseSection', spendClass: 'expenseSpendClass',
                status: 'expenseStatus', receipt: 'expenseReceipt', entryKind: 'expenseEntryKind',
                currency: 'expenseCurrency', search: 'expenseSearch', minAmount: 'expenseMinAmount', maxAmount: 'expenseMaxAmount'
            };
            if (ids[key]) { document.getElementById(ids[key]).value = ''; }
        }
        currentPage = 1;
        loadReport(false);
    }

    document.getElementById('expenseFilters').addEventListener('submit', function (event) {
        event.preventDefault();
        currentPage = 1;
        loadReport(true);
    });

    document.getElementById('expenseReset').addEventListener('click', function () {
        document.getElementById('expenseStartDate').value = initialStartDate;
        document.getElementById('expenseEndDate').value = initialEndDate;
        document.getElementById('expenseIncludeLocalPurchases').checked = true;
        ['expenseCategory', 'expenseCostCenter', 'expenseTabCode', 'expenseUser', 'expenseExpenseCode', 'expenseGlAccount', 'expenseAccountGroup', 'expenseSection', 'expenseSpendClass', 'expenseStatus', 'expenseReceipt', 'expenseEntryKind', 'expenseCurrency', 'expenseSearch', 'expenseMinAmount', 'expenseMaxAmount'].forEach(function (id) { document.getElementById(id).value = ''; });
        currentPage = 1;
        loadReport(true);
    });

    document.getElementById('expenseAdvancedToggle').addEventListener('click', function () {
        var drawer = document.getElementById('expenseAdvancedFilters');
        var open = drawer.getAttribute('aria-hidden') !== 'false';
        drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
        drawer.className = open ? 'expense-filter-advanced is-open' : 'expense-filter-advanced';
        this.innerHTML = open ? '<i class="fa fa-chevron-up"></i> Hide filters' : '<i class="fa fa-sliders"></i> All filters';
    });

    document.getElementById('expenseActiveFilters').addEventListener('click', function (event) {
        var button = event.target.closest ? event.target.closest('[data-clear-filter]') : null;
        if (button) { clearExpenseFilter(button.getAttribute('data-clear-filter')); }
    });

    var expenseTabs = document.querySelectorAll('[data-expense-tab]');
    for (var tabIndex = 0; tabIndex < expenseTabs.length; tabIndex++) {
        expenseTabs[tabIndex].addEventListener('click', function () {
            setActiveExpenseTab(this.getAttribute('data-expense-tab'));
        });
    }

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

    document.getElementById('expenseSort').addEventListener('change', function () {
        currentPage = 1;
        loadReport(false);
    });

    document.getElementById('expensePageSize').addEventListener('change', function () {
        currentPage = 1;
        loadReport(false);
    });

    document.getElementById('expenseSortDirection').addEventListener('click', function () {
        var direction = this.getAttribute('data-direction') === 'asc' ? 'desc' : 'asc';
        this.setAttribute('data-direction', direction);
        this.innerHTML = direction === 'asc' ? '<i class="fa fa-sort-amount-asc"></i>' : '<i class="fa fa-sort-amount-desc"></i>';
        this.setAttribute('title', direction === 'asc' ? 'Sort ascending' : 'Sort descending');
        currentPage = 1;
        loadReport(false);
    });

    loadReport(false);
}());
