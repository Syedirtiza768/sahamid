# Sales Performance Reporting walkthrough

## Recording notes

The included MP4 is a privacy-preserving illustrative UI tour, not a screen capture of the authenticated ERP page. Its interface scenes are synchronized to the narration and captions; live totals, customer details, and ERP case identifiers are omitted.

Four clearly labeled `DEMO ONLY` entries have been saved in the local reporting database against an existing test case with no prior reporting captures: an enquiry receipt, an enquiry response, a follow-up, and a customer PO example. The PO example has no amount. No core ERP case or transaction was edited. A partial coverage review is shown as a draft and is not represented as saved.

For a live demonstration, use the signed-in ERP browser and the report page. The saved test entries are clearly marked `DEMO ONLY`; keep their case identifier and any unrelated business data out of a public recording. The narration WAV and SRT were generated from this script; timecodes below are measured from the rendered WAV.

Do not add further entries while recording; the four demo-only activity entries are already in the local database. The coverage draft is intentionally unsubmitted because its saved review must be validated against the current full-company snapshot. Do not show or imply that the demo PO was an actual customer purchase order. If demonstrating the freeze action, use an authorized account and mask the run key in any shared cut.

**Runtime:** 00:05:15.402 (including 0.65s scene pauses)
**Voice:** Microsoft Zira Desktop, English (United States)
**Source:** report behavior and measures reviewed in the local ERP session connected to the `sahamid` database; definitions are provisional. Video visuals are illustrative, mask ERP identifiers, and distinguish seeded demo entries from real activity.

## 00:00:00.000–00:00:29.861 · Opening: a live ERP report

**Screen:** Open Sales Performance from the ERP reporting navigation. Show the real page and report metadata. Keep the viewer signed in; do not show credentials or unrelated tabs.

**Action:** Hold on the loaded report long enough to establish that these are live ERP records. Do not add or edit any data.

**Narration:** Welcome to Sales Performance Reporting. This walkthrough follows a manager from a live ERP report into the supporting activity, setup and saved exports. The report reads the existing company database. It keeps quotations, actual customer purchase order receipts, ERP order confirmations and delivery challans as separate measures. The figures and source coverage remain provisional where the business definitions have not yet been approved.

## 00:00:30.511–00:00:58.194 · Choose a period and authorized scope

**Screen:** Frame the Reporting period, Compare with, Historical cutoff, Approved team population, Teams and Salespeople controls. Show only controls available to this signed-in user.

**Action:** Point to the period and cutoff. If changing a filter is necessary, use a safe read-only selection and reapply; do not save a setup change.

**Narration:** The journey starts with a reporting period, a comparison period and a historical cutoff. Company viewers may also select an approved team population, teams or salespeople. The server checks each requested scope against the viewer's permissions; choosing a filter cannot expand those permissions. Named populations require a separately approved mapping, and cannot be combined with direct team or salesperson filters.

## 00:00:58.844–00:01:28.461 · Read the monthly picture

**Screen:** Show Month View, the KPI cards, Daily activity, Period comparison and Daily activity trend. Keep displayed values legible; avoid zooming into customer identifiers.

**Action:** Move from KPIs to the comparison table and trend. Read only values that are actually visible and appropriate for the audience.

**Narration:** Month View brings the selected activity together. The KPI cards summarize the current and comparison periods; the daily table and chart show when activity happened. A dash means a value is zero or unavailable, so it must not be read as a confirmed zero. Customer PO receipts are captured receipts, while order confirmations are shown separately as an OC proxy. These are not interchangeable measures or a certified revenue total.

## 00:01:29.111–00:02:02.532 · Move from weeks to team patterns

**Screen:** Show Weekly Overview, then Team Comparison. Include weekly detail and the weekday comparison controls/table if populated.

**Action:** Switch tabs. Point to Monday–Sunday week groupings and the weekday matrix measure/display selectors. Do not infer rank or target attainment where none is shown.

**Narration:** Weekly Overview groups dates from Monday through Sunday, including incomplete weeks. Team Comparison breaks activity down by team and salesperson, with a weekly team view and a weekday matrix. The weekday matrix can show a count, raw value or normalized value. Daily averages use Monday through Saturday reporting days, with configured calendar overrides applied. Older ERP records may use current team membership as a provisional attribution, and ambiguous owners remain unassigned.

## 00:02:03.182–00:02:35.443 · Inspect the supporting records

**Screen:** Scroll to quotation supporting documents or use its in-page drilldown. Show the table structure and status labels without exposing sensitive names, references, or amounts in a public recording.

**Action:** Open a detail panel only if it is safe to show. Point out the source-document basis, date and value status. Do not issue or alter a quotation.

**Narration:** From the summary, the manager can inspect supporting quotation documents and their source facts. Ordinary quotations use option-zero line details; quick quotations use their recorded price and quantity. For older records, the document date is a proxy for issue date. Captured issue events provide dated snapshots going forward. Values in other currencies are not silently converted to Pakistani rupees, and unknown values stay unavailable instead of being filled with zero.

## 00:02:36.093–00:03:10.044 · Enter and save the activity forms

**Screen:** Show the follow-up and enquiry-event forms populated with the masked test case, date, time, response type and `DEMO ONLY` note. Then show the saved event timeline.

**Action:** Demonstrate the saved entries in order: enquiry receipt at 09:15, customer-contact response at 09:25, and call follow-up at 09:35. Each entry is tagged `DEMO ONLY`. Do not show the real internal case reference.

**Narration:** Now we'll enter demo data to show how the forms work. We use an existing test case with no prior reporting captures, and every entry is tagged demo only. First, we record an enquiry receipt at nine fifteen. Next, we record a customer-contact response at nine twenty-five, then a follow-up call at nine thirty-five with a next action. Each form saves to the reporting database and appears in the case timeline. These are synthetic walkthrough records, not real customer activity.

## 00:03:10.694–00:03:40.841 · Record a demo PO and review coverage

**Screen:** Show the Customer PO receipt form with the unique `DEMO ONLY` reference and blank amount, then show the coverage review form with Partial selected and its evidence note. Keep the review visibly unsubmitted.

**Action:** Show the saved test-only PO example at 10:15. Explain that its amount is blank, so the report counts the demo entry but has no receipt value. Then show the Partial coverage draft; do not describe it as a saved coverage submission or a full-company certification.

**Narration:** The customer PO form captures the case, reference, receipt date and time, and any supplied amount. For this walkthrough only, we use a unique demo-only reference and leave the amount blank, so the report shows a receipt count with no invented money value. Next, an administrator can review the calculated figures and select partial with an evidence note. We leave that coverage review unsubmitted; demo entries do not certify full company coverage.

## 00:03:41.491–00:04:13.238 · Handle source comparisons carefully

**Screen:** Show Reference observations and reconciliation, then Normalized value adjustments when authorized. Avoid displaying imported source figures unless their evidence is cleared for recording.

**Action:** Point to Preview CSV and the review/status fields. Explain the second-person approval for value adjustments. Do not import, reconcile, request or approve records on camera.

**Narration:** Historical source figures can be previewed from a CSV and retained first as unverified observations, with their source version and evidence location. After source review, an eligible frozen report can be compared on the same full period, scope and end-of-day cutoff. A discrepancy is recorded for investigation; reconciliation does not alter the report. Normalized value adjustments preserve document counts and affect results only after a different authorized user approves them.

## 00:04:13.888–00:04:42.801 · Freeze and export one consistent report

**Screen:** Show Freeze report and CSV, Excel and PDF controls. If the signed-in account has create-run permission, demonstrate freezing the selected genuine report and then downloading an export only if appropriate for the audience.

**Action:** Freezing creates an auditable saved run. If a real run is created, show its key briefly, then mask it in the final edit. Do not paste run keys into public materials. Exports require an authorized saved run.

**Narration:** When the scope and periods are ready, an authorized user can freeze the report. The saved run keeps the selected scope, periods, cutoff, calendar and supporting source facts together. Later changes to live records do not rewrite that run. CSV, Excel and PDF downloads come from the same saved run, so the figures and supporting detail stay aligned. Access is checked again when someone loads or exports it.

## 00:04:43.451–00:05:15.402 · Close: what the report means today

**Screen:** Return to the report header or a clean overview. End on the real reporting page, not a synthetic dashboard.

**Action:** Hold a clean closing frame. Add a small closing title in editing only if desired: 'Live ERP data · Provisional definitions · Authorized scope'.

**Narration:** The report is connected to the local ERP database, and the walkthrough entries have been saved in its reporting tables. They use an existing test case and every entry is tagged demo only, including the PO example; no amount was invented. The report now demonstrates saved events and a counted receipt while keeping the receipt value unavailable. Historical coverage and business definitions remain provisional, so keep these records in the demo environment and review definitions before operational use.

## Deliverables

- `sales-performance-walkthrough-narration.wav` — complete narration audio.
- `sales-performance-walkthrough.srt` — sentence-level captions aligned to the rendered narration.
- `sales-performance-walkthrough.json` — source scene list used for timing and capture direction.

The MP4 is an illustrative UI tour. Its form values mirror the saved demo workflow but use masked case details; they are not a recording of live ERP screens or live report totals.
