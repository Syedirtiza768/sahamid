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
| 11 Missing measures | partly: PO received, posted invoices, outstanding, pending DC value, gross margin | reconcile test passes on 2 periods. **Not built:** DSO, AP due in 7/30 days, closing bank balance, cycle times, slow movers, top-10 supplier share |
| 12 Comparison logic | default yoy; server period logic reused; JS still pairs trend months by position | `directors_run.php` 30/30 |
| 13 Snapshot history | table + capture job done and idempotent on the fixture DB; **UI does not read it yet** | 5 rows after two runs |
| 14 Compact header | done: one sticky bar, period chip opens a popover, presets and comparison apply at once, the investigation's own Apply button is still there | `docs/directors-ux/after/fold-desktop.png`. Verified on a mock page only; the sticky offset (50px) matches the AdminLTE fixed header but was not seen in the real shell |
| 15 Overview | done: headline tiles, ranked attention, data health, colours, lakh/crore, unavailable list | `docs/directors-ux/after/` (3 widths, synthetic data) |
| 16 Investigation view | done except Chart.js: readable labels, hover/focus tooltip with both values and change, hatched partial months, increases/decreases with share of change and an "Other" row, month focus chip that leaves the global period alone | 60 UI checks pass at 2 widths. **Chart.js not used:** the bundled build is 1.0.2 (2015) and the only newer copy in the repo loads from a CDN; the SVG chart keeps keyboard and screen-reader support |
| 17 Records table / record page | **not done** | |
| 18 Skeleton / error / a11y | **not done** | |
| 19 Exports | **not done** for new sections | |
| 20 Performance | overview 1.5 s fiscal YTD on the local copy (was 7.8 s mid-change, 4.5 s originally) | `directors_pair.php` |
| 21 Reconcile | sales measures exact; GL revenue vs invoices still unexplained | gap audit section 4 |
| 22 Permissions per role | **not done** | |
| 23 UI journeys | **not done** (overview screenshots only, synthetic data) | |
| 24 Tests | all pass: lint, pure 30/30, finance, operations, UI (46 checks, 2 widths), integration 25/25, reconcile, pair. Export test cannot run (no zip) | |
