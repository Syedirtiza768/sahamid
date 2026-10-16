(function ($) {
	'use strict';

	var app = $('#my-activity-app');
	var apiUrl = app.data('api-url');
	var formId = app.data('form-id');
	var followupRows = [];

	function escapeHtml(value) {
		return String(value === null || value === undefined ? '' : value)
			.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
	}

	function makeKey() {
		if (window.crypto && window.crypto.randomUUID) return window.crypto.randomUUID();
		return 'ma-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2);
	}

	function showMessage(text, type) {
		$('#ma-message').removeClass('sp-message-success sp-message-error')
			.addClass(type === 'error' ? 'sp-message-error' : 'sp-message-success').text(text).show();
	}

	function caseLabel(item) {
		var customer = item.customer_name ? ' — ' + item.customer_name : '';
		var branch = item.branch_name ? ' (' + item.branch_name + ')' : '';
		return item.salescaseref + customer + branch;
	}

	function quoteLabel(item) {
		var customer = item.customer_name ? ' · ' + item.customer_name : '';
		return 'Q#' + item.orderno + ' · ' + item.salescaseref + customer + ' · ' + item.orddate;
	}

	// Type-ahead search against the server, limited to the signed-in salesperson's own cases and quotations.
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
				optional ? 'No case (general activity)' : 'Search your cases by number, customer or description…', optional);
		});
		$('select.ma-quote-select').each(function () {
			var select = $(this);
			lookup(select, 'lookup_quotations', quoteLabel, function () {
				return select.closest('form').find('select.ma-case-select').val() || '';
			}, 'None', true);
		});
		lookup($('#ma-quote-form select[name="orderno"]'), 'lookup_quotations', quoteLabel, function () { return ''; },
			'Search your quotations by number or customer…', false);
		// A different case invalidates a quotation chosen for the previous one.
		$('select.ma-case-select').on('change', function () {
			$(this).closest('form').find('select.ma-quote-select').val(null).trigger('change');
		});
	}

	function renderTables(data) {
		var rows = data.followups || [];
		var html = '<thead><tr><th>When</th><th>Case</th><th>Quotation</th><th>Type</th><th>Outcome</th><th>Next action</th><th></th></tr></thead><tbody>';
		$.each(rows, function (_, row) {
			var voided = row.voided_at !== null;
			var status = voided ? 'Voided: ' + escapeHtml(row.void_reason || '') : (row.has_correction ? 'Replaced by an edit' : '');
			var actions = '';
			if (row.can_edit) {
				actions = '<button type="button" class="btn btn-xs btn-default ma-edit-followup" data-id="' + escapeHtml(row.id) + '">Edit</button> ' +
					'<button type="button" class="btn btn-xs btn-link text-danger ma-void" data-kind="followup" data-id="' + escapeHtml(row.id) + '">Void</button>';
			}
			html += '<tr class="' + (voided || row.has_correction ? 'text-muted' : '') + '"><td>' + escapeHtml(row.activity_at) + '</td><td>' + escapeHtml(row.salescaseref || '—') + '</td>' +
				'<td>' + (row.quotation_orderno ? 'Q#' + escapeHtml(row.quotation_orderno) : '—') + '</td><td>' + escapeHtml(row.activity_type) + '</td>' +
				'<td>' + escapeHtml(row.outcome) + '</td><td>' + escapeHtml(row.next_action || '') + (row.next_action_date ? ' · ' + escapeHtml(row.next_action_date) : '') + '</td>' +
				'<td>' + (status ? status + ' ' : '') + actions + '</td></tr>';
		});
		if (!rows.length) html += '<tr><td colspan="7" class="text-muted">No follow-ups in the last 60 days.</td></tr>';
		$('#ma-followup-table').html(html + '</tbody>');

		rows = data.enquiry_events || [];
		html = '<thead><tr><th>When</th><th>Case</th><th>Event</th><th>Response / reason</th><th>Notes</th><th></th></tr></thead><tbody>';
		$.each(rows, function (_, row) {
			html += '<tr class="' + (row.is_voided ? 'text-muted' : '') + '"><td>' + escapeHtml(row.business_at) + '</td><td>' + escapeHtml(row.salescaseref) + '</td>' +
				'<td>' + escapeHtml(row.event_type) + '</td><td>' + escapeHtml(row.response_code || row.reason_code || '') + '</td><td>' + escapeHtml(row.notes || '') + '</td>' +
				'<td>' + (row.is_voided ? 'Voided' : '<button type="button" class="btn btn-xs btn-link text-danger ma-void" data-kind="enquiry" data-id="' + escapeHtml(row.id) + '">Void</button>') + '</td></tr>';
		});
		if (!rows.length) html += '<tr><td colspan="6" class="text-muted">No enquiry events in the last 60 days.</td></tr>';
		$('#ma-enquiry-table').html(html + '</tbody>');

		rows = data.po_receipts || [];
		html = '<thead><tr><th>Received</th><th>Case</th><th>Customer PO</th><th>Amount</th><th>Status</th><th></th></tr></thead><tbody>';
		$.each(rows, function (_, row) {
			var active = row.receipt_status === 'active';
			html += '<tr class="' + (active ? '' : 'text-muted') + '"><td>' + escapeHtml(row.received_at) + '</td><td>' + escapeHtml(row.salescaseref) + '</td>' +
				'<td>' + escapeHtml(row.customer_reference) + '</td><td>' + (row.stated_amount ? escapeHtml(row.currency) + ' ' + escapeHtml(row.stated_amount) : '—') + '</td>' +
				'<td>' + (active ? 'Recorded' : 'Voided: ' + escapeHtml(row.void_reason || '')) + '</td>' +
				'<td>' + (active ? '<button type="button" class="btn btn-xs btn-link text-danger ma-void" data-kind="po" data-id="' + escapeHtml(row.id) + '">Void</button>' : '') + '</td></tr>';
		});
		if (!rows.length) html += '<tr><td colspan="6" class="text-muted">No customer POs in the last 60 days.</td></tr>';
		$('#ma-po-table').html(html + '</tbody>');
	}

	function loadActivity() {
		$.getJSON(apiUrl, { action: 'my_activity' })
			.done(function (data) {
				if (data.status !== 'success') {
					showMessage(data.message || 'Your activity could not be loaded.', 'error');
					return;
				}
				followupRows = data.followups || [];
				renderTables(data);
				$('#ma-loading').hide();
				$('#ma-content').show();
				if (!(data.cases || []).length) showMessage('No sales cases are assigned to you yet. Ask your sales manager to check the case assignment.', 'error');
			})
			.fail(function (xhr) {
				$('#ma-loading').hide();
				showMessage(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Your activity could not be loaded.', 'error');
			});
	}

	function resetForm(form) {
		var keep = {};
		form.find('input[name="activity_date"], input[name="activity_time"], input[name="event_date"], input[name="event_time"], input[name="received_date"], input[name="received_time"], input[name="issued_date"], input[name="issued_time"], input[name="currency"]').each(function () {
			keep[this.name] = this.value;
		});
		form[0].reset();
		form.find('select').each(function () {
			if ($(this).data('select2')) $(this).val(null).trigger('change');
		});
		$.each(keep, function (name, value) { form.find('[name="' + name + '"]').val(value); });
		form.find('[name="idempotency_key"]').val(makeKey());
		form.find('[name="corrected_from_id"]').val('');
		form.find('.ma-correction-only').hide();
		if (form.data('submit-label')) form.find('button[type="submit"]').html(form.data('submit-label'));
		form.find('#ma-cancel-edit').hide();
		$('#ma-edit-banner').hide().text('');
	}

	// Loads one of the signed-in person's own follow-ups into the form as a correction. The original stays in the audit history.
	function startEdit(row) {
		var form = $('#ma-followup-form');
		function setSelect(select, value, text) {
			if (!value) { select.val(null).trigger('change'); return; }
			if (!select.find('option[value="' + value + '"]').length) select.append(new Option(text, value, true, true));
			select.val(value).trigger('change');
		}
		form.find('[name="corrected_from_id"]').val(row.id);
		form.find('[name="activity_date"]').val(row.activity_date);
		form.find('[name="activity_time"]').val((row.activity_at || '').slice(11, 16));
		// Case first: changing the case clears the quotation, so the quotation is set afterwards.
		setSelect(form.find('select[name="salescaseref"]'), row.salescaseref || '', row.salescaseref || '');
		setSelect(form.find('select[name="quotation_orderno"]'), row.quotation_orderno ? String(row.quotation_orderno) : '', row.quotation_orderno ? 'Q#' + row.quotation_orderno : '');
		form.find('[name="activity_type"]').val(row.activity_type);
		form.find('[name="outcome"]').val(row.outcome || '');
		form.find('[name="next_action"]').val(row.next_action || '');
		form.find('[name="next_action_date"]').val(row.next_action_date || '');
		form.find('[name="notes"]').val(row.notes || '');
		form.find('[name="correction_reason"]').val('');
		form.find('[name="idempotency_key"]').val(makeKey());
		form.find('.ma-correction-only').show();
		form.find('button[type="submit"]').html('<i class="fa fa-save"></i> Save edit');
		form.find('#ma-cancel-edit').show();
		$('#ma-edit-banner').text('Editing the follow-up from ' + (row.activity_at || '') + '. Enter the reason for the edit below. The original stays in the audit history.').show();
		$('a[href="#ma-followup-tab"]').tab('show');
		$('html, body').animate({ scrollTop: form.offset().top - 120 }, 200);
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

	$('#ma-followup-form, #ma-enquiry-form, #ma-po-form, #ma-quote-form').each(function () {
		$(this).find('[name="idempotency_key"]').val(makeKey());
	});

	$('#ma-followup-form, #ma-enquiry-form, #ma-po-form, #ma-quote-form').on('submit', function (event) {
		event.preventDefault();
		var form = $(this);
		var missing = false;
		form.find('select[data-required="1"]').each(function () {
			if (!$(this).val()) missing = true;
		});
		if (missing) {
			showMessage('Choose a sales case or quotation from the list.', 'error');
			return;
		}
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

	$('#ma-cancel-edit').on('click', function () {
		resetForm($('#ma-followup-form'));
		$('#ma-followup-form').find('.ma-form-message').text('');
	});

	$(document).on('click', '.ma-void', function () {
		var button = $(this);
		var kind = button.data('kind');
		var reason = window.prompt('Reason for voiding this entry (it stays in the audit history):');
		if (reason === null || $.trim(reason) === '') return;
		var data;
		if (kind === 'followup') data = { action: 'void_followup', record_id: button.data('id'), reason: reason };
		if (kind === 'enquiry') data = { action: 'void_enquiry_event', event_id: button.data('id'), void_reason: reason, idempotency_key: makeKey() };
		if (kind === 'po') data = { action: 'void_po_receipt', receipt_id: button.data('id'), void_reason: reason, idempotency_key: makeKey() };
		if (!data) return;
		data.FormID = formId;
		postAction(data, null);
	});

	$('#ma-followup-form, #ma-enquiry-form, #ma-po-form, #ma-quote-form').each(function () {
		$(this).data('submit-label', $(this).find('button[type="submit"]').html());
	});
	$('#ma-message').hide();
	initLookups();
	loadActivity();
})(jQuery);
