(function () {
    var state = {
        summary: null,
        details: [],
        charts: {},
        filteredDetails: [],
        detailTotal: 0,
        page: 0,
        pageSize: 50,
        detailSort: 'date',
        detailDirection: 'DESC',
        visualMetric: 'spend',
        requestId: 0,
        requestController: null,
        promptRequestId: 0,
        promptController: null,
        urlState: null,
        savedViews: [],
        lookupData: null
    };

    var palette = ['#1d79c8', '#7657bf', '#138b8e', '#b57b14', '#c9525d', '#329360', '#6c8bb2', '#a45d8d', '#4d9c83', '#bf7b42', '#55718f', '#8c6bb3'];
    var savedViewKey = 'sahamid.expense-intelligence.views.v1';

    function byId(id) {
        return document.getElementById(id);
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (character) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[character];
        });
    }

    function formatNumber(value, decimals) {
        if (value === null || value === undefined || value === '' || isNaN(Number(value))) {
            return '—';
        }
        return Number(value).toLocaleString('en-US', { maximumFractionDigits: decimals == null ? 0 : decimals });
    }

    function formatMoney(value) {
        if (value === null || value === undefined || value === '' || isNaN(Number(value))) {
            return '—';
        }
        return 'PKR ' + Number(value).toLocaleString('en-US', { maximumFractionDigits: 0 });
    }

    function formatPercent(value) {
        if (value === null || value === undefined || value === '' || isNaN(Number(value))) {
            return '—';
        }
        return Number(value).toLocaleString('en-US', { maximumFractionDigits: 1 }) + '%';
    }

    function localDate(date) {
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }

    function startOfWeek(date) {
        var result = new Date(date.getTime());
        var day = result.getDay();
        result.setDate(result.getDate() - (day === 0 ? 6 : day - 1));
        return result;
    }

    function startOfQuarter(date) {
        return new Date(date.getFullYear(), Math.floor(date.getMonth() / 3) * 3, 1);
    }

    function setDateRange(start, end) {
        byId('expenseStart').value = localDate(start);
        byId('expenseEnd').value = localDate(end);
        byId('expenseDatePreset').value = '';
    }

    function setAlert(message, type) {
        var alert = byId('expenseAlert');
        if (!message) {
            alert.style.display = 'none';
            alert.textContent = '';
            return;
        }
        alert.className = 'alert expense-alert alert-' + (type || 'warning');
        alert.textContent = message;
        alert.style.display = 'block';
    }

    function requestJson(payload, controller) {
        return fetch(window.SAHAMID_EXPENSE_BI.summaryUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
            signal: controller ? controller.signal : undefined
        }).then(function (response) {
            return response.json().then(function (body) {
                if (!response.ok || !body.ok) {
                    var error = body.error || {};
                    var reportError = new Error(error.message || 'The expense analytics request failed.');
                    reportError.userMessage = error.code === 'forbidden'
                        ? 'You are not authorized to view this report.'
                        : 'We could not load this expense report. Please retry.';
                    throw reportError;
                }
                return body.data;
            });
        });
    }

    function promptFormatValue(value, format) {
        if (format === 'count') {
            return formatNumber(value);
        }
        if (format === 'percent') {
            return formatPercent(value);
        }
        return formatMoney(value);
    }

    function promptTokenMarkup(prompt) {
        var tokens = prompt && Array.isArray(prompt.tokens) ? prompt.tokens : [];
        var unrecognized = {};
        (prompt && Array.isArray(prompt.unrecognized) ? prompt.unrecognized : []).forEach(function (token) {
            unrecognized[String(token).toLowerCase()] = true;
        });
        if (!tokens.length) {
            return '<span class="expense-prompt-token-label">Tokens</span><span class="text-muted">None</span>';
        }
        return '<span class="expense-prompt-token-label">Tokens</span>' + tokens.map(function (token) {
            var className = unrecognized[String(token).toLowerCase()] ? ' expense-prompt-token-unrecognized' : ' expense-prompt-token-recognized';
            return '<span class="expense-prompt-token' + className + '">' + escapeHtml(token) + '</span>';
        }).join('');
    }

    function promptRecognizedMarkup(prompt) {
        var recognized = prompt && Array.isArray(prompt.recognized) ? prompt.recognized : [];
        if (!recognized.length) {
            return '';
        }
        return '<div class="expense-prompt-token-row"><span class="expense-prompt-token-label">Rules applied</span>' + recognized.map(function (rule) {
            return '<span class="expense-prompt-token expense-prompt-token-recognized">' + escapeHtml(rule) + '</span>';
        }).join('') + '</div>';
    }

    function promptWarningsMarkup(prompt) {
        var warnings = prompt && Array.isArray(prompt.warnings) ? prompt.warnings : [];
        if (!warnings.length) {
            return '';
        }
        return '<div class="expense-prompt-warning"><i class="fa fa-exclamation-triangle"></i>' + warnings.map(function (warning) {
            return escapeHtml(warning);
        }).join(' ') + '</div>';
    }

    function promptGroupCell(groupBy, row) {
        var filterable = groupBy === 'category' || groupBy === 'owner' || groupBy === 'tab';
        var label = escapeHtml(row.group_label || row.group_key || 'Unmapped');
        if (!filterable) {
            return label;
        }
        return '<button type="button" class="expense-prompt-group-button" data-prompt-filter-field="' + escapeHtml(groupBy) + '" data-prompt-filter-value="' + escapeHtml(row.group_key || '') + '" title="Apply this value to the main report controls">' + label + '</button>';
    }

    function promptGroupedMarkup(result) {
        var rows = Array.isArray(result.rows) ? result.rows : [];
        var measure = result.measure || {};
        if (!rows.length) {
            return '<div class="expense-prompt-empty"><i class="fa fa-search"></i><span>No grouped rows matched the interpreted request.</span></div>';
        }
        return '<div class="expense-prompt-table-wrap"><table class="table expense-prompt-table"><thead><tr><th>' + escapeHtml(result.group_caption || 'Group') + '</th><th class="text-right">' + escapeHtml(measure.label || 'Value') + '</th><th class="text-right">Spend</th><th class="text-right">Claims</th><th class="text-right">Receipts</th></tr></thead><tbody>' + rows.map(function (row) {
            return '<tr><td>' + promptGroupCell(result.group_by, row) + '</td><td class="text-right"><strong>' + promptFormatValue(row.value, measure.format) + '</strong></td><td class="text-right">' + formatMoney(row.spend) + '</td><td class="text-right">' + formatNumber(row.transaction_count) + '</td><td class="text-right">' + formatPercent(row.receipt_coverage) + '</td></tr>';
        }).join('') + '</tbody></table></div>';
    }

    function promptDetailMarkup(result) {
        var rows = Array.isArray(result.rows) ? result.rows : [];
        if (!rows.length) {
            return '<div class="expense-prompt-empty"><i class="fa fa-search"></i><span>No transactions matched the interpreted request.</span></div>';
        }
        return '<div class="expense-prompt-table-wrap"><table class="table expense-prompt-table"><thead><tr><th>Date</th><th>Category</th><th>Enhanced tag</th><th>Tab</th><th>Owner</th><th>GL</th><th>Status</th><th class="text-right">Spend</th><th>Receipt</th><th>Notes</th></tr></thead><tbody>' + rows.map(function (row) {
            var receipt = Number(row.has_receipt) === 1 ? '<span class="expense-status expense-receipt-yes">Attached</span>' : '<span class="expense-status expense-receipt-no">Missing</span>';
            return '<tr><td>' + escapeHtml(row.date) + '</td><td><strong>' + escapeHtml(row.category) + '</strong><br><span class="text-muted">' + escapeHtml(row.codeexpense) + '</span></td><td><span class="expense-classification-tag">' + escapeHtml(row.enhanced_tag || 'Other / Review') + '</span></td><td>' + escapeHtml(row.tabcode) + '</td><td>' + escapeHtml(row.owner) + '</td><td>' + escapeHtml(row.glaccount) + '</td><td>' + detailStatus(row) + '</td><td class="text-right">' + formatMoney(row.spend) + '</td><td>' + receipt + '</td><td class="notes-cell" title="' + escapeHtml(row.notes) + '">' + escapeHtml(row.notes || '—') + '</td></tr>';
        }).join('') + '</tbody></table></div><p class="text-muted expense-prompt-detail-note">Showing ' + formatNumber(rows.length) + ' of ' + formatNumber(result.total_count || rows.length) + ' matching transactions. Use the full Transaction detail section for pagination.</p>';
    }

    function promptSupportingMarkup(result) {
        var supporting = result.supporting || {};
        var items = [
            ['Spend', formatMoney(supporting.total_spend)],
            ['Claims', formatNumber(supporting.transaction_count)],
            ['Receipt coverage', formatPercent(supporting.receipt_coverage)]
        ];
        return '<div class="expense-prompt-supporting">' + items.map(function (item) {
            return '<span>' + item[0] + '<strong>' + item[1] + '</strong></span>';
        }).join('') + '</div>';
    }

    function promptSqlMarkup(result) {
        var sql = result.sql || {};
        var parameters = Array.isArray(sql.parameters) ? sql.parameters : [];
        var parameterText = parameters.length ? parameters.map(function (value, index) {
            return '$' + (index + 1) + ' = ' + String(value);
        }).join('\n') : 'No bound parameters';
        var template = sql.sql_template || 'SQL template unavailable';
        return '<details class="expense-prompt-sql"><summary><i class="fa fa-code"></i> View generated parameterized SQL plan</summary><pre>' + escapeHtml(template) + '\n\nBound parameters:\n' + escapeHtml(parameterText) + '</pre><p>' + escapeHtml(sql.guardrail || 'Free-form SQL is disabled.') + '</p></details>';
    }

    function renderPromptResult(prompt, data) {
        var result = prompt && prompt.result ? prompt.result : {};
        var interpretation = prompt && prompt.interpretation ? prompt.interpretation : {};
        var dateRange = interpretation.date_range || {};
        var resultType = result.type || interpretation.result_type || 'summary';
        var measure = result.measure || {};
        var resultLabel = resultType === 'detail' ? 'Transaction detail' : (resultType === 'grouped' ? 'Grouped analysis' : 'Summary');
        var limitLabel = 'All matching';
        if (resultType !== 'summary' && interpretation.limit) {
            if (interpretation.limit_explicit) {
                limitLabel = (interpretation.limit_direction === 'ASC' ? 'Bottom ' : 'Top ') + interpretation.limit;
            } else {
                limitLabel = 'Up to ' + interpretation.limit + (resultType === 'grouped' ? ' groups' : ' rows');
            }
        }
        var html = '<div class="expense-prompt-query-heading"><div><h3>' + escapeHtml(prompt && prompt.request ? prompt.request : 'Interpreted expense request') + '</h3><p>Deterministic interpretation returned from the governed expense query compiler.</p></div><span class="expense-prompt-result-badge"><i class="fa fa-check"></i> ' + escapeHtml(resultLabel) + '</span></div>';
        html += '<div class="expense-prompt-interpretation"><div class="expense-prompt-interpretation-item"><span>Date range</span><strong>' + escapeHtml((dateRange.start || '—') + ' to ' + (dateRange.end || '—')) + '</strong></div><div class="expense-prompt-interpretation-item"><span>Group by</span><strong>' + escapeHtml(interpretation.group_by || result.group_caption || 'None') + '</strong></div><div class="expense-prompt-interpretation-item"><span>Measure</span><strong>' + escapeHtml(interpretation.measure || measure.label || 'Total spend') + '</strong></div><div class="expense-prompt-interpretation-item"><span>Limit</span><strong>' + escapeHtml(limitLabel) + '</strong></div></div>';
        html += '<div class="expense-prompt-token-row">' + promptTokenMarkup(prompt) + '</div>' + promptRecognizedMarkup(prompt) + promptWarningsMarkup(prompt);
        if (resultType === 'summary') {
            html += '<div class="expense-prompt-value">' + promptFormatValue(result.value, measure.format) + '</div>' + promptSupportingMarkup(result);
        } else if (resultType === 'grouped') {
            html += promptGroupedMarkup(result);
        } else {
            html += promptDetailMarkup(result);
        }
        html += promptSqlMarkup(result);
        byId('expensePromptResult').innerHTML = html;
    }

    function renderPromptError(message) {
        byId('expensePromptResult').innerHTML = '<div class="expense-prompt-error"><i class="fa fa-exclamation-circle"></i><span>' + escapeHtml(message || 'We could not interpret this request. Please try a supported expense question.') + '</span></div>';
    }

    function setPromptLoading(loading) {
        var button = byId('expensePromptRun');
        if (!button) {
            return;
        }
        button.disabled = loading;
        button.innerHTML = loading ? '<i class="fa fa-spinner fa-spin"></i> Interpreting…' : '<i class="fa fa-play"></i> Run request';
    }

    function runPrompt() {
        var input = byId('expensePromptInput');
        var prompt = input ? input.value.trim() : '';
        if (!prompt) {
            renderPromptError('Write what you want to see, for example: “show spend by category for the last 90 days”.');
            if (input) {
                input.focus();
            }
            return;
        }
        if (state.promptController && state.promptController.abort) {
            state.promptController.abort();
        }
        state.promptRequestId += 1;
        var requestId = state.promptRequestId;
        state.promptController = window.AbortController ? new AbortController() : null;
        setPromptLoading(true);
        byId('expensePromptResult').innerHTML = '<div class="expense-prompt-loading"><i class="fa fa-spinner fa-spin"></i><span>Tokenizing the request and running the safe query plan…</span></div>';
        var baseFilters = {
            startDate: byId('expenseStart').value,
            endDate: byId('expenseEnd').value,
            category: byId('expenseCategory').value,
            tab: byId('expenseTab').value,
            owner: byId('expenseOwner').value,
            groupBy: byId('expenseGroupBy').value,
            auth: byId('expenseAuth').value,
            posting: byId('expensePosting').value,
            search: byId('expenseSearch').value.trim()
        };
        requestJson({ action: 'prompt', prompt: prompt, baseFilters: baseFilters }, state.promptController).then(function (data) {
            if (requestId !== state.promptRequestId) {
                return;
            }
            renderPromptResult(data.prompt, data);
            setPromptLoading(false);
        }).catch(function (error) {
            if (error.name === 'AbortError' || requestId !== state.promptRequestId) {
                return;
            }
            setPromptLoading(false);
            renderPromptError(error.message || error.userMessage || 'We could not interpret this request. Please retry.');
            if (window.console && console.error) {
                console.error('Expense prompt request failed', error);
            }
        });
    }

    function applyPromptGroupFilter(field, value) {
        var selectId = field === 'category' ? 'expenseCategory' : (field === 'owner' ? 'expenseOwner' : (field === 'tab' ? 'expenseTab' : ''));
        if (!selectId) {
            return;
        }
        var select = byId(selectId);
        var found = false;
        for (var index = 0; index < select.options.length; index += 1) {
            if (select.options[index].value === value) {
                found = true;
                break;
            }
        }
        if (!found) {
            renderPromptError('This result cannot be applied as a main report filter because the value is not in the current lookup list.');
            return;
        }
        select.value = value;
        state.page = 0;
        updateFilterChrome();
        setAlert('Applied ' + field + ' from the prompt result to the main report controls.', 'info');
        loadReport();
    }

    function clearPrompt() {
        if (state.promptController && state.promptController.abort) {
            state.promptController.abort();
        }
        state.promptRequestId += 1;
        byId('expensePromptInput').value = '';
        setPromptLoading(false);
        byId('expensePromptResult').innerHTML = '<div class="expense-prompt-empty"><i class="fa fa-terminal"></i><span>Enter a request to create an interactive analysis.</span></div>';
    }

    function readUrlState() {
        var result = {};
        if (!window.URLSearchParams) {
            return result;
        }
        var params = new URLSearchParams(window.location.search);
        ['from', 'to', 'category', 'tab', 'owner', 'group', 'auth', 'posting', 'search', 'metric', 'top'].forEach(function (key) {
            if (params.get(key) !== null) {
                result[key] = params.get(key);
            }
        });
        return result;
    }

    function applyUrlState() {
        var url = state.urlState || {};
        if (url.from) { byId('expenseStart').value = url.from; }
        if (url.to) { byId('expenseEnd').value = url.to; }
        if (url.group) { byId('expenseGroupBy').value = url.group; }
        if (url.auth) { byId('expenseAuth').value = url.auth; }
        if (url.posting) { byId('expensePosting').value = url.posting; }
        if (url.search) { byId('expenseSearch').value = url.search; }
        if (url.metric) {
            state.visualMetric = url.metric;
            byId('expenseVisualMetric').value = url.metric;
        }
        if (url.top) { byId('expenseTopN').value = url.top; }
    }

    function syncUrlState() {
        if (!window.history || !window.URLSearchParams) {
            return;
        }
        var url = new URL(window.location.href);
        var payload = currentPayload('summary');
        var values = {
            from: payload.startDate,
            to: payload.endDate,
            category: payload.category,
            tab: payload.tab,
            owner: payload.owner,
            group: payload.groupBy,
            auth: payload.auth === 'all' ? '' : payload.auth,
            posting: payload.posting === 'all' ? '' : payload.posting,
            search: payload.search,
            metric: state.visualMetric === 'spend' ? '' : state.visualMetric,
            top: payload.topN === '20' ? '' : payload.topN
        };
        Object.keys(values).forEach(function (key) {
            if (values[key]) {
                url.searchParams.set(key, values[key]);
            } else {
                url.searchParams.delete(key);
            }
        });
        window.history.replaceState({}, document.title, url.toString());
    }

    function currentPayload(action) {
        var payload = {
            action: action || 'summary',
            startDate: byId('expenseStart').value,
            endDate: byId('expenseEnd').value,
            category: byId('expenseCategory').value,
            tab: byId('expenseTab').value,
            owner: byId('expenseOwner').value,
            groupBy: byId('expenseGroupBy').value,
            auth: byId('expenseAuth').value,
            posting: byId('expensePosting').value,
            search: byId('expenseSearch').value.trim(),
            topN: byId('expenseTopN').value
        };
        if (action === 'details') {
            payload.pageSize = state.pageSize;
            payload.offset = state.page * state.pageSize;
            payload.sort = state.detailSort;
            payload.direction = state.detailDirection;
        }
        if (action === 'summary') {
            payload.includeLookups = state.lookupData ? '0' : '1';
        }
        if (action === 'export') {
            payload.exportLimit = 25000;
            payload.sort = state.detailSort;
            payload.direction = state.detailDirection;
        }
        return payload;
    }

    function validateDates() {
        var start = byId('expenseStart').value;
        var end = byId('expenseEnd').value;
        if (!start || !end || start > end) {
            setAlert('Choose a valid date range before applying the report controls.', 'warning');
            return false;
        }
        return true;
    }

    function setOptions(id, rows, allLabel) {
        var select = byId(id);
        var current = select.value;
        var html = '<option value="">' + escapeHtml(allLabel) + '</option>';
        (rows || []).forEach(function (row) {
            html += '<option value="' + escapeHtml(row.value) + '">' + escapeHtml(row.label) + '</option>';
        });
        select.innerHTML = html;
        if (current) {
            for (var index = 0; index < select.options.length; index += 1) {
                if (select.options[index].value === current) {
                    select.value = current;
                    break;
                }
            }
        }
    }

    function updateLookups(lookups) {
        if (!lookups) {
            return;
        }
        setOptions('expenseCategory', lookups.categories, 'All categories');
        setOptions('expenseTab', lookups.tabs, 'All tabs');
        setOptions('expenseOwner', lookups.owners, 'All owners');
        var url = state.urlState || {};
        if (url.category) { byId('expenseCategory').value = url.category; }
        if (url.tab) { byId('expenseTab').value = url.tab; }
        if (url.owner) { byId('expenseOwner').value = url.owner; }
        updateFilterChrome();
    }

    function updateFilterChrome() {
        var advanced = [
            byId('expenseCategory').value,
            byId('expenseTab').value,
            byId('expenseOwner').value,
            byId('expenseAuth').value !== 'all' ? byId('expenseAuth').value : '',
            byId('expensePosting').value !== 'all' ? byId('expensePosting').value : ''
        ].filter(function (value) { return value; });
        byId('expenseAdvancedCount').textContent = advanced.length;
        var chips = [];
        function chip(label, value, id) {
            if (value) {
                chips.push('<button type="button" class="expense-filter-chip" data-clear-filter="' + id + '">' + escapeHtml(label) + ': ' + escapeHtml(value) + ' <span aria-hidden="true">×</span></button>');
            }
        }
        chip('Category', byId('expenseCategory').value && byId('expenseCategory').selectedOptions.length ? byId('expenseCategory').selectedOptions[0].textContent : '', 'expenseCategory');
        chip('Tab', byId('expenseTab').value && byId('expenseTab').selectedOptions.length ? byId('expenseTab').selectedOptions[0].textContent : '', 'expenseTab');
        chip('Owner', byId('expenseOwner').value && byId('expenseOwner').selectedOptions.length ? byId('expenseOwner').selectedOptions[0].textContent : '', 'expenseOwner');
        chip('Approval', byId('expenseAuth').value !== 'all' ? byId('expenseAuth').selectedOptions[0].textContent : '', 'expenseAuth');
        chip('Posting', byId('expensePosting').value !== 'all' ? byId('expensePosting').selectedOptions[0].textContent : '', 'expensePosting');
        chip('Search', byId('expenseSearch').value.trim(), 'expenseSearch');
        byId('expenseActiveFilterChips').innerHTML = chips.length ? '<span class="expense-filter-chips-label">Applied:</span> ' + chips.join(' ') : '<span class="expense-filter-chips-empty">No additional filters applied</span>';
        byId('expenseActiveFilterChips').querySelectorAll('[data-clear-filter]').forEach(function (button) {
            button.addEventListener('click', function () {
                var target = byId(button.getAttribute('data-clear-filter'));
                if (target.tagName === 'SELECT') {
                    target.value = target.id === 'expenseAuth' || target.id === 'expensePosting' ? 'all' : '';
                } else {
                    target.value = '';
                }
                state.page = 0;
                loadReport();
            });
        });
    }

    function destroyChart(name) {
        var chartEntry = state.charts[name];
        if (chartEntry) {
            if (chartEntry.handler && chartEntry.canvas && chartEntry.canvas.removeEventListener) {
                chartEntry.canvas.removeEventListener('click', chartEntry.handler);
            }
            if (chartEntry.chart && chartEntry.chart.destroy) {
                chartEntry.chart.destroy();
            }
            state.charts[name] = null;
        }
    }

    function setChartEmpty(canvasId, emptyId, visible) {
        byId(canvasId).style.display = visible ? 'block' : 'none';
        byId(emptyId).style.display = visible ? 'none' : 'block';
    }

    function legacyChartOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            scaleBeginAtZero: true,
            scaleShowGridLines: true,
            scaleGridLineColor: 'rgba(0,0,0,.05)',
            scaleGridLineWidth: 1,
            scaleLabel: 'PKR <%=value%>',
            animation: true,
            animationSteps: 30,
            tooltipTemplate: '<%if (label){%><%=label%>: <%}%><%= value %>'
        };
    }

    function legacyLineDataset(label, data, color, pointColor) {
        return {
            label: label,
            fillColor: 'rgba(255,255,255,0)',
            strokeColor: color,
            pointColor: pointColor || color,
            pointStrokeColor: '#fff',
            pointHighlightFill: '#fff',
            pointHighlightStroke: color,
            data: data
        };
    }

    function legacyBarDataset(label, data, color) {
        return {
            label: label,
            fillColor: color,
            strokeColor: color,
            highlightFill: color,
            highlightStroke: color,
            data: data
        };
    }

    function makeLegacyChart(name, canvasId, method, chartData, options, onClick) {
        destroyChart(name);
        if (!window.Chart) {
            setChartEmpty(canvasId, canvasId.replace('Chart', 'Empty'), false);
            return;
        }
        // The ERP bundles Chart.js 1.0.2, whose supported API is
        // new Chart(context).Line/Bar/Doughnut(data, options).
        var canvas = byId(canvasId);
        if (!Chart.prototype || typeof Chart.prototype[method] !== 'function') {
            setChartEmpty(canvasId, canvasId.replace('Chart', 'Empty'), false);
            return;
        }
        var chart = new Chart(canvas.getContext('2d'))[method](chartData, options || legacyChartOptions());
        var chartEntry = { chart: chart, canvas: canvas, handler: null };
        if (onClick && canvas.addEventListener) {
            chartEntry.handler = function (event) {
                onClick(chart, event);
            };
            canvas.addEventListener('click', chartEntry.handler);
        }
        state.charts[name] = chartEntry;
    }

    function renderKpiComparison(id, comparison, metricKey, formatter, lowerIsBetter, points) {
        var element = byId(id);
        var metric = comparison && comparison.metrics ? comparison.metrics[metricKey] : null;
        if (!metric || metric.change === null || metric.change === undefined) {
            element.textContent = 'No prior-period baseline';
            element.className = 'expense-kpi-comparison is-neutral';
            return;
        }
        var change = Number(metric.change);
        var percent = metric.change_percent === null || metric.change_percent === undefined ? null : Number(metric.change_percent);
        var direction = change > 0 ? '↑' : change < 0 ? '↓' : '→';
        var display = points ? (Math.abs(change).toLocaleString('en-US', { maximumFractionDigits: 1 }) + ' pts') : (percent === null ? formatter(Math.abs(change)) : Math.abs(percent).toLocaleString('en-US', { maximumFractionDigits: 1 }) + '%');
        var isPositive = lowerIsBetter ? change < 0 : change > 0;
        if (change === 0) {
            isPositive = null;
        }
        element.textContent = direction + ' ' + display + ' vs previous period';
        element.className = 'expense-kpi-comparison ' + (isPositive === null ? 'is-neutral' : (isPositive ? 'is-positive' : 'is-negative'));
    }

    function renderKpis(data) {
        var summary = data.summary || {};
        var assignments = data.assignments || {};
        var transactionCount = Number(summary.transaction_count || 0);
        var pendingCount = Number((data.quality || {}).pending_authorization || 0);
        var unpostedCount = Number((data.quality || {}).unposted || 0);
        byId('kpiTotal').textContent = formatMoney(summary.total_spend);
        byId('kpiClaims').textContent = formatNumber(summary.transaction_count);
        byId('kpiAverage').textContent = formatMoney(summary.average_spend);
        byId('kpiPending').textContent = formatMoney(summary.pending_spend);
        byId('kpiUnposted').textContent = formatMoney(summary.unposted_spend);
        byId('kpiReceipts').textContent = formatPercent(summary.receipt_coverage);
        byId('kpiClaimsMeta').textContent = transactionCount === 1 ? '1 transaction' : 'Transactions';
        byId('kpiPendingMeta').textContent = formatNumber(pendingCount) + ' claim' + (pendingCount === 1 ? '' : 's') + ' pending';
        byId('kpiUnpostedMeta').textContent = formatNumber(unpostedCount) + ' claim' + (unpostedCount === 1 ? '' : 's') + ' in queue';
        byId('kpiReceiptsMeta').textContent = formatNumber(summary.receipt_count) + ' of ' + formatNumber(summary.transaction_count) + ' claims';
        renderKpiComparison('kpiTotalCompare', data.comparison, 'total_spend', formatMoney, true, false);
        renderKpiComparison('kpiClaimsCompare', data.comparison, 'transaction_count', formatNumber, true, false);
        renderKpiComparison('kpiAverageCompare', data.comparison, 'average_spend', formatMoney, true, false);
        renderKpiComparison('kpiPendingCompare', data.comparison, 'pending_spend', formatMoney, true, false);
        renderKpiComparison('kpiUnpostedCompare', data.comparison, 'unposted_spend', formatMoney, true, false);
        renderKpiComparison('kpiReceiptsCompare', data.comparison, 'receipt_coverage', formatPercent, false, true);
        byId('expenseServerDuration').textContent = data.meta && data.meta.duration_ms ? formatNumber(data.meta.duration_ms, 1) + ' ms' : '—';
        byId('expenseAssigned').textContent = formatMoney(assignments.assigned_cash);
        byId('expenseAssignedMeta').textContent = formatNumber(assignments.assignment_count) + ' cash assignment' + (Number(assignments.assignment_count || 0) === 1 ? '' : 's') + ' recorded in period';
        byId('expenseSummarySubtitle').textContent = 'Showing ' + data.filters.start + ' to ' + data.filters.end + ' · ' + formatNumber(summary.active_days) + ' active spend day' + (Number(summary.active_days || 0) === 1 ? '' : 's') + ' · ' + formatNumber(summary.tab_count) + ' tab' + (Number(summary.tab_count || 0) === 1 ? '' : 's');
        byId('expenseContext').textContent = (data.meta.company_name || 'Active company') + ' · ' + (data.meta.database_name || 'active database') + ' · read-only';
        byId('expenseAppliedPeriod').textContent = data.filters.start + ' to ' + data.filters.end;
        byId('expenseRecordCount').textContent = formatNumber(summary.transaction_count);
    }

    function renderInsight(data) {
        var summary = data.summary || {};
        var categories = data.category_breakdown || [];
        var trend = data.trend || [];
        var quality = data.quality || {};
        if (!Number(summary.transaction_count || 0)) {
            byId('expenseInsightHeadline').textContent = 'No claims matched the selected controls.';
            byId('expenseInsightBody').textContent = 'Broaden the date range or remove a category, tab, owner, status, or search filter to continue the analysis.';
            return;
        }
        var top = categories[0];
        var topShare = top && Number(summary.total_spend) ? (Number(top.spend) / Number(summary.total_spend)) * 100 : null;
        var spike = trend.reduce(function (winner, item) {
            return !winner || Number(item.spend) > Number(winner.spend) ? item : winner;
        }, null);
        var headline = top ? top.group_label + ' is the largest visible spend driver at ' + formatPercent(topShare) + '.' : 'Spend has been loaded for the selected period.';
        var followUp = [];
        if (Number(quality.pending_authorization || 0) > 0) {
            followUp.push(formatNumber(quality.pending_authorization) + ' claims await authorization');
        }
        if (Number(quality.unposted || 0) > 0) {
            followUp.push(formatNumber(quality.unposted) + ' claims are not posted to GL');
        }
        if (Number(quality.missing_receipt || 0) > 0) {
            followUp.push(formatNumber(quality.missing_receipt) + ' claims have no receipt evidence');
        }
        if (!followUp.length) {
            followUp.push('No authorization, posting, or receipt exceptions were detected in the selected slice');
        }
        byId('expenseInsightHeadline').textContent = headline;
        byId('expenseInsightBody').textContent = (spike ? 'Highest-spend month: ' + spike.period + ' at ' + formatMoney(spike.spend) + '. ' : '') + followUp.join(' · ') + '.';
    }

    function renderExceptions(data) {
        var exceptions = data.exceptions || {};
        var highCount = Number(exceptions.high_value_count || 0);
        var threshold = exceptions.high_value_threshold;
        var topClaims = exceptions.top_claims || [];
        byId('expenseExceptionHeadline').textContent = highCount
            ? formatNumber(highCount) + ' claim' + (highCount === 1 ? '' : 's') + ' exceed 3× the filtered average'
            : 'No unusually large claims detected';
        byId('expenseExceptionBody').textContent = highCount
            ? 'Threshold: ' + formatMoney(threshold) + '. This is a deterministic exception signal, not an AI-generated conclusion.'
            : (threshold ? 'Claims below the deterministic threshold of ' + formatMoney(threshold) + '.' : 'No average baseline is available for the selected slice.');
        byId('expenseExceptionList').innerHTML = topClaims.length ? topClaims.map(function (row) {
            return '<li><strong>' + escapeHtml(row.date) + '</strong> · ' + escapeHtml(row.category) + ' · ' + escapeHtml(row.owner) + ' <span>' + formatMoney(row.spend) + '</span></li>';
        }).join('') : '<li>No high-value claim exceptions in the selected data.</li>';
    }

    function renderTrend(data) {
        var rows = data.trend || [];
        var hasRows = rows.length > 0;
        setChartEmpty('expenseTrendChart', 'expenseTrendEmpty', hasRows);
        setChartEmpty('expenseStatusChart', 'expenseStatusEmpty', hasRows);
        destroyChart('trend');
        destroyChart('status');
        if (!hasRows || !window.Chart) {
            return;
        }
        var labels = rows.map(function (row) { return row.period; });
        var trendOptions = legacyChartOptions();
        trendOptions.datasetFill = false;
        trendOptions.scaleShowGridLines = false;
        makeLegacyChart('trend', 'expenseTrendChart', 'Line', {
            labels: labels,
            datasets: [
                legacyLineDataset('Spend', rows.map(function (row) { return row.spend; }), '#1d79c8'),
                legacyLineDataset('Pending spend', rows.map(function (row) { return row.pending_spend; }), '#b57b14'),
                legacyLineDataset('Unposted spend', rows.map(function (row) { return row.unposted_spend; }), '#c9525d')
            ]
        }, trendOptions);
        var statusOptions = legacyChartOptions();
        statusOptions.datasetFill = false;
        makeLegacyChart('status', 'expenseStatusChart', 'Line', {
            labels: labels,
            datasets: [
                legacyLineDataset('Pending spend', rows.map(function (row) { return row.pending_spend; }), '#b57b14'),
                legacyLineDataset('Unposted spend', rows.map(function (row) { return row.unposted_spend; }), '#c9525d')
            ]
        }, statusOptions);
    }

    function renderBreakdownTable(rows, total) {
        var body = byId('expenseBreakdownTable').querySelector('tbody');
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="12" class="expense-empty-cell">No grouped spend available for the selected controls. Try clearing a filter or widening the date range.</td></tr>';
            return;
        }
        body.innerHTML = rows.map(function (row, index) {
            var share = total ? (Number(row.spend) / total) * 100 : null;
            return '<tr><td>' + (index + 1) + '</td><td><strong>' + escapeHtml(row.group_label) + '</strong><br><span class="text-muted">' + escapeHtml(row.group_key) + '</span></td><td class="text-right">' + formatMoney(row.spend) + '</td><td class="text-right">' + formatPercent(share) + '</td><td class="text-right">' + formatNumber(row.transaction_count) + '</td><td class="text-right">' + formatMoney(row.average_spend) + '</td><td class="text-right">' + formatMoney(row.min_spend) + '</td><td class="text-right">' + formatMoney(row.max_spend) + '</td><td class="text-right">' + formatNumber(row.pending_count) + '</td><td class="text-right">' + formatMoney(row.pending_spend) + '</td><td class="text-right">' + formatMoney(row.unposted_spend) + '</td><td class="text-right">' + formatPercent(row.receipt_coverage) + '</td></tr>';
        }).join('');
    }

    function visualValue(row) {
        var metric = state.visualMetric;
        var value = row[metric];
        if (value === undefined || value === null) {
            return 0;
        }
        return Number(value);
    }

    function visualLabel() {
        var option = byId('expenseVisualMetric').selectedOptions[0];
        return option ? option.textContent : 'Spend';
    }

    function renderMiniTable(id, rows, total, mode) {
        var body = byId(id).querySelector('tbody');
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="4" class="expense-empty-cell">No data available for the selected controls.</td></tr>';
            return;
        }
        body.innerHTML = rows.slice(0, 8).map(function (row) {
            var label = row.group_label;
            if (mode === 'category' && row.enhanced_tag && row.enhanced_tag !== row.group_label) {
                label += ' · ' + row.enhanced_tag;
            }
            var last = mode === 'category' ? formatPercent(row.receipt_coverage) : formatNumber(row.pending_count);
            return '<tr><td>' + escapeHtml(label) + '</td><td class="text-right">' + formatMoney(row.spend) + '</td><td class="text-right">' + formatNumber(row.transaction_count) + '</td><td class="text-right">' + last + '</td></tr>';
        }).join('');
    }

    function renderBreakdown(data) {
        var rows = data.breakdown || [];
        var categories = data.category_breakdown || [];
        var total = Number((data.summary || {}).total_spend || 0);
        var groupCaption = (data.meta || {}).group_caption || 'Expense category';
        byId('expenseBreakdownLabel').textContent = groupCaption;
        byId('expenseBreakdownTableTitle').textContent = groupCaption + ' detail';
        renderBreakdownTable(rows, total);
        renderMiniTable('expenseCategoryTable', categories, total, 'category');
        renderMiniTable('expenseOwnerTable', data.gl_breakdown || [], total, 'gl');
        destroyChart('mix');
        var hasRows = rows.length > 0;
        setChartEmpty('expenseMixChart', 'expenseMixEmpty', hasRows);
        if (hasRows && window.Chart) {
            var mixRows = rows.slice(0, 8);
            var mixOptions = { responsive: true, maintainAspectRatio: false, percentageInnerCutout: 63, animationSteps: 30 };
            makeLegacyChart('mix', 'expenseMixChart', 'Doughnut', mixRows.map(function (row, index) {
                return {
                    value: visualValue(row),
                    color: palette[index % palette.length],
                    highlight: palette[index % palette.length],
                    label: row.group_label
                };
            }), mixOptions, function (chart, event) {
                if (byId('expenseGroupBy').value !== 'category' || !chart.getSegmentsAtEvent) {
                    return;
                }
                var segments = chart.getSegmentsAtEvent(event);
                if (segments && segments.length) {
                    var row = mixRows.filter(function (candidate) {
                        return candidate.group_label === segments[0].label;
                    })[0];
                    if (row) {
                        byId('expenseCategory').value = row.group_key;
                        state.page = 0;
                        loadReport();
                    }
                }
            });
        }
        renderOwnerChart(data.owner_breakdown || []);
    }

    function renderOwnerChart(rows) {
        destroyChart('owner');
        var hasRows = rows.length > 0;
        setChartEmpty('expenseOwnerChart', 'expenseOwnerEmpty', hasRows);
        if (!hasRows || !window.Chart) {
            return;
        }
        var chartRows = rows.slice(0, 10);
        var ownerOptions = legacyChartOptions();
        ownerOptions.scaleShowGridLines = false;
        makeLegacyChart('owner', 'expenseOwnerChart', 'Bar', {
            labels: chartRows.map(function (row) { return row.group_label; }),
            datasets: [legacyBarDataset('Spend', chartRows.map(function (row) { return row.spend; }), '#7657bf')]
        }, ownerOptions, function (chart, event) {
            if (!chart.getBarsAtEvent) {
                return;
            }
            var bars = chart.getBarsAtEvent(event);
            if (bars && bars.length) {
                var row = chartRows.filter(function (candidate) {
                    return candidate.group_label === bars[0].label;
                })[0];
                if (row) {
                    byId('expenseOwner').value = row.group_key;
                    state.page = 0;
                    loadReport();
                }
            }
        });
    }

    function qualityValue(key) {
        return Number((state.summary && state.summary.quality ? state.summary.quality[key] : 0) || 0);
    }

    function renderQuality(data) {
        var quality = data.quality || {};
        byId('qualityMissingReceipt').textContent = formatNumber(quality.missing_receipt);
        byId('qualityMissingCategory').textContent = formatNumber(quality.missing_category);
        byId('qualityMissingGl').textContent = formatNumber(quality.missing_gl);
        byId('qualityMissingTab').textContent = formatNumber(quality.missing_tab_master);
        byId('qualityPending').textContent = formatNumber(quality.pending_authorization);
        byId('qualityUnposted').textContent = formatNumber(quality.unposted);
        byId('qualitySign').textContent = formatNumber(quality.non_negative_source_amounts);
        var issueTotal = Object.keys(quality).reduce(function (sum, key) { return sum + Number(quality[key] || 0); }, 0);
        byId('expenseQualityBadge').textContent = issueTotal ? formatNumber(issueTotal) + ' review signals in selected slice' : 'No review signals in selected slice';
        byId('expenseQualityBadge').style.color = issueTotal ? '#b77b16' : '#329360';
    }

    function renderClassificationCatalog(data) {
        var categories = data.lookups && data.lookups.categories ? data.lookups.categories : [];
        var body = byId('expenseClassificationTable').querySelector('tbody');
        var meta = data.meta || {};
        var reviewCount = Number(meta.classification_review_count || 0);
        byId('expenseClassificationStatus').textContent = formatNumber(categories.length) + ' configured descriptions tagged · ' + formatNumber(reviewCount) + ' marked Other / Review';
        if (!categories.length) {
            body.innerHTML = '<tr><td colspan="4" class="expense-empty-cell">No configured expense descriptions were returned.</td></tr>';
            return;
        }
        body.innerHTML = categories.map(function (category) {
            return '<tr><td><code>' + escapeHtml(category.value) + '</code></td><td>' + escapeHtml(category.raw_description || category.label) + '</td><td><span class="expense-classification-tag">' + escapeHtml(category.enhanced_tag || 'Other / Review') + '</span></td><td><span class="text-muted">' + escapeHtml(category.classification_signal || 'no keyword match') + '</span></td></tr>';
        }).join('');
    }

    function detailStatus(row) {
        var authClass = row.authorization_status === 'Authorized' ? 'authorized' : 'pending';
        var postClass = row.posting_status === 'Posted' ? 'posted' : 'unposted';
        return '<span class="expense-status expense-status-' + authClass + '">' + escapeHtml(row.authorization_status) + '</span><br><span class="expense-status expense-status-' + postClass + '">' + escapeHtml(row.posting_status) + '</span>';
    }

    function renderDetails(rows, totalCount, hasMore) {
        state.details = rows || [];
        state.filteredDetails = state.details.slice();
        state.detailTotal = Number(totalCount || 0);
        var body = byId('expenseDetailTable').querySelector('tbody');
        var first = state.detailTotal ? state.page * state.pageSize + 1 : 0;
        var last = state.detailTotal ? Math.min((state.page + 1) * state.pageSize, state.detailTotal) : 0;
        var rangeText = state.detailTotal ? 'Showing ' + formatNumber(first) + '–' + formatNumber(last) + ' of ' + formatNumber(state.detailTotal) + ' matching claims' : 'No matching claims';
        byId('expenseDetailStatus').textContent = rangeText;
        byId('expensePageStatus').textContent = rangeText;
        byId('expensePrevPage').disabled = state.page <= 0;
        byId('expenseNextPage').disabled = !hasMore;
        if (!state.details.length) {
            body.innerHTML = '<tr><td colspan="11" class="expense-empty-cell">No transaction detail matched the selected controls. Clear a filter or expand the date range.</td></tr>';
            return;
        }
        drawDetailRows(state.details);
    }

    function drawDetailRows(rows) {
        var body = byId('expenseDetailTable').querySelector('tbody');
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="11" class="expense-empty-cell">No loaded rows match the detail search.</td></tr>';
            return;
        }
        body.innerHTML = rows.map(function (row) {
            var receipt = Number(row.has_receipt) === 1 ? '<span class="expense-status expense-receipt-yes">Attached</span>' : '<span class="expense-status expense-receipt-no">Missing</span>';
            return '<tr><td>' + escapeHtml(row.date) + '</td><td><strong>' + escapeHtml(row.category) + '</strong><br><span class="text-muted">' + escapeHtml(row.codeexpense) + '</span></td><td><span class="expense-classification-tag">' + escapeHtml(row.enhanced_tag || 'Other / Review') + '</span></td><td>' + escapeHtml(row.tabcode) + '</td><td>' + escapeHtml(row.owner) + '</td><td><strong>' + escapeHtml(row.glaccount) + '</strong><br><span class="text-muted">' + escapeHtml(row.glaccount_name) + '</span></td><td>' + escapeHtml(row.tab_type) + '</td><td>' + detailStatus(row) + '</td><td class="text-right">' + formatMoney(row.spend) + '</td><td>' + receipt + '</td><td class="notes-cell" title="' + escapeHtml(row.notes) + '">' + escapeHtml(row.notes || '—') + '</td></tr>';
        }).join('');
    }

    function loadDetails(controller) {
        return requestJson(currentPayload('details'), controller);
    }

    function setLoading(loading) {
        byId('expenseReportRoot').setAttribute('aria-busy', loading ? 'true' : 'false');
        byId('expenseReportStatus').textContent = loading ? 'Refreshing' : 'Ready';
    }

    function loadReport() {
        if (!validateDates()) {
            return;
        }
        state.page = 0;
        state.requestId += 1;
        var requestId = state.requestId;
        if (state.requestController && state.requestController.abort) {
            state.requestController.abort();
        }
        state.requestController = window.AbortController ? new AbortController() : null;
        setLoading(true);
        setAlert('Refreshing expense intelligence from the active ERP company…', 'info');
        updateFilterChrome();
        Promise.all([requestJson(currentPayload('summary'), state.requestController), loadDetails(state.requestController)]).then(function (responses) {
            if (requestId !== state.requestId) {
                return;
            }
            var data = responses[0];
            var details = responses[1];
            if (data.lookups) {
                state.lookupData = data.lookups;
            }
            data.lookups = state.lookupData || data.lookups;
            state.summary = data;
            updateLookups(data.lookups);
            syncUrlState();
            renderDetails(details.rows || [], details.total_count, details.has_more);
            renderKpis(data);
            renderInsight(data);
            renderExceptions(data);
            renderTrend(data);
            renderBreakdown(data);
            renderQuality(data);
            renderClassificationCatalog(data);
            byId('expenseLastRefreshed').textContent = new Date().toLocaleString();
            setLoading(false);
            setAlert('', 'info');
        }).catch(function (error) {
            if (error.name === 'AbortError' || requestId !== state.requestId) {
                return;
            }
            setLoading(false);
            setAlert(error.userMessage || 'We could not load this expense report. Please retry.', 'danger');
            if (window.console && console.error) {
                console.error('Expense report request failed', error);
            }
        });
    }

    function refreshDetailsOnly() {
        state.requestId += 1;
        var requestId = state.requestId;
        if (state.requestController && state.requestController.abort) {
            state.requestController.abort();
        }
        state.requestController = window.AbortController ? new AbortController() : null;
        byId('expensePageStatus').textContent = 'Loading detail…';
        loadDetails(state.requestController).then(function (data) {
            if (requestId !== state.requestId) {
                return;
            }
            renderDetails(data.rows || [], data.total_count, data.has_more);
        }).catch(function (error) {
            if (error.name !== 'AbortError' && requestId === state.requestId) {
                setAlert('We could not refresh transaction detail. Please retry.', 'danger');
            }
        });
    }

    function setPeriod(period) {
        var today = new Date();
        var start;
        var end = new Date(today.getTime());
        if (period === 'month') {
            start = new Date(today.getFullYear(), today.getMonth(), 1);
        } else if (period === 'quarter') {
            start = new Date(today.getTime());
            start.setDate(start.getDate() - 89);
        } else {
            start = new Date(today.getFullYear(), 0, 1);
        }
        setDateRange(start, end);
        loadReport();
    }

    function setDatePreset(preset) {
        var today = new Date();
        var start;
        var end = new Date(today.getTime());
        var weekStart = startOfWeek(today);
        if (preset === 'today') {
            start = new Date(today.getTime());
        } else if (preset === 'yesterday') {
            start = new Date(today.getTime());
            start.setDate(start.getDate() - 1);
            end = new Date(start.getTime());
        } else if (preset === 'this_week') {
            start = weekStart;
        } else if (preset === 'last_week') {
            end = new Date(weekStart.getTime());
            end.setDate(end.getDate() - 1);
            start = new Date(end.getTime());
            start.setDate(start.getDate() - 6);
        } else if (preset === 'this_month') {
            start = new Date(today.getFullYear(), today.getMonth(), 1);
        } else if (preset === 'last_month') {
            start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            end = new Date(today.getFullYear(), today.getMonth(), 0);
        } else if (preset === 'this_quarter') {
            start = startOfQuarter(today);
        } else if (preset === 'last_quarter') {
            var currentQuarter = startOfQuarter(today);
            end = new Date(currentQuarter.getFullYear(), currentQuarter.getMonth(), 0);
            start = new Date(end.getFullYear(), end.getMonth() - 2, 1);
        } else if (preset === 'this_year' || preset === 'ytd') {
            start = new Date(today.getFullYear(), 0, 1);
        } else if (preset === 'last_year') {
            start = new Date(today.getFullYear() - 1, 0, 1);
            end = new Date(today.getFullYear() - 1, 11, 31);
        } else if (preset === 'last_7' || preset === 'last_30' || preset === 'last_90') {
            var days = preset === 'last_7' ? 6 : preset === 'last_30' ? 29 : 89;
            start = new Date(today.getTime());
            start.setDate(start.getDate() - days);
        } else if (preset === 'all') {
            start = new Date(1970, 0, 1);
        } else {
            return;
        }
        setDateRange(start, end);
        loadReport();
    }

    function resetFilters() {
        byId('expenseStart').value = window.SAHAMID_EXPENSE_BI.defaultStart;
        byId('expenseEnd').value = window.SAHAMID_EXPENSE_BI.defaultEnd;
        byId('expenseCategory').value = '';
        byId('expenseTab').value = '';
        byId('expenseOwner').value = '';
        byId('expenseGroupBy').value = 'category';
        byId('expenseAuth').value = 'all';
        byId('expensePosting').value = 'all';
        byId('expenseSearch').value = '';
        byId('expenseVisualMetric').value = 'spend';
        byId('expenseTopN').value = '20';
        state.visualMetric = 'spend';
        state.urlState = {};
        state.page = 0;
        clearPrompt();
        loadReport();
    }

    function savedViewPayload() {
        return {
            startDate: byId('expenseStart').value,
            endDate: byId('expenseEnd').value,
            category: byId('expenseCategory').value,
            tab: byId('expenseTab').value,
            owner: byId('expenseOwner').value,
            groupBy: byId('expenseGroupBy').value,
            auth: byId('expenseAuth').value,
            posting: byId('expensePosting').value,
            search: byId('expenseSearch').value,
            metric: state.visualMetric,
            topN: byId('expenseTopN').value
        };
    }

    function renderSavedViews() {
        var select = byId('expenseSavedView');
        select.innerHTML = '<option value="">Saved private views</option>';
        state.savedViews.forEach(function (view, index) {
            select.innerHTML += '<option value="' + index + '">' + escapeHtml(view.name) + '</option>';
        });
    }

    function loadSavedViews() {
        try {
            var stored = window.localStorage.getItem(savedViewKey);
            state.savedViews = stored ? JSON.parse(stored) : [];
            if (!Array.isArray(state.savedViews)) {
                state.savedViews = [];
            }
        } catch (error) {
            state.savedViews = [];
        }
        renderSavedViews();
    }

    function saveView() {
        var name = window.prompt('Name this private expense report view');
        if (!name) {
            return;
        }
        name = name.trim();
        if (!name) {
            return;
        }
        state.savedViews = state.savedViews.filter(function (view) { return view.name !== name; });
        state.savedViews.unshift({ name: name, payload: savedViewPayload() });
        state.savedViews = state.savedViews.slice(0, 20);
        try {
            window.localStorage.setItem(savedViewKey, JSON.stringify(state.savedViews));
        } catch (error) {
            setAlert('This browser could not save the private view.', 'warning');
        }
        renderSavedViews();
        byId('expenseSavedView').value = '0';
    }

    function applySavedView(index) {
        var view = state.savedViews[Number(index)];
        if (!view || !view.payload) {
            return;
        }
        var payload = view.payload;
        byId('expenseStart').value = payload.startDate || window.SAHAMID_EXPENSE_BI.defaultStart;
        byId('expenseEnd').value = payload.endDate || window.SAHAMID_EXPENSE_BI.defaultEnd;
        byId('expenseCategory').value = payload.category || '';
        byId('expenseTab').value = payload.tab || '';
        byId('expenseOwner').value = payload.owner || '';
        byId('expenseGroupBy').value = payload.groupBy || 'category';
        byId('expenseAuth').value = payload.auth || 'all';
        byId('expensePosting').value = payload.posting || 'all';
        byId('expenseSearch').value = payload.search || '';
        state.visualMetric = payload.metric || 'spend';
        byId('expenseVisualMetric').value = state.visualMetric;
        byId('expenseTopN').value = payload.topN || '20';
        state.page = 0;
        loadReport();
    }

    function exportDetails() {
        setAlert('Preparing the filtered XLSX export…', 'info');
        fetch(window.SAHAMID_EXPENSE_BI.exportUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(currentPayload('export'))
        }).then(function (response) {
            var type = response.headers.get('content-type') || '';
            if (!response.ok || type.indexOf('json') !== -1) {
                return response.json().then(function (body) {
                    var error = body.error || {};
                    throw new Error(error.message || 'The XLSX export could not be prepared.');
                });
            }
            return response.blob();
        }).then(function (blob) {
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'expense-intelligence-' + byId('expenseStart').value + '-to-' + byId('expenseEnd').value + '.xlsx';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            setTimeout(function () { URL.revokeObjectURL(link.href); }, 1000);
            setAlert('', 'info');
        }).catch(function (error) {
            setAlert('The XLSX export could not be prepared. Please retry.', 'danger');
            if (window.console && console.error) {
                console.error('Expense export failed', error);
            }
        });
    }

    function wireEvents() {
        byId('expensePromptForm').addEventListener('submit', function (event) {
            event.preventDefault();
            runPrompt();
        });
        byId('expensePromptClear').addEventListener('click', clearPrompt);
        document.querySelectorAll('[data-prompt-example]').forEach(function (button) {
            button.addEventListener('click', function () {
                byId('expensePromptInput').value = this.getAttribute('data-prompt-example') || '';
                runPrompt();
            });
        });
        byId('expensePromptResult').addEventListener('click', function (event) {
            var target = event.target;
            while (target && target !== this && !target.getAttribute('data-prompt-filter-field')) {
                target = target.parentNode;
            }
            if (target && target !== this) {
                applyPromptGroupFilter(target.getAttribute('data-prompt-filter-field'), target.getAttribute('data-prompt-filter-value'));
            }
        });
        byId('expenseApply').addEventListener('click', loadReport);
        byId('expenseRefresh').addEventListener('click', loadReport);
        byId('expenseExport').addEventListener('click', exportDetails);
        byId('expenseReset').addEventListener('click', resetFilters);
        byId('expenseGroupBy').addEventListener('change', loadReport);
        byId('expenseVisualMetric').addEventListener('change', function () {
            state.visualMetric = this.value || 'spend';
            syncUrlState();
            if (state.summary) {
                renderBreakdown(state.summary);
            }
        });
        byId('expenseTopN').addEventListener('change', loadReport);
        byId('expenseDatePreset').addEventListener('change', function () {
            setDatePreset(this.value);
        });
        byId('expenseAdvancedToggle').addEventListener('click', function () {
            var expanded = document.body.classList.toggle('expense-show-advanced');
            this.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            this.innerHTML = (expanded ? 'Hide filters' : 'More filters') + ' <span id="expenseAdvancedCount" class="expense-filter-count">0</span>';
            updateFilterChrome();
        });
        byId('expenseSaveView').addEventListener('click', saveView);
        byId('expenseSavedView').addEventListener('change', function () {
            if (this.value !== '') {
                applySavedView(this.value);
            }
        });
        byId('expensePageSize').addEventListener('change', function () {
            state.pageSize = Math.max(1, Number(this.value) || 50);
            state.page = 0;
            refreshDetailsOnly();
        });
        byId('expensePrevPage').addEventListener('click', function () {
            if (state.page > 0) {
                state.page -= 1;
                refreshDetailsOnly();
            }
        });
        byId('expenseNextPage').addEventListener('click', function () {
            if ((state.page + 1) * state.pageSize < state.detailTotal) {
                state.page += 1;
                refreshDetailsOnly();
            }
        });
        document.querySelectorAll('.expense-detail-table th[data-sort]').forEach(function (header) {
            header.addEventListener('click', function () {
                var sort = this.getAttribute('data-sort');
                if (state.detailSort === sort) {
                    state.detailDirection = state.detailDirection === 'ASC' ? 'DESC' : 'ASC';
                } else {
                    state.detailSort = sort;
                    state.detailDirection = sort === 'date' ? 'DESC' : 'ASC';
                }
                state.page = 0;
                refreshDetailsOnly();
            });
        });
        var detailSearchTimer;
        byId('expenseDetailSearch').addEventListener('keyup', function () {
            var query = this.value.toLowerCase().trim();
            state.filteredDetails = state.details.filter(function (row) {
                return [row.date, row.category, row.codeexpense, row.enhanced_tag, row.tabcode, row.tab_type, row.owner, row.glaccount, row.glaccount_name, row.authorization_status, row.posting_status, row.notes].join(' ').toLowerCase().indexOf(query) !== -1;
            });
            clearTimeout(detailSearchTimer);
            detailSearchTimer = setTimeout(function () { drawDetailRows(state.filteredDetails); }, 120);
        });
        byId('expenseSearch').addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                loadReport();
            }
        });
        document.querySelectorAll('[data-period]').forEach(function (button) {
            button.addEventListener('click', function () { setPeriod(this.getAttribute('data-period')); });
        });
        document.querySelectorAll('.expense-section-nav a').forEach(function (link) {
            link.addEventListener('click', function () {
                document.querySelectorAll('.expense-section-nav a').forEach(function (item) { item.classList.remove('is-active'); });
                link.classList.add('is-active');
            });
        });
        window.addEventListener('popstate', function () {
            state.urlState = readUrlState();
            applyUrlState();
            state.page = 0;
            loadReport();
        });
    }

    state.urlState = readUrlState();
    applyUrlState();
    loadSavedViews();
    wireEvents();
    loadReport();
}());
