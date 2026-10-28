# Sales Reports: State-of-the-Art Analysis (v2)

Prepared 2026-10-28. Scope: the four pages named in the request, plus the report targets their forms post to. The eight director sub-reports (`brandwisesalesdirector.php`, `customerwisesalesdirector.php`, `salespersonwisesalesdirector.php`, `opportunitypipeline.php`, `quotationpipeline.php`, `popipeline.php`, `closedcases.php`, `anomolycases.php`) are **not** reviewed in depth here. Their filters are covered; their queries are not.

Paths are relative to `F:\apps\sahamid\`.

---

## 0. The request, improved

The original ask was to analyse these reports and produce a state-of-the-art executive and functional UI/UX in `/v2`. Rewritten as a specification:

> Audit the four sales-analysis entry points and the result pages they drive. For each report, state its purpose, audience, filters, data basis and every metric with its formula and source tables. Reconcile metrics that share a name but differ in meaning. List correctness, security and performance risks with file and line references. Then propose and prototype an executive-first, drill-down-capable sales report inside `/v2` in which every figure states its basis (cohort or flow) and its formula, and in which the numbers can be trusted by a director and checked by an analyst.

---

## 1. Executive summary

1. **Two incompatible bases.** The case-based pages (`selectsalescaseanalyse*.php`, the director sub-reports) count *sales cases* selected by commencement date or PO date. The dashboard (`v2/salesMainDashboard.php`) counts *transactions* by invoice, DC and shop-sale date. Both label their figures "PO", "OC" and "Pending DC", but the same label often means something different. A director comparing the two will get numbers that do not reconcile.
2. **Likely double count on the dashboard.** "Business Volume" = Invoice + Shop DC + Pending DC (`v2/salesMainDashboard.php:261`). "Pending DC" is the gross value of every DC in the period (`:165-176`), with no netting of DCs that have already been invoiced. `dcdetails` carries `qtyinvoiced`, so an invoiced DC is probably counted twice. This needs a data check before the number is trusted (see §6.1).
3. **Fatal error on an empty window.** `selectsalescaseanalysenew.php:714-715` divides by `$totalQuotationValue['value']` without a zero guard. When the filter matches no quotations, PHP 8 throws `DivisionByZeroError` and the page dies.
4. **No permission gate on the analysis pages.** The director hub (`directoranalysefilternew.php:5-8`) and the dashboard (`salesMainDashboard.php:33`) are gated. The four case-analysis pages are not (`grep userHasPermission` returns nothing). The new page embeds every matching case, with client names, salesperson remarks and comments, as a JSON literal in the HTML (`selectsalescaseanalysenew.php:459`).
5. **Injection and exposure.** Filter values from `$_POST` are interpolated straight into SQL (`selectsalescaseanalysenew.php:118-122, 135-143`). They are also echoed unescaped into the page (`:677-679`). No form ID or CSRF check is applied on these pages. Database credentials are hard-coded in both analysis pages (`selectsalescaseanalysenew.php:63`, `selectsalescaseanalyse.php:48`). The same password appears in a comment in `docker/init-db/08-sales-dashboard-permission.sql`.
6. **The multi-select salesperson filter does not work.** `directoranalysefilternew.php:19, 156` implodes the selections into `"A,B"` and passes that string as a `LIKE "%A,B%"` pattern to the child reports. The "Choose All" option posts the literal value `All`, which matches nothing.
7. **Performance scales with the whole database, not the filter.** The case pages load all DCs, all OCs, all salesorders, all comments and all stock on every request. `selectsalescaseanalysenew.php:212-214` joins `salescase × salesorders × salesorderdetails` with no date filter. The dashboard is better (15-minute cache, indexes in `docker/init-db/07-dashboard-indexes.sql`), but its cache key omits the date anchor (F-16).

---

## 2. The four pages at a glance

| Page | Role | Input | Gate | Basis | Output |
|---|---|---|---|---|---|
| `selectsalescaseanalysefilter.php` | Form (old) | Basis radio (unchecked by default), start/end date, customer, salesperson | None | Form only | Posts to `selectsalescaseanalyse.php` |
| `selectsalescaseanalysefilternew.php` | Form (new) | Same, Commencement Date pre-selected | None | Form only | Posts to `selectsalescaseanalysenew.php` |
| `selectsalescaseanalyse.php` | Result (old) | POST | None | Cases filtered by date basis; quotation values from a per-user table | KPI tiles, anomaly list, server-side DataTable (`server_processing_analysis.php`) |
| `selectsalescaseanalysenew.php` | Result (new) | POST | None | Cases filtered by date basis; latest quote, all OCs, all DCs | KPI list, anomaly list, client-side DataTable with all rows embedded |
| `directoranalysefilternew.php` | Director hub | Date range, customer, customer type (multi), salesperson (multi) | `directorreports` (redirects to `v2/reportLinks.php`) | Form only | Eight links that open child reports in new tabs with the same filters |
| `v2/salesMainDashboard.php` | Executive dashboard | Overall or salesman, monthly/quarterly/yearly resolution, range | `sales_dashboard` (`*` passes) | Flow: invoice date, DC order date, shop-sale date | 12 KPIs, 6 charts, top-10 items; JSON endpoint `?data=1`, cached 15 min |

Who uses which, from the code:
- The **case pages** are for salespeople and sales management. They track each enquiry from commencement to PO and DC.
- The **director hub** is for directors. It fans out to pipelines, closed cases, and anomalies.
- The **dashboard** is the executive view. It is the only page with a salesman filter that applies to all metrics, and the only one with a non-admin path (which is dead code, see §5 F-15).

---

## 3. How the values are calculated

### 3.1 Case analysis pages (`selectsalescaseanalyse.php` and `selectsalescaseanalysenew.php`)

**Filter.** The POST `filtertype` picks the date column: `commencementdate` for `date`, `salescase.podate` for `podate`. Customer and salesperson are `LIKE "%…%"` on `debtorsmaster.name` and on `salescase.salesman` (a name, not a code). The date range is `BETWEEN` on the chosen column, inclusive.

**Per-case values.** All three use the same arithmetic. A line's value is:

```
line value = unitprice × (1 − discountpercent) × quantity × option quantity
```

Only rows with `lineoptionno = 0` and `optionno = 0` are summed. This applies to `salesorderdetails`, `ocdetails` and `dcdetails`.

| Value | Source | Scope | Notes |
|---|---|---|---|
| Quotation value | `salesorderdetails` joined to `salesorderoptions` | Latest quote only: `orderno IN (SELECT MAX(orderno) … GROUP BY salescaseref)` | Old page stores it in `quotationvalue<UserID>` as `int(11)`, which truncates decimals (`selectsalescaseanalyse.php:77-81`). |
| OC value | `ocdetails` joined to `ocoptions` | Every OC per case | A case can have several OCs. |
| DC value | `dcdetails` joined to `dcoptions` | Every DC per case, regardless of DC date | Not limited to the report period. |
| PO value (old page) | Quotation value of cases with `podate ≠ 0` | Quotation value, not OC value | `selectsalescaseanalyse.php:833`. |
| PO value (new page) | Sum of OC values for cases with `podate ≠ 0` | Every OC | Per-row value shows only the last OC (`selectsalescaseanalysenew.php:354-359`). The total sums all OCs (`:315-325`). |

**Status classification** of each case, from `closingreason` and `podate`:

| Status | Rule (new page, `getQuoteStatus`, lines 416-438) | Totals rule (new page, lines 270-311) |
|---|---|---|
| LOST | `closingreason` exactly equals "high prices", "Delivery time offered is not acceptable by customer" or "Customer doesn't want in equal" | `stristr` substring on "high prices", "want in equal", "Delivery time offered is not acceptable by customer" |
| Cancelled | Exact match on "internal cross", "external cross", "Customer has cancelled the enquiry" or "any other reason" | `stristr` substring on the same plus "internal cross" and "external cross" |
| In Pipeline | `closingreason = ''`, `podate` zero, value > 0 | Same rule |
| Out of Scope | `closingreason ≠ ''`, `podate` zero, value = 0 | Regretted: "Out of scope" or value = 0 |
| PO Uploaded | `podate > 0` | Not a total |

The status column and the totals therefore use different matching rules. A case whose reason is written "High Prices" counts as lost in the totals but shows a different status in the table. Lost, Cancelled and Regretted are also not mutually exclusive.

**Totals (new page)**, each a count and a value:

| Tile | Formula |
|---|---|
| Total sales cases | Number of cases matching the filter (all cases, with or without quotes) |
| Proper quotations | Sum of latest quote value where value > 0; count of those cases |
| Total value of POs | Sum of all OC values on cases in the window |
| Total value of DCs | Sum of all DC values on cases in the window |
| Lost / Cancelled / Pipeline | Sum of latest quote value under each status rule above |
| Regretted | Count of cases with "Out of scope" or quote value = 0 |
| Anomaly cases | Cases with `podate ≠ 0` and no row in `salesorders` (PO or DC without a quotation) |
| Success ratio (OC/Q) | Total PO value ÷ total quote value, and count ÷ count. **Mixes cohorts** and divides without a guard (line 714-715). |

**Anomaly list.** `selectsalescaseanalysenew.php:135-145`: `dc RIGHT JOIN salescase` where `podate ≠ 0`, `salescaseref NOT IN (SELECT salescaseref FROM salesorders)`. The director hub labels a different report "Anomaly (DC value > OC value)" (`directoranalysefilternew.php:240`). That label does not match this definition. `anomolycases.php` was not read.

**Table columns (new page).** Sno, commencement date, case reference, client, salesman, quotation number, item manufacturer and code, quote value, quote status, salesperson remarks, PO status, PO date, PO value, DC numbers, DC date, DC value, last remarks. Quote value is the latest quote. PO value is the last OC only, not the sum (see above).

### 3.2 Director hub (`directoranalysefilternew.php`)

Pure form. It has no values of its own. It passes the date range and filters to eight child reports as hidden fields, and it is gated on `directorreports`. The multi-select fields are imploded into comma strings. This is the source of finding F-7.

### 3.3 Executive dashboard (`v2/salesMainDashboard.php`)

Flow basis. Every KPI is a sum over transactions dated inside the selected range. `$INV_VALUE` (line 129) is:

```
SUM( ((unitprice/100) × ((1 − discountpercent) × 100)) × quantity ) × option quantity
  = Σ unitprice × (1 − discountpercent) × quantity × option quantity
```

The `/100` and `×100` cancel. The expression is correct but redundant.

| # | KPI | Formula (PHP line) | Source | Date column | Notes |
|---|---|---|---|---|---|
| 1 | Total Sales Target | Single salesman: `salesman.target`. Overall: `SUM(target)` where `current = 1` (`:191-195`) | `salesman` | None (annual) | Not prorated for the range. Bucket targets are `target ÷ buckets` (`:265`). |
| 2 | Total Invoice Value | Σ `$INV_VALUE` over invoices with `returned = 0 AND inprogress = 0` (`:154-161`) | `invoice`, `invoiceoptions`, `invoicedetails` (+ `custbranch`, `salesman` if one salesman) | `invoice.invoicesdate` | Also the sum of the monthly buckets. |
| 3 | Total Pending DC Value | Σ `dcdetails.unitprice × (1 − discount) × quantity × dcoptions.quantity` (`:165-176`) | `dcs`, `dcdetails`, `dcoptions`, `salescase` (LEFT) | `dcs.orddate` | Gross. Does not net invoiced DC quantities (see F-1). The join has no `optionno` key (see F-8). |
| 4 | PO Values | Σ `salesorderdetails` line value where `salesorders.poplaced = 1` (`:198-208`) | `salesorders` | `salesorders.orddate` | A different PO definition from the case pages (see §4). |
| 5 | CRV | Σ `debtortrans.ovamount` (type 750, `reversed = 0`) for `shopsale.payment = 'crv'`, complete shop sales (`:212-225`) | `shopsale`, `debtortrans` | `shopsale.orddate` | "CRV" is not expanded in these files. Meaning to be confirmed. |
| 6 | CSV | As CRV, for `payment = 'csv'` | As above | As above | As above |
| 7 | Outstanding | Σ open balance of `debtortrans.type = 10` (invoices), `settled = 0`, `reversed = 0`, with GST and WHT withholding subtracted (`:228-249`) | `debtortrans`, `invoice`, `debtorsmaster`, `custbranch`, `salesman` | Not date-filtered. It is a current balance. | Salesman attribution uses the `MAX(branchcode)` branch per debtor. |
| 8 | Total OC Value | Σ `salesorderdetails` line value where `salesorders.quotation = 0` (`:198-208`) | `salesorders` | `salesorders.orddate` | Called "OC" here, but in the case pages OC means the `ocs` table. |
| 9 | Total Business Volume | `Invoice + Shop DC + Pending DC` (`:261`) | Derived | Derived | Probable double count (F-1). |
| 10 | Total Proper Sale | `= Invoice value` (`:262`) | Derived | Derived | |
| 11 | Shop DC | Σ `debtortrans.ovamount` (type 750) for all complete shop sales, all payment types (`:212-225`) | `shopsale`, `debtortrans` | `shopsale.orddate` | |
| 12 | Cart Value | Σ `stockissuance.issued × stockmaster.materialcost × (1 − stockmaster.discount)` where `issued > 0` and `mbflag ∈ {B, M}` (`:252-258`) | `stockissuance`, `stockmaster` | Not date-filtered | Scoped to the salesman's code, or to `cart_report_access` for the user. |

**Charts.**
- *Sales vs Target vs Pending DC* (`cBar`): per bucket, invoice value, `bucketTarget` and DC value.
- *Period over Period* (`cPoP`): per bucket, invoice value for the current range against the same buckets one year earlier (`DATE_ADD(invoicesdate, INTERVAL 1 YEAR)`, line 182).
- *Score Curve* (`cScore`): cumulative invoice value against cumulative bucket target. This is a run-rate or achievement curve. The name is unclear.
- *Business Volume Breakup* (`cVolume`): the four pie slices Invoiced, Shop DC, Cart, Pending DC (`:281-286`). Cart and Pending DC are not in the same basis as Invoiced.
- *Brand Wise* (`cBrand`): invoice value by `manufacturers_name`, top six, rest omitted (`:304-315`).
- *Self vs Other* (`cSelf`): invoice value split by `stockmaster.mbflag`: `M` = "Self Brand", `B` = "Other Brand", otherwise "Unspecified". This reading follows FrontAccounting's flag convention and was not confirmed against `stockmaster` data.
- *Top Items*: top ten `stkcode` by invoice value (`:319-327`).

**Caching.** `cache` table, key `salesMainDash-<type>-<resolution>-<range>`, 15-minute TTL, shared across all users (`:364-383`). Dates are computed from today and are not in the key. The key does not change at midnight, so an entry can be served up to 15 minutes into a new day with the previous day's range boundaries.

---

## 4. Conflicting definitions

| Term | Case pages (old) | Case pages (new) | Dashboard | Director hub / other |
|---|---|---|---|---|
| **PO value** | Quotation value of cases with a PO date | Sum of OC values of cases with a PO date | `salesorders` with `poplaced = 1`, by `orddate` | Not defined |
| **OC** | Not used | `ocs` table, per case | `salesorders` with `quotation = 0` | Not defined |
| **Pending DC** | Not used | All DCs of cases in the window, by case | All DCs by `orddate`, gross | Not defined |
| **Lost** | `LIKE` on three reasons | `stristr` on three reasons (totals), exact match (status column) | Not used | `closedcases.php` not read |
| **Target** | Not used | Not used | Annual `salesman.target`, not prorated for the range | Not defined |
| **Salesman filter** | Name, `LIKE` on `salescase.salesman` | Name, `LIKE` on `salescase.salesman` | Code on `salesorders.salesperson`, `stockissuance.salesperson`; name via `custbranch.salesman` on invoice and shop sales | Name, multi-select, broken (F-7) |
| **Date column** | `commencementdate` or `podate` | Same | `invoicesdate`, `orddate`, shop `orddate`, `dcs.orddate` | Passed through |

The core problem is the **cohort vs flow** split. A cohort report asks: "of the sales cases that started in March, what happened to each one?" A flow report asks: "what was invoiced in March?" Both are valid. The reports must say which one they show, and the dashboard and case pages must not share labels.

---

## 5. Findings

Severity: **H** = wrong number or security exposure in normal use; **M** = wrong number in an edge case or a maintenance risk; **L** = cosmetic or hygiene.

| ID | Sev | Where | Finding | Fix |
|---|---|---|---|---|
| F-1 | H | `salesMainDashboard.php:165-176, 261` | Business Volume probably double-counts DCs that are later invoiced. "Pending DC" is gross DC value, with no netting of `qtyinvoiced`. | Net pending DC to `(quantity − qtyinvoiced)` and label it. Or drop it from Business Volume. Check with a query (§6.1). |
| F-2 | H | `selectsalescaseanalysenew.php:714-715` | `$x / $totalQuotationValue['value']` with zero denominator. Fatal on PHP 8; on PHP 7.3 it returns INF or NAN. | Guard: `$den > 0 ? $num / $den : null`. |
| F-3 | H | `selectsalescaseanalysefilter(new).php`, `selectsalescaseanalyse(new).php` (line 3 only) | No `userHasPermission()` gate. Any logged-in user can run the full case analysis, and the new page embeds all rows in the HTML. | Add the same gate used by the director hub, with a dedicated slug. |
| F-4 | H | `selectsalescaseanalysenew.php:63`, `selectsalescaseanalyse.php:48`, `docker/init-db/08-sales-dashboard-permission.sql` (comment) | Database credentials are hard-coded in source, and the password is in a SQL comment. | Move to `config.php` or environment variables. Rotate the password. Remove it from the comment. |
| F-5 | H | `selectsalescaseanalysenew.php:118-122, 135-143`; `selectsalescaseanalyse.php` (same pattern) | `$_POST` values interpolated into SQL. `LIKE "%…%"` also allows wildcard abuse. | Prepared statements with bound parameters. |
| F-6 | H | `selectsalescaseanalysenew.php:677-679`; `selectsalescaseanalyse.php:813-816` | Reflected, unescaped echo of `$_POST` customer and salesperson. No `FormID` or CSRF check. | `htmlspecialchars` on output. Check `FormID` on POST. |
| F-7 | M | `directoranalysefilternew.php:19, 100-127, 156-181` | Multi-select salesperson and customer type are imploded and used as `LIKE "%A,B%"`, so they match nothing. "All" posts a literal `All`. Unselected multi-selects post `0`, which matches names containing "0". | Pass arrays through and build `IN (…)` lists server-side. Treat "All" as no filter. |
| F-8 | M | `salesMainDashboard.php:165-172` vs `selectsalescaseanalysenew.php:103-107` | Dashboard DC join has no `optionno` or `lineoptionno` filter, and the case pages do. The dashboard may multiply rows on DC lines with options. | Add the same filters, or join on `optionno`. Verify (§6.2). |
| F-9 | M | `salesMainDashboard.php:198-208` | "PO" and "OC" definitions differ from the case pages (§4). | Pick one canonical definition (§7). |
| F-10 | M | `selectsalescaseanalysenew.php:354-359, 315-325` | Row PO value shows only the last OC. Total sums every OC. The row and the total disagree. | Show the sum per row, or list each OC. |
| F-11 | M | `selectsalescaseanalyse.php:77-81` (`quotationvalue` `int(11)`) | Quote values are truncated to integers in the staging table. | Use `DECIMAL(15,2)`. |
| F-12 | M | `selectsalescaseanalyse.php:51-91` | Per-user tables are created and truncated at request time. Two tabs by the same user race each other. The DDL runs on every request. | Use a per-request temporary table, or compute in one query. |
| F-13 | M | `selectsalescaseanalysenew.php:270-311` | Lost, Cancelled and Regretted overlap, so the same value can be in more than one bucket. | Make the buckets mutually exclusive, in a fixed precedence order. |
| F-14 | M | `salesMainDashboard.php:191-195, 265`; target prorating | Target is annual but shown unprorated for ranges. `lastyear` range uses the current target. | Prorate, or show the target for the matching year. |
| F-15 | M | `salesMainDashboard.php:88-102` | Every user who passes the `sales_dashboard` gate is treated as admin, so the `self` branch (`:99-102`) is unreachable. Intended behaviour is unclear. | Decide the access model. Remove the dead branch or make it reachable. |
| F-16 | L | `salesMainDashboard.php:364` | Cache key has no date anchor, so an entry can carry yesterday's range boundaries for up to 15 minutes. | Add the anchor date to the key. |
| F-17 | M | Whole case-analysis family | `utf8_encode` is deprecated since PHP 8.2. `DivisionByZeroError` is PHP 8 behaviour. The code appears to target PHP 7.3 (per the Docker workflow in project memory). | Plan the move, or use `mb_convert_encoding`. |
| F-18 | M | `selectsalescaseanalysenew.php:212-214` | `salescase × salesorders × salesorderdetails` is joined with no date filter, on every request, to build item descriptions. | Limit to the cases in the window, or join in the main query. |
| F-19 | L | `selectsalescaseanalysenew.php:527, 567, 788`; `selectsalescaseanalyse.php:657, 737, 914` | The `$('example')` selector in the second `document.ready` block is missing `#`, so that block does nothing. Each page ends the `</table>` with a stray `;` printed as text. | Clean up. |
| F-20 | L | `directoranalysefilternew.php:240` | Label says "DC value > OC value". The implementation was not reviewed. | Confirm and align the label. |
| F-21 | L | Labels | "PO pipeline Pipeline", "Anomoly", "Commencment" and similar. The directors see these. | Copy edit. |
| F-22 | L | `selectsalescaseanalyse.php` table | Table data comes from `server_processing_analysis.php`, which was not reviewed. | Review it with F-3, F-5. |

---

## 6. Verification queries to run before trusting the numbers

Run these read-only against a copy of production. They are not run here.

**6.1 Is a DC invoiced and also counted as pending?**
```sql
SELECT
  SUM(dd.unitprice * (1 - dd.discountpercent) * dd.quantity * do.quantity) AS dc_gross,
  SUM(CASE WHEN dd.qtyinvoiced > 0
           THEN dd.unitprice * (1 - dd.discountpercent) * dd.qtyinvoiced * do.quantity
           ELSE 0 END) AS dc_invoiced_part
FROM dcdetails dd
JOIN dcoptions do ON dd.orderno = do.orderno AND dd.orderlineno = do.lineno
JOIN dcs ON dcs.orderno = dd.orderno
WHERE dcs.orddate BETWEEN '2026-01-01' AND '2026-10-28';
```
If `dc_invoiced_part` is material, Business Volume overstates by that amount.

**6.2 Does the dashboard DC join multiply rows?** Compare `COUNT(*)` from the dashboard's DC join against the same join with `do.optionno = dd.lineoptionno` added. A difference means rows are multiplied.

**6.3 Which PO definition do the directors mean?** Compare the year total of `salescase.podate`-based OC value, `salesorders.poplaced`-based value and quote value of PO'd cases.

---

## 7. State of the art: what v2 should look like

**Principles**
- One canonical metric dictionary. Each KPI has one name, one formula, one basis (cohort or flow), and one source. Dashboard and case pages both read from it.
- Executive first, drill-down second. A director gets the answer in five seconds. An analyst gets the case list one click away.
- Every number shows its basis and its definition on hover or focus. No silent mixing of cohort and flow.
- Trust markers: data freshness, cache age, and a note when a figure is an estimate.

**Layout (desktop)**
1. **Header band.** Title, basis toggle (Cohort | Flow), period selector (MTD, QTD, YTD, Last 12 Months, Last Year, custom), salesman selector, freshness stamp, Refresh.
2. **Executive band.** Five tiles: Target attainment, Invoiced (flow), PO value (cohort), Quoted pipeline, Win rate (value). Each tile has a sparkline and a comparison to the same period last year.
3. **Funnel.** Quoted → PO → OC → DC → Invoiced, with count and value at each stage, and conversion percentages between stages. This is the cohort story.
4. **Trend.** Monthly invoiced vs target, cumulative run-rate, and last year as a ghost line.
5. **Pipeline by status.** Stacked bar: In Pipeline, Won (PO), Lost, Cancelled, Out of scope. Mutually exclusive by the precedence in §3.1.
6. **Team leaderboard.** Salesman, target, invoiced, PO value, win rate, pipeline. Sortable.
7. **Exceptions.** Anomalies (PO or DC without quote), overdue outstanding, pending DC over N days.
8. **Drill-through.** Case table with server-side pagination, column chooser, sticky header, export to CSV and Excel, and links to the case.

**Functional UI rules**
- Filters are chips with a clear "x" and a count. The URL holds the state, so a view can be shared.
- Tables sort, filter and paginate on the server for large sets. Keyboard navigable. Empty states say why.
- Colour is never the only signal. Status uses a label and an icon as well.
- Charts have a data table alternative and an accessible name.
- Mobile: KPI tiles stack, the funnel becomes a vertical list, and the case table becomes cards.

**Data layer**
- A single PHP service (for example `v2/lib/SalesMetrics.php`) that computes the canonical metrics from prepared statements. Both the dashboard and the case pages call it.
- Cache keys include user scope, period, basis and an anchor date.
- The case pages stop embedding full datasets in HTML and call a JSON endpoint instead.

---

## 8. Prototype

A working prototype of the target UI is in `v2/salesReportsExecutive.html`. It is a static page with inline CSS and JavaScript, and it has no external dependencies. It uses **illustrative data only**. Every number on the page is invented and labelled as such. It demonstrates the layout, the basis toggle, the definition tooltips, the funnel, the pipeline and the drill-down table. It does not connect to the database.

Do not put real figures into the prototype until the metric dictionary in §7 is agreed and the service layer exists. The page is served without authentication if the web server serves static files in `v2/`, so keep it free of real data.

---

## 9. Decisions needed from the business

1. **PO definition.** Use the PO date on the case, the OC table, or `salesorders.poplaced`? The answer changes the headline number.
2. **DC netting.** Should Pending DC be net of invoiced quantities? I recommend yes.
3. **CRV and CSV.** What do they stand for, and should they appear on an executive page?
4. **Who may see the case analysis?** Confirm the permission slug for the four case pages.
5. **Target basis.** Annual target prorated by month, or by working days?

---

## 10. Limits of this review

- No PHP runtime or live database was used. All behaviour is inferred from the code and the schema in `backup/sahamid_structure.sql`.
- The eight director sub-reports, `server_processing_analysis.php` and `anomolycases.php` were not reviewed in depth.
- The formulas were checked against the code. The numbers were not checked against data. Use §6 before relying on any figure.
- The duplicate copies under `.codex/` and `expense-report-pkr-worktree/` were not compared line by line. This review uses the copies in the project root and `v2/`.

---

## 11. Changes applied (second pass, 2026-10-28)

Answers taken from §9:

- **PO definition: "PO Received".** A case has a customer PO when its PO file is uploaded (`salescase.podate`, set by `salescase/api/uploadPO.php`) or an active PO receipt is recorded (`sales_performance_po_receipts`). This is the status rule in `v3/api/salesPerformanceReport.php:1528-1529` and in My Sales Activity. The PO date is the PO file date, else the first receipt. PO value is the OC value (`ocs`) of those cases, the same basis the case pages use.
- **Old PO KPI was measuring the wrong thing.** `salesorders.poplaced` is set when a *supplier* purchase order is placed for a sales order (`SelectSalesOrder.php:394`). The dashboard's old "PO Values" therefore measured supplier purchasing, not customer POs. Replaced in `v2/salesMainDashboard.php` by `poReceivedValue()`.
- **Pending DC is net of invoicing.** Pending = `unitprice × (1 − discount) × max(quantity − qtyinvoiced, 0) × option quantity`. `dcdetails.qtyinvoiced` is maintained by `invoice/api/saveInvoice.php:175`. The DC join also matches `dcdetails.lineoptionno = dcoptions.optionno`, the same keys saveInvoice uses. This also removes the option-row multiplication in F-8.
- **CRV and CSV are shop-sale payment channels.** They are `shopsale.payment = 'crv'` or `'csv'`, complete sales only, valued from `debtortrans.type = 750`. `csvcrvitems.php:22` says the same. The expansion of CSV is not written anywhere in the code. `CustomerReceipt.php` calls the CRV upload "Cash Receipt Voucher".
- **Permission.** The four case pages and `server_processing_analysis.php` now require `directorreports`, the same slug as the Director Reports hub. Pages redirect to `/sahamid/v2/reportLinks.php`. The data endpoint returns 403 JSON.
- **Toggle.** "How calculated" on the live dashboard shows the basis, formula and source for each KPI and a note for each chart. The prototype has the same toggle.
- **Cache.** The dashboard cache key is now `salesMainDash-v2-…`, so payloads built under the old definitions are not served.

### Still open

1. ~~**Case pages use the PO-file rule only.**~~ **Done (third pass).** Both case pages now use the PO Received date (PO file, else first active receipt). See §12.
2. **Credentials in `server_processing_analysis.php`.** It connects as MySQL `root` with an empty password (lines 64, 98, 137). It is now gated, but the connection should move to the shared config.
3. **BI registry conflicts.** `bi/Metrics/MetricRegistry.php` defines `sales.po_value` and `sales.oc_value` on `salesorders`, not `ocs`, and marks both "awaiting validation". The dashboard's Total OC Value still follows `salesorders` (the registry). The case pages use `ocs`. One OC definition needs approving.
4. **Unapproved composites.** The registry marks Business Volume and Proper Sale as not approved. They are labelled as such in the toggle.

### Verification

- PHP 7.3 lint (`php:7.3-cli` in Docker) passes for all six changed PHP files.
- The new SQL was run against the structure-only MariaDB 10.3 schema from `backup/sahamid_structure.sql`. It parses and runs on both paths: with the receipts table installed, and without it (the PHP falls back to the PO-file rule).
- Not run against data. The dashboard was not rendered end to end, because that needs a stubbed session and the seeded database.


---

## 12. Third pass: PO Received on the case pages (2026-10-28)

Both case pages (`selectsalescaseanalyse.php`, `selectsalescaseanalysenew.php`) now use one PO date per case, `$poEff`:

- `COALESCE(NULLIF(salescase.podate, zero), first active sales_performance_po_receipts.received_at)`, so PO status, the PO-date filter, the anomaly list, the pipeline rule and the PO date column all follow the same rule as the dashboard.
- If `sales_performance_po_receipts` is not installed, `$poEff` falls back to the PO file date alone (checked with `SHOW TABLES`).
- The new page's "Total Value of POs" now counts OC value only for cases with a PO received. Before, it summed the OC of every case in the window.

Verification: both pages lint on PHP 7.3. They were also run in a harness (PHP 7.3 + MariaDB 10.3 with the structure, the performance-reporting install and seeded cases) with no warnings. Seeded: case A with a PO file, case B with a receipt only, case C with a quote only.

| Page / filter | PO-date basis result |
|---|---|
| New page, PO date filter | 2 cases; Total Value of POs Rs.9,600 (3,600 + 6,000) |
| Old page, PO date filter | 2 cases; quotations having PO Rs.4,000 |
| Both, commencement filter | 3 cases; PO value unchanged; C stays in pipeline |
| Fallback, receipts table dropped | 1 case (PO file only); Rs.3,600 on the new page |

Not done: the status label still says "PO Uploaded" for receipt-only cases. Changing the wording is a small follow-up.


---

## 13. Fourth pass: success-ratio guard and shared credentials (2026-10-28)

**Success ratio.** `selectsalescaseanalysenew.php` now shows `n/a` for Success Ratio (OC/Q) when there are no quotations in the window. Both the value and the count ratio are guarded. Before, the value ratio divided by zero.

**Credentials in the sales-analysis family.** These now read `$host`, `$DBUser`, `$DBPassword` and `$_SESSION['DatabaseName']`, set by `config.php` and `includes/ConnectDB_mysqli.inc`. The literal values are gone from:

- `selectsalescaseanalyse.php` (1 connect)
- `selectsalescaseanalysenew.php` (1 connect)
- `server_processing_analysis.php`: 7 connects that used MySQL `root` with an empty password, plus the DataTables `sql_details` array, which used the `irtiza` password. The endpoint now uses the application user from config.

The case pages still open their own connection rather than reusing `$db`. This keeps the charset and encoding the same, because reusing `$db` would set `utf8`, and the pages call `utf8_encode()`.

**Done in the fifth pass (§14).** The repo-wide pass is complete for tracked files.

**Rotation.** The password has been in the repository history, and in a SQL comment in `docker/init-db/08-sales-dashboard-permission.sql`. Rotate it, then update `config.php`.

**Verification.** Lint passes on PHP 7.3 for all three files. In the harness (PHP 7.3 with `pdo_mysql`, MariaDB 10.3, seeded data):

- Both case pages return the same totals as before: 2 cases on the PO-date filter, PO value Rs.9,600.
- The empty window renders with `n/a` in both ratio cells, with no warnings from the page.
- The endpoint connects with the configured user. It then reports that its per-user table is missing, which is expected when the page has not been run first for that user.


---

## 14. Fifth pass: repo-wide credential move (2026-10-28)

**Single source.** `db_config.php` (repo root) now holds the connection values. It defines the variables that `config.php` and `ConnectDB_mysqli.inc` already read (`$host`, `$DBUser`, `$DBPassword`), plus constants `SAHAMID_DB_HOST`, `SAHAMID_DB_USER` and `SAHAMID_DB_PASSWORD`. Constants work inside functions and classes, where plain variables would not be visible. `DB_HOST` and `DB_PASSWORD` environment variables override the defaults.

**Rewrite.** A scripted pass changed 283 PHP/INC files. It:

- replaced `mysqli_connect('localhost','irtiza','<pw>', …)` (and the `getenv('DB_HOST')` variant) with the constants;
- replaced the `'pass' => …` entries in DataTables settings and the `$db_password = …` assignments;
- added `include_once __DIR__ . '/…/db_config.php';` after the opening `<?php` tag of each changed file, with the relative path worked out from the file's depth.

Skipped: files without a `<?php` tag or with a `namespace` or `declare` line. None were found. Module `config.php` files in subfolders were included in the pass. The three root-level config files were edited by hand.

**Other files.**

- `configg.php` derives its password from the constant.
- `docker-compose.yml` reads `MYSQL_PASSWORD` from `DB_PASSWORD`, which is now required. Set it in a `.env` file beside the compose file before `docker compose up`.
- `docker/windows/start.ps1` reads `DB_PASSWORD` from the environment and throws if it is not set.
- The SQL comments in `docker/init-db/08` and `09` show `-p<DB_PASSWORD>`.

**Remaining.**

- `db_config.php` still has the password as the fallback default. Set `DB_PASSWORD` or edit that file to rotate it.
- The password stays in git history. Rotate it.
- Two files were already broken in the committed version and fail to parse: `corrections/shopsaleInternalDiscounts.php` (`while()`) and `includes/quotationhistory.php` (a missing `)` around line 38). This pass did not change either.
- Untracked copies under `.codex/` and `expense-report-pkr-worktree/` still contain the old literal. They are excluded from the pass.

**Verification.**

- All 283 changed files lint on PHP 7.3, apart from the two broken files above.
- A test script under PHP 7.3 connected to MariaDB 10.3 using the constants from inside a function and a class.
- `config.php` loads and defines the constants.
- A tracked-file search finds no literal outside `backup/`.
