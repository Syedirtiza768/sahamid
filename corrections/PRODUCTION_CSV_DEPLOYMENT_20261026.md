# Production deployment: CSV attachment validation and voucher repairs

This release contains the corrected missing-component finalization check,
protection against posting an already-posted voucher again, saved component-rate
display, the decimal-allocation migration, read-only diagnostic queries, and
guarded repair procedures for CSVs 9992, 9996 and 10139.

The attach transaction, shared locks in all editing endpoints, pending-save UI,
obsolete-stock selection and integrity/event-log improvements are follow-up
requirements described in `CSV_REPAIR_AND_PREVENTION_20261026.md`. They are not
implemented by this release. Deployment must not be described as completing
every attachment-loss prevention measure.

## 1. Before updating

- Schedule a maintenance window for CSV/CRV editing and finalization.
- Back up the production ERP database and the existing application release.
- Record the current application commit with `git rev-parse HEAD`.
- Check `git status --short`. Preserve/reconcile server modifications before
  pulling; do not use `git reset --hard` to discard them.

## 2. Deploy the application

In the production ERP Git checkout:

```sh
git fetch origin master
git log --oneline HEAD..origin/master
git pull --ff-only origin master
git rev-parse --short HEAD
php -l shop/pos/api/finalizeShopSale.php
php -l shop/pos/editShopSale.php
```

Compare the resulting commit with the release commit supplied in the chat.
Review any intervening commits if production was behind the previous master.
If production is deployed by file copy instead of Git, deploy the two changed
PHP files and the migration/correction files as one reviewed release. Refresh
PHP's opcode cache or reload its service if the server retains old PHP code.
Use the production server's normal service-management procedure.

## 3. Preserve fractional CSV/CRV allocations

Using the actual production ERP database name and an authorized database
administrator, import:

```text
migrations/20261023_preserve_shop_dispatch_quantity.sql
```

For the mysql command-line client, replace `DBA_USER` and `ERP_DATABASE`:

```sh
mysql -u DBA_USER -p ERP_DATABASE < migrations/20261023_preserve_shop_dispatch_quantity.sql
```

This changes `ogpcsvref.quantity` and `ogpcrvref.quantity` to DECIMAL(18,6).
It preserves existing values, but cannot reconstruct fractions already lost.
The two ALTER statements are DDL and commit independently. If one fails, stop
and inspect which table changed before proceeding. Do not force through errors.

## 4. Preview the voucher repairs

Import this file into the selected production ERP database:

```text
corrections/repair_csv_10139_9992_9996_20261026.sql
```

Or use the mysql client:

```sh
mysql -u DBA_USER -p ERP_DATABASE < corrections/repair_csv_10139_9992_9996_20261026.sql
```

Loading the file installs repair routines and backup tables only; it does not
execute any repair or preview. This avoids old phpMyAdmin import handlers which
fail to consume CALL result sets. Routine
installation requires CREATE ROUTINE, ALTER ROUTINE (for replacement), EXECUTE,
CREATE TABLE, SELECT, INSERT, UPDATE and ALTER privileges. Routine execution uses
the caller's existing privileges.

After installation, run `preview_csv_10139_9992_9996_20261026.sql` with the mysql
client, or use database-level **Routines > Execute** in phpMyAdmin for each
repair, with `apply_changes=0`. For 10139's first preview, pass SQL NULL as the
stock code. No operational data is changed by preview mode.

Read all preview result sets. A SIGNAL means the current production state no
longer matches the reviewed scenario; reconcile that condition before applying.
Do not remove the guards to make a script run.

### Recovery from the old phpMyAdmin CALL/import error

`#2014 Commands out of sync`, an error on `SET FOREIGN_KEY_CHECKS = ON`, and
`Undefined index: ORDER BY` notices can occur after a procedure has already run.
The original installer ended with preview calls; reaching the last preview in
that file suggests the routines were already installed, but verify explicitly.

Open a fresh phpMyAdmin page, select the database (rather than the locstock
table), and run `check_csv_repairs_20261026.sql`, one SELECT at a time if needed.
The routine list confirms installation; the committed log and current component,
stock and accounting rows establish whether an apply completed. Keep foreign-key
checks enabled. Use the mysql client or Routines > Execute for later CALLs.

If all three routines already exist and only previews ran, the original
installation need not be repeated. For a fresh installation or reinstallation,
use the revised installer which contains no executable CALL statements.

## 5. Apply each reviewed repair

In the same selected ERP database, apply the first two only after successful
previews:

```sql
CALL csvfix_9992_20261026(1);
CALL csvfix_9996_20261026(1);
```

For 10139, read its dispatch/allocation evidence first:

```sql
CALL csvfix_10139_20261026(0,NULL);
```

Confirm the actual AC SPD stock code. `9381SURGPRHDY3402275` remains a candidate
until the dispatch confirms it. If confirmed, preview and then apply:

```sql
CALL csvfix_10139_20261026(0,'9381SURGPRHDY3402275');
-- Run this only after the dispatch and preview match:
CALL csvfix_10139_20261026(1,'9381SURGPRHDY3402275');
```

If the dispatch confirms the newer reviewed matching code, substitute
`9381SURGPRHDY3N402275`. An unsupported code/state requires reconciliation.
Each voucher has an independent transaction and rolls back on an error.
In older phpMyAdmin versions, use Routines > Execute with `apply_changes=1` for
these apply calls as well, or use the mysql client; avoid its SQL/import CALL
path if it produces the error described above.
Do not reopen and ordinarily finalize the three vouchers to implement repairs.

## 6. Verify before reopening editing

- 9992: fuse attachment exists; four fuses total; completed; bill PKR 28,000;
  original stock and accounting postings retained.
- 9996: total pipe delivery reconciles to the recorded 7.31 metres; component
  discount 0; bill PKR 2,400. Inspect any dated corrective stock movement and
  the remaining allocation. Later cart activity must remain represented.
- 10139: the confirmed SPD is attached to the PKR 6,000 line; its total stock
  delivery is one unit; bill PKR 21,830. Other existing postings are retained.
- Confirm one non-reversed type-750 accounting posting per voucher and unchanged
  customer settlement/allocation. Check `csvfix_20261026_log` and retain the
  `csvfix_20261026_*` backups and snapshot markers.
- In staging, verify that a draft with a missing component cannot finalize,
  that a valid draft can finalize once, and that a reopened-but-posted voucher
  is rejected. Avoid creating dummy financial postings in production.

The full verification SQL is in `CSV_REPAIR_AND_PREVENTION_20261026.md` and
`inspect_csv_10139.sql`. `audit_csv_attachment_issues.sql` can identify the wider
remaining backlog; the three targeted repairs do not repair other vouchers.

## Recovery

Application rollback and data rollback are separate. Revert the application
through the normal deployment process if necessary. Keep decimal allocation
columns; converting them back to INT can lose newly entered fractions.

For data recovery, review the per-voucher first-state backups, repair log and
subsequent inventory activity. Reconcile a corrective movement rather than
blindly restoring an old cart balance. The SQL does not rewrite customer
accounting or payment entries.
