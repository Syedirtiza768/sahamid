(function () {
    var catalog = [];

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

    function setAlert(message, type) {
        var alert = document.getElementById('biAlert');
        if (!message) {
            alert.style.display = 'none';
            alert.textContent = '';
            return;
        }
        alert.className = 'alert bi-alert alert-' + (type || 'warning');
        alert.textContent = message;
        alert.style.display = 'block';
    }

    function requestJson(url, options) {
        return fetch(url, options || {}).then(function (response) {
            return response.json().then(function (body) {
                if (!response.ok || !body.ok) {
                    var error = body.error || {};
                    throw new Error(error.message || 'The BI request could not be completed.');
                }
                return body.data;
            });
        });
    }

    function statusLabel(status) {
        return String(status || 'unknown').replace(/_/g, ' ');
    }

    function renderMetric(metric) {
        var caveats = metric.caveats || [];
        var caveatHtml = caveats.length ? '<ul class="bi-caveats">' + caveats.slice(0, 2).map(function (item) {
            return '<li>' + escapeHtml(item) + '</li>';
        }).join('') + '</ul>' : '<div class="bi-caveats">No additional caveats recorded.</div>';
        var validationAction = metric.id === 'sales.invoice_value'
            ? ' <button type="button" class="btn btn-warning btn-xs bi-run-reconciliation" data-metric-id="' + escapeHtml(metric.id) + '"><i class="fa fa-check-square-o"></i> Reconcile</button>'
            : '';
        var action = metric.executable
            ? '<button type="button" class="btn btn-primary btn-xs bi-run-metric" data-metric-id="' + escapeHtml(metric.id) + '"><i class="fa fa-play"></i> Run metric</button>'
                + (metric.id === 'sales.invoice_value' ? ' <button type="button" class="btn btn-default btn-xs bi-run-evidence" data-metric-id="' + escapeHtml(metric.id) + '"><i class="fa fa-search"></i> Evidence</button>' : '')
                + validationAction
            : '<button type="button" class="btn btn-default btn-xs" disabled title="This metric must be validated before it can return a numeric result."><i class="fa fa-lock"></i> Not published</button>';
        action += metric.executable ? '' : validationAction;

        return '<div class="col-lg-3 col-md-4 col-sm-6">'
            + '<article class="bi-metric-card">'
            + '<div class="bi-card-head"><h4>' + escapeHtml(metric.name) + '</h4>'
            + '<span class="bi-status bi-status-' + escapeHtml(metric.status) + '">' + escapeHtml(statusLabel(metric.status)) + '</span></div>'
            + '<div class="bi-card-body">'
            + '<div class="bi-description">' + escapeHtml(metric.description) + '</div>'
            + '<div class="bi-definition">' + escapeHtml(metric.formula) + '</div>'
            + caveatHtml
            + '<div>' + action + ' <button type="button" class="btn btn-link btn-xs bi-show-lineage" data-metric-id="' + escapeHtml(metric.id) + '">Lineage</button></div>'
            + '</div></article></div>';
    }

    function renderCatalog(data) {
        catalog = data.metrics || [];
        var grid = document.getElementById('biMetricGrid');
        document.getElementById('biContext').textContent = 'Company: ' + (data.context.company_name || 'Unknown')
            + ' · Database: ' + (data.context.database_name || 'Unknown')
            + (data.context.salesperson_scope ? ' · Scope: ' + data.context.salesperson_scope : ' · Scope: overall authorized view');
        document.getElementById('biPublishedCount').textContent = catalog.filter(function (metric) { return metric.executable; }).length;
        document.getElementById('biPendingCount').textContent = catalog.filter(function (metric) { return !metric.executable; }).length;
        document.getElementById('biCatalogUpdated').textContent = 'Generated ' + data.generated_at_utc + ' UTC';
        grid.innerHTML = catalog.length ? catalog.map(renderMetric).join('') : '<div class="col-md-12 bi-empty">No metric definitions are available.</div>';
        setAlert('', 'warning');
    }

    function showResult(data) {
        var box = document.getElementById('biResultBox');
        var body = document.getElementById('biResultBody');
        var row = data.rows && data.rows[0] ? data.rows[0] : null;
        var warnings = data.warnings || [];
        if (!row) {
            body.innerHTML = '<div class="bi-result-warning">The query returned no result row.</div>';
        } else {
            body.innerHTML = '<div class="bi-result-value">Rs ' + Number(row.value || 0).toLocaleString('en-US', { maximumFractionDigits: 2 }) + '</div>'
                + '<div class="bi-result-meta">Invoices: ' + Number(row.invoice_count || 0).toLocaleString('en-US')
                + ' · Detail/option rows: ' + Number(row.detail_option_rows || 0).toLocaleString('en-US')
                + ' · Source mode: ' + escapeHtml(data.metadata.freshness.mode || 'unknown') + '</div>'
                + (warnings.length ? '<div class="bi-result-warning" style="margin-top:12px;">' + warnings.map(escapeHtml).join('<br>') + '</div>' : '');
        }
        box.style.display = 'block';
        box.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function showEvidence(data) {
        var box = document.getElementById('biResultBox');
        var body = document.getElementById('biResultBody');
        var rows = data.rows || [];
        var evidence = data.metadata.drill_through || {};
        if (!rows.length) {
            body.innerHTML = '<div class="bi-result-warning">No supporting invoice rows were found for the selected range.</div>';
        } else {
            var html = '<div class="bi-result-meta">Showing up to ' + Number(evidence.limit || rows.length).toLocaleString('en-US')
                + ' source rows · Grain: ' + escapeHtml(evidence.grain || 'invoice evidence') + '</div>'
                + '<div class="table-responsive"><table class="table table-condensed table-striped"><thead><tr>'
                + '<th>Invoice</th><th>Date</th><th>Item</th><th class="text-right">Qty</th><th class="text-right">Option qty</th><th class="text-right">Value</th>'
                + '</tr></thead><tbody>';
            rows.forEach(function (row) {
                html += '<tr><td>' + escapeHtml(row.invoiceno) + '</td><td>' + escapeHtml(row.invoicesdate) + '</td><td>'
                    + escapeHtml(row.stkcode) + '</td><td class="text-right">' + Number(row.quantity || 0).toLocaleString('en-US')
                    + '</td><td class="text-right">' + Number(row.option_quantity || 0).toLocaleString('en-US')
                    + '</td><td class="text-right">Rs ' + Number(row.line_value || 0).toLocaleString('en-US', { maximumFractionDigits: 2 }) + '</td></tr>';
            });
            body.innerHTML = html + '</tbody></table></div>';
        }
        box.style.display = 'block';
        box.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function formatAmount(value) {
        return 'Rs ' + Number(value || 0).toLocaleString('en-US', { maximumFractionDigits: 2 });
    }

    function formatNumber(value) {
        return Number(value || 0).toLocaleString('en-US');
    }

    function showReconciliation(data) {
        var box = document.getElementById('biResultBox');
        var body = document.getElementById('biResultBody');
        var reconciliation = data.metadata.reconciliation || {};
        var summary = reconciliation.summary || {};
        var rows = data.rows || [];
        var checks = reconciliation.checks || [];
        var status = reconciliation.status || 'unknown';
        var statusClass = status === 'formula_explained_pending_approval' ? 'pass' : (status === 'no_population' ? 'review' : 'exception');
        var html = '<div class="bi-reconciliation-head"><span class="bi-reconciliation-status bi-reconciliation-' + escapeHtml(statusClass) + '">' + escapeHtml(status.replace(/_/g, ' ')) + '</span>'
            + '<span class="bi-result-meta">Metric status: ' + escapeHtml(data.metadata.validation_status || 'unknown') + ' · Source: live ERP database</span></div>'
            + '<div class="row bi-reconciliation-summary">'
            + '<div class="col-md-3"><div class="bi-recon-stat"><span>Invoices</span><strong>' + formatNumber(summary.invoice_count) + '</strong></div></div>'
            + '<div class="col-md-3"><div class="bi-recon-stat"><span>Raw detail formula</span><strong>' + formatAmount(summary.detail_formula_value) + '</strong></div></div>'
            + '<div class="col-md-3"><div class="bi-recon-stat"><span>Linked AR</span><strong>' + formatAmount(summary.ar_value) + '</strong></div></div>'
            + '<div class="col-md-3"><div class="bi-recon-stat"><span>Observed variance</span><strong>' + formatAmount(summary.observed_variance) + '</strong></div></div>'
            + '</div>'
            + '<p class="bi-reconciliation-note">Expected AR uses the observed category relationships (1.18 for exclusive goods and 1.16 for exclusive services) only as review evidence. It is not an approved tax policy and cannot publish the metric.</p>'
            + '<h4>Tax-basis evidence</h4><div class="table-responsive"><table class="table table-condensed table-striped"><thead><tr><th>Category</th><th class="text-right">Invoices</th><th class="text-right">Detail formula</th><th class="text-right">AR</th><th class="text-right">Expected AR</th><th class="text-right">Model residual</th></tr></thead><tbody>';
        if (!rows.length) {
            html += '<tr><td colspan="6">No invoices matched the selected range and scope.</td></tr>';
        } else {
            rows.forEach(function (row) {
                html += '<tr><td>' + escapeHtml(row.label || row.tax_bucket) + '</td><td class="text-right">' + formatNumber(row.invoice_count)
                    + '</td><td class="text-right">' + formatAmount(row.detail_formula_value) + '</td><td class="text-right">' + formatAmount(row.ar_value)
                    + '</td><td class="text-right">' + (row.expected_ar_value == null ? '—' : formatAmount(row.expected_ar_value))
                    + '</td><td class="text-right">' + (row.model_variance == null ? '—' : formatAmount(row.model_variance)) + '</td></tr>';
            });
        }
        html += '</tbody></table></div><h4>Certification checks</h4><ul class="bi-reconciliation-checks">';
        checks.forEach(function (check) {
            html += '<li><span class="bi-check-' + escapeHtml(check.status || 'review') + '">' + escapeHtml((check.status || 'review').toUpperCase()) + '</span> <strong>' + escapeHtml(check.label) + '</strong> — ' + escapeHtml(check.detail) + '</li>';
        });
        html += '</ul><div class="bi-result-warning">Approval required: Finance/Sales must select the governed net or gross definition and approve the date/tax policy. This read-only reconciliation does not change metric trust status.</div>';
        body.innerHTML = html;
        box.style.display = 'block';
        box.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function runMetric(metricId) {
        var start = document.getElementById('biStartDate').value;
        var end = document.getElementById('biEndDate').value;
        if (!start || !end || start > end) {
            setAlert('Choose a valid date range before running a metric.', 'warning');
            return;
        }
        setAlert('Running ' + metricId + ' against the active ERP company…', 'info');
        fetch(window.SAHAMID_BI.queryUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                metricIds: [metricId],
                dateRange: { start: start, end: end },
                limit: 100
            })
        }).then(function (response) {
            return response.json().then(function (body) {
                if (!response.ok || !body.ok) {
                    var error = body.error || {};
                    throw new Error(error.message || 'The metric query failed.');
                }
                return body.data;
            });
        }).then(function (data) {
            setAlert('', 'warning');
            showResult(data);
        }).catch(function (error) {
            setAlert(error.message, 'warning');
        });
    }

    function runEvidence(metricId) {
        var start = document.getElementById('biStartDate').value;
        var end = document.getElementById('biEndDate').value;
        var body = {
            metricIds: [metricId],
            dateRange: { start: start, end: end },
            limit: 100
        };
        var invoiceNo = document.getElementById('biInvoiceNo').value.trim();
        if (invoiceNo) {
            body.invoiceNo = invoiceNo;
        }
        if (!start || !end || start > end) {
            setAlert('Choose a valid date range before loading evidence.', 'warning');
            return;
        }
        setAlert('Loading supporting invoice rows from the active ERP company…', 'info');
        fetch(window.SAHAMID_BI.evidenceUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        }).then(function (response) {
            return response.json().then(function (payload) {
                if (!response.ok || !payload.ok) {
                    var error = payload.error || {};
                    throw new Error(error.message || 'The evidence query failed.');
                }
                return payload.data;
            });
        }).then(function (data) {
            setAlert('', 'warning');
            showEvidence(data);
        }).catch(function (error) {
            setAlert(error.message, 'warning');
        });
    }

    function runReconciliation(metricId) {
        var start = document.getElementById('biStartDate').value;
        var end = document.getElementById('biEndDate').value;
        if (!start || !end || start > end) {
            setAlert('Choose a valid date range before running reconciliation.', 'warning');
            return;
        }
        setAlert('Computing read-only invoice reconciliation from the active ERP company…', 'info');
        fetch(window.SAHAMID_BI.reconciliationUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                metricIds: [metricId],
                dateRange: { start: start, end: end },
                limit: 100
            })
        }).then(function (response) {
            return response.json().then(function (body) {
                if (!response.ok || !body.ok) {
                    var error = body.error || {};
                    throw new Error(error.message || 'The reconciliation query failed.');
                }
                return body.data;
            });
        }).then(function (data) {
            setAlert('', 'warning');
            showReconciliation(data);
        }).catch(function (error) {
            setAlert(error.message, 'warning');
        });
    }

    function loadCatalog() {
        document.getElementById('biMetricGrid').innerHTML = '<div class="col-md-12 bi-loading"><i class="fa fa-spinner fa-spin"></i> Loading governed metric definitions…</div>';
        requestJson(window.SAHAMID_BI.catalogUrl, { credentials: 'same-origin' }).then(renderCatalog).catch(function (error) {
            setAlert(error.message, 'danger');
            document.getElementById('biMetricGrid').innerHTML = '<div class="col-md-12 bi-empty">The metric catalog could not be loaded.</div>';
        });
    }

    document.addEventListener('click', function (event) {
        var runButton = event.target.closest ? event.target.closest('.bi-run-metric') : null;
        if (runButton) {
            runMetric(runButton.getAttribute('data-metric-id'));
            return;
        }
        var evidenceButton = event.target.closest ? event.target.closest('.bi-run-evidence') : null;
        if (evidenceButton) {
            runEvidence(evidenceButton.getAttribute('data-metric-id'));
            return;
        }
        var reconciliationButton = event.target.closest ? event.target.closest('.bi-run-reconciliation') : null;
        if (reconciliationButton) {
            runReconciliation(reconciliationButton.getAttribute('data-metric-id'));
            return;
        }
        var lineageButton = event.target.closest ? event.target.closest('.bi-show-lineage') : null;
        if (lineageButton) {
            var metric = catalog.find(function (item) { return item.id === lineageButton.getAttribute('data-metric-id'); });
            if (metric) {
                var lineage = (metric.lineage || []).join(', ') || 'No lineage recorded.';
                setAlert(metric.name + ' lineage: ' + lineage + ' · Grain: ' + metric.grain + ' · Date role: ' + (metric.date_role || 'not defined'), 'info');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }
    });

    document.getElementById('biRefreshCatalog').addEventListener('click', loadCatalog);
    loadCatalog();
}());
