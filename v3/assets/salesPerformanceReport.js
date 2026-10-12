(function ($) {
	'use strict';

	var app = $('#sales-performance-app');
	if (!app.length) return;

	var apiUrl = app.data('api-url');
	var reportData = null;
	var documentQuery = null;
	var documentPage = 1;
	var defaultFilters = {
		from: $('#sp-filter-form [name="from"]').val(),
		to: $('#sp-filter-form [name="to"]').val(),
		business_cutoff: $('#sp-filter-form [name="business_cutoff"]').val(),
		compare_from: $('#sp-filter-form [name="compare_from"]').val(),
		compare_to: $('#sp-filter-form [name="compare_to"]').val()
	};
	var metricLabels = {
		enquiries: 'Enquiries',
		quotations: 'Initial quotations',
		revised_quotations: 'Revised quotations',
		purchase_orders: 'Customer PO receipts',
		order_confirmations: 'OCs (proxy)',
		delivery_challans: 'Delivery challans',
		followups: 'Follow-ups',
		enquiry_responses: 'Enquiry responses',
		company_regrets: 'Company regrets',
		customer_rejections: 'Customer rejections'
	};

	function escapeHtml(value) {
		return String(value === null || typeof value === 'undefined' ? '' : value)
			.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;').replace(/'/g, '&#039;');
	}

	function number(value) {
		var n = Number(value || 0);
		return isFinite(n) ? n : 0;
	}

	function formatNumber(value) {
		return Math.round(number(value)).toLocaleString('en');
	}

	function incrementDigits(value) {
		var digits = value.split('');
		for (var i = digits.length - 1; i >= 0; i--) {
			if (digits[i] !== '9') { digits[i] = String(Number(digits[i]) + 1); return digits.join(''); }
			digits[i] = '0';
		}
		return '1' + digits.join('');
	}

	function formatDecimal(value) {
		var source = String(value === null || typeof value === 'undefined' ? '0' : value).trim();
		if (!/^-?\d+(?:\.\d+)?$/.test(source)) return '—';
		var negative = source.charAt(0) === '-';
		if (negative) source = source.slice(1);
		var parts = source.split('.');
		var whole = parts[0].replace(/^0+(?=\d)/, '');
		var fraction = parts.length > 1 ? parts[1] : '';
		var cents = (fraction + '00').slice(0, 2);
		if (fraction.length > 2 && fraction.charAt(2) >= '5') {
			var roundedCents = Number(cents) + 1;
			if (roundedCents >= 100) { whole = incrementDigits(whole); cents = '00'; }
			else cents = roundedCents < 10 ? '0' + roundedCents : String(roundedCents);
		}
		whole = whole.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
		return (negative ? '-' : '') + whole + '.' + cents;
	}

	function formatMoney(value) {
		return 'PKR ' + formatDecimal(value);
	}

	function formatSourceCurrency(value, currency) {
		var formatted = formatDecimal(value);
		if (formatted === '—') return formatted;
		return (currency ? String(currency) : 'Unknown currency') + ' ' + formatted;
	}

	function amountText(item, field) {
		if (!item || item.value_status === 'unavailable') return 'Unavailable';
		var unknown = field === 'raw_value' ? number(item.raw_unknown_count)
			: (field === 'normalized_value' ? number(item.normalized_unknown_count) : number(item.net_unknown_count));
		var amount = formatMoney(item[field]);
		return unknown ? amount + ' · partial (' + formatNumber(unknown) + ' unknown)' : amount;
	}

	function metric(period, key) {
		return period && period.metrics && period.metrics[key] ? period.metrics[key] : {
			count: 0, raw_value: 0, normalized_value: 0, adjusted_documents: 0
		};
	}

	function perDay(period, key, field) {
		var days = number(period.reporting_days);
		return days > 0 ? number(metric(period, key)[field]) / days : 0;
	}

	function valuePerDayText(period, key, field) {
		var item = metric(period, key);
		if (!number(period.reporting_days)) return '—';
		if (item.value_status === 'unavailable' || number(item.normalized_unknown_count) > 0 || number(item.net_unknown_count) > 0) return 'Unavailable';
		return formatMoney(number(item[field]) / number(period.reporting_days));
	}

	function itemPerDayText(item, days) {
		if (!days) return '—';
		if (item.value_status === 'unavailable' || number(item.normalized_unknown_count) > 0 || number(item.net_unknown_count) > 0) return 'Unavailable';
		return formatMoney(number(item.normalized_value) / days);
	}

	function chartAmount(item) {
		return item.value_status === 'unavailable' || number(item.normalized_unknown_count) > 0 ? null : number(item.normalized_value);
	}

	function pctChange(current, previous) {
		current = number(current);
		previous = number(previous);
		if (previous === 0) return current === 0 ? '0%' : '—';
		var delta = ((current - previous) / Math.abs(previous)) * 100;
		return (delta > 0 ? '+' : '') + delta.toFixed(1) + '%';
	}

	function showMessage(text, type) {
		$('#sp-message').removeClass('error success').addClass(type || 'error').text(text).show();
	}

	function clearMessage() {
		$('#sp-message').removeClass('error success').hide().text('');
	}

	function currentFilters() {
		var form = $('#sp-filter-form');
		return {
			from: form.find('[name="from"]').val(),
			to: form.find('[name="to"]').val(),
			business_cutoff: form.find('[name="business_cutoff"]').val(),
			compare_from: form.find('[name="compare_from"]').val(),
			compare_to: form.find('[name="compare_to"]').val(),
			population_scope: $('#sp-population-filter').val() || '',
			team_ids: $('#sp-team-filter').val() || [],
			salespeople: $('#sp-salesperson-filter').val() || []
		};
	}

	function renderNamedScopeFilter() {
		var filter = $('#sp-population-filter');
		if (!filter.length) return;
		var selected = String(filter.val() || '');
		var options = reportData.named_reporting_scope_options || [];
		var html = '<option value="">No named population</option>';
		$.each(options, function (_, option) {
			var description = String(option.label || 'Approved team population') + ' · v' + String(option.version || '');
			description += (String(option.include_unassigned) === '1' || option.include_unassigned === true) ? ' · includes unassigned' : ' · excludes unassigned';
			html += '<option value="' + escapeHtml(option.scope_key) + '">' + escapeHtml(description) + '</option>';
		});
		filter.html(html);
		filter.val(options.some(function (option) { return String(option.scope_key) === selected; }) ? selected : '').trigger('change.select2');
	}

	function renderMeta() {
		var filters = currentFilters();
		var teamNames = $('#sp-team-filter option:selected').map(function () { return $(this).text(); }).get();
		var peopleNames = $('#sp-salesperson-filter option:selected').map(function () { return $(this).text(); }).get();
		var chipList = [];
		if (teamNames.length) chipList.push('Teams: ' + teamNames.join(', '));
		if (peopleNames.length) chipList.push('Salespeople: ' + peopleNames.join(', '));
		var effectiveScope = reportData.meta && reportData.meta.effective_scope ? reportData.meta.effective_scope : {};
		if (effectiveScope.named_population) {
			var populationText = effectiveScope.named_population.label + ' · v' + effectiveScope.named_population.version;
			if (effectiveScope.named_population.include_unassigned) populationText += ' · includes unassigned';
			chipList.push('Population: ' + populationText);
		}
		var adjustmentNote = reportData.pending_adjustments && reportData.pending_adjustments.length
			? ' ' + reportData.pending_adjustments.length + ' adjustment request(s) await review.'
			: '';
		var note = 'Current period ' + filters.from + ' to ' + filters.to + ' (' + reportData.current.reporting_days + ' complete captured workdays); cutoff ' + filters.business_cutoff + '. Comparison ' +
			filters.compare_from + ' to ' + filters.compare_to + ' (' + reportData.comparison.reporting_days + ' complete captured workdays).';
		note += ' Current-period values are reported through ' + reportData.current.as_of + '; future days are excluded from daily rates.';
		if (chipList.length) note += ' Filters: ' + chipList.join(' · ') + '.';
		note += ' Refreshed ' + new Date().toLocaleString() + '.';
		note += ' Follow-up history begins ' + reportData.followup_start_date + '. Scope: ' + (effectiveScope.kind || 'authorized').replace(/_/g, ' ') + '. Definitions remain provisional.';
		if (reportData.frozen_run) note += ' Frozen run ' + reportData.frozen_run.run_key + ' · ' + reportData.frozen_run.content_hash + '.';
		var enquiryStatus = reportData.enquiry_status && reportData.enquiry_status.current;
		if (enquiryStatus) note += ' Captured-event unanswered new: ' + enquiryStatus.new_unanswered_count + '; open backlog: ' + enquiryStatus.backlog_count + '. Enquiry-event coverage is incomplete.';
		var adjusted = 0;
		$.each(reportData.current.metrics, function (_, item) { adjusted += number(item.adjusted_documents); });
		$.each(reportData.comparison.metrics, function (_, item) { adjusted += number(item.adjusted_documents); });
		if (!adjusted) note += ' No approved value exclusions are configured; raw and normalized totals currently match.';
		note += adjustmentNote;
		$('#sp-report-meta').text(note);
		var assumptions = reportData.assumptions || {};
		$('#sp-assumptions').text('Report rules: ' + assumptions.enquiry_date + ' ' + assumptions.po_label + ' ' + assumptions.team + ' ' + assumptions.values + ' Unanswered backlog and company-regret counts are unavailable until dated event capture is in use.');
	}

	function renderKpis() {
		var keys = ['enquiries', 'quotations', 'revised_quotations', 'purchase_orders', 'order_confirmations', 'delivery_challans', 'followups', 'enquiry_responses', 'company_regrets'];
		var html = '';
		$.each(keys, function (_, key) {
			var item = metric(reportData.current, key);
			var eventOnly = ['followups', 'enquiry_responses', 'company_regrets'].indexOf(key) >= 0;
			var value = eventOnly ? 'Captured events; reporting coverage is incomplete' : amountText(item, 'normalized_value') + ' normalized · ' + amountText(item, 'raw_value') + ' raw';
			html += '<div class="sp-kpi"><div class="sp-kpi-label">' + escapeHtml(metricLabels[key]) + '</div>' +
				'<div class="sp-kpi-count">' + formatNumber(item.count) + '</div>' +
				'<div class="sp-kpi-value">' + escapeHtml(value) + '</div></div>';
		});
		$('#sp-kpis').html(html);
	}

	function weekdayName(dateText) {
		var parts = dateText.split('-').map(Number);
		var names = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
		return names[new Date(Date.UTC(parts[0], parts[1] - 1, parts[2])).getUTCDay()];
	}

	function renderDaily() {
		var html = '<thead><tr><th>Date</th><th>Weekday</th><th>Enquiries</th><th>Unanswered</th><th>Responses</th><th>Company regrets</th><th>Initial quotes</th><th>Initial quote net</th><th>Revisions</th><th>Revision net</th><th>Follow-ups</th><th>Customer POs</th><th>OCs (proxy)</th><th>DCs</th><th>Coverage</th></tr></thead><tbody>';
		$.each(reportData.current.daily || [], function (_, day) {
			var m = day.metrics;
			var state = (day.coverage_status || 'not_submitted').replace(/_/g, ' ');
			if (day.date > reportData.current.as_of) state = day.state === 'future' ? 'future' : 'after cutoff';
			var initialCount = '<button type="button" class="btn btn-link btn-xs sp-document-drilldown" data-metric="quotations" data-date="' + escapeHtml(day.date) + '">' + formatNumber(m.quotations.count) + '</button>';
			var revisionCount = '<button type="button" class="btn btn-link btn-xs sp-document-drilldown" data-metric="revised_quotations" data-date="' + escapeHtml(day.date) + '">' + formatNumber(m.revised_quotations.count) + '</button>';
			html += '<tr><th>' + escapeHtml(day.date) + '</th><td>' + weekdayName(day.date) + '</td><td>' + formatNumber(m.enquiries.count) + '</td>' +
			'<td>' + escapeHtml((day.unanswered_new_count || 0) + ' captured · incomplete') + '</td><td>' + escapeHtml(formatNumber(m.enquiry_responses.count) + ' captured') + '</td><td>' + escapeHtml(formatNumber(m.company_regrets.count) + ' captured') + '</td><td>' + initialCount + '</td>' +
				'<td>' + amountText(m.quotations, 'net_value') + '</td><td>' + revisionCount + '</td><td>' + amountText(m.revised_quotations, 'net_value') + '</td>' +
				'<td>' + formatNumber(m.followups.count) + '</td><td>' + formatNumber(m.purchase_orders.count) + '</td><td>' + formatNumber(m.order_confirmations.count) + '</td><td>' + formatNumber(m.delivery_challans.count) + '</td>' +
				'<td class="text-muted">' + escapeHtml(state) + '</td></tr>';
		});
		html += '</tbody>';
		$('#sp-daily-table').html(html);
	}

	function renderPeriodTable() {
		var rows = [
			['enquiries', 'Enquiries'], ['quotations', 'Initial quotations'], ['revised_quotations', 'Revised quotations'],
			['followups', 'Follow-ups'], ['purchase_orders', 'Customer PO receipts'], ['order_confirmations', 'OCs (proxy)'], ['delivery_challans', 'Delivery challans']
		];
		var html = '<thead><tr><th>Activity</th><th>Current count</th><th>Current value (raw)</th><th>Current value (normalized)</th><th>Average value / record</th><th>Count / reporting day</th><th>Value / reporting day</th><th>Comparison count</th><th>Comparison normalized value</th><th>Comparison value / reporting day</th><th>Total value change</th><th>Rate change</th></tr></thead><tbody>';
		$.each(rows, function (_, row) {
			var current = metric(reportData.current, row[0]);
			var previous = metric(reportData.comparison, row[0]);
			var valueChange = current.value_status === 'unavailable' || previous.value_status === 'unavailable' || current.normalized_unknown_count > 0 || previous.normalized_unknown_count > 0 ? '—' : pctChange(current.normalized_value, previous.normalized_value);
			var currentRate = perDay(reportData.current, row[0], 'normalized_value');
			var previousRate = perDay(reportData.comparison, row[0], 'normalized_value');
			var valueUnknown = current.value_status === 'unavailable' || previous.value_status === 'unavailable' || number(current.normalized_unknown_count) > 0 || number(previous.normalized_unknown_count) > 0;
			var rateChange = valueUnknown ? '—' : pctChange(currentRate, previousRate);
			var valueCell = row[0] === 'followups' ? '—' : amountText(current, 'normalized_value');
			var rawCell = row[0] === 'followups' ? '—' : amountText(current, 'raw_value');
			var compareCell = row[0] === 'followups' ? '—' : amountText(previous, 'normalized_value');
			var averageCell = row[0] === 'followups' || !number(current.count) || current.net_unknown_count > 0 || current.normalized_unknown_count > 0 ? '—' : formatMoney(current.normalized_value / number(current.count));
			html += '<tr><th>' + escapeHtml(row[1]) + '</th><td class="text-right">' + formatNumber(current.count) + '</td>' +
				'<td class="text-right">' + rawCell + '</td><td class="text-right">' + valueCell + '</td><td class="text-right">' + averageCell + '</td>' +
				'<td class="text-right">' + (number(reportData.current.reporting_days) ? perDay(reportData.current, row[0], 'count').toFixed(2) : '—') + '</td>' +
				'<td class="text-right">' + (row[0] === 'followups' ? '—' : valuePerDayText(reportData.current, row[0], 'normalized_value')) + '</td>' +
				'<td class="text-right">' + formatNumber(previous.count) + '</td><td class="text-right">' + compareCell + '</td>' +
				'<td class="text-right">' + (row[0] === 'followups' ? '—' : valuePerDayText(reportData.comparison, row[0], 'normalized_value')) + '</td>' +
				'<td class="text-right">' + (row[0] === 'followups' ? '—' : escapeHtml(valueChange)) + '</td>' +
				'<td class="text-right">' + (row[0] === 'followups' ? '—' : escapeHtml(rateChange)) + '</td></tr>';
		});
		html += '</tbody>';
		$('#sp-period-table').html(html);
	}

	function renderMonthChart() {
		var dates = reportData.current.daily || [];
		var categories = dates.map(function (item) { return item.date; });
		var series = [
			{ key: 'quotations', name: 'Quotations' },
			{ key: 'purchase_orders', name: 'POs' },
			{ key: 'delivery_challans', name: 'Delivery challans' }
		];
		if (window.Highcharts) {
			Highcharts.chart('sp-month-chart', {
				chart: { type: 'spline', backgroundColor: 'transparent' },
				title: { text: null },
				xAxis: { categories: categories, labels: { rotation: -35 } },
				yAxis: { min: 0, title: { text: 'PKR (normalized)' } },
				tooltip: { shared: true, valuePrefix: 'PKR ', valueDecimals: 0 },
				legend: { enabled: true },
				credits: { enabled: false },
				series: series.map(function (entry) {
				return { name: entry.name, data: dates.map(function (day) { var item = day.metrics[entry.key]; return day.date > reportData.current.as_of || item.value_status === 'unavailable' || number(item.normalized_unknown_count) > 0 ? null : number(item.normalized_value); }) };
				})
			});
		} else {
			$('#sp-month-chart').text('Chart library unavailable. Use the period and weekly tables below.');
		}
	}

	function renderWeekly() {
		var weeks = reportData.current.weekly || [];
		var html = '<thead><tr><th>Week starting</th><th>Enquiries</th><th>Quotations</th><th>Quote value (raw)</th><th>Quote value (normalized)</th><th>Revisions</th><th>Follow-ups</th><th>Customer POs</th><th>Customer PO net value</th><th>OCs (proxy)</th><th>OC value (proxy)</th><th>DC count</th><th>DC value (normalized)</th></tr></thead><tbody>';
		$.each(weeks, function (_, week) {
			var m = week.metrics;
			html += '<tr><th>' + escapeHtml(week.week_start) + '</th><td class="text-right">' + formatNumber(m.enquiries.count) + '</td>' +
				'<td class="text-right">' + formatNumber(m.quotations.count) + '</td><td class="text-right">' + formatMoney(m.quotations.raw_value) + '</td>' +
				'<td class="text-right">' + formatMoney(m.quotations.normalized_value) + '</td><td class="text-right">' + formatNumber(m.revised_quotations.count) + '</td>' +
				'<td class="text-right">' + formatNumber(m.followups.count) + '</td><td class="text-right">' + formatNumber(m.purchase_orders.count) + '</td>' +
				'<td class="text-right">' + amountText(m.purchase_orders, 'net_value') + '</td><td class="text-right">' + formatNumber(m.order_confirmations.count) + '</td>' +
				'<td class="text-right">' + amountText(m.order_confirmations, 'normalized_value') + '</td><td class="text-right">' + formatNumber(m.delivery_challans.count) + '</td>' +
				'<td class="text-right">' + formatMoney(m.delivery_challans.normalized_value) + '</td></tr>';
		});
		html += '</tbody>';
		$('#sp-week-table').html(html);
		if (window.Highcharts) {
			Highcharts.chart('sp-week-chart', {
				chart: { type: 'column', backgroundColor: 'transparent' }, title: { text: null },
				xAxis: { categories: weeks.map(function (week) { return week.week_start; }) },
				yAxis: { min: 0, title: { text: 'PKR (normalized)' } },
				tooltip: { shared: true, valuePrefix: 'PKR ', valueDecimals: 0 },
				credits: { enabled: false },
				series: [
					{ name: 'Quotations', data: weeks.map(function (w) { return chartAmount(w.metrics.quotations); }) },
					{ name: 'Customer POs', data: weeks.map(function (w) { return chartAmount(w.metrics.purchase_orders); }) },
					{ name: 'Delivery challans', data: weeks.map(function (w) { return chartAmount(w.metrics.delivery_challans); }) }
				]
			});
		}
	}

	function renderTeams() {
		var teams = reportData.current.teams || [];
		var html = '<thead><tr><th>Team</th><th>Enquiries</th><th>Quotations</th><th>Quote value (raw)</th><th>Quote value (normalized)</th><th>Quote share</th><th>Revised quotes</th><th>Follow-ups</th><th>PO count</th><th>PO value (normalized)</th><th>PO / reporting day</th><th>PO share</th><th>DC count</th><th>DC value (normalized)</th><th>DC / reporting day</th><th>DC share</th></tr></thead><tbody>';
		var visibleTeams = teams.filter(function (team) {
			return team.id !== 0 || number(team.metrics.enquiries.count) + number(team.metrics.quotations.count) + number(team.metrics.purchase_orders.count) + number(team.metrics.delivery_challans.count) + number(team.metrics.followups.count) > 0;
		});
		$.each(visibleTeams, function (_, team) {
			var m = team.metrics;
			var quoteTotal = reportData.current.metrics.quotations;
			var poTotal = reportData.current.metrics.purchase_orders;
			var dcTotal = reportData.current.metrics.delivery_challans;
			var quoteShare = quoteTotal.value_status === 'unavailable' || quoteTotal.normalized_unknown_count > 0 ? '—' : (number(quoteTotal.normalized_value) ? (number(m.quotations.normalized_value) / number(quoteTotal.normalized_value) * 100).toFixed(1) + '%' : '—');
			var poShare = poTotal.value_status === 'unavailable' || poTotal.normalized_unknown_count > 0 ? '—' : (number(poTotal.normalized_value) ? (number(m.purchase_orders.normalized_value) / number(poTotal.normalized_value) * 100).toFixed(1) + '%' : '—');
			var dcShare = dcTotal.value_status === 'unavailable' || dcTotal.normalized_unknown_count > 0 ? '—' : (number(dcTotal.normalized_value) ? (number(m.delivery_challans.normalized_value) / number(dcTotal.normalized_value) * 100).toFixed(1) + '%' : '—');
			var days = number(reportData.current.reporting_days);
			html += '<tr><th>' + escapeHtml(team.name) + '</th><td class="text-right">' + formatNumber(m.enquiries.count) + '</td>' +
				'<td class="text-right">' + formatNumber(m.quotations.count) + '</td><td class="text-right">' + amountText(m.quotations, 'raw_value') + '</td>' +
				'<td class="text-right">' + amountText(m.quotations, 'normalized_value') + '</td><td class="text-right">' + quoteShare + '</td>' +
				'<td class="text-right">' + formatNumber(m.revised_quotations.count) + '</td><td class="text-right">' + formatNumber(m.followups.count) + '</td>' +
				'<td class="text-right">' + formatNumber(m.purchase_orders.count) + '</td><td class="text-right">' + amountText(m.purchase_orders, 'normalized_value') + '</td>' +
				'<td class="text-right">' + itemPerDayText(m.purchase_orders, days) + '</td><td class="text-right">' + poShare + '</td>' +
				'<td class="text-right">' + formatNumber(m.delivery_challans.count) + '</td><td class="text-right">' + amountText(m.delivery_challans, 'normalized_value') + '</td>' +
				'<td class="text-right">' + itemPerDayText(m.delivery_challans, days) + '</td><td class="text-right">' + dcShare + '</td></tr>';
		});
		html += '</tbody>';
		$('#sp-team-table').html(html);
		var weekHtml = '<thead><tr><th>Week starting</th><th>Team</th><th>Quotes</th><th>Quote value (normalized)</th><th>Revisions</th><th>POs</th><th>PO value (normalized)</th><th>DCs</th><th>DC value (normalized)</th></tr></thead><tbody>';
		var weekRowCount = 0;
		$.each(visibleTeams, function (_, team) {
			$.each(team.weeks || [], function (_, week) {
				var m = week.metrics;
				var activity = number(m.quotations.count) + number(m.revised_quotations.count) + number(m.purchase_orders.count) + number(m.delivery_challans.count) + number(m.followups.count) + number(m.enquiries.count);
				if (!activity) return;
				weekRowCount++;
				weekHtml += '<tr><td>' + escapeHtml(week.week_start) + '</td><th>' + escapeHtml(team.name) + '</th>' +
					'<td class="text-right">' + formatNumber(m.quotations.count) + '</td><td class="text-right">' + amountText(m.quotations, 'normalized_value') + '</td>' +
					'<td class="text-right">' + formatNumber(m.revised_quotations.count) + '</td><td class="text-right">' + formatNumber(m.purchase_orders.count) + '</td>' +
					'<td class="text-right">' + amountText(m.purchase_orders, 'normalized_value') + '</td><td class="text-right">' + formatNumber(m.delivery_challans.count) + '</td>' +
					'<td class="text-right">' + amountText(m.delivery_challans, 'normalized_value') + '</td></tr>';
			});
		});
		if (!weekRowCount) weekHtml += '<tr><td colspan="9" class="text-muted">No activity was found in the selected period.</td></tr>';
		weekHtml += '</tbody>';
		$('#sp-team-week-table').html(weekHtml);
		if (window.Highcharts) {
			Highcharts.chart('sp-team-chart', {
				chart: { type: 'bar', backgroundColor: 'transparent' }, title: { text: null },
				xAxis: { categories: visibleTeams.map(function (team) { return team.name; }) },
				yAxis: { min: 0, title: { text: 'PKR (normalized)' } },
				tooltip: { shared: true, valuePrefix: 'PKR ', valueDecimals: 0 },
				credits: { enabled: false },
				series: [
					{ name: 'Quotations', data: visibleTeams.map(function (t) { return chartAmount(t.metrics.quotations); }) },
					{ name: 'Customer POs', data: visibleTeams.map(function (t) { return chartAmount(t.metrics.purchase_orders); }) },
					{ name: 'Delivery challans', data: visibleTeams.map(function (t) { return chartAmount(t.metrics.delivery_challans); }) }
				]
			});
		}

		var selected = $('#sp-matrix-team').val();
		var options = '';
		$.each(visibleTeams, function (_, team) {
			options += '<option value="' + escapeHtml(team.id) + '"' + (String(team.id) === String(selected) ? ' selected' : '') + '>' + escapeHtml(team.name) + '</option>';
		});
		$('#sp-matrix-team').html(options);
		if (!$('#sp-matrix-team').val() && visibleTeams.length) $('#sp-matrix-team').val(String(visibleTeams[0].id));
		renderMatrix();
	}

	function renderMatrix() {
		if (!reportData) return;
		var teamId = String($('#sp-matrix-team').val() || '');
		var selectedTeam = null;
		$.each(reportData.current.teams || [], function (_, team) {
			if (String(team.id) === teamId) selectedTeam = team;
		});
		if (!selectedTeam) {
			$('#sp-team-matrix').html('<p class="text-muted">Choose a team to see the weekday comparison.</p>');
			return;
		}
		var key = $('#sp-matrix-metric').val();
		var field = $('#sp-matrix-value').val();
		var fieldLabel = field === 'count' ? 'Count' : (field === 'raw_value' ? 'Raw value' : 'Normalized value');
		var html = '<table class="sp-matrix"><thead><tr><th>Week</th><th>Monday</th><th>Tuesday</th><th>Wednesday</th><th>Thursday</th><th>Friday</th><th>Saturday</th></tr></thead><tbody>';
		$.each(selectedTeam.weeks || [], function (_, week) {
			html += '<tr><th class="sp-week-label">Week of ' + escapeHtml(week.week_start) + '</th>';
			for (var dayIndex = 1; dayIndex <= 6; dayIndex++) {
				var day = week.days[dayIndex] || week.days[String(dayIndex)];
				if (!day) {
					html += '<td class="state-outside_period">—</td>';
					continue;
				}
				var state = key === 'followups' ? day.followup_state : day.state;
				var text = '';
				if (state === 'future') text = 'Future';
				else if (state === 'after_cutoff') text = 'After cutoff';
				else if (state === 'outside_period') text = 'Outside period';
				else if (state === 'not_captured') text = 'Not captured';
				else {
			var value = number(day.metrics[key][field]);
			text = field === 'count' ? formatNumber(value) : (day.metrics[key].value_status === 'unavailable' || day.metrics[key].normalized_unknown_count > 0 ? 'Unavailable' : formatMoney(value));
				}
				html += '<td class="state-' + escapeHtml(state) + '" title="' + escapeHtml(day.date + ' · ' + metricLabels[key] + ' · ' + fieldLabel) + '">' + escapeHtml(text) + '</td>';
			}
			html += '</tr>';
		});
		html += '</tbody></table>';
		$('#sp-team-matrix').html(html);
	}

	function renderFollowups() {
		var rows = reportData.followups || [];
		var html = '<thead><tr><th>Date and time</th><th>Salesperson</th><th>Case</th><th>Type</th><th>Outcome</th><th>Next action date</th><th>Next action</th><th>Entered by</th><th>Status and audit reason</th><th>Notes</th><th>Actions</th></tr></thead><tbody>';
		if (!rows.length) html += '<tr><td colspan="11" class="text-muted">No follow-up entries were recorded in this period.</td></tr>';
		$.each(rows, function (_, row) {
			var status = row.voided_at ? (row.has_correction ? 'Corrected on ' : 'Voided on ') + row.voided_at + ' by ' + (row.voided_by || 'unknown') : (row.corrected_from_id ? 'Corrected version' : 'Active');
			var auditReason = row.void_reason ? '<br><span class="text-muted">' + escapeHtml(row.void_reason) + '</span>' : '';
			var actions = '';
			if (row.can_correct) actions += '<button type="button" class="btn btn-xs btn-default sp-correct-followup" data-followup-id="' + escapeHtml(row.id) + '">Correct</button> ';
			if (row.can_void) actions += '<button type="button" class="btn btn-xs btn-link text-danger sp-void-followup" data-followup-id="' + escapeHtml(row.id) + '">Void</button>';
			html += '<tr><td>' + escapeHtml(row.activity_at || row.activity_date) + '</td><td>' + escapeHtml(row.salesman) + '</td><td>' + escapeHtml(row.salescaseref || '—') + '</td>' +
				'<td>' + escapeHtml(row.activity_type) + '</td><td>' + escapeHtml(row.outcome || '—') + '</td><td>' + escapeHtml(row.next_action_date || '—') + '</td>' +
				'<td>' + escapeHtml(row.next_action || '—') + '</td><td>' + escapeHtml(row.entered_by_user_id || row.created_by || '—') + '</td>' +
				'<td>' + escapeHtml(status) + auditReason + '</td><td>' + escapeHtml(row.notes || '—') + '</td><td class="sp-followup-actions">' + actions + '</td></tr>';
		});
		html += '</tbody>';
		$('#sp-followup-table').html(html);
	}

	function renderEnquiryEvents() {
		var rows = reportData.enquiry_events || [];
		var labels = { received: 'Enquiry received', response: 'Response made', company_decline: 'Company declined', customer_rejection: 'Customer rejected', cancellation: 'Cancelled', duplicate: 'Duplicate', success: 'Successful close', reopen: 'Reopened', 'void': 'Void audit entry' };
		var html = '<thead><tr><th>Event date and time</th><th>Case</th><th>Episode</th><th>Event</th><th>Details</th><th>Entered by</th><th>Recorded</th><th>Status and audit reason</th><th>Actions</th></tr></thead><tbody>';
		if (!rows.length) html += '<tr><td colspan="9" class="text-muted">No enquiry events are available in this reporting scope.</td></tr>';
		$.each(rows, function (_, row) {
			var status = row.event_type === 'void' ? 'Void audit entry' : (row.voided_at ? 'Voided on ' + row.voided_at + ' by ' + (row.voided_by || 'unknown') : (row.has_active_correction ? 'Corrected version recorded' : (row.corrects_event_id ? 'Correction version' : 'Active')));
			var auditReason = row.linked_void_reason || row.void_reason || row.replacement_reason || row.correction_reason || '';
			var details = [];
			if (row.response_code) details.push('Response: ' + row.response_code);
			if (row.reason_code) details.push('Reason: ' + row.reason_code);
			if (row.evidence_reference) details.push('Evidence: ' + row.evidence_reference);
			if (row.notes) details.push(row.notes);
			if (row.corrects_event_id) details.push('Corrects event #' + row.corrects_event_id);
			if (row.voids_event_id) details.push('Voids event #' + row.voids_event_id);
			var auditReasonHtml = auditReason ? '<br><span class="text-muted">' + escapeHtml(auditReason) + '</span>' : '';
			var actions = '';
			if (row.can_correct) actions += '<button type="button" class="btn btn-xs btn-default sp-correct-enquiry-event" data-event-id="' + escapeHtml(row.id) + '">Correct</button> ';
			if (row.can_void) actions += '<button type="button" class="btn btn-xs btn-link text-danger sp-void-enquiry-event" data-event-id="' + escapeHtml(row.id) + '">Void</button>';
			html += '<tr><td>' + escapeHtml(row.business_at) + '</td><td>' + escapeHtml(row.salescaseref) + '</td><td>' + escapeHtml(row.episode_no) + '</td>' +
				'<td>' + escapeHtml(labels[row.event_type] || row.event_type) + '</td><td>' + escapeHtml(details.join(' · ') || '—') + '</td>' +
				'<td>' + escapeHtml(row.entered_by_user_id || '—') + '</td><td>' + escapeHtml(row.recorded_at || '—') + '</td>' +
				'<td>' + escapeHtml(status) + auditReasonHtml + '</td><td class="sp-followup-actions">' + actions + '</td></tr>';
		});
		html += '</tbody>';
		$('#sp-enquiry-event-table').html(html);
	}

	function renderPoReceipts() {
		var rows = reportData.po_receipts || [];
		var html = '<thead><tr><th>Received</th><th>Case</th><th>Customer PO</th><th>Stated amount</th><th>Tax basis</th><th>ERP links and evidence</th><th>Entered by</th><th>Status and audit reason</th><th>Actions</th></tr></thead><tbody>';
		if (!rows.length) html += '<tr><td colspan="9" class="text-muted">No customer PO receipts are available in this reporting scope.</td></tr>';
		$.each(rows, function (_, row) {
			var status = row.receipt_status === 'void' ? 'Voided on ' + (row.voided_at || 'unknown date') + ' by ' + (row.voided_by || 'unknown') :
				(row.receipt_status === 'corrected' ? 'Corrected on ' + (row.voided_at || 'unknown date') + ' by ' + (row.voided_by || 'unknown') : (row.correction_of_id ? 'Correction version' : 'Active'));
			var auditReason = row.void_reason || row.correction_reason || '';
			var links = [];
			$.each(row.bridges || [], function (_, bridge) { links.push(bridge.source_type + ': ' + bridge.source_key); });
			if (row.evidence_reference) links.push('Evidence: ' + row.evidence_reference);
			var amount = row.stated_amount === null ? '—' : formatDecimal(row.stated_amount) + ' ' + (row.currency || '');
			var actions = '';
			if (row.can_correct) actions += '<button type="button" class="btn btn-xs btn-default sp-correct-po-receipt" data-receipt-id="' + escapeHtml(row.id) + '">Correct</button> ';
			if (row.can_void) actions += '<button type="button" class="btn btn-xs btn-link text-danger sp-void-po-receipt" data-receipt-id="' + escapeHtml(row.id) + '">Void</button>';
			html += '<tr><td>' + escapeHtml(row.received_at) + '</td><td>' + escapeHtml(row.salescaseref || '—') + '</td>' +
				'<td>' + escapeHtml(row.customer_reference_original) + '</td><td>' + escapeHtml(amount) + '</td><td>' + escapeHtml(row.tax_basis) + '</td>' +
				'<td>' + escapeHtml(links.join(' · ') || '—') + '</td><td>' + escapeHtml(row.entered_by_user_id || '—') + '</td>' +
				'<td>' + escapeHtml(status) + (auditReason ? '<br><span class="text-muted">' + escapeHtml(auditReason) + '</span>' : '') + '</td>' +
				'<td class="sp-followup-actions">' + actions + '</td></tr>';
		});
		html += '</tbody>';
		$('#sp-po-receipt-table').html(html);
	}

	function renderAdjustments() {
		var canApprove = String(app.data('can-approve-adjustments')) === '1';
		var canRequest = String(app.data('can-manage-adjustments')) === '1';
		var pending = reportData.pending_adjustments || [];
		var history = reportData.adjustment_history || [];
		var metricNames = { quotation: 'Quotation', purchase_order: 'OC proxy', delivery_challan: 'Delivery challan' };
		function adjustmentLabel(item) {
			var name = metricNames[item.metric_key] || item.metric_key;
			return item.reversal_of_id ? 'Reversal: ' + name : name;
		}
		var html = '';
		if (pending.length) {
			html += '<h4>Waiting for review</h4><div class="table-responsive sp-adjustment-list"><table class="table table-bordered sp-table"><thead><tr><th>Measure</th><th>Document key</th><th>Exclusion / reversal amount</th><th>Reason</th><th>Requested by</th><th>Requested</th>' +
				(canApprove ? '<th>Review</th>' : '') + '</tr></thead><tbody>';
			$.each(pending, function (_, item) {
				html += '<tr><td>' + escapeHtml(adjustmentLabel(item)) + '</td><td>' + escapeHtml(item.source_key) + '</td><td>' + formatMoney(item.excluded_amount) + '</td>' +
					'<td class="sp-adjustment-reason">' + escapeHtml(item.reason) + '</td><td>' + escapeHtml(item.requested_by) + '</td><td>' + escapeHtml(item.requested_at) + '</td>';
				if (canApprove) {
					html += '<td class="sp-adjustment-actions"><button class="btn btn-xs btn-success sp-review-adjustment" data-id="' + escapeHtml(item.id) + '" data-decision="approved">Approve</button>' +
						'<button class="btn btn-xs btn-danger sp-review-adjustment" data-id="' + escapeHtml(item.id) + '" data-decision="rejected">Reject</button></td>';
				}
				html += '</tr>';
			});
			html += '</tbody></table></div>';
		} else {
			html += '<p class="text-muted">No adjustment requests are waiting for review.</p>';
		}
		if (history.length) {
			html += '<h4>Recent adjustment history</h4><div class="table-responsive sp-adjustment-list"><table class="table table-bordered sp-table"><thead><tr><th>Measure</th><th>Document key</th><th>Amount</th><th>Status</th><th>Reason</th><th>Requested by</th><th>Reviewed by</th><th>Review note</th>' +
				(canRequest ? '<th>Action</th>' : '') + '</tr></thead><tbody>';
			$.each(history, function (_, item) {
				html += '<tr><td>' + escapeHtml(adjustmentLabel(item)) + '</td><td>' + escapeHtml(item.source_key) + '</td><td>' + formatMoney(item.excluded_amount) + '</td>' +
					'<td>' + escapeHtml(item.status) + '</td><td>' + escapeHtml(item.reason) + '</td><td>' + escapeHtml(item.requested_by) + '</td>' +
					'<td>' + escapeHtml(item.reviewed_by || '—') + '</td><td>' + escapeHtml(item.review_note || '—') + '</td>';
				if (canRequest) html += '<td>' + (String(item.can_reverse) === '1' ? '<button class="btn btn-xs btn-default sp-reverse-adjustment" data-id="' + escapeHtml(item.id) + '">Request reversal</button>' : '—') + '</td>';
				html += '</tr>';
			});
			html += '</tbody></table></div>';
		}
		$('#sp-adjustment-queue').html(html);
	}

	function coverageSnapshotDetails(snapshotJson) {
		if (!snapshotJson) return '<span class="text-muted">No review snapshot is stored.</span>';
		var snapshot;
		try { snapshot = JSON.parse(snapshotJson); } catch (ignore) { return '<span class="text-muted">Saved review snapshot could not be read.</span>'; }
		var metrics = snapshot && snapshot.metrics && snapshot.metrics.metrics ? snapshot.metrics.metrics : {};
		var rows = '';
		$.each(metrics, function (key, item) {
			var value = item.value_status === 'unavailable' ? 'Unavailable' : amountText(item, 'normalized_value');
			rows += '<li>' + escapeHtml(metricLabels[key] || key) + ': ' + escapeHtml(formatNumber(item.count)) + ' · ' + escapeHtml(value) + '</li>';
		});
		if (snapshot && snapshot.metrics && snapshot.metrics.unanswered_new_count !== null && typeof snapshot.metrics.unanswered_new_count !== 'undefined') {
			rows += '<li>New unanswered enquiries: ' + escapeHtml(formatNumber(snapshot.metrics.unanswered_new_count)) + ' · captured-event coverage incomplete</li>';
		}
		var context = snapshot && snapshot.context ? snapshot.context : {};
		return '<details><summary>Review from ' + escapeHtml(context.period_from || '') + ' to ' + escapeHtml(context.period_through || '') + '</summary><small>Cutoff ' + escapeHtml(context.business_cutoff || '') + ' · Calendar ' + escapeHtml(context.calendar_version || '') + '</small><ul>' + rows + '</ul></details>';
	}

	function renderAdminSetup() {
		if (!$('#sp-reference-card').length) return;
		var memberships = reportData && reportData.team_memberships ? reportData.team_memberships : [];
		var teamChanges = reportData && reportData.team_membership_history ? reportData.team_membership_history : [];
		var teamChangesById = {};
		$.each(teamChanges, function (_, item) {
			var id = String(item.membership_id);
			if (!teamChangesById[id]) teamChangesById[id] = [];
			teamChangesById[id].push(item);
		});
		var teamHtml = '<thead><tr><th>Salesperson</th><th>Team</th><th>Effective dates</th><th>Evidence</th><th>Recorded by</th><th>History</th></tr></thead><tbody>';
		if (!memberships.length) teamHtml += '<tr><td colspan="6" class="text-muted">No effective membership intervals have been entered.</td></tr>';
		$.each(memberships, function (_, item) {
			var changesForItem = teamChangesById[String(item.id)] || [];
			var historyHtml = '<details><summary>' + changesForItem.length + ' audit event' + (changesForItem.length === 1 ? '' : 's') + '</summary><ul>';
			$.each(changesForItem, function (_, change) {
				historyHtml += '<li>' + escapeHtml(change.changed_at) + ' · ' + escapeHtml(change.action_type) + ' · ' + escapeHtml(change.changed_by) +
					(change.previous_valid_to || change.new_valid_to ? ' · end ' + escapeHtml(change.previous_valid_to || 'open') + ' → ' + escapeHtml(change.new_valid_to || 'open') : '') + '</li>';
			});
			historyHtml += '</ul></details>';
			teamHtml += '<tr><td>' + escapeHtml(item.salesmanname || item.salesmancode) + '<br><small>' + escapeHtml(item.salesmancode) + '</small></td><td>' + escapeHtml(item.team_name || item.team_id) + '</td>' +
				'<td>' + escapeHtml(item.valid_from) + ' to ' + escapeHtml(item.valid_to || 'ongoing') + '</td><td>' + escapeHtml(item.provenance) + '</td><td>' + escapeHtml(item.recorded_by) + ' · ' + escapeHtml(item.recorded_at) + '</td><td>' + historyHtml + '</td></tr>';
		});
		$('#sp-team-membership-table').html(teamHtml + '</tbody>');

		var namedScopes = reportData && reportData.named_reporting_scopes ? reportData.named_reporting_scopes : [];
		var namedScopeHistory = reportData && reportData.named_reporting_scope_history ? reportData.named_reporting_scope_history : [];
		var scopeTeams = reportData && reportData.named_reporting_scope_teams ? reportData.named_reporting_scope_teams : [];
		var scopeTeamNames = {};
		$.each(scopeTeams, function (_, team) { scopeTeamNames[String(team.id)] = team.name; });
		var latestScope = namedScopes.length ? namedScopes[0] : null;
		if (latestScope) {
			var selectedScopeTeams = {};
			$.each(String(latestScope.team_ids_text || '').split(','), function (_, teamId) { if (teamId) selectedScopeTeams[String(teamId)] = true; });
			$('#sp-named-scope-form [name="scope_label"]').val(latestScope.label || '');
			$('#sp-named-scope-form [name="team_ids[]"]').each(function () { $(this).prop('checked', !!selectedScopeTeams[String($(this).val())]); });
			$('#sp-named-scope-form [name="include_unassigned"]').prop('checked', String(latestScope.include_unassigned) === '1');
		}
		var scopeHistoryByVersion = {};
		$.each(namedScopeHistory, function (_, item) {
			var version = String(item.scope_version);
			if (!scopeHistoryByVersion[version]) scopeHistoryByVersion[version] = [];
			scopeHistoryByVersion[version].push(item);
		});
		var namedScopeHtml = '<thead><tr><th>Version</th><th>Population</th><th>Included teams</th><th>Unassigned</th><th>Status</th><th>Proposed by</th><th>Audit / review</th></tr></thead><tbody>';
		if (!namedScopes.length) namedScopeHtml += '<tr><td colspan="7" class="text-muted">The named population seed is not available. Apply the base reporting migration.</td></tr>';
		$.each(namedScopes, function (_, item) {
			var teamNames = [];
			$.each(String(item.team_ids_text || '').split(','), function (_, teamId) {
				if (teamId) teamNames.push(scopeTeamNames[String(teamId)] || ('Team ' + teamId));
			});
			var historyHtml = '<details><summary>' + ((scopeHistoryByVersion[String(item.scope_version)] || []).length) + ' audit event(s)</summary><ul>';
			$.each(scopeHistoryByVersion[String(item.scope_version)] || [], function (_, change) {
				historyHtml += '<li>' + escapeHtml(change.changed_at) + ' · ' + escapeHtml(change.action_type) + ' · ' + escapeHtml(change.previous_validation_status || 'new') + ' → ' + escapeHtml(change.new_validation_status) +
					' · ' + escapeHtml(change.changed_by) + ' · ' + escapeHtml(change.change_note || '') + '</li>';
			});
			historyHtml += '</ul></details>';
			if (item.can_approve) historyHtml += '<div class="sp-scope-approval"><input class="form-control sp-scope-review-note" type="text" maxlength="1000" placeholder="Independent review note"><button type="button" class="btn btn-default sp-approve-named-scope" data-scope-version="' + escapeHtml(item.scope_version) + '">Approve mapping</button></div>';
			namedScopeHtml += '<tr><td>v' + escapeHtml(item.scope_version) + '</td><td>' + escapeHtml(item.label) + '</td><td>' + escapeHtml(teamNames.length ? teamNames.join(', ') : 'No teams configured') + '</td>' +
				'<td>' + (String(item.include_unassigned) === '1' ? 'Included' : 'Excluded') + '</td><td>' + escapeHtml(item.validation_status.replace(/_/g, ' ')) + '</td>' +
				'<td>' + escapeHtml(item.updated_by || '') + '<br><small>' + escapeHtml(item.updated_at || '') + '</small></td><td>' + historyHtml + '</td></tr>';
		});
		$('#sp-named-scope-table').html(namedScopeHtml + '</tbody>');

		var calendar = reportData && reportData.calendar_overrides ? reportData.calendar_overrides : [];
		var calendarHistory = reportData && reportData.calendar_history ? reportData.calendar_history : [];
		$('#sp-calendar-version').text(reportData && reportData.calendar_version ? '· ' + reportData.calendar_version : '');
		var calendarHistoryById = {};
		$.each(calendarHistory, function (_, item) {
			var id = String(item.business_date);
			if (!calendarHistoryById[id]) calendarHistoryById[id] = [];
			calendarHistoryById[id].push(item);
		});
		var calendarHtml = '<thead><tr><th>Date</th><th>Type</th><th>Reason</th><th>Recorded by</th><th>History</th><th></th></tr></thead><tbody>';
		if (!calendar.length) calendarHtml += '<tr><td colspan="6" class="text-muted">No date overrides. The default Monday–Saturday calendar is active.</td></tr>';
		$.each(calendar, function (_, item) {
			var changesForDay = calendarHistoryById[String(item.business_date)] || [];
			var historyHtml = '<details><summary>' + changesForDay.length + ' audit event' + (changesForDay.length === 1 ? '' : 's') + '</summary><ul>';
			$.each(changesForDay, function (_, change) {
				historyHtml += '<li>' + escapeHtml(change.changed_at) + ' · ' + (change.previous_workday === null ? 'new date' : (String(change.previous_workday) === '1' ? 'workday' : 'non-working day')) +
					' → ' + (String(change.new_workday) === '1' ? 'workday' : 'non-working day') + ' · ' + escapeHtml(change.new_reason) + ' · ' + escapeHtml(change.changed_by) + '</li>';
			});
			historyHtml += '</ul></details>';
			calendarHtml += '<tr><td>' + escapeHtml(item.business_date) + '</td><td>' + (String(item.planned_workday) === '1' ? 'Workday' : 'Non-working day') + '</td><td>' + escapeHtml(item.override_reason) + '</td>' +
				'<td>' + escapeHtml(item.recorded_by) + ' · ' + escapeHtml(item.recorded_at) + '</td><td>' + historyHtml + '</td><td><button type="button" class="btn btn-xs btn-default sp-edit-calendar" data-date="' + escapeHtml(item.business_date) + '" data-workday="' + escapeHtml(item.planned_workday) + '" data-reason="' + escapeHtml(item.override_reason) + '">Edit</button></td></tr>';
		});
		$('#sp-calendar-table').html(calendarHtml + '</tbody>');

		var coverageSubmissions = reportData && reportData.coverage_submissions ? reportData.coverage_submissions : [];
		var coverageHistory = reportData && reportData.coverage_history ? reportData.coverage_history : [];
		var coverageHistoryById = {};
		$.each(coverageHistory, function (_, item) {
			var id = String(item.coverage_id);
			if (!coverageHistoryById[id]) coverageHistoryById[id] = [];
			coverageHistoryById[id].push(item);
		});
		var coverageHtml = '<thead><tr><th>Date / scope</th><th>Status</th><th>Evidence / unresolved items</th><th>Reviewed figures</th><th>Submitted by</th><th>Audit history</th></tr></thead><tbody>';
		if (!coverageSubmissions.length) coverageHtml += '<tr><td colspan="6" class="text-muted">No coverage submissions have been recorded.</td></tr>';
		$.each(coverageSubmissions, function (_, item) {
			var itemHistory = coverageHistoryById[String(item.id)] || [];
			var historyHtml = '<details><summary>' + itemHistory.length + ' audit event' + (itemHistory.length === 1 ? '' : 's') + '</summary><ul>';
			$.each(itemHistory, function (_, change) {
				historyHtml += '<li>' + escapeHtml(change.changed_at) + ' · ' + escapeHtml(change.action_type) + ' · ' +
					(change.previous_state ? escapeHtml(change.previous_state) + ' → ' : '') + escapeHtml(change.new_state) + ' · by ' + escapeHtml(change.changed_by) +
					(change.previous_submitted_at ? '<br><small>Submitted: ' + escapeHtml(change.previous_submitted_at) + ' → ' + escapeHtml(change.new_submitted_at) + ' · reporter ' + escapeHtml(change.previous_reporter_user_id || '') + ' → ' + escapeHtml(change.new_reporter_user_id || '') + '</small>' : '<br><small>Submitted: ' + escapeHtml(change.new_submitted_at) + ' · reporter ' + escapeHtml(change.new_reporter_user_id || '') + '</small>') +
					(change.previous_note ? '<br><small>Before: ' + escapeHtml(change.previous_note) + '</small>' : '') +
					(change.new_note ? '<br><small>After: ' + escapeHtml(change.new_note) + '</small>' : '') +
					(change.previous_review_snapshot_json ? '<br><small>Prior reviewed figures: ' + coverageSnapshotDetails(change.previous_review_snapshot_json) + '</small>' : '') +
					'<br>' + coverageSnapshotDetails(change.new_review_snapshot_json) + '</li>';
			});
			historyHtml += '</ul></details>';
			coverageHtml += '<tr><td>' + escapeHtml(item.coverage_date) + '<br><small>' + escapeHtml(item.scope_key) + (item.team_name ? ' · ' + escapeHtml(item.team_name) : '') + '</small></td>' +
				'<td>' + escapeHtml(item.coverage_state) + '</td><td>' + escapeHtml(item.note || item.evidence_reference || '') + '</td><td>' + coverageSnapshotDetails(item.review_snapshot_json) + '</td>' +
				'<td>' + escapeHtml(item.reporter_user_id || '') + ' · ' + escapeHtml(item.submitted_at || '') + '</td><td>' + historyHtml + '</td></tr>';
		});
		$('#sp-coverage-table').html(coverageHtml + '</tbody>');

		var observations = reportData && reportData.reference_observations ? reportData.reference_observations : [];
		var reconciliations = reportData && reportData.reconciliations ? reportData.reconciliations : [];
		var changes = reportData && reportData.reconciliation_history ? reportData.reconciliation_history : [];
		var referenceChanges = reportData && reportData.reference_observation_history ? reportData.reference_observation_history : [];
		var referenceHistoryById = {};
		$.each(referenceChanges, function (_, item) {
			var id = String(item.observation_id);
			if (!referenceHistoryById[id]) referenceHistoryById[id] = [];
			referenceHistoryById[id].push(item);
		});
		var html = '<thead><tr><th>Source / observation</th><th>Period</th><th>Scope</th><th>Measure</th><th>Count</th><th>Value / basis</th><th>Verification</th><th>Evidence review</th><th>Actions</th></tr></thead><tbody>';
		if (!observations.length) html += '<tr><td colspan="9" class="text-muted">No reference observations imported yet.</td></tr>';
		$.each(observations, function (_, item) {
			var value = item.observed_value === null ? '—' : formatDecimal(item.observed_value) + ' ' + (item.value_currency || '') + ' ' + (item.value_basis || '');
			var scope = item.scope_key || 'Unspecified';
			if (item.team_name) scope += ' · ' + item.team_name;
			if (item.salesperson_name) scope += ' · ' + item.salesperson_name;
			var reviewHistory = referenceHistoryById[String(item.id)] || [];
			var reviewHtml = '<span class="text-muted">' + escapeHtml(item.verification_note || 'Review the cited source before comparing as verified.') + '</span><br><select class="form-control input-sm sp-reference-verification"><option value="source_verified"' + (item.verification_state === 'source_verified' ? ' selected' : '') + '>Source verified</option><option value="disputed"' + (item.verification_state === 'disputed' ? ' selected' : '') + '>Disputed</option></select>' +
				'<input class="form-control input-sm sp-reference-review-note" maxlength="2000" value="' + escapeHtml(item.verification_note || '') + '" placeholder="Evidence checked / discrepancy">';
			if (reviewHistory.length) {
				reviewHtml += '<details><summary>' + reviewHistory.length + ' review change' + (reviewHistory.length === 1 ? '' : 's') + '</summary><ul>';
				$.each(reviewHistory, function (_, change) { reviewHtml += '<li>' + escapeHtml(change.changed_at) + ' · ' + escapeHtml(change.previous_state) + ' → ' + escapeHtml(change.new_state) + ' · ' + escapeHtml(change.changed_by) + ' · ' + escapeHtml(change.review_note) + '</li>'; });
				reviewHtml += '</ul></details>';
			}
			html += '<tr data-reference-id="' + escapeHtml(item.id) + '"><td>' + escapeHtml(item.source_name) + '<br><small>' + escapeHtml(item.observation_key) + ' · ' + escapeHtml(item.source_version) + '</small><br><small>Evidence: ' + escapeHtml(item.evidence_location) + ' · CSV SHA-256 ' + escapeHtml(String(item.source_sha256 || '').slice(0, 12)) + '</small>' + (item.notes ? '<br><small>' + escapeHtml(item.notes) + '</small>' : '') + '</td>' +
				'<td>' + escapeHtml(item.period_from) + ' to ' + escapeHtml(item.period_through) + '<br><small>' + escapeHtml(item.granularity) + '</small></td>' +
				'<td>' + escapeHtml(scope) + '</td><td>' + escapeHtml(metricLabels[item.metric_key] || item.metric_key) + '</td>' +
				'<td>' + (item.observed_count === null ? '—' : escapeHtml(formatDecimal(item.observed_count))) + '</td><td>' + escapeHtml(value) + '</td>' +
				'<td>' + escapeHtml(item.verification_state) + '</td><td>' + reviewHtml + '</td><td><button type="button" class="btn btn-xs btn-primary sp-review-reference" data-id="' + escapeHtml(item.id) + '">Save review</button> ' +
				'<button type="button" class="btn btn-xs btn-default sp-select-reconciliation" data-reference-id="' + escapeHtml(item.id) + '">Compare</button></td></tr>';
		});
		html += '</tbody>';
		$('#sp-reference-table').html(html);

		var historyById = {};
		$.each(changes, function (_, item) {
			var id = String(item.reconciliation_id);
			if (!historyById[id]) historyById[id] = [];
			historyById[id].push(item);
		});
		var register = '';
		if (!reconciliations.length) {
			register = '<p class="text-muted">No discrepancies have been registered.</p>';
		} else {
			register = '<div class="table-responsive"><table class="table table-striped sp-table"><thead><tr><th>Reference / period</th><th>Measure</th><th>ERP count / value</th><th>Reference count / value</th><th>Difference</th><th>Run / class</th><th>Status / owner / resolution</th><th>History</th></tr></thead><tbody>';
			$.each(reconciliations, function (_, item) {
				var recordedBasis = {};
				try { recordedBasis = JSON.parse(item.source_basis || '{}'); } catch (ignore) { recordedBasis = {}; }
				var basisLabel = recordedBasis.value_basis ? ((recordedBasis.value_currency || '') + ' ' + recordedBasis.value_basis) : 'Count comparison';
				var comparisonLabel = item.reference_scope_key === 'team' ? ('Team · ' + (item.team_name || 'Unknown team')) : (item.reference_scope_key === 'salesperson' ? ('Salesperson · ' + (item.salesperson_name || 'Unknown salesperson')) : 'Company-wide');
				var erp = 'Count: ' + (item.erp_count === null ? '—' : formatDecimal(item.erp_count)) + '<br>Value: ' + (item.erp_value === null ? '—' : 'PKR ' + formatDecimal(item.erp_value));
				var reference = 'Count: ' + (item.reference_count === null ? '—' : formatDecimal(item.reference_count)) + '<br>Value: ' + (item.reference_value === null ? '—' : 'PKR ' + formatDecimal(item.reference_value));
				var difference = 'Count: ' + (item.difference_count === null ? '—' : formatDecimal(item.difference_count)) + '<br>Value: ' + (item.difference_value === null ? '—' : 'PKR ' + formatDecimal(item.difference_value));
				var id = String(item.id);
				register += '<tr data-reconciliation-id="' + escapeHtml(id) + '"><td>' + escapeHtml(item.source_name || 'Reference') + '<br><small>' + escapeHtml(item.observation_key || '') + ' · ' + escapeHtml(item.period_from) + ' to ' + escapeHtml(item.period_through) + '</small><br><small>' + escapeHtml(comparisonLabel) + '</small></td>' +
					'<td>' + escapeHtml(metricLabels[item.metric_key] || item.metric_key) + '</td><td>' + erp + '</td><td>' + reference + '</td><td>' + difference + '</td><td>' + escapeHtml(item.run_key || 'No saved run') + '<br><small>' + escapeHtml(item.classification) + ' · ' + escapeHtml(basisLabel) + '</small></td>' +
					'<td><select class="form-control input-sm sp-recon-status"><option value="open"' + (item.status === 'open' ? ' selected' : '') + '>Open</option><option value="in_review"' + (item.status === 'in_review' ? ' selected' : '') + '>In review</option><option value="resolved"' + (item.status === 'resolved' ? ' selected' : '') + '>Resolved</option><option value="closed"' + (item.status === 'closed' ? ' selected' : '') + '>Closed</option></select>' +
					'<select class="form-control input-sm sp-recon-classification"><option value="unresolved">Unresolved</option><option value="rounding">Rounding</option><option value="source_variance">Source variance</option><option value="definition_difference">Definition difference</option><option value="data_quality">Data quality</option><option value="explained">Explained</option></select>' +
					'<input class="form-control input-sm sp-recon-owner" maxlength="50" value="' + escapeHtml(item.owner_user_id || '') + '" placeholder="Owner user ID">' +
					'<input class="form-control input-sm sp-recon-resolution" maxlength="2000" value="' + escapeHtml(item.resolution_note || '') + '" placeholder="Resolution note (required to resolve)">' +
					'<button type="button" class="btn btn-xs btn-primary sp-save-reconciliation" data-id="' + escapeHtml(id) + '">Save</button><span class="sp-recon-message" aria-live="polite"></span></td><td>' + escapeHtml(item.created_at) + '</td></tr>';
				var itemHistory = historyById[id] || [];
				var auditHtml = '<details><summary>' + itemHistory.length + ' recorded change' + (itemHistory.length === 1 ? '' : 's') + '</summary><ul>';
				$.each(itemHistory, function (_, change) {
					auditHtml += '<li>' + escapeHtml(change.changed_at) + ' · ' + escapeHtml(change.action_type) + ' · ' + escapeHtml(change.status) + ' · ' + escapeHtml(change.classification) + ' · ' + escapeHtml(change.changed_by) +
						(change.owner_user_id ? ' · owner ' + escapeHtml(change.owner_user_id) : '') + (change.resolution_note ? ' · ' + escapeHtml(change.resolution_note) : '') + '</li>';
				});
				auditHtml += '</ul></details>';
				register += '<tr class="sp-recon-history"><td colspan="8">' + auditHtml + '</td></tr>';
			});
			register += '</tbody></table></div>';
		}
		$('#sp-reconciliation-register').html(register);
		$.each(reconciliations, function (_, item) {
			$('#sp-reconciliation-register tr[data-reconciliation-id="' + String(item.id).replace(/"/g, '') + '"] .sp-recon-classification').val(item.classification);
		});
		updateCoverageReview();
	}

	function coverageMetricsFor(date, teamId) {
		if (!reportData || !reportData.current) return null;
		if (!teamId) {
			for (var i = 0; i < reportData.current.daily.length; i++) {
				if (reportData.current.daily[i].date === date) return { metrics: reportData.current.daily[i].metrics, unanswered_new_count: reportData.current.daily[i].unanswered_new_count };
			}
			return null;
		}
		for (var teamIndex = 0; teamIndex < (reportData.current.teams || []).length; teamIndex++) {
			var team = reportData.current.teams[teamIndex];
			if (String(team.id) !== String(teamId)) continue;
			for (var weekIndex = 0; weekIndex < (team.weeks || []).length; weekIndex++) {
				var days = team.weeks[weekIndex].days || {};
				for (var weekday in days) {
					if (Object.prototype.hasOwnProperty.call(days, weekday) && days[weekday].date === date) return { metrics: days[weekday].metrics, unanswered_new_count: null };
				}
			}
		}
		return null;
	}

	function updateCoverageReview() {
		var form = $('#sp-coverage-form');
		if (!form.length) return;
		var date = form.find('[name="coverage_date"]').val();
		var teamId = form.find('[name="team_id"]').val();
		var options = reportData && reportData.coverage_review_options ? reportData.coverage_review_options : [];
		var option = null;
		$.each(options, function (_, candidate) {
			var sameTeam = teamId ? String(candidate.team_id) === String(teamId) : candidate.team_id === null;
			if (candidate.date === date && candidate.scope_key === 'erp_company' && sameTeam) { option = candidate; return false; }
		});
		var button = $('#sp-coverage-submit');
		if (!option) {
			form.find('[name="coverage_review_metrics_json"], [name="coverage_review_context_json"], [name="coverage_review_signature"]').val('');
			button.prop('disabled', true).text('Review report first');
			$('#sp-coverage-review-preview').html('<p class="text-muted">Load an unfiltered full-company report that includes this date, then review the displayed totals before submitting coverage. Frozen runs and filtered reports cannot be used for a new coverage submission.</p>');
			return;
		}
		var metrics = coverageMetricsFor(date, teamId);
		if (!metrics || !metrics.metrics) {
			button.prop('disabled', true).text('Review report first');
			return;
		}
		var metricsJson = JSON.stringify(metrics);
		form.find('[name="coverage_review_metrics_json"]').val(metricsJson);
		form.find('[name="coverage_review_context_json"]').val(option.context_json);
		form.find('[name="coverage_review_signature"]').val(option.signature);
		var html = '<h4>Calculated report figures for ' + escapeHtml(date) + (teamId ? ' · selected team' : ' · whole company') + '</h4>' +
			'<p>Generated at ' + escapeHtml(new Date(Number(JSON.parse(option.context_json).issued_at) * 1000).toLocaleString()) + '. These figures use provisional ERP definitions. Confirm them and add an evidence or unresolved-items note.</p>' +
			'<div class="table-responsive"><table class="table table-condensed sp-table"><thead><tr><th>Measure</th><th>Count</th><th>Normalized value</th><th>Value status</th></tr></thead><tbody>';
		$.each(metrics.metrics, function (key, item) {
			var value = item.value_status === 'unavailable' ? 'Unavailable' : amountText(item, 'normalized_value');
			html += '<tr><td>' + escapeHtml(metricLabels[key] || key) + '</td><td>' + escapeHtml(formatNumber(item.count)) + '</td><td>' + escapeHtml(value) + '</td><td>' + escapeHtml(item.value_status) + '</td></tr>';
		});
		if (metrics.unanswered_new_count !== null) html += '<tr><td>New unanswered enquiries</td><td>' + escapeHtml(formatNumber(metrics.unanswered_new_count)) + '</td><td>Not applicable</td><td>Captured-event coverage incomplete</td></tr>';
		html += '</tbody></table></div>';
		$('#sp-coverage-review-preview').html(html);
		button.prop('disabled', false).text('Submit reviewed coverage');
	}

	function renderAll() {
		renderNamedScopeFilter();
		renderMeta();
		renderKpis();
		renderDaily();
		renderPeriodTable();
		renderMonthChart();
		renderWeekly();
		renderTeams();
		renderFollowups();
		renderEnquiryEvents();
		renderPoReceipts();
		renderAdjustments();
		renderAdminSetup();
		$('#sp-loading').hide();
		$('#sp-report-content').show();
	}

	function loadReport() {
		clearMessage();
		$('#sp-loading').show();
		$('#sp-report-content').hide();
		$.getJSON(apiUrl, currentFilters())
			.done(function (response) {
				if (response.status !== 'success') {
					showMessage(response.message || 'The report could not be loaded.');
					$('#sp-loading').hide();
					return;
				}
				reportData = response;
				renderAll();
			})
			.fail(function (xhr) {
				var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'The report could not be loaded. Confirm the reporting database setup and try again.';
				showMessage(message);
				$('#sp-loading').hide();
			});
	}

	function createFrozenRun(callback) {
		clearMessage();
		var filters = currentFilters();
		var request = $.extend({}, filters, { action: 'create_run', FormID: app.data('form-id') });
		$.ajax({ url: apiUrl + '?' + $.param(filters), method: 'POST', data: request, dataType: 'json' })
			.done(function (response) {
				if (response.status !== 'success' || !response.frozen_run) {
					showMessage(response.message || 'The frozen report could not be saved.');
					return;
				}
				reportData = response;
				$('#sp-run-key').val(response.frozen_run.run_key);
				renderAll();
				showMessage('Frozen report saved. Key: ' + response.frozen_run.run_key, 'success');
				if (callback) callback();
			})
			.fail(function (xhr) {
				showMessage(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'The frozen report could not be saved.');
			});
	}

	function loadFrozenRun() {
		var runKey = $.trim($('#sp-run-key').val() || '');
		if (!runKey) { showMessage('Enter a frozen report key to load.'); return; }
		clearMessage();
		$('#sp-loading').show();
		$('#sp-report-content').hide();
		$.getJSON(apiUrl, { action: 'get_run', run_key: runKey })
			.done(function (response) {
				if (response.status !== 'success') { showMessage(response.message || 'The frozen report could not be loaded.'); return; }
				reportData = response;
				$('#sp-run-key').val(response.frozen_run && response.frozen_run.run_key ? response.frozen_run.run_key : runKey);
				renderAll();
			})
			.fail(function (xhr) {
				showMessage(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'The frozen report could not be loaded.');
				$('#sp-loading').hide();
			});
	}

	function clearReferencePreview(message) {
		var form = $('#sp-reference-import-form');
		form.find('[name="preview_token"]').val('');
		$('#sp-reference-import').prop('disabled', true);
		$('#sp-reference-preview').empty();
		if (message) $('#sp-reference-import-message').text(message).css('color', '#667580');
	}

	function renderReferencePreview(response) {
		var rows = response.rows || [];
		var html = '<p><strong>' + escapeHtml(String(response.row_count)) + ' rows</strong> · ' + escapeHtml(String(response.new_count)) + ' new · ' + escapeHtml(String(response.duplicate_count)) + ' already imported · SHA-256 ' + escapeHtml(response.source_sha256 || '') + '. No rows have been imported yet.</p>';
		html += '<div class="table-responsive"><table class="table table-striped sp-table"><thead><tr><th>#</th><th>Observation</th><th>Period / cutoff</th><th>Scope</th><th>Metric</th><th>Count</th><th>Value / basis</th><th>Import status</th><th>Notes</th></tr></thead><tbody>';
		$.each(rows, function (index, row) {
			var scope = row.scope_key || 'Unspecified';
			if (row.team_name) scope += ' · ' + row.team_name;
			if (row.salesperson_name) scope += ' · ' + row.salesperson_name;
			var period = row.period_from + ' to ' + row.period_through + (row.cutoff_at ? ' · Cutoff ' + row.cutoff_at : '');
			var value = row.observed_value === null ? '—' : String(row.observed_value) + ' ' + (row.value_currency || '') + ' · ' + (row.value_basis || '');
			html += '<tr><td>' + (index + 1) + '</td><td>' + escapeHtml(row.observation_key) + '<br><small>' + escapeHtml(row.granularity) + '</small></td>' +
				'<td>' + escapeHtml(period) + '</td><td>' + escapeHtml(scope) + '</td><td>' + escapeHtml(metricLabels[row.metric_key] || row.metric_key) + '</td>' +
				'<td>' + escapeHtml(row.observed_count === null ? '—' : String(row.observed_count)) + '</td><td>' + escapeHtml(value) + '</td>' +
				'<td>' + (row.already_imported ? 'Already imported' : 'New · unverified') + '</td><td>' + escapeHtml(row.notes || '') + '</td></tr>';
		});
		$('#sp-reference-preview').html(html + '</tbody></table></div>');
	}

	function previewReferenceObservations(form) {
		clearReferencePreview('Checking the CSV. No data will be written during preview.');
		$('#sp-reference-preview-button').prop('disabled', true);
		$.ajax({ url: apiUrl, method: 'POST', data: form.serialize(), dataType: 'json' })
			.done(function (response) {
				if (response.status !== 'success' || !response.preview_token) {
					clearReferencePreview(response.message || 'The CSV preview could not be prepared.');
					return;
				}
				form.find('[name="preview_token"]').val(response.preview_token);
				renderReferencePreview(response);
				$('#sp-reference-import-message').text('Review every row and source detail below. The preview expires in 15 minutes.').css('color', '#246b39');
				$('#sp-reference-import').prop('disabled', false);
			})
			.fail(function (xhr) {
				var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'The CSV preview could not be prepared.';
				clearReferencePreview(message);
			})
			.always(function () { $('#sp-reference-preview-button').prop('disabled', false); });
	}

	function importReferenceObservations(form) {
		var tokenField = form.find('[name="preview_token"]');
		if (!tokenField.val()) { clearReferencePreview('Preview the exact source and CSV before importing.'); return; }
		form.find('[name="action"]').val('import_reference_observations');
		var data = form.serialize();
		form.find('[name="action"]').val('preview_reference_observations');
		$('#sp-reference-import').prop('disabled', true);
		$.ajax({ url: apiUrl, method: 'POST', data: data, dataType: 'json' })
			.done(function (response) {
				clearReferencePreview((response.message || 'Import complete.') + ' Added ' + response.inserted + '; deduplicated ' + response.duplicates + '. Saved observations remain unverified until source evidence is reviewed.');
				loadReport();
			})
			.fail(function (xhr) {
				var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'The reference observations could not be imported.';
				clearReferencePreview(message + ' Preview the CSV again before retrying.');
			});
	}

	function postForm(form, messageId) {
		var idempotency = form.find('[name="idempotency_key"]');
		if (idempotency.length && !idempotency.val()) idempotency.val(makeIdempotencyKey());
		return $.ajax({ url: apiUrl, method: 'POST', data: form.serialize(), dataType: 'json' })
			.done(function (response) {
				$('#' + messageId).text(response.message || 'Saved.').css('color', '#246b39');
				if (idempotency.length) {
					var dateField = form.find('[name="activity_date"], [name="event_date"], [name="received_date"]').first();
					var date = dateField.val();
					form[0].reset();
					dateField.val(date || defaultFilters.to);
					form.find('[name="activity_time"], [name="event_time"], [name="received_time"]').val(new Date().toTimeString().slice(0, 5));
					idempotency.val(makeIdempotencyKey());
					if (form.attr('id') === 'sp-enquiry-event-form') $('#sp-enquiry-event-submit').text('Record enquiry event');
					if (form.attr('id') === 'sp-po-receipt-form') {
						$('#sp-po-receipt-submit').text('Record PO receipt');
						form.find('[name="quotation_orderno"], [name="oc_orderno"], [name="oc_pono"]').prop('readonly', false);
					}
				}
				loadReport();
			})
			.fail(function (xhr) {
				var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'The change could not be saved.';
				$('#' + messageId).text(message).css('color', '#a94442');
			});
	}

	function makeIdempotencyKey() {
		if (window.crypto && window.crypto.randomUUID) return window.crypto.randomUUID();
		return 'sp-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2);
	}

	function loadSupportingDocuments(page, append) {
		if (!documentQuery) return;
		page = page || 1;
		$('#sp-documents-message').text('Loading supporting quotation records…');
		$('#sp-documents-more').hide();
		var request = $.extend({}, currentFilters(), {
			action: 'supporting_documents', metric: documentQuery.metric, date: documentQuery.date, page: page, page_size: 50
		});
		$.getJSON(apiUrl, request).done(function (response) {
			if (response.status !== 'success') {
				$('#sp-documents-message').text(response.message || 'Supporting records are unavailable.');
				return;
			}
			var rowsHtml = '';
			$.each(response.documents || [], function (_, fact) {
				var issueKind = fact.is_revision ? 'revised' : 'issued';
				var issueActions = String(app.data('can-capture-events')) === '1'
					? '<button type="button" class="btn btn-xs btn-primary sp-record-quote-issue" data-orderno="' + escapeHtml(fact.source_key) + '" data-kind="' + issueKind + '">Record sent</button> <button type="button" class="btn btn-xs btn-default sp-record-quote-issue" data-orderno="' + escapeHtml(fact.source_key) + '" data-kind="resent">Record resend</button>'
					: '—';
				rowsHtml += '<tr><th>Q#' + escapeHtml(fact.source_key) + '</th><td>' + escapeHtml(fact.business_at + ' · legacy document date') + '</td>' +
					'<td>' + escapeHtml(fact.salescaseref || '—') + '</td><td>' + escapeHtml(fact.credited_salesperson || 'Unassigned') + '</td>' +
				'<td>' + (fact.raw_amount === null ? 'Unavailable' : formatSourceCurrency(fact.raw_amount, fact.currency)) + '</td>' +
					'<td>' + (fact.net_amount === null ? 'Unavailable' : formatMoney(fact.net_amount)) + '</td>' +
					'<td>' + (fact.normalized_amount === null ? 'Unavailable' : formatMoney(fact.normalized_amount)) + '</td>' +
					'<td>' + escapeHtml(fact.value_status + ' · ' + fact.tax_basis) + '</td><td>' + issueActions + '</td></tr>';
			});
			if (!append) {
				var tableHtml = '<thead><tr><th>Quotation</th><th>Date basis</th><th>Case</th><th>Credited owner</th><th>Source price</th><th>Net without GST</th><th>Normalized</th><th>Value status</th><th>Issue capture</th></tr></thead><tbody>';
				if (!rowsHtml) rowsHtml = '<tr><td colspan="9" class="text-muted">No eligible quotation documents were found for this date and scope.</td></tr>';
				$('#sp-documents-table').html(tableHtml + rowsHtml + '</tbody>');
			} else if (rowsHtml) {
				$('#sp-documents-table tbody').append(rowsHtml);
			}
			$('#sp-documents-message').text('Showing ' + Math.min(response.total, page * response.page_size) + ' of ' + response.total + ' quotation documents. Values use the provisional source policy.');
			if (response.has_more) { documentPage = page; $('#sp-documents-more').show(); }
		}).fail(function (xhr) {
			$('#sp-documents-message').text(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Supporting records could not be loaded.');
		});
	}

	function downloadRunExport(format) {
		var run = reportData && reportData.frozen_run;
		if (!run || !run.run_key) {
			showMessage('Freeze or load a report before downloading it.');
			return;
		}
		var query = $.param({ action: 'export_run', run_key: run.run_key, format: format });
		window.location.href = apiUrl + '?' + query;
	}

	function exportCurrentRun(format) {
		if (reportData && reportData.frozen_run && reportData.frozen_run.run_key) {
			downloadRunExport(format);
			return;
		}
		createFrozenRun(function () { downloadRunExport(format); });
	}

	$('#sp-filter-form').on('submit', function (event) {
		event.preventDefault();
		var filters = currentFilters();
		if (!filters.from || !filters.to || !filters.business_cutoff || !filters.compare_from || !filters.compare_to || filters.from > filters.to || filters.business_cutoff < filters.from || filters.business_cutoff > filters.to || filters.compare_from > filters.compare_to) {
			showMessage('Choose valid reporting periods and a cutoff inside the current period.');
			return;
		}
		loadReport();
	});

	$('#sp-reset-filters').on('click', function () {
		$.each(defaultFilters, function (key, value) { $('#sp-filter-form [name="' + key + '"]').val(value); });
		$('#sp-population-filter').val('').trigger('change.select2');
		$('#sp-team-filter, #sp-salesperson-filter').val([]).trigger('change');
		loadReport();
	});
	$('#sp-population-filter').on('change', function () {
		if ($(this).val()) $('#sp-team-filter, #sp-salesperson-filter').val([]).trigger('change.select2');
	});
	$('#sp-team-filter, #sp-salesperson-filter').on('change', function () {
		if ($(this).val() && $(this).val().length) $('#sp-population-filter').val('').trigger('change.select2');
	});
	$('#sp-filter-form [name="to"]').on('change', function () {
		var cutoff = $('#sp-filter-form [name="business_cutoff"]');
		if (cutoff.val() > this.value) cutoff.val(this.value);
	});
	$('#sp-filter-form [name="from"]').on('change', function () {
		var cutoff = $('#sp-filter-form [name="business_cutoff"]');
		if (cutoff.val() < this.value) cutoff.val(this.value);
	});

	$('#sp-freeze-run').on('click', function () { createFrozenRun(); });
	$('#sp-export-csv').on('click', function () { exportCurrentRun('csv'); });
	$('#sp-export-xlsx').on('click', function () { exportCurrentRun('xlsx'); });
	$('#sp-export-pdf').on('click', function () { exportCurrentRun('pdf'); });
	$('#sp-load-run').on('click', loadFrozenRun);
	$('#sp-matrix-team, #sp-matrix-metric, #sp-matrix-value').on('change', renderMatrix);
	$('a[data-toggle="tab"]').on('shown.bs.tab', function () {
		if (window.Highcharts) {
			$.each(Highcharts.charts, function (_, chart) { if (chart) chart.reflow(); });
		}
	});
	$('#sp-followup-form').on('submit', function (event) {
		event.preventDefault();
		postForm($(this), 'sp-followup-message');
	});
	$(document).on('click', '.sp-correct-followup', function () {
		var id = String($(this).data('followup-id'));
		var rows = reportData.followups || [];
		var row = null;
		$.each(rows, function (_, item) { if (String(item.id) === id) row = item; });
		if (!row || !row.can_correct) return;
		var reason = window.prompt('Why does this follow-up need correction?');
		if (!reason || !reason.replace(/\s/g, '')) return;
		var stamp = String(row.activity_at || row.activity_date || '').split(' ');
		var form = $('#sp-followup-form');
		form.find('[name="corrected_from_id"]').val(id);
		form.find('[name="idempotency_key"]').val(makeIdempotencyKey());
		form.find('[name="correction_reason"]').val(reason);
		form.find('[name="activity_date"]').val(stamp[0] || '');
		form.find('[name="activity_time"]').val(stamp[1] ? stamp[1].slice(0, 5) : '09:00');
		form.find('[name="salescaseref"]').val(row.salescaseref || '');
		form.find('[name="quotation_orderno"]').val(row.quotation_orderno || '');
		form.find('[name="activity_type"]').val(row.activity_type || 'other');
		form.find('[name="outcome"]').val(row.outcome || '');
		form.find('[name="next_action"]').val(row.next_action || '');
		form.find('[name="next_action_date"]').val(row.next_action_date || '');
		form.find('[name="notes"]').val(row.notes || '');
		$('#sp-followup-message').text('Review the prefilled details and save to replace the prior version. The prior record will be retained as corrected history.').css('color', '#665c25');
		form[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
		form.find('[name="activity_date"]').trigger('focus');
	});
	$(document).on('click', '.sp-void-followup', function () {
		var id = String($(this).data('followup-id'));
		var reason = window.prompt('Why should this follow-up be voided?');
		if (!reason || !reason.replace(/\s/g, '')) return;
		var form = $('#sp-void-followup-form');
		form.find('[name="record_id"]').val(id);
		form.find('[name="reason"]').val(reason);
		postForm(form, 'sp-followup-message');
	});
	$('#sp-enquiry-event-form').on('submit', function (event) {
		event.preventDefault();
		postForm($(this), 'sp-enquiry-event-message');
	});
	$(document).on('click', '.sp-correct-enquiry-event', function () {
		var id = String($(this).data('event-id'));
		var rows = reportData.enquiry_events || [];
		var row = null;
		$.each(rows, function (_, item) { if (String(item.id) === id) row = item; });
		if (!row || !row.can_correct) return;
		var reason = window.prompt('Why does this enquiry event need correction?');
		if (!reason || !reason.replace(/\s/g, '')) return;
		var stamp = String(row.business_at || '').split(' ');
		var form = $('#sp-enquiry-event-form');
		form.find('[name="action"]').val('correct_enquiry_event');
		form.find('[name="event_id"]').val(id);
		form.find('[name="idempotency_key"]').val(makeIdempotencyKey());
		form.find('[name="correction_reason"]').val(reason);
		form.find('[name="salescaseref"]').val(row.salescaseref || '');
		form.find('[name="event_type"]').val(row.event_type || 'received');
		form.find('[name="event_date"]').val(stamp[0] || '');
		form.find('[name="event_time"]').val(stamp[1] ? stamp[1].slice(0, 5) : '09:00');
		form.find('[name="response_code"]').val(row.response_code || '');
		form.find('[name="reason_code"]').val(row.reason_code || '');
		form.find('[name="evidence_reference"]').val(row.evidence_reference || '');
		form.find('[name="notes"]').val(row.notes || '');
		$('#sp-enquiry-event-submit').text('Save enquiry correction');
		$('#sp-enquiry-event-message').text('Review the prefilled details. Saving creates a linked replacement and retains the prior version in the audit history.').css('color', '#665c25');
		form[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
		form.find('[name="event_type"]').trigger('focus');
	});
	$(document).on('click', '.sp-void-enquiry-event', function () {
		var id = String($(this).data('event-id'));
		var reason = window.prompt('Why should this enquiry event be voided?');
		if (!reason || !reason.replace(/\s/g, '')) return;
		var form = $('#sp-void-enquiry-event-form');
		form.find('[name="event_id"]').val(id);
		form.find('[name="void_reason"]').val(reason);
		form.find('[name="idempotency_key"]').val(makeIdempotencyKey());
		postForm(form, 'sp-enquiry-event-message');
	});
	$('#sp-po-receipt-form').on('submit', function (event) {
		event.preventDefault();
		postForm($(this), 'sp-po-receipt-message');
	});
	$(document).on('click', '.sp-correct-po-receipt', function () {
		var id = String($(this).data('receipt-id'));
		var rows = reportData.po_receipts || [];
		var row = null;
		$.each(rows, function (_, item) { if (String(item.id) === id) row = item; });
		if (!row || !row.can_correct) return;
		var reason = window.prompt('Why does this customer PO receipt need correction?');
		if (!reason || !reason.replace(/\s/g, '')) return;
		var stamp = String(row.received_at || '').split(' ');
		var quoteOrder = '';
		var ocOrder = '';
		var ocPo = '';
		$.each(row.bridges || [], function (_, bridge) {
			if (bridge.source_type === 'quotation' && !quoteOrder) quoteOrder = bridge.source_key;
			if (bridge.source_type === 'order_confirmation' && !ocOrder) { var pair = String(bridge.source_key).split('|'); ocOrder = pair[0] || ''; ocPo = pair[1] || ''; }
		});
		var form = $('#sp-po-receipt-form');
		form.find('[name="action"]').val('correct_po_receipt');
		form.find('[name="receipt_id"]').val(id);
		form.find('[name="idempotency_key"]').val(makeIdempotencyKey());
		form.find('[name="correction_reason"]').val(reason);
		form.find('[name="salescaseref"]').val(row.salescaseref || '');
		form.find('[name="customer_reference"]').val(row.customer_reference_original || '');
		form.find('[name="received_date"]').val(stamp[0] || '');
		form.find('[name="received_time"]').val(stamp[1] ? stamp[1].slice(0, 5) : '09:00');
		form.find('[name="stated_amount"]').val(row.stated_amount === null ? '' : row.stated_amount);
		form.find('[name="currency"]').val(row.currency || '');
		form.find('[name="tax_basis"]').val(row.tax_basis || 'unknown');
		form.find('[name="quotation_orderno"]').val(quoteOrder).prop('readonly', true);
		form.find('[name="oc_orderno"]').val(ocOrder).prop('readonly', true);
		form.find('[name="oc_pono"]').val(ocPo).prop('readonly', true);
		form.find('[name="evidence_reference"]').val(row.evidence_reference || '');
		$('#sp-po-receipt-submit').text('Save PO correction');
		$('#sp-po-receipt-message').text('Review the corrected receipt details. Existing quotation and OC links will be copied to the replacement record.').css('color', '#665c25');
		form[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
		form.find('[name="customer_reference"]').trigger('focus');
	});
	$(document).on('click', '.sp-void-po-receipt', function () {
		var id = String($(this).data('receipt-id'));
		var reason = window.prompt('Why should this customer PO receipt be voided?');
		if (!reason || !reason.replace(/\s/g, '')) return;
		var form = $('#sp-void-po-receipt-form');
		form.find('[name="receipt_id"]').val(id);
		form.find('[name="void_reason"]').val(reason);
		form.find('[name="idempotency_key"]').val(makeIdempotencyKey());
		postForm(form, 'sp-po-receipt-message');
	});
	$('#sp-coverage-form').on('submit', function (event) {
		event.preventDefault();
		postForm($(this), 'sp-coverage-message');
	});
	$('#sp-coverage-form [name="coverage_date"], #sp-coverage-form [name="team_id"]').on('change', updateCoverageReview);
	$('#sp-team-membership-form').on('submit', function (event) {
		event.preventDefault();
		postForm($(this), 'sp-team-membership-message');
	});
	$('#sp-named-scope-form').on('submit', function (event) {
		event.preventDefault();
		postForm($(this), 'sp-named-scope-message');
	});
	$(document).on('click', '.sp-approve-named-scope', function () {
		var button = $(this);
		var note = $.trim(button.closest('.sp-scope-approval').find('.sp-scope-review-note').val() || '');
		if (!note) { $('#sp-named-scope-message').text('Record what you reviewed before approving this mapping.').css('color', '#a94442'); return; }
		button.prop('disabled', true);
		$.ajax({ url: apiUrl, method: 'POST', dataType: 'json', data: {
			FormID: app.data('form-id'), action: 'approve_named_scope', scope_version: button.data('scope-version'), scope_review_note: note
		} }).done(function (response) {
			$('#sp-named-scope-message').text(response.message || 'The scope mapping was approved.').css('color', '#246b39');
			loadReport();
		}).fail(function (xhr) {
			button.prop('disabled', false);
			$('#sp-named-scope-message').text(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'The scope mapping could not be approved.').css('color', '#a94442');
		});
	});
	$('#sp-calendar-form').on('submit', function (event) {
		event.preventDefault();
		postForm($(this), 'sp-calendar-message');
	});
	$(document).on('click', '.sp-edit-calendar', function () {
		var form = $('#sp-calendar-form');
		form.find('[name="business_date"]').val($(this).data('date'));
		form.find('[name="planned_workday"]').val(String($(this).data('workday')));
		form.find('[name="override_reason"]').val($(this).data('reason'));
		form[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
		form.find('[name="override_reason"]').trigger('focus');
	});
	$('#sp-reference-import-form').on('submit', function (event) {
		event.preventDefault();
		previewReferenceObservations($(this));
	});
	$('#sp-reference-import').on('click', function () { importReferenceObservations($('#sp-reference-import-form')); });
	$('#sp-reference-import-form').on('input change', 'input:not([type="hidden"]), select, textarea', function () {
		clearReferencePreview('Source details changed. Preview the revised CSV before importing.');
	});
	$('#sp-reconciliation-form').on('submit', function (event) {
		event.preventDefault();
		postForm($(this), 'sp-reconciliation-message');
	});
	$(document).on('click', '.sp-select-reconciliation', function () {
		var form = $('#sp-reconciliation-form');
		form.find('[name="reference_observation_id"]').val($(this).data('reference-id'));
		if (!form.find('[name="run_key"]').val()) {
			var frozenKey = reportData && reportData.frozen_run ? reportData.frozen_run.run_key : $('#sp-run-key').val();
			if (frozenKey) form.find('[name="run_key"]').val(frozenKey);
		}
		form.show();
		form[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
	});
	$(document).on('click', '.sp-review-reference', function () {
		var button = $(this);
		var row = button.closest('tr');
		var note = row.find('.sp-reference-review-note').val();
		if (!note || !note.replace(/\s/g, '')) { window.alert('Record which source evidence you checked or why the observation is disputed.'); return; }
		$.ajax({ url: apiUrl, method: 'POST', dataType: 'json', data: {
			FormID: app.data('form-id'), action: 'update_reference_verification', observation_id: button.data('id'),
			verification_state: row.find('.sp-reference-verification').val(), verification_note: note
		} }).done(function () {
			loadReport();
		}).fail(function (xhr) {
			window.alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'The source review could not be saved.');
		});
	});
	$(document).on('click', '.sp-save-reconciliation', function () {
		var button = $(this);
		var row = button.closest('tr');
		var message = row.find('.sp-recon-message');
		$.ajax({ url: apiUrl, method: 'POST', dataType: 'json', data: {
			FormID: app.data('form-id'), action: 'update_reconciliation', reconciliation_id: button.data('id'),
			status: row.find('.sp-recon-status').val(), classification: row.find('.sp-recon-classification').val(),
			owner_user_id: row.find('.sp-recon-owner').val(), resolution_note: row.find('.sp-recon-resolution').val()
		} }).done(function (response) {
			message.text(response.message || 'Saved.').css('color', '#246b39');
			loadReport();
		}).fail(function (xhr) {
			message.text(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'The discrepancy could not be updated.').css('color', '#a94442');
		});
	});
	$('#sp-followup-form [name="idempotency_key"], #sp-enquiry-event-form [name="idempotency_key"], #sp-po-receipt-form [name="idempotency_key"]').val(makeIdempotencyKey());
	$(document).on('click', '.sp-document-drilldown', function () {
		documentQuery = { metric: $(this).data('metric'), date: $(this).data('date') };
		documentPage = 1;
		$('#sp-documents-table').empty();
		$('#sp-documents-modal').modal('show');
		loadSupportingDocuments(1, false);
	});
	$('#sp-documents-more').on('click', function () { loadSupportingDocuments(documentPage + 1, true); });
	$(document).on('click', '.sp-record-quote-issue', function () {
		var button = $(this);
		var kind = String(button.data('kind'));
		var confirmation = kind === 'resent'
			? 'Confirm that this unchanged quotation was sent again to the customer?'
			: 'Confirm that this quotation was sent to the customer?';
		if (!window.confirm(confirmation)) return;
		$('#sp-documents-message').text('Saving an immutable quotation issue snapshot…');
		$.ajax({ url: apiUrl, method: 'POST', dataType: 'json', data: {
			FormID: app.data('form-id'), action: 'record_quote_issue', orderno: button.data('orderno'), issue_kind: kind,
			issued_date: $('#sp-issued-date').val(), issued_time: $('#sp-issued-time').val(), evidence_reference: '', idempotency_key: makeIdempotencyKey()
		} }).done(function (response) {
			$('#sp-documents-message').text(response.message || 'Quotation issue recorded.');
		}).fail(function (xhr) {
			$('#sp-documents-message').text(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'The quotation issue could not be recorded.');
		});
	});
	$('#sp-adjustment-form').on('submit', function (event) {
		event.preventDefault();
		postForm($(this), 'sp-adjustment-message');
	});
	$(document).on('click', '.sp-review-adjustment', function () {
		var button = $(this);
		var note = window.prompt('Optional review note:') || '';
		var form = $('<form><input name="FormID"><input name="action" value="review_adjustment"><input name="adjustment_id"><input name="decision"><input name="review_note"></form>');
		form.find('[name="FormID"]').val(app.data('form-id') || $('#sp-followup-form [name="FormID"]').val());
		form.find('[name="adjustment_id"]').val(button.data('id'));
		form.find('[name="decision"]').val(button.data('decision'));
		form.find('[name="review_note"]').val(note);
		postForm(form, 'sp-adjustment-message');
	});
	$(document).on('click', '.sp-reverse-adjustment', function () {
		var button = $(this);
		if (!window.confirm('Request reversal of the remaining value for this approved exclusion? The request will need approval by another authorized user.')) return;
		var form = $('<form><input name="FormID"><input name="action" value="request_adjustment_reversal"><input name="adjustment_id"></form>');
		form.find('[name="FormID"]').val(app.data('form-id') || $('#sp-followup-form [name="FormID"]').val());
		form.find('[name="adjustment_id"]').val(button.data('id'));
		postForm(form, 'sp-adjustment-message');
	});
	if ($.fn.select2) {
		$('#sp-population-filter').select2({ width: '100%', placeholder: 'Choose approved population', allowClear: true });
		$('#sp-team-filter').select2({ width: '100%', placeholder: 'All teams', closeOnSelect: false });
		$('#sp-salesperson-filter').select2({ width: '100%', placeholder: 'All salespeople', closeOnSelect: false });
	}

	loadReport();
})(jQuery);
