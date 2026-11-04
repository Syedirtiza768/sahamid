<div class="col-md-4 item" style="height:430px; overflow:auto; width:45%" data-code="outstandingCRV">
	<div style="position:relative; padding:5px; background:white; color:black; z-index:15; width:100%;">
		<select id="crv-salesman-filter" class="js-example-basic-multiple" name="salesmen[]" multiple="multiple" style="width:95%" aria-label="Filter CRV outstanding by salesperson"></select>
		<i class="fa fa-trash removeWidget" style="cursor:pointer"></i>
	</div>
	<div id="totaloutstandingcontcrv" class="item-content" style="background:white; width:98.3%; height:250px"></div>
	<div class="crv-outstanding-details" style="background:white; padding:8px;">
		<strong>Outstanding CRV Orders</strong>
		<div style="max-height:155px; overflow:auto; margin-top:6px;">
			<table class="table table-striped table-condensed" style="margin-bottom:0; color:#222; font-size:12px;">
				<thead>
					<tr>
						<th>Order</th>
						<th>Client</th>
						<th>Salesperson</th>
						<th>Age</th>
						<th style="text-align:right">Remaining</th>
					</tr>
				</thead>
				<tbody id="crv-outstanding-list">
					<tr><td colspan="5">Loading CRV outstanding orders…</td></tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script>
	(function($) {
		var endpoint = 'dashboard/widgets/updated_widgets/outstandingTotalCRVUpdate.php';
		var $salesmanFilter = $('#crv-salesman-filter');
		var optionsLoaded = false;

		function escapeHtml(value) {
			return String(value == null ? '' : value).replace(/[&<>"']/g, function(character) {
				return {
					'&': '&amp;',
					'<': '&lt;',
					'>': '&gt;',
					'"': '&quot;',
					"'": '&#039;'
				}[character];
			});
		}

		function renderChart(data) {
			Highcharts.chart('totaloutstandingcontcrv', {
				chart: { type: 'funnel', height: 250 },
				title: { text: 'Total Outstanding (CRV)' },
				plotOptions: {
					series: {
						dataLabels: {
							enabled: true,
							format: '<b>{point.name}</b> ({point.y:,.0f})',
							color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
							softConnector: true
						},
						center: ['15%', '50%'],
						neckWidth: '30%',
						neckHeight: '35%',
						width: '30%'
					}
				},
				legend: { enabled: false },
				series: [{ name: 'PKR', data: data.chart || [] }]
			});
		}

		function renderRows(rows) {
			if (!rows || rows.length === 0) {
				$('#crv-outstanding-list').html('<tr><td colspan="5">No outstanding CRV orders are available in your access.</td></tr>');
				return;
			}

			var html = rows.map(function(row) {
				var remaining = Number(row.remaining || 0);
				return '<tr>' +
					'<td>' + escapeHtml(row.orderno) + '</td>' +
					'<td>' + escapeHtml(row.client) + '</td>' +
					'<td>' + escapeHtml(row.salesperson) + '</td>' +
					'<td>' + escapeHtml(row.age) + '</td>' +
					'<td style="text-align:right">' + remaining.toLocaleString('en-PK', { maximumFractionDigits: 0 }) + '</td>' +
				'</tr>';
			}).join('');
			$('#crv-outstanding-list').html(html);
		}

		function loadCRVOutstanding() {
			$.ajax({
				type: 'POST',
				url: endpoint,
				dataType: 'json',
				data: { salesmen: $salesmanFilter.val() || [] }
			}).done(function(data) {
				if (!optionsLoaded) {
					(data.salesmen || []).forEach(function(name) {
						$salesmanFilter.append($('<option></option>').val(name).text(name));
					});
					optionsLoaded = true;
					if ($.fn.select2) {
						$salesmanFilter.select2({ placeholder: 'All accessible salespeople', width: '100%' });
					}
				}
				renderChart(data);
				renderRows(data.rows || []);
			}).fail(function() {
				$('#crv-outstanding-list').html('<tr><td colspan="5">Unable to load CRV outstanding orders.</td></tr>');
			});
		}

		$(document).ready(function() {
			$salesmanFilter.on('change', loadCRVOutstanding);
			loadCRVOutstanding();
		});
	})(jQuery);
</script>
