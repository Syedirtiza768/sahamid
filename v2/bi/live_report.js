(function (window, document) {
    'use strict';

    var config = window.SAHAMID_BI_LIVE_REPORT || {};
    var root = document.getElementById('biLiveRoot');
    var frame = document.getElementById('biLiveFrame');
    if (!root || !frame) {
        return;
    }

    var hiddenColumns = {};
    var primaryTable = null;
    var currentSourceUrl = config.sourceUrl || '';
    var frameObserver = null;
    var decorateTimer = null;

    function byId(id) { return document.getElementById(id); }

    function escapeHtml(value) {
        return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, function (character) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[character];
        });
    }

    function showAlert(message, type) {
        var alert = byId('biLiveAlert');
        alert.className = 'alert bi-live-alert alert-' + (type || 'info');
        alert.innerHTML = message;
        alert.style.display = 'block';
    }

    function hideAlert() { byId('biLiveAlert').style.display = 'none'; }

    function frameDocument() {
        try {
            return frame.contentDocument || frame.contentWindow.document;
        } catch (error) {
            return null;
        }
    }

    function frameUrl() {
        try {
            return frame.contentWindow.location.href;
        } catch (error) {
            return currentSourceUrl;
        }
    }

    function rowCells(row) {
        return Array.prototype.slice.call(row.cells || []);
    }

    function tableBodyRows(table) {
        var rows = [];
        Array.prototype.forEach.call(table.tBodies || [], function (body) {
            rows = rows.concat(Array.prototype.slice.call(body.rows || []));
        });
        if (!rows.length && table.rows) {
            rows = Array.prototype.slice.call(table.rows);
            if (rows.length && rows[0].querySelector('th')) {
                rows.shift();
            }
        }
        return rows;
    }

    function choosePrimaryTable(doc) {
        var selected = null;
        var highestScore = 0;
        Array.prototype.forEach.call(doc.querySelectorAll('table'), function (table) {
            var rows = tableBodyRows(table);
            var columns = table.rows && table.rows.length ? rowCells(table.rows[0]).length : 0;
            var score = rows.length * Math.max(1, columns);
            if (rows.length > 0 && columns > 1 && score > highestScore) {
                selected = table;
                highestScore = score;
            }
        });
        return selected;
    }

    function tableHeaders(table) {
        if (!table) { return []; }
        var headerCells = table.querySelectorAll('thead tr:last-child th, thead tr:last-child td');
        if (!headerCells.length && table.rows && table.rows.length) {
            headerCells = table.rows[0].cells;
        }
        return Array.prototype.map.call(headerCells, function (cell, index) {
            var label = (cell.innerText || cell.textContent || '').replace(/\s+/g, ' ').trim();
            return label || 'Column ' + (index + 1);
        });
    }

    function injectWorkspaceStyle(doc) {
        if (doc.getElementById('bi-live-frame-style')) { return; }
        var style = doc.createElement('style');
        style.id = 'bi-live-frame-style';
        style.textContent = '.main-header,.main-sidebar,.control-sidebar,.main-footer{display:none!important}'
            + '#HeaderDiv,#FooterDiv{display:none!important}'
            + '#BodyDiv,#BodyWrapDiv{margin:0!important;width:auto!important;max-width:none!important}'
            + '.content-wrapper,.right-side{margin-left:0!important;min-height:0!important}'
            + '.wrapper{min-height:0!important;background:#fff!important}'
            + 'body{background:#fff!important;overflow:auto!important}'
            + 'body.bi-frame-compact table td,body.bi-frame-compact table th{padding:3px 5px!important;font-size:10px!important}'
            + 'table tr.bi-live-search-hidden{display:none!important}'
            + 'table td.bi-live-column-hidden,table th.bi-live-column-hidden{display:none!important}';
        (doc.head || doc.documentElement).appendChild(style);
    }

    function scheduleDecorate() {
        if (decorateTimer) {
            window.clearTimeout(decorateTimer);
        }
        decorateTimer = window.setTimeout(function () {
            decorateTimer = null;
            decorateFrame();
        }, 60);
    }

    function observeFrameDocument(doc) {
        if (frameObserver) {
            frameObserver.disconnect();
            frameObserver = null;
        }
        if (!doc || !doc.body || !window.MutationObserver) {
            return;
        }
        frameObserver = new MutationObserver(function () {
            scheduleDecorate();
        });
        frameObserver.observe(doc.body, {childList: true, subtree: true});
    }

    function sourceReportStatus(doc) {
        var status = doc.querySelector('[data-bi-report-status], #masterReportStatus');
        if (!status) {
            return null;
        }
        return {
            state: status.getAttribute('data-state') || 'ready',
            message: (status.innerText || status.textContent || '').replace(/\s+/g, ' ').trim()
        };
    }

    function applyDensity() {
        var doc = frameDocument();
        if (!doc || !doc.body) { return; }
        doc.body.classList.toggle('bi-frame-compact', byId('biLiveDensity').value === 'compact');
    }

    function applyColumnVisibility() {
        if (!primaryTable) { return; }
        Array.prototype.forEach.call(primaryTable.rows || [], function (row) {
            rowCells(row).forEach(function (cell, index) {
                cell.classList.toggle('bi-live-column-hidden', !!hiddenColumns[index]);
            });
        });
    }

    function renderColumnOptions() {
        var options = byId('biLiveColumnOptions');
        var headers = tableHeaders(primaryTable);
        if (!headers.length) {
            options.innerHTML = '<span class="text-muted">Generate or load a table to configure columns.</span>';
            return;
        }
        options.innerHTML = headers.map(function (label, index) {
            return '<label><input type="checkbox" data-column-index="' + index + '"' + (hiddenColumns[index] ? '' : ' checked') + '> ' + escapeHtml(label) + '</label>';
        }).join('');
    }

    function applySearch() {
        if (!primaryTable) { return; }
        var query = byId('biLiveSearch').value.toLowerCase().trim();
        var visible = 0;
        var rows = tableBodyRows(primaryTable);
        rows.forEach(function (row) {
            var matches = !query || (row.innerText || row.textContent || '').toLowerCase().indexOf(query) !== -1;
            row.classList.toggle('bi-live-search-hidden', !matches);
            if (matches) { visible++; }
        });
        byId('biLiveResultStatus').textContent = rows.length
            ? visible + ' of ' + rows.length + ' loaded rows visible'
            : 'Report filters are ready';
    }

    function decorateFrame() {
        var doc = frameDocument();
        if (!doc || !doc.documentElement) {
            primaryTable = null;
            byId('biLiveFrameStatus').innerHTML = '<i class="fa fa-external-link"></i> Report opened';
            byId('biLiveResultStatus').textContent = 'Use Open full page if this document cannot be shown inline';
            return;
        }

        injectWorkspaceStyle(doc);
        primaryTable = choosePrimaryTable(doc);
        applyDensity();
        applyColumnVisibility();
        renderColumnOptions();
        applySearch();

        var tableCount = doc.querySelectorAll('table').length;
        var resultRows = primaryTable ? tableBodyRows(primaryTable).length : 0;
        var sourceStatus = sourceReportStatus(doc);

        if (sourceStatus && sourceStatus.state === 'error') {
            byId('biLiveFrameStatus').innerHTML = '<i class="fa fa-exclamation-circle"></i> Report error';
            byId('biLiveResultStatus').textContent = sourceStatus.message || 'The source report could not load.';
        } else if (sourceStatus && sourceStatus.state === 'loading' && !resultRows) {
            byId('biLiveFrameStatus').innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading report';
            byId('biLiveResultStatus').textContent = sourceStatus.message || 'Loading report rows…';
        } else if (sourceStatus && sourceStatus.state === 'empty' && !resultRows) {
            byId('biLiveFrameStatus').innerHTML = '<i class="fa fa-info-circle"></i> No accessible rows';
            byId('biLiveResultStatus').textContent = sourceStatus.message || 'No rows are available for the current permissions.';
        } else {
            byId('biLiveFrameStatus').innerHTML = '<i class="fa fa-check-circle"></i> Live report loaded';
            byId('biLiveResultStatus').textContent = resultRows
                ? resultRows + ' result rows · ' + tableCount + ' table' + (tableCount === 1 ? '' : 's')
                : 'Source filters and report actions are ready';
        }
    }

    function savedViewsKey() {
        return 'sahamid.bi.live-report-views.' + (config.reportId || 'report');
    }

    function readSavedViews() {
        try {
            return JSON.parse(window.localStorage.getItem(savedViewsKey()) || '[]');
        } catch (error) {
            return [];
        }
    }

    function renderSavedViews() {
        var select = byId('biLiveSavedViews');
        select.innerHTML = '<option value="">Saved views</option>' + readSavedViews().map(function (view, index) {
            return '<option value="' + index + '">' + escapeHtml(view.name) + '</option>';
        }).join('');
    }

    function saveView() {
        var name = window.prompt('Name this private report view:', 'My ' + (config.title || 'report') + ' view');
        if (!name) { return; }
        var views = readSavedViews();
        views.push({
            name: name,
            search: byId('biLiveSearch').value,
            density: byId('biLiveDensity').value,
            hiddenColumns: hiddenColumns,
            savedAt: new Date().toISOString()
        });
        window.localStorage.setItem(savedViewsKey(), JSON.stringify(views.slice(-10)));
        renderSavedViews();
        showAlert('Saved “' + escapeHtml(name) + '” privately in this browser. Only table display preferences are stored.', 'success');
    }

    function restoreView(index) {
        var view = readSavedViews()[Number(index)];
        if (!view) { return; }
        byId('biLiveSearch').value = view.search || '';
        byId('biLiveDensity').value = view.density || 'comfortable';
        hiddenColumns = view.hiddenColumns || {};
        decorateFrame();
    }

    function exportVisibleTable() {
        hideAlert();
        if (!primaryTable) {
            showAlert('No result table is loaded yet. Run the report filters first, then export the visible rows.', 'warning');
            return;
        }
        var headers = tableHeaders(primaryTable);
        var visibleIndexes = [];
        headers.forEach(function (label, index) { if (!hiddenColumns[index]) { visibleIndexes.push(index); } });
        if (!visibleIndexes.length) {
            showAlert('Select at least one visible column before exporting.', 'warning');
            return;
        }
        var rows = tableBodyRows(primaryTable).filter(function (row) {
            return !row.classList.contains('bi-live-search-hidden');
        });
        if (rows.length > 5000) {
            showAlert('This table has more than 5,000 visible rows. Use the source filters or table search to narrow it before export.', 'warning');
            return;
        }
        var sourcePath = config.sourceUrl || '';
        try { sourcePath = new URL(frameUrl(), window.location.origin).pathname; } catch (error) { sourcePath = config.sourceUrl || ''; }
        var payload = {
            title: config.title || 'BI report',
            source: sourcePath,
            columns: visibleIndexes.map(function (index) { return headers[index]; }),
            rows: rows.map(function (row) {
                var cells = rowCells(row);
                return visibleIndexes.map(function (index) {
                    return cells[index] ? (cells[index].innerText || cells[index].textContent || '').replace(/\s+/g, ' ').trim() : '';
                });
            })
        };
        byId('biLiveExport').disabled = true;
        showAlert('Preparing the visible XLSX export…', 'info');
        window.fetch(config.exportUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json', 'X-BI-Request': '1'},
            body: JSON.stringify(payload)
        }).then(function (response) {
            if (!response.ok) {
                return response.json().then(function (body) {
                    throw new Error(body.error && body.error.message ? body.error.message : 'The XLSX export could not be generated.');
                });
            }
            return response.blob();
        }).then(function (blob) {
            var url = window.URL.createObjectURL(blob);
            var link = document.createElement('a');
            link.href = url;
            link.download = (config.title || 'bi-report').replace(/[^A-Za-z0-9._-]+/g, '-') + '.xlsx';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
            showAlert('The visible table was exported successfully.', 'success');
        }).catch(function (error) {
            showAlert(escapeHtml(error.message || 'The XLSX export could not be generated.'), 'danger');
        }).then(function () {
            byId('biLiveExport').disabled = false;
        });
    }

    frame.addEventListener('load', function () {
        currentSourceUrl = frameUrl();
        byId('biLiveOpenSource').href = currentSourceUrl;
        byId('biLiveLoading').style.display = 'none';
        byId('biLiveRefreshed').textContent = new Date().toLocaleString();
        root.setAttribute('aria-busy', 'false');
        var doc = frameDocument();
        observeFrameDocument(doc);
        scheduleDecorate();
    });

    byId('biLiveReload').addEventListener('click', function () {
        hideAlert();
        byId('biLiveLoading').style.display = 'flex';
        root.setAttribute('aria-busy', 'true');
        try { frame.contentWindow.location.reload(); } catch (error) { frame.src = currentSourceUrl || config.sourceUrl; }
    });
    byId('biLiveBack').addEventListener('click', function () { try { frame.contentWindow.history.back(); } catch (error) { showAlert('There is no earlier page in this report.', 'info'); } });
    byId('biLiveForward').addEventListener('click', function () { try { frame.contentWindow.history.forward(); } catch (error) { showAlert('There is no later page in this report.', 'info'); } });
    byId('biLiveSearch').addEventListener('input', applySearch);
    byId('biLiveDensity').addEventListener('change', applyDensity);
    byId('biLiveColumns').addEventListener('click', function () {
        var panel = byId('biLiveColumnPanel');
        if (panel.hasAttribute('hidden')) { panel.removeAttribute('hidden'); } else { panel.setAttribute('hidden', 'hidden'); }
    });
    byId('biLiveColumnOptions').addEventListener('change', function (event) {
        var input = event.target.closest('[data-column-index]');
        if (!input) { return; }
        var index = Number(input.getAttribute('data-column-index'));
        if (input.checked) { delete hiddenColumns[index]; } else { hiddenColumns[index] = true; }
        applyColumnVisibility();
    });
    byId('biLiveExport').addEventListener('click', exportVisibleTable);
    byId('biLivePrint').addEventListener('click', function () {
        try { frame.contentWindow.focus(); frame.contentWindow.print(); } catch (error) { showAlert('Open the report full page to print this document.', 'warning'); }
    });
    byId('biLiveFullscreen').addEventListener('click', function () {
        var panel = byId('biLiveFramePanel');
        var request = panel.requestFullscreen || panel.webkitRequestFullscreen || panel.msRequestFullscreen;
        if (request) { request.call(panel); } else { showAlert('Fullscreen is not available in this browser.', 'warning'); }
    });
    byId('biLiveSaveView').addEventListener('click', saveView);
    byId('biLiveSavedViews').addEventListener('change', function () { if (this.value !== '') { restoreView(this.value); this.value = ''; } });

    renderSavedViews();
})(window, document);
