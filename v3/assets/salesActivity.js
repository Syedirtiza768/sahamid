(function ($) {
	'use strict';

	var app = $('#my-activity-app');
	var apiUrl = app.data('api-url');
	var formId = app.data('form-id');
	var cases = [];
	var quotations = [];

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
		return 'Q#' + item.orderno + ' · ' + item.salescaseref + ' · ' + item.orddate;
	}

	function fillCaseSelects() {
		$('select.ma-case-select').each(function () {
			var select = $(this);
			var optional = select.attr('name') === 'salescaseref' && select.closest('form').attr('id') === 'ma-followup-form';
			var html = optional ? '<option value="">No case (general activity)</option>' : '<option value="">Choose a case</option>';
			$.each(cases, function (_, item) {
				html += '<option value="' + escapeHtml(item.salescaseref) + '">' + escapeHtml(caseLabel(item)) + '</option>';
			});
			select.html(html);
		});
	}

	function fillQuoteSelects() {
		$('select.ma-quote-select').each(function () {
			var select = $(this);
			var caseRef = select.closest('form').find('select.ma-case-select').val() || '';
			var html = '<option value="">None</option>';
			$.each(quotations, function (_, item) {
				if (caseRef !== '' && item.salescaseref !== caseRef) return;
				html += '<option value="' + escapeHtml(item.orderno) + '">' + escapeHtml(quoteLabel(item)) + '</option>';
			});
			select.html(html);
		});
		var quoteSent = $('#ma-quote-form select[name="orderno"]');
		var quoteHtml = '<option value="">Choose a quotation</option>';
		$.each(quotations, function (_, item) {
			quoteHtml += '<option value="' + escapeHtml(item.orderno) + '">' + escapeHtml(quoteLabel(item)) + '</option>';
		});
		quoteSent.html(quoteHtml);
	}

	function renderTables(data) {
		var rows = data.followups || [];
		var html = '<thead><tr><th>When</th><th>Case</th><th>Quotation</th><th>Type</th><th>Outcome</th><th>Next action</th><th></th></tr></thead><tbody>';
		$.each(rows, function (_, row) {
			var voided = row.voided_at !== null;
			html += '<tr class="' + (voided ? 'text-muted' : '') + '"><td>' + escapeHtml(row.activity_at) + '</td><td>' + escapeHtml(row.salescaseref || '—') + '</td>' +
				'<td>' + (row.quotation_orderno ? 'Q#' + escapeHtml(row.quotation_orderno) : '—') + '</td><td>' + escapeHtml(row.activity_type) + '</td>' +
				'<td>' + escapeHtml(row.outcome) + '</td><td>' + escapeHtml(row.next_action || '') + (row.next_action_date ? ' · ' + escapeHtml(row.next_action_date) : '') + '</td>' +
				'<td>' + (voided ? 'Voided: ' + escapeHtml(row.void_reason || '') : '<button type="button" class="btn btn-xs btn-link text-danger ma-void" data-kind="followup" data-id="' + escapeHtml(row.id) + '">Void</button>') + '</td></tr>';
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
				cases = data.cases || [];
				quotations = data.quotations || [];
				fillCaseSelects();
				fillQuoteSelects();
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

	function resetForm(form) {
		var keep = {};
		form.find('input[name="activity_date"], input[name="activity_time"], input[name="event_date"], input[name="event_time"], input[name="received_date"], input[name="received_time"], input[name="issued_date"], input[name="issued_time"], input[name="currency"]').each(function () {
			keep[this.name] = this.value;
		});
		form[0].reset();
		$.each(keep, function (name, value) { form.find('[name="' + name + '"]').val(value); });
		form.find('[name="idempotency_key"]').val(makeKey());
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
				fillQuoteSelects();
				loadActivity();
			})
			.fail(function (xhr) {
				var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Could not save. Check the details and try again.';
				showMessage(message, 'error');
				if (form) form.find('.ma-form-message').text(message);
			})
			.always(function () { if (button) button.prop('disabled', false); });
	}

	$('.ma-case-select').on('change', fillQuoteSelects);

	$('#ma-followup-form, #ma-enquiry-form, #ma-po-form, #ma-quote-form').each(function () {
		$(this).find('[name="idempotency_key"]').val(makeKey());
	});

	$('#ma-followup-form, #ma-enquiry-form, #ma-po-form, #ma-quote-form').on('submit', function (event) {
		event.preventDefault();
		var form = $(this);
		var data = form.serializeArray();
		var caseField = form.find('select.ma-case-select');
		if (caseField.length && caseField.prop('required') && !caseField.val()) {
			showMessage('Choose one of your sales cases.', 'error');
			return;
		}
		postAction($.param(data), form);
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

	$('#ma-message').hide();
	loadActivity();
})(jQuery);
