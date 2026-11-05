# Directors' reporting dashboard: gap audit (second pass)

Branch `directors-dashboard-v2`, audited 2026-11-05 against the first build (commit `58b45abd`).

**Evidence base**
- Code read end to end: `v2/bi/directors.php`, `directors.js`, `directors.css`, `v2/bi/api/directors.php`, `directors-record.php`, `bi/Directors/*`.
- Read-only runs against the local development database `sahamid` (container `sahamid-db`, session set to `READ ONLY`). Nothing was written to it.
- The company `YearEnd` setting in that database is **3**, so the fiscal year starts **1 April**.
- Rendered the real page and JS with synthetic data at 1440, 1024 and 390 px (`docs/directors-ux/before/`, 12 images). The record evidence page was **not** captured, because it needs the full PHP session stack.
- Figures below come from the local development copy. They show how the rules behave. They are not certified production numbers.

## 1. Test baseline (before any change)

| Test | Result |
|---|---|
| PHP lint of 11 Directors files | clean |
| `bi/tests/directors_run.php` (pure) | 30/30 pass |
| `directors_finance.php` (fixture DB) | pass |
| `directors_operations.php` (needs `BI_OPERATIONS_FIXTURE_LOAD=1` on a fixture DB that already exists) | pass; fails with "expected 740" if the load flag is missing |
| `directors_ui.py` (headless Chrome, 1440 and 390 px) | pass |
| `directors_integration.php` on the local `sahamid` DB, Sep 2026 | 22/22 datasets pass, 12.7 s total |
| `directors_export.py` | **not run**: `php73-full-local` has no `zip` extension |
| `directors_volume.php` | not run yet (Step 20) |

## 2. Changes since 58b45abd
`git log 58b45abd..HEAD` is empty. The inventories are current as of that commit. Their route and source CSVs were not re-diffed, because nothing changed.

## 3. Coverage matrix

Status: C = covered, P = partial, M = missing (data exists), B = blocked by data.
Label = R4 label shown today (A = Actual, E = Estimated).

| # | Area | Measure | Source / dashboard location | Label | Status | Evidence / gap |
|---|---|---|---|---|---|---|
| 1 | Profit | GL revenue | `gl_revenue`, overview | A | P | Not reconciled to invoices (section 4, R7/R8) |
| 2 | Profit | Cost of sales | `gl_cost_of_sales` | A | C | |
| 3 | Profit | Gross margin % | none | – | C | Derivable from rows 1 and 2; must be labelled Estimated |
| 4 | Profit | Operating / other costs | `gl_expenses` | A | C | |
| 5 | Profit | Net profit | `gl_result` | A | P | Shows +2,583% on a 0.6M base; UI has no "small base" guard |
| 6 | Profit | Budget vs actual | `gl_budget`, `gl_budget_actual` | E | P | Budget value null when the selection has no non-zero budget |
| 7 | Cash | Bank net movement | `cash_movement` | A | P | Shows **0 and "available"** for a user with no assigned bank accounts, 15.8M for one with 20 |
| 8 | Cash | Closing bank balance | none | – | P | `banktrans` can give a running balance; not built |
| 9 | Cash | Cash forecast | none | – | B | No integrated forecast source |
| 10 | Receivables | Net receivables | `receivables` | A | P | 21,333 of 25,919 rows are `settled=1` with a residual balance |
| 11 | Receivables | Overdue | `receivables.overdue` | A | P | Gross overdue 321.5M is larger than net receivables 125.6M; the Outstanding Invoices rule gives 140.2M |
| 12 | Receivables | Aging buckets | `age_bucket` dimension | A | P | Available when investigating; not on the overview |
| 13 | Receivables | Top overdue customers | breakdown by customer | A | P | Needs the `due_status` filter; no shortcut |
| 14 | Receivables | DSO | none | – | C | |
| 15 | Payables | Net payables | `payables` | A | C | Formula matches AgedSuppliers by code reading; report not executed |
| 16 | Payables | Due in 7 / 30 days | none | – | B | `due_date` exists on the facts |
| 17 | Payables | Overdue / aging | `payables.overdue` | E | P | Overdue = 0 while 3,071 balances have an unknown due date |
| 18 | Payables | Top suppliers owed | breakdown by supplier | A | P | No shortcut |
| 19 | Sales | PO received (approved) | none | – | C | Rule exists in `salesExecutiveData.php`; not in Directors |
| 20 | Sales | Pending DC List: count | `pending_deliveries` | A | C | **1,805 vs 308** in the Pending DC List rule (section 4, R5) |
| 21 | Sales | Pending DC List: value | none | – | C | Rule exists (308 DCs, 68.67M) |
| 22 | Sales | Posted invoices (approved revenue) | none | – | C | Dashboard shows "Invoice detail value", a different figure |
| 23 | Sales | Invoice line value | `invoice_value` | A | C | Matches the Sales Main Dashboard exactly |
| 24 | Sales | Shop sales | `shop_sales` | A | C | Matches exactly |
| 25 | Sales | By salesperson / customer / branch / category | dimensions | A | P | "Salesperson" = case owner; the executive report uses `custbranch.salesman` |
| 26 | Sales | Target vs achievement | none | – | M | `salesman.target` exists (annual, no dated history); must be Estimated |
| 27 | Sales | Outstanding invoices | none | – | C | Rule exists (1,499 invoices, 186.4M) |
| 28 | Purchasing | Purchases received (MPIW) | `purchasing` | A | C | Title uses "cohort" wording |
| 29 | Purchasing | Unreceived commitments | 3 datasets | E | P | 13/462, 6/11 and 1,844/1,846 rows excluded; three overlapping measures |
| 30 | Purchasing | Supplier spend | `purchasing` by supplier | A | C | |
| 31 | Inventory | Stock value | `inventory` | E | P | 8,403 of 20,394 positions have no valid cost; 1,193.6M is a subtotal |
| 32 | Inventory | By category / location | dimensions | E | C | |
| 33 | Inventory | Below reorder / negative | `stock_exceptions` | A | C | 8,896 positions |
| 34 | Inventory | Slow movers / stock aging | none | – | B | No movement-lineage history validated (already in limitations) |
| 35 | Concentration | Top-10 customer share | breakdown `share_percent` | A | C | Only inside an investigation |
| 36 | Concentration | Top-10 supplier share | breakdown | A | C | Only inside an investigation |
| 37 | Operations | OC → DC → invoice cycle time | none | – | C | Dates exist in `ocs`, `dcs`, `invoice` |
| 38 | Operations | Invoice → collection time | none | – | B | Allocations can be edited, so history is unreliable |
| 39 | Operations | Stuck / aged pending DCs | none | – | P | The executive report has age buckets |
| 40 | Operations | Open work orders | `work_orders` | A | C | |
| 41 | Operations | Open sales cases | `open_cases` | A | P | 68,801 "open" = 83% of 82,894 cases; 50,801 started before 2024 |
| 42 | Operations | Pipeline value | `pipeline` | E | P | 68,758 of 68,801 open cases (99.9%) have no enquiry value |
| 43 | Expenses | Petty cash by type | `petty_cash` | A | C | |
| 44 | Cross | Consolidation across companies | none | – | B | One company per ERP session |
| 45 | Cross | Forecast figures | none | – | B | No approved model |

**Counts at the original audit (45 rows):** covered 10, partial 19, missing 11, blocked by data 5.

**Counts after the second-pass build (same 45 rows, counted from this table):** covered 21, partial 17, missing 1, blocked by data 6. Rows changed: 3, 8, 14, 16, 19, 20, 21, 22, 27, 28, 35, 36, 37, 39. Several "covered" rows are labelled Estimated (gross margin, DSO, cycle time, bank book) and two carry open reconciliation findings (N7 bank vs GL, R7/R8 GL revenue).

## 4. Reconciliation of existing measures
Local `sahamid` DB, 2026-11-05. Sep 2026 is a closed month. FY YTD = 1 Apr – 5 Nov 2026, the fiscal year of the configured `YearEnd=3`.

| Ref | Measure | Period | Dashboard | Source (rule) | Difference | Cause |
|---|---|---|---|---|---|---|
| R1 | Invoice detail value | Sep | 25,764,833.24 | 25,764,833.24 (Sales Executive "Invoice line value") | 0 | Same rule |
| R2 | Invoice detail value | FY YTD | 280,182,772.10 | 280,182,772.10 | 0 | Same rule |
| R3 | Shop sales | Sep / FY | 4,104,947 / 29,549,450 | 4,104,947 / 29,549,450 | 0 / 0 | Same rule |
| R4 | **Posted invoices (approved "Invoiced")** | Sep / FY | not shown | 29,242,058.23 / 319,359,873.79 | line value is 11.9% / 12.3% lower | Posted amount includes tax; line value does not. The approved headline is missing |
| R5 | **Pending DC count** | today | 1,805 | 308 (Pending DC List rule) | +1,497 (+486%) | **Verified:** 1,497 of the 1,805 DCs have no first-option line in `dcdetails`/`dcoptions`, which the approved rule requires. 1,805 − 1,497 = 308 |
| R6 | Net receivables vs Outstanding | today | 125,628,786 | 186,429,018 | −32.6% | Different rules. Directors nets every `debtortrans` row (AgedDebtors formula). Outstanding Invoices covers unsettled invoices on a case, less GST/WHT |
| R6b | Overdue receivables | today | 321,549,637 | 140,165,109 | +129% | Directors adds only positive overdue rows, so it ignores 324.4M of credits and unapplied receipts |
| R7 | **GL revenue** | Sep | 45,499,182 | 29,242,058 + 4,104,947 = 33,347,005 | +12.15M (+36%) | **Unexplained.** Partly explained: GL type-10 journals cover 222 invoice documents, not 197. For those documents GL revenue is 36.65M against 41.38M of posted amount. A further 2.59M is type-602 revenue |
| R8 | **GL revenue** | FY | 313,580,169 | 319,359,874 + 29,549,450 = 348,909,324 | −35.3M (−10.1%) | **Unexplained** |
| R9 | Cash movement | Sep | 0 (user with no bank rows) / 15,810,509 (a user with bank rows) | n/a | n/a | Same dataset, different user. The card does not say "no authorized bank accounts" |
| R10 | Net payables, stock value, petty cash | – | 70.4M, 1,193.6M, 8.8M | not executed | not reconciled | AgedSuppliers, InventoryValuation and PcReportTab were not run. Formulas match by code reading only |

**YTD definition conflict:** the Sales Executive report uses calendar-year YTD (`date('Y')-01-01`). The Directors dashboard uses the company fiscal year (1 April here). Putting both on one page needs one agreed definition.

## 5. Data-quality issues (local DB)

| Check | Count | Example | Affects |
|---|---|---|---|
| `gltrans` with zero or invalid date | 3,551 | 1-32882 (idx 204920) | Ledger totals (omitted from dated results) |
| `gltrans` future-dated | 6 | 1-66499 @ 2055-04-14 | Ledger |
| `gltrans` unposted | 182 | 10-23538 (idx 537412) | GL measures (excluded) |
| `debtortrans` future-dated / invalid date | 1 / 1 | 12-21585 @ 2026-12-23 / 12-10960 @ 0202-04-08 | Receivables |
| `supptrans` future-dated | 2 | 22-16861 @ 2026-11-07 | Payables |
| Receivable rows `settled=1` with a residual | 21,333 | 10-100 | Receivables and overdue |
| Positive vs negative receivable balances | 21,886 / 4,033 | – | 450.1M vs −324.4M. The net hides both |
| Stock with quantity but cost ≤ 0 | 8,403 | 1001ACCPF117049 | Stock value (excluded) |
| Negative stock quantity | 49 | 1021GIVEAWCatalog-2009@MT | Stock |
| Open cases with enquiry value > 0 | 43 of 68,801 | – | Pipeline |
| Open cases started before 2024 | 50,801 | MT-143-2016-07-01--111629 | Open cases |
| Customer branches with no salesman | 277 | MV-1/MV-1 | Salesperson analysis |
| Pending DCs with no first-option lines | 1,497 | – | Pending DC count |
| Pending DCs dated before 2020 | 284 | oldest year 2017 | Stuck-order risk |
| Duplicate invoice numbers / unposted invoices in Sep | 0 / 0 | – | Invoice figures (clean this month) |
| Bank accounts with no assigned user | 3 | account 1010 | Cash (never visible through this dashboard) |

## 6. Findings from the runbook: confirmed, corrected, or new

- **F1** confirmed: the MPIW title and its test messages contain "cohort".
- **F2** confirmed, and sharper: PO received, Pending DC value, posted invoices and Outstanding all exist as approved rules in `salesExecutiveData.php` and are not on the dashboard.
- **F3** confirmed and quantified (R4): 25.8M against 29.2M for Sep.
- **F4** confirmed in code (`previous` is the default). The fiscal start in this database is April, not July.
- **F8 corrected:** aging buckets do exist as the `age_bucket` dimension. They are missing only from the overview.
- **F9** confirmed. **F10** confirmed. **F11** confirmed.
- **Performance changed:** the overview took **0.85 s** for Sep. It took **4.5 s** for FY YTD, above the 3 s target. The Sales Executive report already caches in its own table (`sales_executive_cache`, 15 minutes); that pattern can be reused.
- **New N1:** net receivables 125.6M sits next to overdue 321.5M. A director will read this as an error.
- **New N2:** the cash card showed 0 as "Actual" when the user has no bank rows (R9). **Fixed:** it now shows unavailable with the reason. It still differs between users because bank scope is per user; the tile does not yet say so.
- **New N3:** "Open sales cases" (68,801) and "Open enquiry value" (99.9% unvalued) are not usable as headline figures as built.
- **New N4:** the overview runs one query per measure plus an "undated rows" scan per flow measure, one after another. There is no per-measure timeout or caching.
- **New N6 (access, found by `directors_roles.php`):** a user who holds `directorreports` but is not an administrator (the real role-27 director on the local copy) can read only 1 of 25 measures (petty cash), because every other source page's security token is missing from that role. The Sales Executive Report gates on `directorreports` alone, so the same user sees full company sales there but a nearly empty Directors dashboard. Administrators (access levels 8, 10, 22, or the `*` permission) see everything. **Resolved 2026-11-05:** directors-permission holders now read all company-level measures; bank and expense scopes stay per user.
- **New N7 (found while building the bank balance):** on the local copy the bank book total for one user's 20 accounts is 1,357.5M, while the posted general-ledger balance for the same account codes is 845.6M (a 511.9M gap, +60%). Not explained here. The measure is labelled Estimated until the two are reconciled.
- **New N8:** none of the 3,071 open supplier balances has a recorded due date or usable payment terms, so "payments due in 7/30 days" cannot be produced from this data. The tile says so instead of showing 0.
- **New N5:** `executive_listing` is not referenced anywhere in `bi/Directors`. Access is `directorreports` or `directors_dashboard`, plus each source page's security token.

## 7. Verification of the counts
An `awk` count over the matrix gives 45 rows: C 10, P 19, M 11, B 5.
