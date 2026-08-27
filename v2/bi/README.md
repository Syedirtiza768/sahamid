# Embedded governed BI screen

`index.php` is the first native BI frontend slice. It uses the existing ERP
session, shell, and `sales_dashboard` permission. It does not replace
`v2/salesMainDashboard.php`; that page remains the rollback path while metric
definitions are validated.

The page loads definitions from `api/catalog.php` and sends date-bounded
queries to `api/query.php`. A metric is only given a Run button when its
registry status is `trusted`. Draft and awaiting-validation definitions remain
visible so users can inspect the formula, source lineage, grain, and caveats.
The invoice metric also has two read-only evidence paths. `api/invoice_drillthrough.php`
shows supporting invoice-line rows, while `api/invoice_reconciliation.php`
compares the live invoice formula with linked non-reversed type-10 receivables
and shows the observed GST/services relationship. Reconciliation remains
available while the metric is awaiting validation, but it cannot publish or
change the metric status.

Deployment notes:

- Deploy the `bi/` module and `v2/bi/` files together.
- Apply `migrations/bi_foundation.sql` once per company database before using
  the cache and query-log tables.
- Grant `sales_dashboard` through the existing ERP permission mechanism.
- Validate the production database and metric status before publishing a
  numeric metric.

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
It does not publish `sales.invoice_value`, alter metric metadata, or write to
the source database.
