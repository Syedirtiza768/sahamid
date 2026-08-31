# Embedded governed BI screen

`index.php` is the first native BI frontend slice. It uses the existing ERP
session, shell, and `sales_dashboard` permission. It does not replace
`v2/salesMainDashboard.php`; that page remains the rollback path while metric
definitions are validated.

The page loads definitions from `api/catalog.php` and sends date-bounded
queries to `api/query.php`. A metric is only given a Run button when its
registry status is `trusted`. The invoice metric is published as the validated
raw invoice-detail measure; its gross AR and tax-basis comparison remains
available through the reconciliation action.
The invoice metric also has two read-only evidence paths. `api/invoice_drillthrough.php`
shows supporting invoice-line rows, while `api/invoice_reconciliation.php`
compares the live invoice formula with linked non-reversed type-10 receivables
and shows the observed GST/services relationship. Reconciliation remains
available after publication and does not mutate source data or metric status.

## Enhanced report library

`reports.php` is an additive BI library for the complete current Reports hub:
81 rendered hub entries plus five report-only sidebar entries. The catalog also
contains the Supplier Relationship Intelligence and Invoice Value Analysis
BI-native workspaces, for 88 catalog entries in total. The duplicate `Reorder
Level PS` source key is intentionally listed once because PHP renders only the
second key.

Every catalog entry has a stable ID, category, legacy route, source/grain
metadata, and migration status. The 86 legacy-source entries run their existing
production report page and report-specific filters inside `live_report.php`,
so they return the same authorized information as the source route instead of
showing an empty migration placeholder. The shared workspace adds live table
search, column visibility, compact/comfortable density, navigation, refresh,
print, fullscreen, private display-only saved views, and bounded visible-table
XLSX export. Supplier Relationship Intelligence, Expense Intelligence, and
Invoice Value Analysis are the three BI-native workspaces with dedicated data
APIs.

`api/table_export.php` accepts only a same-session, marked POST request and
exports the already rendered visible table. It does not query operational
tables, caps browser exports at 5,000 rows and 100,000 cells, and stores only
the source path in workbook metadata. Saved live-report views never persist
source query parameters or report data.

`invoice.php` and `api/invoice_report.php` add server-side summary, monthly
trend, sortable/paginated detail, invoice-number drill-through, salesperson
scope enforcement, and bounded XLSX export. The row-level value formula is the
same published formula used by `QueryService`; the endpoint is read-only and
includes company/database context in its governed metadata.

## Expense Intelligence

expenses.php is the first granular expense report under the BI section. It
reads petty-cash claims from pcashdetails, enriches them with the configured
expense description, petty-cash tab, owner, GL account, tab type, and legacy
tag, and keeps ASSIGNCASH cash transfers separate from spend. The report
supports date, category, tab, owner, authorization, posting, and text-search
filters; grouping by category, enhanced description classification, GL,
owner, tab, tab type, or legacy tag; monthly trend analysis; drill-down rows;
previous-period KPI comparisons; deterministic high-value exceptions; paginated
and sortable detail; genuine XLSX export; and data-quality signals for missing
masters, receipts, pending approval, unposted claims, and source-sign anomalies.

The enhanced classification is derived at read time from normalized
pcexpenses.description plus codeexpense using whole-word and phrase rules
tailored to the live expense vocabulary. The UI displays the full
description-to-tag dictionary, the matching keyword signal, and the count of
descriptions left as Other / Review for human confirmation. It does not write
back to the operational expense tables. Refresh responses include measured
server duration, while the first filter catalog is reused by the browser on
subsequent refreshes to keep the payload focused.

The report also includes a deterministic prompt box. A user can describe a
requested view in plain language, such as “show pending claims by owner for
last month” or “list top 5 transactions with missing receipts over 100k”. The
server tokenizes the request, recognizes only allowlisted dates, measures,
dimensions, lookup values, statuses, receipt rules, amount thresholds, and
limits, then executes bound parameters against the same read-only report
query. The UI shows the interpretation, recognized and ignored tokens,
warnings, a parameterized SQL template, and an interactive summary, grouped
table, or transaction-detail result. Free-form SQL and AI-generated queries
are disabled by design.

Deployment notes:

- Deploy the `bi/` module and `v2/bi/` files together.
- Apply `migrations/bi_foundation.sql` once per company database before using
  the cache and query-log tables.
- Apply `migrations/expense_intelligence_indexes.sql` once per company
  database so date-range expense scans can use the dedicated
  `pcashdetails.date` index.
- Grant `sales_dashboard` through the existing ERP permission mechanism.
- Run the read-only production verifier after deployment to confirm the
  published metric against the target company database.

## Production verification

The production check is explicit and read-only. Supply the production target
through environment variables; no host, password, or database target is
committed to the repository:

```text
BI_PROD_DB_HOST=db.example.internal
BI_PROD_DB_PORT=3306
BI_PROD_DB_USER=read_only_user
BI_PROD_DB_PASSWORD=...
BI_PROD_DB_NAME=sahamid
php bi/tools/production_invoice_check.php --start=2026-01-01 --end=2026-08-27
```

The command first checks the invoice reconciliation schema, then runs the same
live reconciliation service used by the BI frontend. It returns non-zero when
the target cannot be reached, the schema is incompatible, or the query fails.
It does not alter operational source data. The BI metadata publication is
performed separately by `migrations/bi_publish_invoice_value.sql` after the
foundation migration.
