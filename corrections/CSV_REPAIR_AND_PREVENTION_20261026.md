# SQL repairs and attachment prevention

Prepared 26 October 2026. The production findings were observed on 23 October;
the repair procedures verify the current database state rather than assuming it
is unchanged. No production repair has been executed by this work.

## Run the three voucher corrections

Use `repair_csv_10139_9992_9996_20261026.sql` in a MariaDB/MySQL client which
supports `DELIMITER`, such as the mysql client or phpMyAdmin's import interface.
Select the ERP database first. Use a database administrator with CREATE ROUTINE,
CREATE TABLE, SELECT, INSERT and UPDATE privileges. Routines run with the
caller's permissions, not extra privileges inherited from their creator.

1. Take a database backup and stop editing/finalizing these three vouchers while
   the repair runs. The procedures lock their headers and affected component,
   cart and accounting rows; not all legacy application writers use those locks.
2. Load the SQL file. This creates first-state backup tables and repair routines.
   Its default calls are **previews**, so no operational voucher/stock rows are
   changed. DDL creates the supporting objects outside the repair transactions.
3. Read every result set. A SIGNAL error means the current state differs from
   the reviewed case; that repair rolls back. Investigate the reported condition
   instead of removing its check. Each voucher has an independent transaction.
4. Apply 9992 and 9996 only after their previews match the expected records:

```sql
CALL csvfix_9992_20261026(1);
CALL csvfix_9996_20261026(1);
```

5. The default preview for 10139 prints its allocations, dispatch references and
   existing stock movements. Confirm the SPD actually supplied. The old matching
   catalogue code `9381SURGPRHDY3402275` was an **unconfirmed candidate**, not a
   proven dispatch match. If it is the code shown by the original dispatch:

```sql
-- First preview the confirmed code:
CALL csvfix_10139_20261026(0,'9381SURGPRHDY3402275');
-- Then apply only if that preview is correct:
CALL csvfix_10139_20261026(1,'9381SURGPRHDY3402275');
```

   If the dispatch instead confirms the reviewed newer matching AC model,
   substitute `9381SURGPRHDY3N402275`. The procedures reject unrelated models,
   insufficient stock, unsupported allocations and unexpected posting counts.
   Do not substitute a matching description for evidence of the supplied code.
6. Refresh the internal prints. Run `inspect_csv_10139.sql` and check the repair
   log and backup tables:

```sql
SELECT * FROM csvfix_20261026_log ORDER BY id;
SELECT orderno,complete FROM shopsale WHERE orderno IN (9992,9996,10139);
SELECT orderno,lineno,stockid,quantity,rate,discountpercent
FROM shopsalesitems WHERE orderno IN (9992,9996,10139)
ORDER BY orderno,lineno,id;
SELECT transno,stockid,SUM(qty) AS delivered_quantity
FROM stockmoves WHERE type=750 AND transno IN (9992,9996,10139)
GROUP BY transno,stockid;
SELECT transno,id,ovamount,alloc,settled,reversed
FROM debtortrans WHERE type=750 AND transno IN (9992,9996,10139);
```

### What each correction does

| CSV | Correction | Financial amount retained |
|---|---|---:|
| 9992 | Restore one fuse component per customer unit (four fuses total); set completion to 1. Keep the existing stock movement and accounting posting. | PKR 28,000 |
| 9996 | Set the component to `7.31E0/24` metres per displayed foot and rate to `2400E0/7.31E0`, discount 0. Reconcile the outward movement's known 7-versus-7.31 rounding error and remaining allocation. If needed, append one current-dated stock correction for the missing 6.5792 metres. | PKR 2,400 |
| 10139 | Attach the confirmed SPD to the uniquely identified PKR 6,000 line. If its one-unit stock delivery already exists, only restore the link. Otherwise validate the original dispatch and allocation, then deduct one unit exactly once. | PKR 21,830 |

The 9996 correction uses the **recorded dispatch of 7.31 metres**, not a claim
that 24 international feet is exactly 7.31 metres. It preserves later cart
activity by applying a difference rather than setting the entire cart to zero.
A new inventory correction is dated when the SQL is applied and uses that
month's existing accounting period. The original historical delivery stays in
place. Previously repaired single-movement states are also recognized.

The procedures never insert or update `debtortrans`, `gltrans`, receipts or
payments. They verify that the existing accounting amount, allocation and
settlement state are retained. The `csvfix_20261026_*` backup tables preserve
the first pre-repair versions of original rows; the log records the database
operator and repair action. Repeating an apply does not deduct stock again.
Keep these backups until reconciliation is signed off. A rollback of an applied
inventory correction requires reconciling subsequent activity; do not blindly
replace current stock balances with historical backup values.

## What the evidence proves

For 9992, the earlier audit records an attachment and a completed stock/accounting
posting, while the later data has no attachment and an open header. This proves
the association was lost after it existed. It does not identify every later
deletion or administrative change.

For 10139, the internal print proves the link was absent. That print alone
cannot establish whether the insert never persisted, the row was deleted later,
or a concurrent request changed the voucher. Obsolescence hides an item from
the attachment search, but does not itself delete an existing attachment.

`DB_query()` normally exits on a database error, so an INSERT failure alone has
not been proven to cause a successful response. The attachment endpoint still
does not perform a committed read-back before reporting success.

## Permanent application fix

The SQL correction repairs existing data. Reliable attachment needs these
application changes, followed by deployment and verification on production.

### 1. Save the component and its quantity together

`shop/pos/api/attachOrignalSKU.php` inserts a component with **quantity 0**, then
reports success. `editShopSale.php` later saves quantity/discount in another
request. Therefore "attached" currently means a zero-quantity draft row was
created, not that a complete usable component was saved.

Have the attach request send validated quantity and save its rate/discount in
the same transaction. If zero-quantity drafts must be retained, show them as
**Pending quantity**, not **Attached and saved**, and reject finalization until
they are complete.

### 2. Serialize all writes to the same voucher

Every attachment, removal, component edit and customer-line edit must follow
the same lock order as finalization:

```sql
START TRANSACTION;
SELECT id,complete FROM shopsale WHERE orderno=? FOR UPDATE;
-- Reject complete=1 and any existing non-reversed type-750 posting.
-- Validate ownership, line/order relationship, allocation and quantity.
-- Save the component; check the write result and the exact persisted row.
-- Record the before/after event in the same transaction.
COMMIT;
-- Only now return success and the persisted component to the browser.
```

Apply this to attach/remove, component quantity/discount, delete-line, line
quantity/price/description and header-discount endpoints. A pre-write ordinary
SELECT of `complete` is insufficient: deletion can pass that check before
finalization commits, then run afterward. The shared header lock closes that
race. Check the existing ledger posting too, so reopening a header cannot make
an already-posted sale editable again.

Return JSON errors consistently. For example, `removeInternalItem.php` currently
returns `status: success` for permission-denied and missing-parameter cases,
which lets the browser remove a row even when the server did not delete it.

### 3. Finalize from persisted data only

The original missing-line query tested `shopsalesitems.orderno` on the missing
side of a LEFT JOIN, where that value is NULL. It consequently missed an
unattached line. The corrected check already prepared locally is:

```sql
SELECT l.id
FROM shopsalelines l
LEFT JOIN shopsalesitems i ON i.orderno=l.orderno AND i.lineno=l.id
WHERE l.orderno=? AND i.id IS NULL;
```

Run this under the voucher lock, then validate every component's positive
quantity, valid stock code and total stock requirement. Commit components,
inventory, accounting and the completion flag atomically. Repeated finalize
requests must return the already-created posting or a clear error, rather than
create another posting. Give non-stock/service lines an explicit business type
and exemption instead of silently bypassing this validation.

### 4. Make browser status reflect committed database state

- Await every attachment/quantity/delete request before enabling Finalize.
- Check both HTTP/network failure and the structured JSON result. On a failed
  save, leave the row marked unsaved and keep Finalize disabled.
- After success, render the server's persisted component, then reload/read the
  server's component list. Use `item.line` for row ownership; the current
  `addInternalItem()` reads the mutable global `selecteditemindex` instead.
- Save only the changed row. The current `.itemQuantity` handler loops over
  every component and sends parallel updates whenever any quantity changes.
- Use an idempotency key and a unique `(orderno,lineno,stockid)` constraint so
  retries or double clicks cannot create duplicate attachments.

### 5. Keep allocated legacy stock selectable

`itemSearch.php` filters `stockmaster.discontinued=0`. Permit an obsolete code
only when it is already allocated to this voucher and salesperson with a
positive available quantity; label it Obsolete in the result. Do not reactivate
the entire catalogue item or allow unrestricted new obsolete-stock allocation.

Deploy `migrations/20261023_preserve_shop_dispatch_quantity.sql` to preserve
fractional CSV/CRV allocations. New unit conversions must use explicit units and
approved rounding rules. Do not shrink the component quantity to pass a
whole-number availability check.

### 6. Add database integrity and traceability

After reviewing existing duplicates/orphans, add a unique voucher number,
unique component key, and composite foreign key from `(lineno,orderno)` to
`shopsalelines(id,orderno)`. Retain a stock-master foreign key compatible with
legacy catalogue records. Validate these changes against the live schema and
historical exceptions before adding constraints.

Keep an attachment event log with user, timestamp, request ID, action, voucher,
line, stock code and before/after values. Log attach, quantity changes, removal,
reopen, finalization and reversal in the same transaction as the change. Posted
components must only be changed through a controlled reconciliation/reversal
workflow with a reason. An exception report should highlight completed vouchers
with missing/zero components and open headers that already have a posting.

## Deployment status and validation

- Existing local changes: corrected finalization missing-line query, duplicate
  posting check, saved component-rate display, and decimal-allocation migration.
- The other permanent changes above are implementation requirements, not a claim
  that they have already been deployed. The three SQL repair procedures do not
  install those application protections.
- Repair SQL was exercised on local MariaDB 10.3 in unique disposable schemas,
  including the original reviewed 9992/9996 states and explicit 10139 fixtures.
  The committed test suite uses tracked, sanitized fixtures and needs no private
  database backup or existing ERP schema. With local MariaDB running, install
  PyMySQL and run `python tests/CsvRepairSqlTest.py`.
  Production 10139 dispatch details remain unverified.
- Tests cover unchanged previews, unconfirmed/wrong SKU rejection, insufficient
  allocation, late-failure rollback, repeat execution, existing stock postings,
  later cart activity, prior local-style repairs and unchanged accounting.

Acceptance tests for the permanent patch must also cover failed attachment,
navigation/reload after success, simultaneous attach/finalize and
remove/finalize requests, attempts to edit a reopened-but-posted voucher,
obsolete-but-allocated stock, and fractional units.
