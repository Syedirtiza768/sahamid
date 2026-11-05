# Directors dashboard v2: progress log

Branch `directors-dashboard-v2`. Runbook: `docs/directors-dashboard-runbook.md`.

| Step | What was done | Produced | Open |
|---|---|---|---|
| 1 | Created branch from master | branch `directors-dashboard-v2` | none |
| 2 | Read the Directors page, JS, CSS, API, record page and all `bi/Directors` classes | dataset inventory in the gap audit (22 datasets) | the three catalogs were read through their SQL contracts, not line by line for every dataset |
| 3 | Read `salesExecutiveData.php`, the AgedDebtors formula and the access memory | approved rules located; `YearEnd=3` found in the `config` table; timezone `Asia/Karachi` in `config.php` | AgedSuppliers, InventoryValuation and PcReportTab not read or executed |
| 4 | Created `dir-db` (MariaDB 10.3, schema only) and ran the tests | baseline table in the gap audit | export test not run (no `zip` extension in the image) |
| 5 | Rendered the current page with synthetic data at 3 widths | 12 screenshots in `docs/directors-ux/before/` | the record page was not captured |
| 6 | Built the coverage matrix | 45 rows: C 10, P 19, M 11, B 5 | |
| 7 | Reconciled against approved rules on the local dev DB | table R1–R10 | R7 and R8 (GL revenue) unexplained; R10 not executed |
| 8 | Ran data-quality probes | 16-row table with example IDs | |
| 9 | Checkpoint 1 (stopped, then answered by the user) (R7/R8 off by more than 0.5%, R5 and R4 conflict with the fixed sales rules) | decisions requested from the user | answered 2026-11-05 |

Read-only access note: audit queries against the local `sahamid` database ran with `SET SESSION TRANSACTION READ ONLY` or through the `mysql` client with SELECTs only. The scratch scripts are in the session scratchpad, not in the repo.

**Decisions (2026-11-05):** revenue = posted invoices + shop sales; default comparison = same period last year; lakh/crore; daily snapshots approved. Defaults applied without asking: thresholds (20% change, 30% overdue share, 25% concentration, 90 days stuck DC), headline set of six, `executive_listing` left as is (not consulted in `bi/Directors`).

| Step | Status | Evidence |
|---|---|---|
| 10 Fix definitions | done | cohort wording removed; Pending DC now 308 / 68.67M |
| 11 Missing measures | done where the data allows: PO received, posted invoices, outstanding, pending DC value, gross margin, DSO, top-10 customer and supplier share, order-to-invoice median days, bank book balance (Estimated), payments due in 30 days | each checked against independently written SQL (`directors_reconcile.php`, 2 periods). **Blocked by data:** payments due (no supplier balance has a due date or usable terms: shown as unavailable), slow movers and collection time. **Not built:** sales target vs achievement |
| 12 Comparison logic | default yoy; server period logic reused; JS still pairs trend months by position | `directors_run.php` 30/30 |
| 13 Snapshot history | table + capture job done and idempotent on the fixture DB; **UI does not read it yet** | 5 rows after two runs |
| 14 Compact header | done: one sticky bar, period chip opens a popover, presets and comparison apply at once, the investigation's own Apply button is still there | `docs/directors-ux/after/fold-desktop.png`. Verified on a mock page only; the sticky offset (50px) matches the AdminLTE fixed header but was not seen in the real shell |
| 15 Overview | done: headline tiles, ranked attention, data health, colours, lakh/crore, unavailable list | `docs/directors-ux/after/` (3 widths, synthetic data) |
| 16 Investigation view | done except Chart.js: readable labels, hover/focus tooltip with both values and change, hatched partial months, increases/decreases with share of change and an "Other" row, month focus chip that leaves the global period alone | 60 UI checks pass at 2 widths. **Chart.js not used:** the bundled build is 1.0.2 (2015) and the only newer copy in the repo loads from a CDN; the SVG chart keeps keyboard and screen-reader support |
| 17 Records table / record page | record page done: grouped fields, formatted dates and money, "Not recorded" for empty values, definition and limits, a back link guarded against open redirects, print. The records table already has a server-side total (summary) and the export now carries a total row. **Not done:** sorting on every column, search-as-you-type, sticky header | `directors_record.php` 17 checks; template rendered with synthetic evidence (`docs/directors-ux/after/record-*.png`); the full page was not rendered in the ERP shell |
| 18 Skeleton / error / a11y | done: skeleton tiles and cards in the final layout instead of fading the page, a slim progress bar while refreshing, an error panel with Try again (overview and investigation), no duplicate announcement, tabs with ARIA roles and arrow/Home/End keys, a specific empty-records message, A4 landscape print layout | 66 browser checks at 2 widths; screenshots `docs/directors-ux/after/state-*.png` (mock). **Not done:** per-widget errors inside the investigation (trend, contributors and records still load in one request) and a contrast measurement |
| 19 Exports | done for row content: overview export adds attention and data-health items; records export adds a total row for the whole filtered set | `directors_export_rows.php` on real data (11 checks). **The XLSX file itself was not generated** (no zip extension in the image); `directors_export.py` still cannot run here |
| 20 Performance | **corrected and redone, see the performance pass below.** The earlier figures (0.85 s, 1.5 s, 4.5 s) were measured with the database's query cache switched on, so repeat runs were served from that cache. | |
| 21 Reconcile | sales measures exact; GL revenue vs invoices still unexplained | gap audit section 4 |
| 22 Permissions per role | done: `bi/tests/directors_roles.php` runs the real adapter with real users and role tokens. Admin sees all; a delegated director is limited per source page token; executive_listing-only, a salesperson and a user with no permission are refused at the page gate and the service (which covers overview, drill-down, record and export); bank and petty-cash row counts equal the user's own accounts and tabs | 150+ checks pass on the local copy. **Finding (resolved):** the one real non-admin director (role 27) could read 1 of 25 measures. After the 2026-11-05 decision all 25 are readable; bank and petty-cash scope still follow the user |
| 23 UI journeys | **not done** (overview screenshots only, synthetic data) | |
| 24 Tests | all pass: lint, pure 30/30, finance, operations, UI (46 checks, 2 widths), integration 25/25, reconcile, pair. Export test cannot run (no zip) | |

## Performance pass (2026-11-06)

**Correction.** The local MariaDB has `query_cache_type=ON`, so earlier timings of the same query were cache hits. With it switched off for the session the real overview cost about **24-27 s** (fiscal YTD against the same period last year, one request, nothing cached).

| Cause | Fix | Effect (local copy, query cache off) |
|---|---|---|
| `gl_budget` and `gl_budget_actual` ran a correlated subquery per `chartdetails` row | one small join for period starts | about 7 s each to under 0.1 s |
| Flow measures read their whole history and filtered the dates last | `{{RANGE:column}}` marker: the service pushes the reporting window into the source query | `invoice_value` 1.1 s to 0.2 s; `gl_*` a fraction of before |
| Four overview requests ran one after another inside one request | three slices (finance, sales, operations) requested at the same time; tiles appear as each slice arrives | wall time is the slowest slice: about 2-4 s uncached instead of about 7 s end to end |
| Nothing was saved between loads | result cache, 5 minutes, per user and permission; balances saved separately so a period change does not re-read them; Refresh bypasses it | a saved result is served in about 1 ms |
| One investigation read its source 6-7 times | read once into a session-scoped working table; falls back silently if the account may not create one | `po_received` 5.9 s to 1.0 s, `invoice_value` 3.4 s to 0.6 s, `gl_result` 3.9 s to 2.1 s |
| Missing indexes | `migrations/directors_reporting_indexes_2.sql` (`gltrans (account, trandate)`, `ocoptions (orderno, lineno, optionno)`) | `gl_result` 0.7 s to 0.22 s when tried temporarily; **not applied to any database** |

**Proof that nothing changed in the numbers:** `directors_pair.php` (single pass equals the old two-call path), `directors_scope.php` (windowed and working-table results equal full-history results for every measure and every part of an investigation, floating-point sums to 0.001), `directors_parts.php` (the three slices add up to the whole overview), `directors_reconcile.php` (still exact against the approved report rules), and the fixture suites with hand-calculated values.

**Still slow or unmeasured:** the first load after a database restart (cold buffer pool: 17.8 s once); the finance slice at 2.7-4 s (four separate ledger scans, one per measure); `stock_exceptions` and `po_received` at about 0.7-1.2 s each; production volumes and a production database have not been measured; the web endpoint (cache, compression) was not run through a real HTTP request here.
