(function ($) {
	'use strict';

	var app = $('#my-activity-app');
	var apiUrl = app.data('api-url');
	var formId = app.data('form-id');
	var quoteUrl = app.data('quote-url');
	var followupRows = [];
	var cases = [];
	var caseQuotes = {};
	var scope = 'self';
	var filters = {};
	var statusLabels = { open: 'Open', po_received: 'PO received', closed: 'Closed' };

	function escapeHtml(value) {
		return String(value === null || value === undefined ? '' : value)
			.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
	}

	function makeKey() {
		if (window.crypto && window.crypto.randomUUID) return window.crypto.randomUUID();
		return 'ma-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2);
	}

	function money(value) {
		if (value === null || value === undefined || value === '') return '—';
		return 'Rs. ' + Number(value).toLocaleString('en-PK', { maximumFractionDigits: 0 });
	}

	function timeLeft(minutes) {
		var hours = Math.floor(minutes / 60);
		return (hours ? hours + ' h ' : '') + (minutes % 60) + ' min';
	}

	function quoteLink(orderNo, caseRef) {
		return quoteUrl + '?identifier=&QuotationNo=' + encodeURIComponent(orderNo) + '&salescaseref=' + encodeURIComponent(caseRef || '');
	}

	function showMessage(text, type) {
		$('#ma-message').removeClass('sp-message-success sp-message-error success error')
			.addClass(type === 'error' ? 'sp-message-error error' : 'sp-message-success success').text(text).show();
	}

	function showsOthers() {
		return scope !== 'self';
	}

	function caseLabel(item) {
		var customer = item.customer_name ? ' — ' + item.customer_name : '';
		var branch = item.branch_name ? ' (' + item.branch_name + ')' : '';
		var owner = showsOthers() && item.salesman ? ' · ' + item.salesman : '';
		return item.salescaseref + customer + branch + owner;
	}

	function quoteLabel(item) {
		var customer = item.customer_name ? ' · ' + item.customer_name : '';
		var value = item.value !== undefined && item.value !== null ? ' · ' + money(item.value) : '';
		return 'Q#' + item.orderno + ' · ' + item.salescaseref + customer + ' · ' + item.orddate + value;
	}

	function caseByRef(caseRef) {
		return $.grep(cases, function (item) { return item.salescaseref === caseRef; })[0];
	}

	// Sets a type-ahead select to a value, adding the option first when the search has not loaded it.
	function setSelect(select, value, text) {
		if (!value) { select.val(null).trigger('change'); return; }
		if (!select.find('option[value="' + value + '"]').length) select.append(new Option(text, value, true, true));
		select.val(value).trigger('change');
	}

	// Type-ahead search against the server, limited to the cases and quotations in the user's activity scope.
	function lookup(select, action, labelFor, caseFor, placeholder, allowClear) {
		if (!select.find('option[value=""]').length) select.prepend('<option value=""></option>');
		select.select2({
			width: '100%',
			placeholder: placeholder,
			allowClear: allowClear,
			minimumInputLength: 0,
			ajax: {
				url: apiUrl,
				dataType: 'json',
				delay: 250,
				data: function (params) {
					return { action: action, q: params.term || '', case: caseFor(), limit: 20 };
				},
				processResults: function (response) {
					var items = response.status === 'success' ? (action === 'lookup_cases' ? response.cases : response.quotations) : [];
					return {
						results: $.map(items || [], function (item) {
							var id = action === 'lookup_cases' ? item.salescaseref : item.orderno;
							return { id: String(id), text: labelFor(item) };
						})
					};
				}
			}
		});
	}

	function initLookups() {
		$('select.ma-case-select').each(function () {
			var select = $(this);
			var optional = select.closest('form').attr('id') === 'ma-followup-form';
			lookup(select, 'lookup_cases', caseLabel, function () { return ''; },
				optional ? 'No case (general activity)' : 'Search cases by number, customer or description…', optional);
		});
		$('select.ma-quote-select').each(function () {
			var select = $(this);
			lookup(select, 'lookup_quotations', quoteLabel, function () {
				return select.closest('form').find('select.ma-case-select').val() || '';
			}, 'None', true);
		});
		lookup($('select.ma-filter-case'), 'lookup_cases', caseLabel, function () { return ''; }, 'All cases', true);
		// A different case invalidates a quotation chosen for the previous one, and shows that case's quotations.
		$('#ma-followup-form select.ma-case-select').on('change', function () {
			var form = $(this).closest('form');
			form.find('select.ma-quote-select').val(null).trigger('change');
			loadCaseQuotes($(this).val() || '');
		});
	}

	function renderCaseQuotes(caseRef) {
		var panel = $('#ma-case-quotes');
		if (!caseRef) { panel.hide(); return; }
		var list = caseQuotes[caseRef];
		var html;
		if (!list) {
			html = '<tbody><tr><td class="text-muted"><i class="fa fa-circle-o-notch fa-spin"></i> Loading quotations…</td></tr></tbody>';
		} else if (!list.length) {
			html = '<tbody><tr><td class="text-muted">No quotations on this case yet.</td></tr></tbody>';
		} else {
			html = '<thead><tr><th>Quotation</th><th>Date</th><th class="text-right">Value</th><th></th></tr></thead><tbody>';
			$.each(list, function (_, item) {
				html += '<tr><td><a href="' + escapeHtml(quoteLink(item.orderno, item.salescaseref)) + '" target="_blank" rel="noopener">Q#' + escapeHtml(item.orderno) + ' <i class="fa fa-external-link"></i></a></td>' +
					'<td>' + escapeHtml(item.orddate) + '</td><td class="text-right">' + escapeHtml(money(item.value)) + '</td>' +
					'<td class="text-right"><button type="button" class="btn btn-xs btn-default ma-use-quote" data-orderno="' + escapeHtml(item.orderno) + '">Link to follow-up</button></td></tr>';
			});
			html += '</tbody>';
		}
		panel.find('table').html(html);
		panel.show();
	}

	function loadCaseQuotes(caseRef) {
		var done = $.Deferred();
		if (!caseRef) { renderCaseQuotes(''); return done.resolve().promise(); }
		if (caseQuotes[caseRef]) { renderCaseQuotes(caseRef); return done.resolve().promise(); }
		renderCaseQuotes(caseRef);
		$.getJSON(apiUrl, { action: 'case_quotations', salescaseref: caseRef })
			.done(function (data) { caseQuotes[caseRef] = data.quotations || []; })
			.fail(function () {
				caseQuotes[caseRef] = [];
				showMessage('The quotations for that case could not be loaded.', 'error');
			})
			.always(function () {
				if ($('#ma-followup-form select.ma-case-select').val() === caseRef) renderCaseQuotes(caseRef);
				done.resolve();
			});
		return done.promise();
	}

	function renderCases() {
		var owner = showsOthers();
		$('#ma-cases table[data-status]').each(function () {
			var table = $(this);
			var status = table.data('status');
			var html = '<thead><tr><th>Case</th><th>Customer</th>' + (owner ? '<th>Owner</th>' : '') + '<th>Started</th>' +
				(status === 'po_received' ? '<th>PO</th>' : '') + (status === 'closed' ? '<th>Closed</th><th>Reason</th>' : '') + '<th></th></tr></thead><tbody>';
			var rows = 0;
			$.each(cases, function (_, item) {
				if (item.status !== status) return;
				rows++;
				var po = [];
				if (item.po_file_date) po.push('File ' + item.po_file_date);
				if (item.po_recorded_date) po.push('Recorded ' + item.po_recorded_date);
				html += '<tr><td>' + escapeHtml(item.salescaseref) + '</td><td>' + escapeHtml(item.customer_name || '') + (item.branch_name ? ' <span class="text-muted">(' + escapeHtml(item.branch_name) + ')</span>' : '') + '</td>' +
					(owner ? '<td>' + escapeHtml(item.salesman) + '</td>' : '') + '<td>' + escapeHtml(item.commenced || '') + '</td>' +
					(status === 'po_received' ? '<td>' + escapeHtml(po.join(' · ')) + '</td>' : '') +
					(status === 'closed' ? '<td>' + escapeHtml(item.closed_date || '') + '</td><td>' + escapeHtml(item.closingreason || '') + '</td>' : '') +
					'<td class="text-right"><button type="button" class="btn btn-xs btn-default ma-case-followup" data-case="' + escapeHtml(item.salescaseref) + '">Record follow-up</button></td></tr>';
			});
			if (!rows) html += '<tr><td colspan="8" class="text-muted">No ' + escapeHtml(statusLabels[status].toLowerCase()) + ' cases.</td></tr>';
			table.html(html + '</tbody>');
			$('#ma-cases .badge[data-count="' + status + '"]').text(rows);
		});
	}

	// Recorded by is always shown (a lead may record on a member's case); Owner only when the list spans other people's cases.
	function whoHeaders() {
		return (showsOthers() ? '<th>Owner</th>' : '') + '<th>Recorded by</th>';
	}

	function whoCells(row) {
		return (showsOthers() ? '<td>' + escapeHtml(row.owner || '—') + '</td>' : '') + '<td>' + escapeHtml(row.entered_by_name || '') + '</td>';
	}

	function renderTables(data) {
		var rows = data.followups || [];
		var html = '<thead><tr><th>When</th><th>Case</th><th>Quotation</th><th class="text-right">Quotation value</th><th>Type</th><th>Outcome</th><th>Next action</th>' +
			whoHeaders() + '<th></th></tr></thead><tbody>';
		$.each(rows, function (_, row) {
			var voided = row.voided_at !== null;
			var actions = [];
			if (voided) {
				actions.push('<span>Voided: ' + escapeHtml(row.void_reason || '') + '</span>');
			} else {
				if (row.can_edit) actions.push('<button type="button" class="btn btn-xs btn-default ma-edit-followup" data-id="' + escapeHtml(row.id) + '" title="Editable for another ' + escapeHtml(timeLeft(row.edit_minutes_left)) + '">Edit</button>');
				if (row.can_void) actions.push('<button type="button" class="btn btn-xs btn-link text-danger ma-void" data-kind="followup" data-id="' + escapeHtml(row.id) + '">Void</button>');
				if (row.salescaseref) actions.push('<button type="button" class="btn btn-xs btn-link ma-another" data-id="' + escapeHtml(row.id) + '">Add another follow-up</button>');
			}
			html += '<tr class="' + (voided ? 'text-muted' : '') + '"><td>' + escapeHtml(row.activity_at) + (row.is_edited ? ' <span class="label label-default">edited</span>' : '') + '</td><td>' + escapeHtml(row.salescaseref || '—') + '</td>' +
				'<td>' + (row.quotation_orderno ? '<a href="' + escapeHtml(quoteLink(row.quotation_orderno, row.salescaseref)) + '" target="_blank" rel="noopener">Q#' + escapeHtml(row.quotation_orderno) + '</a>' : '—') + '</td>' +
				'<td class="text-right">' + (row.quotation_orderno ? escapeHtml(money(row.quotation_value)) : '—') + '</td><td>' + escapeHtml(row.activity_type) + '</td>' +
				'<td>' + escapeHtml(row.outcome) + '</td><td>' + escapeHtml(row.next_action || '') + (row.next_action_date ? ' · ' + escapeHtml(row.next_action_date) : '') + '</td>' +
				whoCells(row) + '<td class="text-nowrap">' + actions.join(' ') + '</td></tr>';
		});
		if (!rows.length) html += '<tr><td colspan="10" class="text-muted">No follow-ups match these filters.</td></tr>';
		$('#ma-followup-table').html(html + '</tbody>');

		rows = data.enquiry_events || [];
		html = '<thead><tr><th>When</th><th>Case</th>' + (showsOthers() ? '<th>Owner</th>' : '') + '<th>Event</th><th>Response / reason</th><th>Notes</th><th>Last updated</th><th></th></tr></thead><tbody>';
		$.each(rows, function (_, row) {
			var voided = String(row.is_voided) === '1';
			html += '<tr class="' + (voided ? 'text-muted' : '') + '"><td>' + escapeHtml(row.business_at) + '</td><td>' + escapeHtml(row.salescaseref) + '</td>' +
				(showsOthers() ? '<td>' + escapeHtml(row.owner || '') + '</td>' : '') +
				'<td>' + escapeHtml(row.event_type) + (String(row.is_edited) === '1' ? ' <span class="label label-default">edited</span>' : '') + '</td><td>' + escapeHtml(row.response_code || row.reason_code || '') + '</td><td>' + escapeHtml(row.notes || '') + '</td>' +
				'<td>' + escapeHtml(row.last_updated_at || '') + (row.last_updated_by ? ' · ' + escapeHtml(row.last_updated_by) : '') + '</td>' +
				'<td>' + (voided ? 'Voided' : '') + '</td></tr>';
		});
		if (!rows.length) html += '<tr><td colspan="8" class="text-muted">No enquiry events match these filters.</td></tr>';
		$('#ma-enquiry-table').html(html + '</tbody>');

		rows = data.po_receipts || [];
		html = '<thead><tr><th>Received</th><th>Case</th><th>Customer PO</th><th>Amount</th>' + whoHeaders() + '<th>Status</th></tr></thead><tbody>';
		$.each(rows, function (_, row) {
			var active = row.receipt_status === 'active';
			html += '<tr class="' + (active ? '' : 'text-muted') + '"><td>' + escapeHtml(row.received_at) + '</td><td>' + escapeHtml(row.salescaseref) + '</td>' +
				'<td>' + escapeHtml(row.customer_reference) + '</td><td>' + (row.stated_amount ? escapeHtml(row.currency) + ' ' + escapeHtml(row.stated_amount) : '—') + '</td>' +
				whoCells(row) + '<td>' + (active ? 'Recorded' : 'Voided: ' + escapeHtml(row.void_reason || '')) + '</td></tr>';
		});
		if (!rows.length) html += '<tr><td colspan="7" class="text-muted">No customer POs match these filters.</td></tr>';
		$('#ma-po-table').html(html + '</tbody>');
	}

	function loadActivity() {
		$.getJSON(apiUrl, $.extend({ action: 'my_activity' }, filters))
			.done(function (data) {
				if (data.status !== 'success') {
					showMessage(data.message || 'Your activity could not be loaded.', 'error');
					return;
				}
				scope = data.scope || 'self';
				cases = data.cases || [];
				followupRows = data.followups || [];
				if (data.filters) {
					$('#ma-filter-form input[name="from"]').val(data.filters.from);
					$('#ma-filter-form input[name="to"]').val(data.filters.to);
				}
				renderCases();
				renderTables(data);
				$('#ma-loading').hide();
				$('#ma-content').show();
				if (!cases.length) showMessage('No sales cases are assigned to you yet. Ask your sales manager to check the case assignment.', 'error');
			})
			.fail(function (xhr) {
				$('#ma-loading').hide();
				showMessage(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Your activity could not be loaded.', 'error');
			});
	}

	function unlockCase(form) {
		form.find('.ma-edit-case').remove();
		form.find('select.ma-case-select').prop('disabled', false);
	}

	function resetForm(form) {
		var keep = {};
		form.find('input[name="activity_date"], input[name="activity_time"]').each(function () {
			keep[this.name] = this.value;
		});
		unlockCase(form);
		form[0].reset();
		// select2 versions differ in the data key they set; the hidden-accessible class is always present once initialised.
		form.find('select').each(function () {
			if ($(this).hasClass('select2-hidden-accessible')) $(this).val(null).trigger('change');
		});
		$.each(keep, function (name, value) { form.find('[name="' + name + '"]').val(value); });
		form.find('[name="idempotency_key"]').val(makeKey());
		form.find('[name="corrected_from_id"]').val('');
		form.find('.ma-correction-only').hide();
		if (form.data('submit-label')) form.find('button[type="submit"]').html(form.data('submit-label'));
		form.find('#ma-cancel-edit').hide();
		$('#ma-edit-banner').hide().text('');
	}

	function showFollowupForm() {
		var form = $('#ma-followup-form');
		$('a[href="#ma-followup-tab"]').tab('show');
		$('html, body').animate({ scrollTop: form.offset().top - 120 }, 200);
		return form;
	}

	function caseText(caseRef) {
		var item = caseByRef(caseRef);
		return item ? caseLabel(item) : caseRef;
	}

	// Starts a new follow-up on a case, optionally linked to a quotation.
	function startFollowup(caseRef, quotationNo) {
		var form = $('#ma-followup-form');
		resetForm(form);
		setSelect(form.find('select[name="salescaseref"]'), caseRef || '', caseText(caseRef));
		if (quotationNo) setSelect(form.find('select[name="quotation_orderno"]'), String(quotationNo), 'Q#' + quotationNo);
		showFollowupForm();
	}

	// Loads one of the signed-in person's own follow-ups into the form as a correction. The original stays in the audit history.
	function startEdit(row) {
		var form = $('#ma-followup-form');
		resetForm(form);
		form.find('[name="corrected_from_id"]').val(row.id);
		form.find('[name="activity_date"]').val(row.activity_date);
		form.find('[name="activity_time"]').val((row.activity_at || '').slice(11, 16));
		// Case first: changing the case clears the quotation, so the quotation is set afterwards.
		var caseSelect = form.find('select[name="salescaseref"]');
		setSelect(caseSelect, row.salescaseref || '', caseText(row.salescaseref || ''));
		setSelect(form.find('select[name="quotation_orderno"]'), row.quotation_orderno ? String(row.quotation_orderno) : '', row.quotation_orderno ? 'Q#' + row.quotation_orderno : '');
		// A correction must stay on the same case; the disabled select is not submitted, so carry the case separately.
		caseSelect.prop('disabled', true);
		form.append($('<input type="hidden" class="ma-edit-case" name="salescaseref">').val(row.salescaseref || ''));
		form.find('[name="activity_type"]').val(row.activity_type);
		form.find('[name="outcome"]').val(row.outcome || '');
		form.find('[name="next_action"]').val(row.next_action || '');
		form.find('[name="next_action_date"]').val(row.next_action_date || '');
		form.find('[name="notes"]').val(row.notes || '');
		form.find('[name="correction_reason"]').val('');
		form.find('.ma-correction-only').show();
		form.find('button[type="submit"]').html('<i class="fa fa-save"></i> Save edit');
		form.find('#ma-cancel-edit').show();
		$('#ma-edit-banner').text('Editing the follow-up from ' + (row.activity_at || '') + '. You can edit it for another ' + timeLeft(row.edit_minutes_left) + '. Enter the reason for the edit below; the original stays in the audit history.').show();
		showFollowupForm();
	}

	function postAction(data, form) {
		var button = form ? form.find('button[type="submit"]') : null;
		if (button) button.prop('disabled', true);
		return $.ajax({ url: apiUrl, method: 'POST', data: data, dataType: 'json' })
			.done(function (response) {
				showMessage(response.message || 'Saved.', 'success');
				if (form) {
					form.find('.ma-form-message').text('');
					resetForm(form);
				}
				loadActivity();
			})
			.fail(function (xhr) {
				var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Could not save. Check the details and try again.';
				showMessage(message, 'error');
				if (form) form.find('.ma-form-message').text(message);
			})
			.always(function () { if (button) button.prop('disabled', false); });
	}

	$('#ma-followup-form').find('[name="idempotency_key"]').val(makeKey());

	$('#ma-followup-form').on('submit', function (event) {
		event.preventDefault();
		var form = $(this);
		if (form.find('[name="corrected_from_id"]').val() && $.trim(form.find('[name="correction_reason"]').val()) === '') {
			showMessage('Enter the reason for the edit.', 'error');
			return;
		}
		postAction($.param(form.serializeArray()), form);
	});

	$(document).on('click', '.ma-edit-followup', function () {
		var id = String($(this).data('id'));
		var row = $.grep(followupRows, function (item) { return String(item.id) === id; })[0];
		if (row) startEdit(row);
	});

	$(document).on('click', '.ma-another', function () {
		var id = String($(this).data('id'));
		var row = $.grep(followupRows, function (item) { return String(item.id) === id; })[0];
		if (row) startFollowup(row.salescaseref, row.quotation_orderno);
	});

	$(document).on('click', '.ma-case-followup', function () {
		startFollowup(String($(this).data('case')), null);
	});

	$(document).on('click', '.ma-use-quote', function () {
		var orderNo = String($(this).data('orderno'));
		var caseRef = $('#ma-followup-form select.ma-case-select').val() || '';
		var item = $.grep(caseQuotes[caseRef] || [], function (quote) { return String(quote.orderno) === orderNo; })[0];
		setSelect($('#ma-followup-form select.ma-quote-select'), orderNo, item ? quoteLabel(item) : 'Q#' + orderNo);
	});

	$('#ma-cancel-edit').on('click', function () {
		resetForm($('#ma-followup-form'));
		$('#ma-followup-form').find('.ma-form-message').text('');
	});

	$(document).on('click', '.ma-void', function () {
		var button = $(this);
		if (button.data('kind') !== 'followup') return;
		var reason = window.prompt('Reason for voiding this entry (it stays in the audit history):');
		if (reason === null || $.trim(reason) === '') return;
		postAction({ action: 'void_followup', record_id: button.data('id'), reason: reason, FormID: formId }, null);
	});

	$('#ma-filter-form').on('submit', function (event) {
		event.preventDefault();
		var form = $(this);
		var quotation = $.trim(form.find('[name="quotation"]').val());
		if (quotation !== '' && !/^\d+$/.test(quotation)) {
			showMessage('Enter the quotation number as digits only.', 'error');
			return;
		}
		filters = {};
		$.each(['case', 'from', 'to'], function (_, name) {
			var value = form.find('[name="' + name + '"]').val();
			if (value) filters[name] = value;
		});
		if (quotation !== '') filters.quotation = quotation;
		loadActivity();
	});

	$('#ma-filter-reset').on('click', function () {
		filters = {};
		var form = $('#ma-filter-form');
		form[0].reset();
		form.find('select.ma-filter-case').val(null).trigger('change');
		loadActivity();
	});

	$('#ma-followup-form').data('submit-label', $('#ma-followup-form').find('button[type="submit"]').html());
	$('#ma-message').hide();
	initLookups();
	loadActivity();
})(jQuery);
