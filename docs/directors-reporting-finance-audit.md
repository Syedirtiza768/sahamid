# Directors reporting: finance audit and calculation evidence

Audit scope: source-code and schema inspection of the active PHP application and embedded BI module. No production database credentials were used. This document distinguishes structural risks from measured data defects; a schema or code risk is not a claim that production totals are wrong.

## Architecture and existing reporting

The application is a customized webERP-style PHP/MySQL ERP. Company selection is an authenticated session database, not a company ID supplied by a report request. The established enhanced-report module lives in `bi/` with pages and JSON endpoints in `v2/bi/`; `bi/bootstrap.php` provides autoloading without requiring a framework migration.

`bi/Reports/ReportRegistry.php` is the most useful active report inventory: it enumerates the Reports hub, report-only sidebar entries, and BI-native workspaces. Its catalog is navigation metadata; a listed legacy report does not imply a governed or reconciled metric. `v2/bi/README.md` documents the older enhanced-report library as 88 entries, of which 86 retain legacy calculation pages. This number should be treated as that implementation's documented baseline, not a fresh runtime count. Copies, old pages, and separate root/v2/v3 trees create many more source files than current rendered report entries.

| Business question | Existing finance reports | Principal source and grain | Existing filters / definitions | Dashboard coverage and qualification |
|---|---|---|---|---|
| Is the business profitable? | `GLProfit_Loss.php`, `GLTagProfit_Loss.php`, `GLTrialBalance.php` | `chartdetails` per account/period, joined `chartmaster` and `accountgroups` | Accounting periods; current/prior year; budget; summary/detail; account group sections | Journal-date posted revenue, COGS, other P&L costs, and net result; recorded complete-period budget comparison separately |
| What is our financial position? | `GLBalanceSheet.php`, `GLTrialBalance.php`, `GLAccountInquiry.php` | `chartdetails.bfwd + actual` at selected period; GL account hierarchy | Period end; account and period | Source reports remain available. Full balance-sheet classification and equity rollover are not reconstructed by summing period movements |
| Which accounts explain movement? | `GLAccountInquiry.php`, `GLAccountReport.php`, `GLTransInquiry.php`, `GLJournalInquiry.php` | `gltrans.counterindex`; transaction type + number identify journal | Account, periods, transaction date/type/number; `posted=1` in account actuals | Account/group/section/job/tag/type breakdown; current versus comparison contributors; GL transaction drill-through |
| Are we meeting budget? | `GLBudgets.php`, P&L and trial-balance budget columns | `chartdetails.budget` per account/period | Annual allocation apportioned over 12 months; account; year | Recorded P&L budget versus recorded P&L actual for identical complete periods. No inferred daily budget, approval, forecast, or salesperson historical target |
| What do customers owe? | `AgedDebtors.php`, `DebtorsAtPeriodEnd.php`, `CustomerInquiry.php`, customer statements, custom outstanding reports | `debtortrans.id`; `custallocns.id`; customer/branch masters | Currency, customer range, salesperson, overdue thresholds, as-of/period; custom invoice-only reports differ | Current signed net AR, positive overdue exposure, customer/branch/type/age investigation and source customer records |
| What do suppliers owe us / what do we owe them? | `AgedSuppliers.php`, `SupplierBalsAtPeriodEnd.php`, `SupplierInquiry.php`, `SupplierPayablesReport.php` | `supptrans.id`, `suppallocs.id`, supplier and payment terms | Supplier/type/currency/status/due date/as-of; enhanced report adds payment method, timing, hold, project | Current signed net AP, overdue/hold/due-basis breakdown and supplier records; due dates inferred from current terms are marked estimates |
| How is cash moving? | `DailyBankTransactions.php`, `BankReconciliation.php`, `BankMatching.php`, banking summaries | `banktrans.banktransid`; `bankaccountusers` restricts visible accounts | Authorized bank account; date; matched/unmatched | Signed base-currency bank movement, bank/method/type/currency/clearance. This is not bank balance, available cash, or a statutory cash-flow statement |
| Where are petty-cash claims increasing? | `PcReportTab.php`, `PcAuthorizeExpenses.php`, `v2/bi/expenses.php` | `pcashdetails.counterindex`, expense code, tab, owner, GL account | Date/category/tab/owner/auth/posting/receipt/text; grouping/classification; previous period; XLSX | Dedicated claims measure, excluding ASSIGNCASH; remains separate from GL P&L to prevent double counting |
| Are invoices and ledger revenue consistent? | `v2/bi/invoice.php`, invoice reconciliation APIs and `bi/Reconciliation/InvoiceReconciliationService.php` | Invoice detail + option combination versus non-reversed type-10 `debtortrans` | Invoice dates, invoice number, salesperson, tax diagnostic | Operational invoice value and GL revenue remain distinct. Their difference is not automatically a defect or a safe tax adjustment |

## Calculation contracts and double-counting boundaries

### GL result

The existing P&L identifies `accountgroups.pandl=1`, section 1 as income and section 2 as cost of sales (`GLProfit_Loss.php` income / gross-profit branches). Therefore:

- Revenue = negative sum of income-section signed amounts.
- Cost of sales = signed sum of section-2 amounts.
- Other P&L costs = signed sum of other P&L sections.
- Net profit/loss = negative sum of all P&L signed amounts.
- Gross profit = revenue minus cost of sales. Any gross-margin percentage needs a positive nonzero revenue denominator and the same scope/period.

`includes/GLPostings.inc` rolls `gltrans.posted=0` into `chartdetails.actual` and then marks those GL rows posted. The dashboard's posted-journal filter follows `GLAccountInquiry.php` and `GLAccountReport.php`. It must disclose that unposted journals are excluded. Calling reporting code must not include `GLPostings.inc` merely to make totals match, because this would make an ostensibly read-only report mutate source data.

The legacy P&L's period actual is ending `bfwd + actual` less beginning `bfwd`, with analogous budget fields. A journal-date report can legitimately differ from a period report when dates fall in locked periods or posting is delayed. The dashboard must compare `chartdetails.actual` with `chartdetails.budget` for the same complete periods when displaying budget variance; it must not imply that a journal-date partial month is directly comparable with an entire accounting-period budget.

Financial joins must preserve one fact per GL primary key. Account groups and sections are masters with primary keys. Inner joins hide missing account classifications, so an explicit orphan / unmapped GL diagnostic is still needed if referential constraints are missing or imports bypass them.

### Current AR and AP

The legacy balance formulas are not simply `ovamount - alloc`:

- AR original signed amount = `ovamount + ovgst + ovfreight + ovdiscount`; current net open = original minus `alloc`, divided by positive transaction `rate`.
- AP original signed amount = `ovamount + ovgst`; current net open = original minus `alloc`, divided by positive transaction `rate`.
- Exclude reversed rows. Include negative credit/unapplied rows in net balances. A metric that includes only positive invoices is gross open exposure, not net ledger balance.
- Net balances and positive overdue exposure should not be expected to equal: unapplied credits are not assigned to arbitrary invoices by the dashboard.
- Never replace a missing/zero/negative exchange rate with 1. Omit its amount from base-currency totals and surface the excluded-row count.
- Join invoice due dates after aggregating at document number + customer + branch. `invoice.invoiceno` is not declared unique in the supplied schema. A direct invoice join can multiply AR amounts. Multiple candidate records leave the due date unavailable, rather than selecting an arbitrary due date.
- Invoice due dates and shop due dates exist (`invoice.due`, `shopsale.due`). Receipts, credits and other ledger types may have no equivalent due date; they require explicit unknown-due treatment.
- AP uses stored `supptrans.duedate` when available. Falling back to current `paymentterms` estimates the due date and can change when master terms change. The dashboard clamps a following-month day to the actual next month; the legacy supplier helper can overflow at month end. This deliberate correction is covered by Jan-31 / leap-Feb fixtures and can explain differences in legacy aging.

`v2/paymentsDue.php` uses invoice-only `ovamount - alloc`, joins `custbranch` on branch code alone, and its lower requested date bound is commented out. It is not a safe canonical definition for company-wide net AR. `AgedDebtors.php` includes the wider amount components but is currency-specific and does not consistently handle the custom reversal field. These differences explain why consolidation must publish one explicit definition rather than add totals from multiple legacy pages.

### Historical balance limitations

`custallocns` and `suppallocs` have allocation dates. The enhanced supplier helper reconstructs prior AP from surviving allocations dated through the selected as-of date. However, `CustomerAllocations.php` and `SupplierAllocations.php` delete prior allocation rows when changed. Current reversal flags and current customer terms also overwrite state. Consequently, these tables do not by themselves constitute an immutable historical ledger snapshot.

The directors overview publishes current AR/AP snapshots and does not compare them with a fabricated prior snapshot. Reliable historical exposure needs immutable allocation/reversal events or periodic balance snapshots, plus a reconciled opening state and preserved terms/due dates. An as-of reconstruction from surviving allocations may be offered only as an explicitly qualified estimate after reconciliation, not as a restated historical fact.

### Bank movement and cash flow

`DailyBankTransactions.php` calculates functional value as `amount / functionalexrate / exrate`; the two rates have distinct source meanings. Both must be positive. Do not use `amount / exrate` alone or mix original transaction/account currency amounts with base currency.

`bankaccountusers` is an additional access boundary. The aggregate, drill-down, trend, group choices and export must all use `EXISTS` for the signed-in user, including administrators. Repeated permission rows must not multiply bank facts.

Bank net movement is not cash balance: chartdetail opening balances, unreconciled items and bank-statement evidence are separate. Gross inflows/outflows contain transfers; partial account access can prevent a transfer from cancelling at company total. A statutory operating/investing/financing cash-flow statement needs an approved transaction mapping and opening/closing reconciliation, absent from the audited BI metric registry.

### Petty cash and invoices

The established expense service (`bi/Expense/ExpenseReportService.php`) defines claims as negative of `pcashdetails.amount`, excludes `ASSIGNCASH`, preserves positive source rows as credits, reports missing receipts and approval/posting states, and treats all amounts as PKR without conversion. Non-PKR tab metadata produces a mismatch diagnostic. Local purchases and balance-sheet claims are distinct from P&L claims. The dashboard must not relabel the full claims measure as operating expense or combine it with GL costs.

The trusted raw invoice formula is `unitprice × (1 − discountpercent) × detail.quantity × option.quantity`; `bi/Metrics/MetricRegistry.php` explicitly says currency and GL posting-date behavior are not modeled. Observed 16%/18% tax relationships are diagnostics, not a global tax conversion policy. Revenue, gross AR, operational invoice value, shop-sale value, order commitments and pipeline estimates are separate facts with different grains/status boundaries.

## Comparison and fiscal-calendar rules

`config.YearEnd` defines the fiscal year-end month. `periods.periodno` and `lastdate_in_period` define accounting periods. Calendar comparison shortcuts must not assume every company has a December fiscal year or infer a complete first period without a predecessor.

For flow comparisons, use explicit displayed inclusive start/end dates and equal elapsed intervals for incomplete current periods. Clamp calendar shifts to valid month days. Show absolute change; zero/missing baselines yield an unavailable percentage, not infinity or 100%. Negative baselines require a clearly documented percentage denominator, and percentage-point changes must remain distinct from percentage changes.

Budget capture in `GLBudgets.php` is unversioned and has no approval/forecast state. Zero is a default in every account/period and cannot prove an approved zero budget exists. The dashboard should call this a recorded budget and mark an all-zero/absent budget unavailable. Historical `salesman.target` is a current master attribute, with no audited effective-date history; it cannot be extrapolated into past monthly targets.

## Access controls

`SessionAuthorizationAdapter` identifies an authenticated company database and current user. Administrator access is inferred from levels 8/10/22 or `*`; `sales_dashboard` grants existing analytics but may enforce salesperson scope. A finance-wide directors view must not be implicitly exposed to every salesperson with that sales permission.

The directors implementation needs its own permission (or explicit administrator scope), the legacy per-page security tokens for each dataset, and bank-account restrictions. Missing page mappings should fail closed for new financial aggregation. The endpoint must obtain the database from session configuration; no request may select another database or company. Displaying a company selector without a secure cross-company model would be misleading. Company consolidation and currency translation are unavailable without explicit authorized company connections and intercompany-elimination rules.

The existing `ReportAuthorization` defaults to allowing routes absent from its map; that legacy library behavior is not a suitable fallback for new consolidated finance metrics. Dedicated BI routes use `canUseSalesAnalytics`; the new directors endpoint must enforce stronger authorization independently.

## Data-quality and evidence checklist

Structural risks observed in code/schema:

1. Business document numbers often lack unique constraints; aggregate before joins and diagnose ambiguous links.
2. FX `rate` defaults to zero in `debtortrans`; invalid rates need explicit excluded-row counts.
3. Legacy reports disagree on AR components, reversal handling, branch joins and date roles.
4. Allocation mutation prevents unqualified historical snapshots.
5. Unposted GL rows can diverge from chartdetails; query date and accounting period can differ.
6. Current terms/master classifications are not historical dimensions.
7. Petty-cash description classification is derived at read time and not approved accounting reclassification.
8. The existing AP following-month fallback uses month-addition minus original day-of-month, which can produce unintended dates at month end. The dashboard corrects this by adding the clamped target day to the preceding month-end; stored due dates remain authoritative. Updating the legacy helper remains a separate compatibility decision.
9. Pure finance metrics have no safe department/product/salesperson dimension unless an explicit source relationship exists. Do not infer a director's preferred grouping by a many-to-many join.

Checks required on the target company before declaring financial reconciliation complete:

- By account + accounting period, compare posted GL sum with `chartdetails.actual` and investigate unmatched accounts/periods.
- Verify `GL revenue − COGS − other P&L costs = net profit` for the same filtered facts.
- Sum each breakdown and source-row export back to its filtered summary; no top-N list should be labeled as the complete total.
- Reconcile net AR/AP to non-reversed source ledger components; separately count invalid-rate rows, missing masters and unknown/ambiguous due dates.
- Check duplicate operational numbers by customer/branch and allocation current totals versus surviving allocation rows.
- Verify bank movement using both FX factors and assert that an unauthorized account is absent from summary, dimensions, drill-down and export.
- Check budget/actual use identical complete periods and exclude ranges containing only partial periods.
- Test concurrent company sessions, empty/unavailable sources, permission denial, large grouped sets, bounded pagination/export, and comparison contexts.

Runtime validation results and target-specific measured discrepancies belong in the implementation handoff; this audit does not claim any production reconciliation or production performance measurements.
