(function () {
    'use strict';

    var reports = [];
    var root = window.SAHAMID_BI_REPORT_LIBRARY || {};

    function byId(id) { return document.getElementById(id); }

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
        var alert = byId('biLibraryAlert');
        if (!message) {
            alert.style.display = 'none';
            alert.textContent = '';
            return;
        }
        alert.className = 'alert bi-library-alert alert-' + (type || 'warning');
        alert.textContent = message;
        alert.style.display = 'block';
    }

    function requestJson(url) {
        return fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(function (response) {
            return response.json().then(function (body) {
                if (!response.ok || !body.ok) {
                    throw new Error((body.error || {}).message || 'The report catalog could not be loaded.');
                }
                return body.data;
            });
        });
    }

    function statusLabel(status) {
        return status === 'enhanced' ? 'BI-native' : 'Live source';
    }

    function reportUrl(report) {
        if (report.bi_route) {
            return root.rootUrl + report.bi_route;
        }
        return root.workspaceUrl + '?report=' + encodeURIComponent(report.id);
    }

    function legacyUrl(report) {
        return report.legacy_route ? root.rootUrl + report.legacy_route : '';
    }

    function renderCard(report) {
        var status = report.status || 'compatibility';
        var notes = report.notes || [];
        var filters = report.filters || [];
        var original = legacyUrl(report);
        var primaryLabel = report.bi_route ? 'Open enhanced workspace' : 'Open live report';
        var primaryIcon = report.bi_route ? 'line-chart' : 'dashboard';
        var note = notes.length ? notes[0] : 'Runs live in BI using the report’s existing production calculation and filters.';
        return '<div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">'
            + '<article class="bi-report-card bi-report-status-' + escapeHtml(status) + '">'
            + '<div class="bi-report-card-top"><span class="bi-report-category">' + escapeHtml(report.category) + '</span><span class="bi-report-status">' + escapeHtml(statusLabel(status)) + '</span></div>'
            + '<h3>' + escapeHtml(report.title) + '</h3>'
            + '<p class="bi-report-description">' + escapeHtml(report.description) + '</p>'
            + '<dl class="bi-report-meta"><div><dt>Source</dt><dd>' + escapeHtml(report.legacy_route || 'BI-native source') + '</dd></div><div><dt>Controls</dt><dd>' + escapeHtml(filters.slice(0, 3).join(' · ')) + '</dd></div></dl>'
            + '<p class="bi-report-note"><i class="fa fa-info-circle"></i> ' + escapeHtml(note) + '</p>'
            + '<div class="bi-report-actions"><a class="btn btn-primary btn-sm" href="' + escapeHtml(reportUrl(report)) + '"><i class="fa fa-' + primaryIcon + '"></i> ' + primaryLabel + '</a>'
            + (original ? '<a class="btn btn-default btn-sm" href="' + escapeHtml(original) + '" target="_blank" rel="noopener"><i class="fa fa-external-link"></i> Original</a>' : '')
            + '</div></article></div>';
    }

    function renderFilters() {
        var select = byId('biLibraryCategory');
        var values = {};
        reports.forEach(function (report) { values[report.category] = true; });
        select.innerHTML = '<option value="">All modules</option>';
        Object.keys(values).sort().forEach(function (value) {
            select.innerHTML += '<option value="' + escapeHtml(value) + '">' + escapeHtml(value) + '</option>';
        });
    }

    function filteredReports() {
        var query = byId('biLibrarySearch').value.toLowerCase().trim();
        var category = byId('biLibraryCategory').value;
        var status = byId('biLibraryStatus').value;
        return reports.filter(function (report) {
            var haystack = [report.title, report.description, report.category, report.legacy_route, (report.filters || []).join(' ')].join(' ').toLowerCase();
            return (!query || haystack.indexOf(query) !== -1)
                && (!category || report.category === category)
                && (!status || report.status === status);
        });
    }

    function render() {
        var visible = filteredReports();
        byId('biLibraryResultCount').textContent = visible.length + ' of ' + reports.length + ' reports';
        byId('biLibraryGrid').innerHTML = visible.length
            ? visible.map(renderCard).join('')
            : '<div class="col-md-12 bi-library-empty"><i class="fa fa-search"></i><strong>No reports match these filters.</strong><span>Clear the search or choose another module.</span></div>';
    }

    function renderSummary(data) {
        var counts = data.counts || {};
        var context = data.context || {};
        byId('biLibraryTotal').textContent = counts.total == null ? '—' : counts.total;
        byId('biLibraryEnhanced').textContent = counts.enhanced == null ? '—' : counts.enhanced;
        byId('biLibraryCompatibility').textContent = counts.compatibility == null ? '—' : counts.compatibility;
        byId('biLibraryCompany').textContent = context.company_name || 'Active company';
        byId('biLibraryGenerated').textContent = 'Catalog refreshed ' + new Date().toLocaleTimeString();
    }

    function loadCatalog() {
        byId('biLibraryGrid').innerHTML = '<div class="col-md-12 bi-library-loading"><i class="fa fa-spinner fa-spin"></i> Loading the report catalog…</div>';
        requestJson(root.catalogUrl).then(function (data) {
            reports = data.reports || [];
            renderFilters();
            renderSummary(data);
            render();
            setAlert('', 'warning');
        }).catch(function (error) {
            setAlert(error.message, 'danger');
            byId('biLibraryGrid').innerHTML = '<div class="col-md-12 bi-library-empty"><i class="fa fa-warning"></i><strong>The catalog could not be loaded.</strong><span>Retry after confirming your BI permission.</span></div>';
        });
    }

    byId('biLibrarySearch').addEventListener('input', render);
    byId('biLibraryCategory').addEventListener('change', render);
    byId('biLibraryStatus').addEventListener('change', render);
    byId('biLibraryRefresh').addEventListener('click', loadCatalog);
    loadCatalog();
}());
