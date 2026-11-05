# Directors’ Reporting Dashboard — implementation and evidence

Implemented 5 November 2026 in the existing namespaced PHP BI module and v2 shell. Entry: `v2/bi/directors.php`, linked from the sidebar and Reports hub. The endpoint is `v2/bi/api/directors.php`; exact read-only evidence without a suitable native single-record page uses `v2/bi/directors-record.php`.

This is an implemented dashboard with explicit coverage limits, not a claim that every legacy report has become a certified metric. No production data, permissions, financial configuration, or operational status was changed. Production reconciliation and deployment were not performed.

## Audit artifacts

- `directors-reporting-business-audit.md`: workflows, dimensions, definition conflicts, gaps and business-question coverage.
- `directors-reporting-finance-audit.md`: finance calculation and permission evidence.
- `directors-reporting-route-inventory.csv`: 581 statically discovered menu/path entries before this implementation.
- `directors-reporting-source-inventory.csv`: 739 reporting candidates across 3,413 business PHP source files, with statically discoverable tables, request filters, permissions and date fields. These include maintenance/wrapper candidates and are not 739 independent production reports. Dynamic SQL/includes and runtime menu conditions remain an inventory limitation.
- Earlier `BI_REPORT_CATALOG.md` and reconciliation packs remain supporting historical evidence; their production counts are explicitly dated, not measurements of the current database.

## Delivered coverage

| Section / question | Implemented datasets | Grain and important boundary |
|---|---|---|
| Financial performance | `gl_result`, `gl_revenue`, `gl_cost_of_sales`, `gl_expenses` | Posted P&L journal rows, journal-date basis. Section 1 income and section 2 COGS follow existing P&L logic. GL integration flags, unposted P&L rows and missing account/group masters are surfaced. |
| Budget versus actual | `gl_budget`, `gl_budget_actual` | Same complete accounting periods and P&L account rows in chartdetails. Unversioned recorded budget, no approval claim or daily proration. All-zero budgets remain unavailable. |
| Working capital | `receivables`, `payables` | Current signed non-reversed ledger residuals / recorded positive FX rate; includes credits and residuals despite stored settled flags. Overdue, unknown due, estimated AP due, hold, settlement, customer/supplier and aging breakdowns. |
| Cash movement | `cash_movement` | Authorized bank transaction rows, both FX factors, signed inflow/outflow. Not available cash, reconciled balance or statutory cash flow. |
| Sales | `invoice_value`, `shop_sales` | Published raw invoice option/detail formula and unique completed CSV/CRV posted values remain separate. Ambiguous documents/options/postings and invalid currency/rates retain unknown amounts. |
| Purchasing | `purchasing`, `purchase_commitments`, `legacy_po_commitments`, `procurement_pipeline` | MPIW received value, dated by slip creation date; estimated unreceived market slips; authorized/printed incomplete legacy PO lines; active procurement-plan lines by stage/publication/ETA. These streams are never added together. |
| Inventory | `inventory`, `stock_exceptions` | Current item/location standard-cost estimate including material, labour and overhead; negative/missing cost/master/reorder exceptions. Unlike stock units are not added to a headline quantity. |
| Expenses | `petty_cash` | Signed PKR claims excluding ASSIGNCASH, including local purchases and credits; assigned-tab scope, approval/posting/receipt/currency-mismatch investigation. Separate from GL expense and MPIW spend. |
| Operational backlog | `open_cases`, `pipeline`, `work_orders`, `pending_deliveries` | Unique current cases/enquiry estimates, open manufacturing orders and canonical ungrouped/uninvoiced/uncouriered/unreturned DC state. No invented historical funnel or delivery service level. |

There are 22 datasets. Each has definitions, source lineage, units, actual/estimate status, available dimensions, supporting detail and exports. Unsupported dimensions are rejected rather than silently ignored. Stable customer/supplier codes and composite customer/branch identities prevent same-name aggregation. Stock product grouping uses narrow stable keys, then enriches only the top groups with labels.

## Investigation and comparison behavior

The overview groups period results and current exposure, highlights source-quality and material-movement exceptions, and exposes a budget/actual comparison. Selecting a measure opens a shared workspace with paired monthly values, ranked contributions, exact cross-filters, removable filter chips, search, sorting, paginated records and source links. A month selection narrows the current interval; comparison mode and supported investigation filters remain in the URL. Browser back/forward restores context. Source records open separately so the investigation remains available.

Previous equal periods, month/quarter/year comparisons, fiscal YTD, complete prior fiscal periods and arbitrary custom comparisons are supported. Fiscal start follows the ERP YearEnd setting. Complete month/quarter/year blocks compare complete previous blocks, while incomplete blocks use elapsed-day windows capped at the previous block end. Leap days clamp to valid dates. Both dates and day counts are visible. Percentage change uses `(current − previous) / abs(previous)`; zero/missing baselines have no percentage. Partial source amounts suppress aggregate and affected-group deltas. Unknown-only amounts retain a path to supporting records.

Current snapshots ignore flow dates and have no historical comparison/trend. Source date ranges and query time are separate. Zero months mean no matching source rows; they do not establish that every external transaction was captured. MPIW cohort values can be restated by later receiving because source quantities are cumulative.

Overview and filtered detail export use the established XLSX writer. Detail exports include active filters, definitions, period context, source dates, completeness and source links. Over 5,000 source records are refused with a narrowing instruction; no silent truncation. Export rows are checked against the summary, and a concurrent source change produces a retry response. Charts return at most 500 groups plus an explicit Other aggregate that reconciles to the summary. Detail is at most 100 rows per request.

## Authorization and deployment

The existing `directorreports` permission or the optional `directors_dashboard` permission grants entry; existing BI administrators (levels 8/10/22 or wildcard) retain administrator access. Ordinary `sales_dashboard` access does not grant directors data. Delegated directors additionally require the relevant existing script token; missing mappings fail closed. Canonical module authorities are used for custom operational source pages. Bank accounts always use bankaccountusers, even for administrators. Delegated expense claims use expense_listing_access. The database/company is resolved from the authenticated ERP session and cannot be selected by request parameters.

Deploy `bi/Directors/`, the new v2 pages/assets/API, and navigation changes together. The existing PHP 7.3+, mysqli/mysqlnd, Composer BI bootstrap, session permission store and PhpSpreadsheet XLSX requirements apply. No new operational capture table or historical backfill is required or silently created. `migrations/directors_reporting_indexes.sql` adds idempotent indexes for invoice/option/detail joins, shop/DC numbers, market-slip links, procurement details, claim dates and posted GL dates. Apply it deliberately per company using the established deployment process; it was applied twice successfully only on the disposable fixture. Existing equivalent indexes should be reviewed before production deployment.

Pending DC group CSV values are parsed once per service instance, matching the established group-membership rule; canonical numeric IDs form the exclusion predicate. Malformed group members make the count unavailable, avoiding false pending results. This removes a repeated group-list scan per DC without adding reporting writes or a new access requirement.

No shared business-data cache is introduced. Responses are private/no-store. Expected source failures become unavailable cards; failed investigations hide stale records and disable exports. Different sources are queried live in sequence; a multi-module overview is not an immutable cross-table snapshot, particularly where source tables use MyISAM.

## Checks performed

All checks below used an isolated local fixture, never production credentials/data. The temporary runtime was official PHP 8.5.12 / MariaDB 11.4.8; source remains PHP 7.3 compatible. The application's older PHP/MariaDB production versions still require deployment validation.

- `php bi/tests/directors_run.php`: 30 date, comparison, input-validation and permission-boundary checks.
- `php bi/tests/directors_finance.php`: full supplied structural schema plus adversarial finance data. GL revenue 1,000 − COGS 400 − other costs 150 = result 450; full-period budget 550 versus actual 450; AR 65 and AP 40; excluded invalid FX; credit signs; duplicate due-date joins; corrected month-end AP due dates; bank authorization and both FX factors; export/detail parity; permission denial.
- `php bi/tests/directors_operations.php`: adversarial operational data. Invoice known subtotal 740 with three excluded amounts; shop posted value 60; MPIW received by slip date 100; market residual estimate 170; stock 45 with unknown-cost rows; stock exceptions 4; unweighted enquiry estimate 15,000; canonical pending DC and case/work-order duplicate handling; legacy PO 30; procurement plan 90; claims 170, scoped claims 80; exact record URL denial across tab boundaries.
- `php bi/tests/directors_integration.php`: all 22 source SQL definitions, plans, summaries, monthly/breakdown/detail reconciliation, pagination and bounded export paths on the populated fixture; approximately 191 ms for the small synthetic fixture on this machine.
- `python bi/tests/directors_ui.py --chrome <chromium-path>`: real production JS and markup with explicit synthetic fetch fixtures, at 1440px and 390px. 23 assertions at each width cover navigation, escaping, unsafe-link blocking, cross-filter/API/URL context, sorting, removal, snapshots, budgets, definitions, partial/unknown amounts, loading, source failure, stale-export prevention and empty state. Restricted local environments can pass `--no-sandbox` for this local fixture only.
- `python bi/tests/directors_export.py --php <php-path> [--ext-dir <portable-extension-directory>]`: real XLSX package, numeric value preservation and literal formula-like source text.
- Existing `bi/tests/run.php`: 22 passed. Existing `bi/tests/expense_run.php`: 17 passed. PHP lint, JavaScript syntax and diff-whitespace checks passed. The existing expense request emits a nullable-parameter deprecation under PHP 8.5; it is unchanged by this implementation.

Fixture commands require explicit `BI_TEST_DB_HOST/PORT/USER/PASSWORD` and a guarded `BI_FIXTURE_DB_NAME=codex_directors_fixture_<unique-name>`. Finance creates the disposable schema. Operations uses it and loads its additional rows only when `BI_OPERATIONS_FIXTURE_LOAD=1`. The read-only integration tool separately requires `BI_TEST_DB_NAME`, `BI_TEST_USER_ID` and optional `BI_TEST_START/END`. These tools never load config.php or infer a production target.

The optional `bi/tests/directors_volume.php` adds only guarded synthetic data when explicitly enabled with `BI_DIRECTORS_VOLUME_LOAD=1`: 400,000 stock positions, 30,000 invoices / 100,000 details, 100,000 journal rows, 100,000 claims, 30,000 DCs and 15,000 group lists. With fixture indexes, measured runs were about 4.5 seconds for the full overview, 3.4 seconds invoice exploration, 6.4–7.3 seconds inventory/product exploration, 0.23 seconds pending DC, and 0.6 seconds claims / GL. Group totals including Other reconciled; detail stayed at 50 rows and oversized exports were rejected. These are local synthetic measurements, not a production SLA. Inventory exploration remains the slowest path and needs target-server plans/timing before release.

## Remaining evidence and data limitations

- Production reconciliation, realistic production distribution/concurrency, actual authenticated browser sessions and deployment were not exercised. Per-company permissions, GL configuration and source schema must be checked in the target environment.
- No certified full balance sheet/equity rollover, bank availability, statutory cash flow, cross-company elimination/translation, historical inventory valuation, immutable historical aging, product gross-margin history, approved forecast or effective-dated target achievement is claimed. These remain visible limitations; the original source reports remain available in the report library.
- Reliable receiving-period purchasing needs dated receipt events linked to the line; current quantity_received supports analysis dated by slip creation only. Current master currency/ownership/terms can restate historical classifications.
- Historical AR/AP allocations can be changed/deleted. Historical snapshot comparison requires immutable snapshots or validated audit reconstruction. No historical records were fabricated.
- Department is exposed only as an explicitly labeled petty-cash cost-center proxy; company branch, fully attributed project profitability, service levels, asset roll-forwards and forecast probabilities require additional validated capture/integrations.
- Approved budget versions and target currency/measure/owner/effective periods are unavailable. The existing unversioned budget can be investigated but cannot establish approval.

## Second pass (2026-11-05, branch `directors-dashboard-v2`)

Audit, reconciliation and findings: `docs/directors-reporting-gap-audit.md`. Runbook: `docs/directors-dashboard-runbook.md`.

**Decisions (2026-11-05):** headline Revenue is posted invoices plus completed shop sales; the default comparison is the same period last year; amounts show in lakh / crore; a daily balance snapshot is approved.

**Added measures** (each reconciles exactly with the Sales Executive Report rule, `bi/tests/directors_reconcile.php`):
`posted_invoices`, `po_received`, `outstanding_invoices`, and `pending_deliveries`, which is now the valued Pending DC List rule (first-option lines only; 308 DCs, not 1,805). Composite headline measures `sales_revenue` and `gross_margin_pct` (Estimated) are built in `DirectorsService::compositeMetrics()` from other measures. They carry no records of their own and open their first component.

**Overview response** now also returns `attention` (ranked, unfavourable changes and exposures above the thresholds in `DirectorsService` constants), `data_health` (records the figures leave out), `polarity` on each metric, and company-time metadata. Default comparison is `yoy`.

**Performance:** the overview reads each measure once (`summaryPair()`: current, comparison and undated rows in one pass). `bi/tests/directors_pair.php` proves it equals the old two-call path on every measure.

**Snapshots:** `migrations/directors_balance_snapshots.sql` plus `bi/tools/directors_snapshot.php` (CLI, idempotent per day, explicit `BI_DB_*` target). Nothing in the UI reads the history yet. Any reader must check access to the source measure first, because the capture is company-wide.

**Tests added:** `directors_reconcile.php`, `directors_pair.php` (read-only, explicit target). The UI fixture checks look tiles up by measure id, not position.

**Layout (second pass, part 2):** one sticky bar holds the title, a period chip (opens the period and comparison popover; presets apply immediately), refresh and export. The investigation view has a hover/focus tooltip on the monthly chart, hatched partial months, contributors split into increases and decreases (top 10 each, an Other row, and a line showing how they add to the total change), and a month focus chip (`month=` in the URL) that narrows the investigation without changing the global period. The chart stays hand-drawn SVG: the repository's bundled Chart.js is v1.0.2 and the only newer copy is loaded from a CDN.
