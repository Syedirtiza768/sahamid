(function (window, document) {
    'use strict';

    var config = window.SAHAMID_BI_REPORT || {};
    var root = document.querySelector('.bi-workspace');
    if (!root) {
        return;
    }

    var preset = document.getElementById('biWorkspacePreset');
    var start = document.getElementById('biWorkspaceStart');
    var end = document.getElementById('biWorkspaceEnd');
    var search = document.getElementById('biWorkspaceSearch');
    var group = document.getElementById('biWorkspaceGroup');
    var chips = document.getElementById('biWorkspaceChips');
    var alertBox = document.getElementById('biWorkspaceAlert');
    var advancedPanel = document.getElementById('biWorkspaceAdvancedPanel');
    var advancedButton = document.getElementById('biWorkspaceAdvanced');
    var refreshed = document.getElementById('biWorkspaceRefreshed');

    function pad(value) {
        return value < 10 ? '0' + value : String(value);
    }

    function isoDate(date) {
        return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
    }

    function parseDate(value) {
        if (!value) {
            return null;
        }
        var parts = value.split('-');
        if (parts.length !== 3) {
            return null;
        }
        return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
    }

    function startOfWeek(date) {
        var result = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        var day = result.getDay();
        var offset = day === 0 ? -6 : 1 - day;
        result.setDate(result.getDate() + offset);
        return result;
    }

    function setDateRange(value) {
        var today = new Date();
        var rangeStart = null;
        var rangeEnd = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        var weekStart;
        var monthStart;
        var quarterStart;

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
        } else if (value === 'ytd') {
            rangeStart = new Date(today.getFullYear(), 0, 1);
        } else if (value === 'all') {
            rangeStart = null;
            rangeEnd = null;
        }

        start.value = rangeStart ? isoDate(rangeStart) : '';
        end.value = rangeEnd ? isoDate(rangeEnd) : '';
    }

    function showAlert(message, type) {
        alertBox.className = 'alert bi-workspace-alert alert-' + (type || 'info');
        alertBox.innerHTML = message;
        alertBox.style.display = 'block';
    }

    function hideAlert() {
        alertBox.style.display = 'none';
    }

    function stateFromControls() {
        return {
            preset: preset.value || 'ytd',
            start: start.value || '',
            end: end.value || '',
            search: search.value || '',
            group: group.value || ''
        };
    }

    function syncUrl() {
        var state = stateFromControls();
        var url = new URL(window.location.href);
        url.searchParams.set('report', config.reportId || '');
        url.searchParams.set('preset', state.preset);
        if (state.start) {
            url.searchParams.set('start', state.start);
        } else {
            url.searchParams.delete('start');
        }
        if (state.end) {
            url.searchParams.set('end', state.end);
        } else {
            url.searchParams.delete('end');
        }
        if (state.search) {
            url.searchParams.set('search', state.search);
        } else {
            url.searchParams.delete('search');
        }
        if (state.group) {
            url.searchParams.set('group', state.group);
        } else {
            url.searchParams.delete('group');
        }
        window.history.replaceState({}, '', url.toString());
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (character) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[character];
        });
    }

    function renderChips() {
        var state = stateFromControls();
        var output = [];
        if (state.start || state.end) {
            output.push('<button type="button" class="bi-filter-chip" data-remove="date">Date: ' + escapeHtml(state.start || 'All') + ' → ' + escapeHtml(state.end || 'All') + ' <i class="fa fa-times"></i></button>');
        }
        if (state.search) {
            output.push('<button type="button" class="bi-filter-chip" data-remove="search">Search: ' + escapeHtml(state.search) + ' <i class="fa fa-times"></i></button>');
        }
        if (state.group) {
            output.push('<button type="button" class="bi-filter-chip" data-remove="group">Group: ' + escapeHtml(state.group) + ' <i class="fa fa-times"></i></button>');
        }
        chips.innerHTML = output.join('');
    }

    function updateState() {
        if (start.value && end.value && start.value > end.value) {
            showAlert('The start date cannot be after the end date.', 'warning');
            return;
        }
        hideAlert();
        renderChips();
        syncUrl();
    }

    function restoreState() {
        var params = new URLSearchParams(window.location.search);
        var selectedPreset = params.get('preset') || 'ytd';
        preset.value = selectedPreset;
        if (params.has('start') || params.has('end')) {
            start.value = params.get('start') || '';
            end.value = params.get('end') || '';
            if (!params.has('preset')) {
                preset.value = 'custom';
            }
        } else if (selectedPreset === 'all') {
            setDateRange('all');
        } else if (!params.has('start') && !params.has('end')) {
            setDateRange(selectedPreset);
        }
        search.value = params.get('search') || '';
        group.value = params.get('group') || '';
        renderChips();
    }

    function saveView() {
        var name = window.prompt('Name this private saved view:', 'My ' + (config.title || 'report') + ' view');
        if (!name) {
            return;
        }
        var key = 'sahamid.bi.saved-views.' + (config.reportId || 'report');
        var views = [];
        try {
            views = JSON.parse(window.localStorage.getItem(key) || '[]');
        } catch (error) {
            views = [];
        }
        views.push({name: name, state: stateFromControls(), savedAt: new Date().toISOString()});
        window.localStorage.setItem(key, JSON.stringify(views.slice(-10)));
        showAlert('Saved “' + escapeHtml(name) + '” in this browser. It contains filter state only and is private to this device.', 'success');
    }

    document.getElementById('biWorkspacePreset').addEventListener('change', function () {
        if (this.value !== 'custom') {
            setDateRange(this.value);
        }
        updateState();
    });
    start.addEventListener('change', function () { preset.value = 'custom'; updateState(); });
    end.addEventListener('change', function () { preset.value = 'custom'; updateState(); });
    search.addEventListener('input', updateState);
    group.addEventListener('change', updateState);
    document.getElementById('biWorkspaceClear').addEventListener('click', function () {
        preset.value = 'ytd';
        setDateRange('ytd');
        search.value = '';
        group.value = '';
        updateState();
        showAlert('Filters reset to year to date.', 'info');
    });
    advancedButton.addEventListener('click', function () {
        var isHidden = advancedPanel.hasAttribute('hidden');
        if (isHidden) {
            advancedPanel.removeAttribute('hidden');
        } else {
            advancedPanel.setAttribute('hidden', 'hidden');
        }
        advancedButton.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
    });
    chips.addEventListener('click', function (event) {
        var button = event.target.closest('[data-remove]');
        if (!button) {
            return;
        }
        if (button.getAttribute('data-remove') === 'date') {
            preset.value = 'all';
            setDateRange('all');
        } else if (button.getAttribute('data-remove') === 'search') {
            search.value = '';
        } else if (button.getAttribute('data-remove') === 'group') {
            group.value = '';
        }
        updateState();
    });
    document.getElementById('biWorkspaceRefresh').addEventListener('click', function () {
        updateState();
        refreshed.textContent = new Date().toLocaleString();
        showAlert('Workspace state refreshed. This compatibility workspace does not issue a replacement data query until its handler is validated.', 'info');
    });
    document.getElementById('biWorkspaceSave').addEventListener('click', saveView);

    Array.prototype.forEach.call(document.querySelectorAll('.bi-workspace-tabs [data-tab]'), function (tab) {
        tab.addEventListener('click', function () {
            var selected = this.getAttribute('data-tab');
            Array.prototype.forEach.call(document.querySelectorAll('.bi-workspace-tabs [data-tab]'), function (item) {
                var active = item === tab;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            Array.prototype.forEach.call(document.querySelectorAll('.bi-workspace-view[data-view]'), function (view) {
                var visible = view.getAttribute('data-view') === selected;
                view.classList.toggle('is-active', visible);
                if (visible) {
                    view.removeAttribute('hidden');
                } else {
                    view.setAttribute('hidden', 'hidden');
                }
            });
        });
    });

    restoreState();
    refreshed.textContent = new Date().toLocaleString();
})(window, document);
