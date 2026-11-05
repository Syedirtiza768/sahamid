# Runbook: Directors' Reporting Dashboard, second pass (audit, fill gaps, redesign UX)

Target page: `/sahamid/v2/bi/directors.php`

You are working in the SAHamid ERP repo (PHP 7.3, MariaDB, v2/ pages on AdminLTE).
A first version of the Directors' dashboard is already built (commit 58b45abd). Your
job is to audit it, fix the gaps listed in Section 2 and any others you find, and
redesign the experience so a director can answer "how are we doing, what needs my
attention, and why" within the first screen.

Follow the steps in order. After each step, add one line to
`docs/directors-reporting-progress.md`: step number, what you did, what it produced,
and anything still open. If a step's **Done when** check fails, fix it before moving
on. If you cannot fix it, record it as a blocker and continue only if later steps do
not depend on it.

---------------------------------------------------------------------------------
## 1. Rules that apply to every step

- **R1.** Extend the existing code. Do not rebuild it, and do not copy logic that
  already exists elsewhere. Reuse `bi/Directors/*` and the existing reports.
- **R2.** Sales metrics use the definitions approved on 2026-10-28:
  - "PO received"
  - "Pending DC List"
  - revenue = posted invoices only

  Do not use cohort or flow wording in any label, tooltip, export header, or doc.
- **R3.** Access control:
  - Use the existing gates, with both layers: the page-open gate and the data filter.
  - `executive_listing` sees everything.
  - Every other user sees exactly what the source report would show them.
  - This applies to KPIs, charts, attention items, drill-downs, record pages, and
    XLSX exports.
- **R4.** Every figure carries exactly one label:
  - **Actual:** posted ledger or transaction data.
  - **Estimated:** derived; show the formula.
  - **Forecast:** projected; show the method.
  - **Unavailable:** say what data is missing.

  Never invent history or fill gaps with guesses.
- **R5.** Currency, fiscal year, and period boundaries come from company settings.
  Never hard-code them.
- **R6.** Never change existing transaction flows. New data capture is allowed only
  as small, separate, additive migrations, each listed in the final report.
- **R7.** Make no claim without evidence: a query result, test output, or screenshot.
- **R8.** Visual work follows the existing v2 conventions:
  - AdminLTE layout, header, and sidebar.
  - Font Awesome 4 icons.
  - The `--dir-*` CSS variables in `v2/bi/directors.css`.
  - The Chart.js build already used in `v2/bi/index.php`. Do not add a new chart
    library or a CDN dependency.
  - Keep the page usable without JavaScript errors in the supported browsers.
- **R9.** Write labels in plain business language that a director understands
  without training. Technical terms belong in the Definitions view only.

---------------------------------------------------------------------------------
## 2. Known findings from the current build (verify each one, then fix it)

These were found by reading the code and rendering the page with synthetic data at
1440×900 and 375×812. For each finding, confirm it, record the evidence, and fix it in
the step named after it.

### Definitions and correctness

| ID | Finding | Where | Fix in |
|---|---|---|---|
| F1 | A measure is titled "MPIW received value by slip cohort". This uses cohort wording, which breaks R2. | `bi/Directors/OperationsDatasetCatalog.php` (~line 111) | Step 10 |
| F2 | There is no "PO received" measure, even though it is an approved sales metric. Pending DC exists only as a count, with no value. | Operations catalog | Step 11 |
| F3 | The main sales figure is "Invoice detail value": a raw line formula covering PKR customers only. It has not been reconciled with the approved "posted invoices" revenue in `v2/salesReportsExecutive.php`. | Operations catalog `invoice_value` | Steps 7 and 10 |
| F4 | The default comparison is "Previous equal period". Fiscal YTD (Jul–Nov) is compared with Feb–Jun, and the trend chart pairs July with February. Directors expect last year over the same number of elapsed days. | `directors.php` `#dirComparison`, `DirectorsRequest` | Step 12 |
| F5 | The current partial month is not marked in the trend, so it looks like a collapse. | `renderTrend` in `directors.js` | Step 16 |
| F6 | Changes are never coloured: `.dir-change-up` and `.dir-change-down` exist in the CSS but no JS applies them. There is also no polarity, so a rise in costs reads the same as a rise in revenue. | `changeText` in `directors.js` | Step 15 |
| F7 | Clicking a month in the trend chart quietly changes the global reporting period and reloads the overview. | `chooseMonth` in `directors.js` | Step 16 |
| F8 | The API returns the overdue amount for receivables, but the overview card does not show it. There are no AR or AP aging buckets anywhere. | `renderOverview`, receivables and payables datasets | Steps 11 and 15 |
| F9 | AR, AP, stock, and the pipeline are live snapshots only, so they have no history and no trend. | Service `limitations()` | Step 13 |
| F10 | The "generated" time is shown in UTC, but users work in Pakistan time. | `useMetadata` | Step 15 |
| F11 | Before the data loads, the page says "Fiscal year begins January" and shows empty dates, because the JS defaults `fiscalStart` to 1. | `directors.js` startup | Step 18 |
| F12 | Counts are shortened ("1.2k supporting records"). Money uses m/bn, not lakh/crore. | `shortNumber` | Steps 9 and 15 |

### Usability and visuals

| ID | Finding | Fix in |
|---|---|---|
| U1 | The overview is a flat wall of about 21 equal-weight cards in 7 groups, with no hierarchy. Headline results and minor counts look the same. | Step 15 |
| U2 | "Risks & opportunities" sits about 3 screens down. It mixes business risks (overdue AR, profit drop) with data-quality notices (unposted journals, invalid amounts). Nothing is ranked, every item is the same amber, and the amount is not the headline. | Step 15 |
| U3 | The overview has no charts at all: no sparklines and no trend. | Step 15 |
| U4 | At 1440×900, the header, filters, tabs, and section heading use about 55% of the screen before the first number. On mobile, the first screen shows no figures. | Step 14 |
| U5 | Changing the period preset only previews the dates; nothing updates until Apply is clicked. The Investigate view has a second "Apply investigation" button. | Step 14 |
| U6 | Labels use jargon: "Actual source", "known subtotal", "Deterministic exceptions", "at the source grain", "slip cohort", "Authorized legacy PO". | Step 15 |
| U7 | The contributors panel is ranked by the size of the change, but the bar length shows the value. The change itself is tiny grey text. Increases and decreases are not separated, and the list is capped at 15. | Step 16 |
| U8 | The trend chart is hand-drawn SVG with 10px labels. Values appear only in the native `<title>` tooltip. | Step 16 |
| U9 | While loading, the whole page fades to 52% opacity. There are no skeleton placeholders, and one slow measure holds up the entire overview. | Steps 18 and 20 |
| U10 | In the records table only Date, Reference, and Amount sort. There is no totals row, and search needs a button press. | Step 17 |
| U11 | The view tabs have no ARIA tab roles or arrow-key navigation. | Step 18 |
| U12 | The record evidence page (`directors-record.php`) is a raw field dump: underscores turned into spaces, with no formatting. | Step 17 |
| U13 | Unavailable measures (for example, Budget) take up a full card slot showing "—". | Step 15 |

---------------------------------------------------------------------------------
## STAGE A: Orientation

### Step 1. Create a branch and record the baseline
- From master, run: `git checkout -b directors-dashboard-v2`
- Done when: you are on the new branch, and the only untracked files are the ones
  that were already there.

### Step 2. Read the implementation end to end
- Read these files:
  - `v2/bi/directors.php`, `directors.js`, `directors.css`
  - `v2/bi/api/directors.php`, `v2/bi/directors-record.php`
  - `bi/Directors/*.php`
  - `docs/directors-reporting-*.md`
  - `bi/tests/directors_*`
- In the progress log, write a table with columns: dataset id | title | kind
  (flow/snapshot/budget) | R4 label | unit | dimensions | function that computes it.
- Done when: every dataset in the three catalogs appears in the table.

### Step 3. Read the related reports and settings
- Read `v2/salesMainDashboard.php` and `v2/salesReportsExecutive.php`, the
  balance/aging reports, and the permission code behind them (search for
  `executive_listing`).
- Find where these are stored: currency, fiscal year start (`$_SESSION['YearEnd']`),
  and the company's display time zone.
- Done when: the progress log names the source report that each dashboard measure
  must reconcile to, and the settings keys used for R5.

### Step 4. Set up the verification environment
- Follow `php-verify-workflow`:
  - `php73-full-local` image, plus `mariadb:10.3 --sql-mode=""` on one docker network.
  - Import the structure file and a hand-calculated seed.
  - Bind-mount the session stub over `includes/session.inc`.
  - Set `MSYS_NO_PATHCONV=1`.
- Run every `bi/tests/directors_*` test. Run the UI test with
  `python bi/tests/directors_ui.py --chrome <path>`.
- Done when: you have recorded the baseline pass/fail for each test. Do not fix
  anything yet.

### Step 5. Capture "before" screenshots
- Render the page with the seed data at 1440×900, 1024×768, and 375×812. Capture:
  - the overview
  - an investigation of one flow measure
  - an investigation of one snapshot measure
  - the Definitions view
  - a record page
- Save them under `docs/directors-ux/before/`.
- Done when: all 15 screenshots exist. Every later UX step is compared against them.

---------------------------------------------------------------------------------
## STAGE B: Gap audit (deliverable: `docs/directors-reporting-gap-audit.md`)

### Step 6. Build the coverage matrix
- Use these columns:

  Area | Business question | Measure | Definition | Source | Dashboard location | R4 label | Status

  Status is one of: covered / partial / missing / blocked-by-data.
- Cover at least these areas:
  - **Profitability:** revenue, cost of sales, gross margin %, operating costs, net
    result, budget vs actual.
  - **Cash:** bank movement in and out, closing bank position.
  - **Receivables:** total, overdue, aging buckets (0–30, 31–60, 61–90, 90+), top
    overdue customers, days sales outstanding (DSO).
  - **Payables:** total, due in the next 7/30 days, aging, top suppliers owed.
  - **Sales:** PO received, Pending DC List (count and value), posted invoices, by
    salesperson, customer, branch, and category.
  - **Purchasing:** POs raised, unreceived commitments, MPIW received, supplier spend.
  - **Inventory:** value, value by category, stock-outs and reorder items, slow
    movers.
  - **Concentration:** top-10 customer share of revenue, top-10 supplier share of
    spend.
  - **Operations:** order → DC → invoice → collection cycle times, stuck orders, open
    work orders.
  - **Expenses:** petty cash by type, largest movements.
- Mark findings F1–F12 against the rows they affect.
- Done when: every area has at least one row and every row has a status.

### Step 7. Reconcile existing measures
- For every existing measure, compute its value for one closed month and for fiscal
  YTD. Compare each with its source report (Step 3) using the same filters.
- Record each in a table: Measure | Period | Dashboard | Source | Difference | % |
  Cause.
- Pay particular attention to F3: compare `invoice_value` with posted-invoice revenue
  in `salesReportsExecutive.php`, and with GL revenue.
- Done when: every measure has a row, and every non-zero difference has a cause or
  is marked "unexplained".

### Step 8. Record data-quality issues
- Run queries for:
  - orphan rows
  - unposted documents in closed periods
  - non-PKR or invalid exchange rates
  - duplicate IDs
  - rows missing a salesperson, branch, customer, or due date
- For each, record: query | count | one example ID | which measure it affects.
- Done when: each check has a result, even if the result is zero.

### Step 9. ⛔ CHECKPOINT 1: stop and get decisions from the user
Send the user:
- the coverage counts by status
- the reconciliation table
- the data-quality list
- the "before" screenshots
- your proposed overview layout (the wireframe from Step 14, as a sketch or a short
  list)

Then ask the user to decide:
1. **Sales headline (F3):** use posted-invoice revenue, keep "Invoice detail value"
   but relabel it, or show both.
2. **Default comparison (F4):** recommend "Same period last year, same elapsed days".
3. **Number format (F12):** recommend lakh/crore (PKR convention), or keep m/bn.
4. **Snapshot history (F9):** approve or decline a daily snapshot table and cron job
   for AR, AP, and stock (R6).
5. **Thresholds** for "needs attention": the material-change %, the overdue amount,
   the concentration share, and the number of days before an order counts as stuck.
6. **Headline KPIs:** confirm the 6 headline measures (default list in Step 15).

Do not start Stage C until the user has answered. Record each answer in the progress
log, in their words.

---------------------------------------------------------------------------------
## STAGE C: Data and calculation gaps
After each step, run the existing tests again. Nothing may regress from the Step 4
baseline.

### Step 10. Fix definitions and reconciliation defects
- Rename the F1 measure without cohort wording, for example "Market purchases
  received (MPIW)". Update the title, export header, docs, and tests.
- Apply the user's F3 decision.
- Fix every measure marked unexplained or off by more than 0.5% in Step 7.
- For each fix, add a regression test with a fixture under `bi/tests/fixtures/`.
- Done when: re-running Step 7 shows a difference of 0, or a tolerance the user
  accepted.

### Step 11. Add the missing measures
- Add these to the catalogs, reusing source queries from the existing reports where
  they exist (R1):
  - **PO received:** value and count (R2 definition).
  - **Pending DC List:** value as well as count.
  - **AR aging:** buckets, overdue total, top overdue customers, DSO.
  - **AP aging:** buckets, plus amounts due in the next 7 and 30 days.
  - **Gross margin %:** (GL revenue − cost of sales) / GL revenue. Label it Estimated
    if the GL link settings are incomplete.
  - **Concentration:** top-10 customer share and top-10 supplier share.
  - **Cycle times:** OC → DC → invoice → receipt, as the median days and the 90th
    percentile.
  - **Inventory:** value by category, plus a slow-mover count (no movement in N days).
- Every new measure must:
  - carry an R4 label, definition, source, and dimensions
  - go through `DirectorsAuthorization` and the data filters (R3)
  - have a fixture test with figures worked out by hand
- If the data for a measure does not exist, add it as Unavailable with the reason,
  and keep going.
- Done when: every "missing" row in the matrix is either covered or Unavailable with
  a stated reason.

### Step 12. Fix the comparison logic (F4)
- Put all period logic in `DirectorsRequest` (or one helper) and have the JS use the
  server's result only. Remove the duplicate date maths in `previewPreset`.
- Supported comparisons:
  - previous period
  - month over month
  - quarter over quarter
  - same period last year, over the same elapsed days (default, per the Step 9
    decision)
  - custom range
  - none
- Trend comparison must pair like with like: July 2026 with July 2025, not with
  February 2026.
- Each result returns: value, comparison value, absolute change, % change (null when
  the baseline is 0 or missing), an `is_partial` flag, and the number of days on each
  side.
- Unit tests must cover: fiscal year boundaries, a leap year, a partial current
  month, a zero baseline, and a missing baseline.
- Done when: all the tests pass, and the trend for a fiscal-YTD view pairs each month
  with the same month last year.

### Step 13. Snapshot history (F9; only if approved at Step 9)
- If approved:
  - Add a migration for a `directors_balance_snapshots` table (date, measure,
    dimension key, value).
  - Add an idempotent daily job, using the same scheduling method as other ERP jobs.
  - Show history only from the first captured date. Label anything before that date
    "No history before <date>".
  - Never reconstruct past balances.
- If declined, the snapshot measures keep their current label, plus a single line
  saying what is needed to get history.
- Done when: the snapshot measures show either real captured history or a clear
  limitation.

---------------------------------------------------------------------------------
## STAGE D: Experience redesign
Compare every step in this stage with the "before" screenshots. A change counts only
if it is visible in an "after" screenshot.

### Step 14. Page structure and filters (U4, U5)
- Replace the tall hero and filter panel with **one compact sticky bar**. It holds:
  - title and company
  - a period chip, for example "FY to date · 1 Jul – 5 Nov 2026 vs same period last
    year"
  - the data-freshness time
  - Refresh and Export buttons
- Clicking the period chip opens a popover with presets, custom dates, and the
  comparison choice. A preset applies at once; custom dates apply on "Apply".
- Remove "Apply investigation". Changing the breakdown applies at once, and search
  runs 400 ms after the user stops typing.
- Target: at 1440×900, the headline KPI row and the "Needs attention" list are fully
  visible without scrolling. At 375px, the first headline KPI is visible without
  scrolling.
- Done when: the "after" screenshots meet both targets.

### Step 15. Executive overview (U1, U2, U3, U6, U13, F6, F8, F10, F12)
Build the overview from top to bottom in this order:

1. **Needs attention.** Up to 5 business items, ranked by money at risk, each with a
   severity colour:
   - overdue AR above the threshold
   - a profit or margin decline above the threshold
   - customer concentration above the threshold
   - stuck orders
   - payables due within 7 days

   Each item has its amount as the headline, one line of plain-language context, and
   a "See records" link. Data-quality notices go to a separate, collapsible "Data
   health" panel with a count badge. They are not business risks.

2. **Six headline KPI tiles.** Default set, unless changed at Step 9:
   - Revenue (approved definition)
   - Gross margin %
   - Net profit
   - Bank movement
   - Receivables, with the overdue amount as a sub-line
   - Payables, with the 7-day due amount as a sub-line

   Each tile shows: the value, its change vs the comparison period (coloured by
   polarity: green = favourable, red = unfavourable, so a rise in costs is red), a
   12-month sparkline (or "No history before <date>"), and an R4 badge only when the
   figure is not Actual.

3. **Two charts.**
   - Revenue by month vs the same month last year, with the partial month hatched.
   - A profit bridge: revenue → cost of sales → operating costs → net result.

4. **Working capital row.**
   - AR aging as a stacked bar.
   - AP due profile.
   - Stock value by category.

5. **Sales and operations row.**
   - Pipeline funnel: PO received → Pending DC → invoiced, with counts and values.
   - Top 10 customers, with each one's share of revenue.
   - Cycle-time tiles.

6. **All measures.** Every remaining measure, as a compact sortable table:
   name | value | change | label | open. Unavailable measures appear here only,
   in muted text with the reason. They do not get a card.

Also:
- Replace the jargon labels (U6) using a single label map in JS. Keep the technical
  terms in the Definitions view.
- Number format follows the Step 9 decision. Counts are never shortened. The tooltip
  always shows the full figure.
- Show times in the company time zone, with a relative age, for example "Updated 4
  min ago · 12:41 PKT".
- Use Chart.js from the existing bundle (R8). Every chart has a text alternative,
  using the existing "View values" table pattern.
- Done when: the "after" overview screenshots show this structure at all three
  widths, and each item and tile links to the right drill-down.

### Step 16. Investigation view (F5, F7, U7, U8)
- **Header:** measure name, a plain-language definition (one line), the R4 badge,
  and a "Definition & source" expander.
- **Trend:** Chart.js bars or a line, current vs comparison, with:
  - hover tooltips showing both values and the change
  - the partial month marked
  - axis labels of at least 12px
- **Month click (F7):** clicking a month adds a **month filter chip** inside the
  investigation. It must not change the global period. Removing the chip restores
  the previous view.
- **Contributors (U7):**
  - Two columns: "Increases" and "Decreases".
  - Bars are proportional to each item's change.
  - Each row shows its value, its change, and its % of the total change.
  - Show the top 10 in each column, then an "Other (n)" row so the column adds up to
    the total change.
  - When there is no comparison, show the top 10 by share of value.
- Breakdowns that are snapshots show concentration only, with no change.
- Done when: clicking a contributor filters the records table, the chip appears, and
  the back button restores the previous state exactly.

### Step 17. Records table and record page (U10, U12)
- **Records table:**
  - Every column with a value can be sorted. Sorting happens on the server.
  - A sticky header.
  - A totals row for the filtered set, calculated on the server, not just the
    current page.
  - Search as you type.
  - A count of matching rows that agrees with the export.
- **Record page (`directors-record.php`):**
  - Group the fields into Document, Party, Amounts, Status, and Quality.
  - Format dates and money.
  - Show the measure definition.
  - Add "Open in ERP" when a native page exists.
  - Add "Back to investigation", which uses `directors_return`.
- Done when: the table totals equal the export totals for the same filters, and the
  record page renders correctly for one record from every dataset.

### Step 18. Loading, empty, error, and accessibility states (U9, U11, F11)
- **Loading:**
  - Show skeleton placeholders for tiles, charts, and tables. Do not fade the whole
    page.
  - Never show default values such as "Fiscal year begins January" before the
    metadata arrives. Render the fiscal start on the server.
- **Errors:** each widget shows its own error with a Retry button. One failed measure
  must not blank the overview.
- **Empty:** a specific message that says which filter to remove.
- **Accessibility:**
  - `role="tablist"` / `tab` / `tabpanel` on the view tabs, with arrow-key
    navigation.
  - Visible focus on every control.
  - Colour is never the only signal: changes also show ▲/▼ and a sign.
  - Contrast of at least 4.5:1 for text.
- **Print:** the overview prints on A4 landscape with the period and the freshness
  time.
- Done when: forcing an empty result, a 500 response for a single measure, and a slow
  response (3 s) each shows the correct state in screenshots.

### Step 19. Exports
- **Overview export:** headline KPIs, attention items, and all measures, each with
  its period, comparison, R4 label, and definition.
- **Records export:** exactly the filtered rows, plus a totals row and the active
  filters in the header block.
- Both exports use the existing `XlsxExporter` and the R3 filters.
- Done when: an export's totals and row count match the screen, for two different
  filter sets.

---------------------------------------------------------------------------------
## STAGE E: Performance and verification

### Step 20. Performance (U9)
- Split the overview into independent requests: one per section, or one per measure
  group. That lets each section render as soon as its data arrives, and lets a slow
  section fail on its own.
- Run `EXPLAIN` on every new or changed query. Put any new index in a migration
  file, with a comment explaining why it is needed (R6).
- Use the existing BI cache, if `bi/` has one, with a TTL of 5 minutes or less, and
  show the cache age.
- Targets with `bi/tests/directors_volume.php` at production volumes:
  - first headline KPI shown in under 1.5 s
  - full overview in under 3 s
  - drill-down in under 2 s
- Done when: the targets are met, or the gap is recorded together with the query
  causing it.

### Step 21. Reconcile again
- Repeat the Step 7 table for two closed months and fiscal YTD, covering every
  headline KPI and every new measure.
- Confirm there is no double counting across credit notes, returns, reversals, and
  multi-currency documents.
- Done when: every row is an exact match, or within a tolerance the user accepted.

### Step 22. Permissions
- Test as four roles: `executive_listing`, a sales lead, a sales member, and a user
  with no access.
- For each role, check: the overview, attention items, a drill-down, a record page,
  and both exports.
- Done when: each role sees exactly what the source reports show them, and the
  no-access role is blocked at both the page and the API. Add an automated test per
  role.

### Step 23. UI journeys and "after" screenshots
- At 1440×900, 1024×768, and 375×812, walk through these journeys:
  1. Overview → attention item → contributors → record → back (twice) → same state.
  2. Change the preset → the KPIs update without pressing Apply.
  3. Custom comparison → a shared URL reproduces the same view in a new tab.
  4. Export the overview and a filtered records table.
  5. Keyboard only: tabs, KPI tiles, the chart months, and the contributors.
- Save the screenshots under `docs/directors-ux/after/`, with the same names as the
  "before" set.
- Check the console: there must be no errors or warnings.
- Done when: every journey passes, and each finding F1–F12 and U1–U13 is marked
  fixed, with screenshot or test evidence, or deferred with a reason.

### Step 24. Run the full test suite
- Run every `bi/tests/directors_*` test plus the new tests. Update
  `directors_ui_checks.js` for the new structure.
- Done when: all tests pass, or each failure is listed with its cause. Nothing may
  regress from the Step 4 baseline.

---------------------------------------------------------------------------------
## STAGE F: Wrap-up

### Step 25. Update the docs
- Update `docs/directors-reporting-implementation.md` and `v2/bi/README.md`: the
  layout, the measures, the comparison rules, the migrations, and the label map.
- Update the coverage matrix statuses to their final values.

### Step 26. Commit
- Make one commit per stage: B, C, D, E. Do not push or open a PR unless the user
  asks.

### Step 27. Final report (keep it concise)
1. **Delivered:** files, endpoints, sections, and migrations.
2. **Findings:** a table of F1–F12 and U1–U13, each marked fixed or deferred, with
   its evidence.
3. **Coverage:** counts of covered / partial / missing / blocked-by-data, taken from
   the matrix.
4. **Reconciliation:** the table from Step 21.
5. **Checks:** the results of Steps 20–24, including any failures.
6. **Before and after:** three paired screenshots (overview, investigation, mobile).
7. **Limitations:** each remaining limitation, with the exact data, integration, or
   decision needed to resolve it.

Do not claim full coverage. Report the counts.
