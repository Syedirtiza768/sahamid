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
| 9 | **Stopped at checkpoint 1** (R7/R8 off by more than 0.5%, R5 and R4 conflict with the fixed sales rules) | decisions requested from the user | waiting for decisions |

Read-only access note: audit queries against the local `sahamid` database ran with `SET SESSION TRANSACTION READ ONLY` or through the `mysql` client with SELECTs only. The scratch scripts are in the session scratchpad, not in the repo.
