<?php

namespace SAHamid\BI\Directors;

/**
 * Code-owned, native-grain operational reporting datasets.
 *
 * The caller applies the directors permission and legacy page security before
 * executing a dataset. All dimensions are explicit: a customer branch is not a
 * company branch, and current master attributes are not historical snapshots.
 * NULL amounts retain ambiguous/unpriced rows for the service's quality count;
 * they must never be silently converted into known zero amounts.
 */
class OperationsDatasetCatalog
{
    public static function definitions($userId, $isAdministrator)
    {
        // No user input enters these statements. Directors' company-wide access
        // is checked by DirectorsAuthorization; source pages retain their ACLs.
        $datasets = array();

        $datasets['invoice_value'] = array(
            'title' => 'Invoice detail value', 'unit' => 'PKR', 'kind' => 'flow', 'status' => 'actual',
            'source' => 'invoice + invoicedetails + invoiceoptions; customer and case masters',
            'definition' => 'Non-returned, completed invoices: unit price × (1 − fractional discount) × detail quantity × matching option quantity, by invoice date.',
            'caveat' => 'Published raw invoice formula, not certified net revenue or gross AR. Only current customer currency PKR is included; historical currency/tax classification is unavailable. Missing/duplicate headers or options are excluded from value and counted as quality exceptions. Current master ownership is used.',
            'route' => 'SalesGraph.php', 'source_route' => 'PDFInvoice.php', 'source_param' => 'InvoiceNo',
            'dimensions' => array('customer' => 'Customer', 'branch' => 'Customer branch', 'salesperson' => 'Case owner', 'product' => 'Product', 'category' => 'Category', 'brand' => 'Brand', 'location' => 'Dispatch location', 'project' => 'Sales case'),
            'sql' => <<<'SQL'
SELECT CAST(d.invoicedetailsindex AS CHAR) AS id, i.invoicesdate AS date,
       CONCAT('Invoice ', i.invoiceno, ' / line ', d.invoicelineno, ' / option ', d.invoiceoptionno) AS reference,
       CASE WHEN i.header_count=1 AND o.option_count=1 AND dm.currcode='PKR'
            THEN d.unitprice * (1-d.discountpercent) * d.quantity * o.quantity ELSE NULL END AS amount,
       d.quantity * CASE WHEN o.option_count=1 THEN o.quantity ELSE NULL END AS quantity,
       CASE WHEN i.header_count<>1 THEN 'Ambiguous invoice number' WHEN o.option_count IS NULL THEN 'Missing option'
            WHEN o.option_count<>1 THEN 'Ambiguous option' WHEN COALESCE(dm.currcode,'')<>'PKR' THEN 'Unsupported currency'
            ELSE 'Completed' END AS status,
       CASE WHEN i.header_count<>1 OR COALESCE(o.option_count,0)<>1 OR COALESCE(dm.currcode,'')<>'PKR' THEN 1 ELSE 0 END AS quality_issue,
       CAST(i.invoiceno AS CHAR) AS source_key,
       CONCAT(i.debtorno,' · ',COALESCE(NULLIF(dm.name,''),'[Missing customer]')) AS customer,
       CONCAT(i.debtorno,' / ',COALESCE(NULLIF(i.branchcode,''),'[Unassigned]')) AS branch,
       COALESCE(NULLIF(sc.salesman,''),'[Unassigned]') AS salesperson,
       CONCAT(d.stkcode,' · ',COALESCE(sm.description,'[Missing master]')) AS product,
       COALESCE(NULLIF(cat.categorydescription,''),'[Unassigned]') AS category,
       COALESCE(NULLIF(m.manufacturers_name,''),'[Unassigned]') AS brand,
       COALESCE(NULLIF(i.fromstkloc,''),'[Unassigned]') AS location,
       COALESCE(NULLIF(i.salescaseref,''),'[Unassigned]') AS project
FROM invoicedetails d
INNER JOIN (SELECT invoiceno, COUNT(*) AS header_count, MIN(invoicesdate) AS invoicesdate,
                   MIN(debtorno) AS debtorno, MIN(branchcode) AS branchcode, MIN(salescaseref) AS salescaseref,
                   MIN(fromstkloc) AS fromstkloc, MIN(returned) AS returned, MIN(inprogress) AS inprogress
            FROM invoice GROUP BY invoiceno) i ON i.invoiceno=d.invoiceno
LEFT JOIN (SELECT invoiceno,invoicelineno,invoiceoptionno,COUNT(*) AS option_count,MIN(quantity) AS quantity
           FROM invoiceoptions GROUP BY invoiceno,invoicelineno,invoiceoptionno) o
       ON o.invoiceno=d.invoiceno AND o.invoicelineno=d.invoicelineno AND o.invoiceoptionno=d.invoiceoptionno
LEFT JOIN debtorsmaster dm ON dm.debtorno=i.debtorno
LEFT JOIN (SELECT salescaseref,CASE WHEN COUNT(*)=1 THEN MIN(salesman) ELSE '[Ambiguous case]' END AS salesman
           FROM salescase GROUP BY salescaseref) sc ON sc.salescaseref=i.salescaseref
LEFT JOIN stockmaster sm ON sm.stockid=d.stkcode
LEFT JOIN stockcategory cat ON cat.categoryid=sm.categoryid
LEFT JOIN manufacturers m ON m.manufacturers_id=sm.brand
WHERE i.returned=0 AND i.inprogress=0
SQL
        );

        $datasets['shop_sales'] = array(
            'title' => 'Shop sales posted value', 'unit' => 'base', 'kind' => 'flow', 'status' => 'actual',
            'source' => 'shopsale + non-reversed debtortrans type 750',
            'definition' => 'Completed CSV/CRV shop-sale headers linked to exactly one non-reversed type-750 posting: posted ovamount / recorded transaction rate; dated by shop sale date.',
            'caveat' => 'Posted amount is separate from component pricing and invoice detail value. Missing/duplicate postings, duplicate sale headers and invalid exchange rates are excluded from value. Entered shop-sale owner is retained; current customer ownership is not substituted. Sale date may precede ledger posting.',
            'route' => 'SalesGraph.php', 'source_route' => 'shop/pos/shopSalePrint.php', 'source_param' => 'orderno',
            'dimensions' => array('customer' => 'Customer', 'branch' => 'Customer branch', 'salesperson' => 'Entered shop owner', 'channel' => 'Payment channel'),
            'sql' => <<<'SQL'
SELECT CAST(ss.id AS CHAR) AS id, ss.orddate AS date, CONCAT(UPPER(ss.payment),' ',ss.orderno) AS reference,
       CASE WHEN ss.header_count=1 AND dt.posting_count=1 AND dt.rate>0 THEN dt.ovamount/dt.rate ELSE NULL END AS amount,
       NULL AS quantity,
       CASE WHEN ss.header_count<>1 THEN 'Ambiguous shop order' WHEN dt.posting_count IS NULL THEN 'Missing posting'
            WHEN dt.posting_count<>1 THEN 'Ambiguous posting' WHEN COALESCE(dt.rate,0)<=0 THEN 'Invalid exchange rate' ELSE 'Completed' END AS status,
       CASE WHEN ss.header_count<>1 OR COALESCE(dt.posting_count,0)<>1 OR COALESCE(dt.rate,0)<=0 THEN 1 ELSE 0 END AS quality_issue,
       CAST(ss.orderno AS CHAR) AS source_key,
       CONCAT(ss.debtorno,' · ',COALESCE(NULLIF(dm.name,''),'[Missing customer]')) AS customer,
       CONCAT(ss.debtorno,' / ',COALESCE(NULLIF(ss.branchcode,''),'[Unassigned]')) AS branch,
       COALESCE(NULLIF(ss.salesman,''),'[Unassigned]') AS salesperson, UPPER(ss.payment) AS channel
FROM (SELECT orderno,COUNT(*) AS header_count,MIN(id) AS id,MIN(orddate) AS orddate,MIN(payment) AS payment,
             MIN(debtorno) AS debtorno,MIN(branchcode) AS branchcode,MIN(salesman) AS salesman,MIN(complete) AS complete
      FROM shopsale GROUP BY orderno) ss
LEFT JOIN (SELECT transno,COUNT(*) AS posting_count,MIN(ovamount) AS ovamount,MIN(rate) AS rate
           FROM debtortrans WHERE type=750 AND reversed=0 GROUP BY transno) dt ON dt.transno=ss.orderno
LEFT JOIN debtorsmaster dm ON dm.debtorno=ss.debtorno
WHERE ss.complete=1 AND LOWER(ss.payment) IN ('csv','crv')
SQL
        );

        $receiptDimensions = array('supplier' => 'Supplier', 'product' => 'Product', 'category' => 'Category', 'brand' => 'Brand');
        $receiptFrom = <<<'SQL'
FROM bpitems b
INNER JOIN bazar_parchi p ON p.parchino=b.parchino
LEFT JOIN suppliers s ON s.supplierid=p.svid
LEFT JOIN stockmaster sm ON sm.stockid=b.stockid
LEFT JOIN stockcategory cat ON cat.categoryid=sm.categoryid
LEFT JOIN manufacturers m ON m.manufacturers_id=sm.brand
SQL;
        $receiptFields = <<<'SQL'
       p.parchino AS source_key,
       CONCAT(COALESCE(NULLIF(p.svid,''),'[Missing supplier code]'),' · ',COALESCE(NULLIF(s.suppname,''),'[Missing supplier]')) AS supplier,
       CONCAT(b.stockid,' · ',COALESCE(sm.description,b.name,'[Missing master]')) AS product,
       COALESCE(NULLIF(cat.categorydescription,''),'[Unassigned]') AS category,
       COALESCE(NULLIF(m.manufacturers_name,''),'[Unassigned]') AS brand
SQL;
        $datasets['purchasing'] = array(
            'title' => 'MPIW received value by slip cohort', 'unit' => 'PKR', 'kind' => 'flow', 'status' => 'actual',
            'source' => 'Completed active bazar_parchi type 601 + non-deleted bpitems',
            'definition' => 'Current cumulative received quantity × entered unit price on completed, non-discarded, non-returned inward market slips, grouped into cohorts by slip creation date.',
            'caveat' => 'Restated slip-cohort analysis, not receipts occurring in the selected period, expense, supplier balance or paid cash. Later receipts change older cohorts because quantity_received is a current cumulative counter. Dated receiving events are required for acquisition-period trends. No supplier-ledger/IGP join is added to value. Only positive priced received lines with current supplier currency PKR are valued; missing/foreign currency and nonpositive prices remain excluded quality rows.',
            'route' => 'SupplierInquiry.php', 'source_route' => 'shop/parchi/inward/inwardParchiPrint.php', 'source_param' => 'parchi',
            'dimensions' => $receiptDimensions,
            'sql' => "SELECT CAST(b.id AS CHAR) AS id, DATE(p.created_at) AS date, CONCAT(p.parchino,' / item ',b.id) AS reference,
       CASE WHEN b.price>0 AND s.currcode='PKR' THEN b.quantity_received*b.price ELSE NULL END AS amount,
       b.quantity_received AS quantity,
       CASE WHEN b.price<=0 THEN 'Unpriced receipt' WHEN COALESCE(s.currcode,'')<>'PKR' THEN 'Unsupported currency' ELSE 'Received' END AS status,
       CASE WHEN b.price<=0 OR COALESCE(s.currcode,'')<>'PKR' THEN 1 ELSE 0 END AS quality_issue,\n" . $receiptFields . "\n" . $receiptFrom . "\nWHERE p.type=601 AND p.inprogress=0 AND p.discarded=0 AND p.returned=0 AND b.deleted_at IS NULL AND b.quantity_received>0",
        );

        $datasets['purchase_commitments'] = array(
            'title' => 'Unreceived market-slip value', 'unit' => 'PKR', 'kind' => 'snapshot', 'status' => 'estimated',
            'source' => 'Active bazar_parchi type 601 + non-deleted bpitems',
            'definition' => 'Current positive (requested quantity − received quantity) × entered price on non-discarded, non-returned inward market-slip lines.',
            'caveat' => 'Operational estimate including drafts/in-progress slips; not a contractual PO commitment or future cash forecast. Unpriced and non-PKR/missing supplier-currency lines are excluded from amount and counted. Other procurement/legacy PO streams are not included. Current snapshot ignores date filters and has no historical comparison.',
            'route' => 'SupplierInquiry.php', 'source_route' => 'shop/parchi/inward/inwardParchiPrint.php', 'source_param' => 'parchi',
            'dimensions' => $receiptDimensions,
            'sql' => "SELECT CAST(b.id AS CHAR) AS id, DATE(p.created_at) AS date, CONCAT(p.parchino,' / item ',b.id) AS reference,
       CASE WHEN b.price>0 AND s.currcode='PKR' THEN (b.quantity-b.quantity_received)*b.price ELSE NULL END AS amount,
       (b.quantity-b.quantity_received) AS quantity,
       CASE WHEN b.price<=0 THEN 'Unpriced' WHEN COALESCE(s.currcode,'')<>'PKR' THEN 'Unsupported currency'
            WHEN p.inprogress=1 THEN 'In progress / draft' ELSE 'Not fully received' END AS status,
       CASE WHEN b.price<=0 OR COALESCE(s.currcode,'')<>'PKR' THEN 1 ELSE 0 END AS quality_issue,\n" . $receiptFields . "\n" . $receiptFrom . "\nWHERE p.type=601 AND p.discarded=0 AND p.returned=0 AND b.deleted_at IS NULL AND b.quantity>b.quantity_received",
        );

        $stockCost = '(sm.materialcost+sm.labourcost+sm.overheadcost)';
        $stockFrom = <<<'SQL'
FROM locstock ls
LEFT JOIN stockmaster sm ON sm.stockid=ls.stockid
LEFT JOIN stockcategory cat ON cat.categoryid=sm.categoryid
LEFT JOIN manufacturers m ON m.manufacturers_id=sm.brand
SQL;
        $stockFields = <<<'SQL'
       ls.stockid AS source_key, ls.stockid AS product,
       CONCAT(ls.stockid,' · ',COALESCE(sm.description,'[Missing master]')) AS product_label,
       COALESCE(NULLIF(cat.categorydescription,''),'[Unassigned]') AS category,
       COALESCE(NULLIF(m.manufacturers_name,''),'[Unassigned]') AS brand,
       CAST(ls.loccode AS CHAR) AS location, COALESCE(NULLIF(sm.units,''),'[Unknown]') AS stock_unit
SQL;
        $stockStatus = "CASE WHEN ls.quantity<0 THEN 'Negative stock' WHEN sm.stockid IS NULL THEN 'Missing stock master'
            WHEN ls.quantity<>0 AND $stockCost<=0 THEN 'Missing / nonpositive standard cost'
            WHEN ls.reorderlevel>0 AND ls.quantity<ls.reorderlevel THEN 'Below reorder level' ELSE 'On hand' END";
        $stockQuality = "CASE WHEN ls.quantity<0 OR sm.stockid IS NULL OR (ls.quantity<>0 AND $stockCost<=0) THEN 1 ELSE 0 END";
        $stockDimensions = array('location' => 'Stock location', 'product' => 'Product', 'category' => 'Category', 'brand' => 'Brand', 'stock_unit' => 'Stock unit');
        $datasets['inventory'] = array(
            'title' => 'Current stock at standard cost', 'unit' => 'base', 'kind' => 'snapshot', 'status' => 'estimated',
            'source' => 'locstock + current stockmaster material/labour/overhead costs',
            'definition' => 'Current location/item quantity × (material cost + labour cost + overhead cost), matching the standard Inventory Valuation cost basis.',
            'caveat' => 'Current standard-cost estimate, not historical stock, historical COGS, market value or guaranteed availability. Nonzero quantity with missing/nonpositive cost remains unknown value. Negative quantities retain their signed value and are quality exceptions. Quantities use unlike units and must not be added into a headline total. Current snapshot ignores date filters.',
            'route' => 'InventoryValuation.php', 'source_route' => 'StockStatus.php', 'source_param' => 'StockID',
            'dimensions' => $stockDimensions,
            'dimension_labels' => array('product' => 'product_label'), 'search_columns' => array('product_label'),
            'sql' => "SELECT CONCAT(ls.loccode,'|',ls.stockid) AS id, NULL AS date, CONCAT(ls.stockid,' @ ',ls.loccode) AS reference,
       CASE WHEN ls.quantity=0 THEN 0 WHEN $stockCost>0 THEN ls.quantity*$stockCost ELSE NULL END AS amount,
       ls.quantity AS quantity, $stockStatus AS status, $stockQuality AS quality_issue,\n" . $stockFields . "\n" . $stockFrom . "\nWHERE ls.quantity<>0 OR ls.reorderlevel>0",
        );
        $datasets['stock_exceptions'] = array(
            'title' => 'Stock positions requiring attention', 'unit' => 'count', 'kind' => 'snapshot', 'status' => 'actual',
            'source' => 'locstock + current stockmaster/reorder metadata',
            'definition' => 'Item/location positions with negative stock, missing master/nonpositive cost for nonzero stock, or quantity below a positive configured reorder level; one count per position.',
            'caveat' => 'A position can have several issues but is counted once. The status shows the first issue. Counts are item/location positions, not distinct products or summed stock units. A missing reorder level is not an approved zero safety-stock policy. Current snapshot ignores date filters.',
            'route' => 'InventoryValuation.php', 'source_route' => 'StockStatus.php', 'source_param' => 'StockID',
            'dimensions' => $stockDimensions,
            'dimension_labels' => array('product' => 'product_label'), 'search_columns' => array('product_label'),
            'sql' => "SELECT CONCAT(ls.loccode,'|',ls.stockid) AS id, NULL AS date, CONCAT(ls.stockid,' @ ',ls.loccode) AS reference,
       1 AS amount, ls.quantity AS quantity, $stockStatus AS status, $stockQuality AS quality_issue,\n" . $stockFields . "\n" . $stockFrom . "\nWHERE ls.quantity<0 OR sm.stockid IS NULL OR (ls.quantity<>0 AND $stockCost<=0) OR (ls.reorderlevel>0 AND ls.quantity<ls.reorderlevel)",
        );

        $caseFrom = <<<'SQL'
FROM (SELECT salescaseref, COUNT(*) AS case_count, MIN(salescaseindex) AS salescaseindex,
             MIN(commencementdate) AS commencementdate, MIN(debtorno) AS debtorno, MIN(branchcode) AS branchcode,
             MIN(salesman) AS salesman, MIN(priority) AS priority, MIN(enquiryvalue) AS enquiryvalue, MIN(closed) AS closed
      FROM salescase GROUP BY salescaseref) sc
LEFT JOIN debtorsmaster dm ON dm.debtorno=sc.debtorno
WHERE sc.closed=0
SQL;
        $caseFields = <<<'SQL'
       sc.salescaseref AS source_key,
       CONCAT(sc.debtorno,' · ',COALESCE(NULLIF(dm.name,''),'[Missing customer]')) AS customer,
       CONCAT(sc.debtorno,' / ',COALESCE(NULLIF(sc.branchcode,''),'[Unassigned]')) AS branch,
       COALESCE(NULLIF(sc.salesman,''),'[Unassigned]') AS salesperson,
       sc.salescaseref AS project, COALESCE(NULLIF(sc.priority,''),'[Unassigned]') AS priority
SQL;
        $caseDimensions = array('customer' => 'Customer', 'branch' => 'Customer branch', 'salesperson' => 'Current case owner', 'project' => 'Sales case', 'priority' => 'Priority');
        $datasets['open_cases'] = array(
            'title' => 'Open sales cases', 'unit' => 'count', 'kind' => 'snapshot', 'status' => 'actual',
            'source' => 'salescase current closed flag',
            'definition' => 'One open sales case per unique salescaseref, based on closed=0; duplicate case references retain an unknown count.',
            'caveat' => 'Current case backlog, not new enquiries, active quotations or a historical sales funnel. Legacy closure flags can include stale cases. Current snapshot ignores date filters and has no prior-period comparison.',
            'route' => 'SalesGraph.php', 'source_route' => 'salescase/salescaseview.php', 'source_param' => 'salescaseref',
            'dimensions' => $caseDimensions,
            'sql' => "SELECT CAST(sc.salescaseindex AS CHAR) AS id, DATE(sc.commencementdate) AS date, sc.salescaseref AS reference,
       CASE WHEN sc.case_count=1 THEN 1 ELSE NULL END AS amount, NULL AS quantity,
       CASE WHEN sc.case_count=1 THEN 'Open' ELSE 'Ambiguous case reference' END AS status,
       CASE WHEN sc.case_count<>1 OR dm.debtorno IS NULL THEN 1 ELSE 0 END AS quality_issue,\n" . $caseFields . "\n" . $caseFrom,
        );
        $datasets['pipeline'] = array(
            'title' => 'Open enquiry value estimate', 'unit' => 'PKR', 'kind' => 'snapshot', 'status' => 'estimated',
            'source' => 'Open salescase.enquiryvalue',
            'definition' => 'Positive entered enquiry value on unique currently open cases whose current customer currency is PKR.',
            'caveat' => 'Unweighted, entered enquiry estimates; not quotation value, orders, recognized revenue or a sales forecast. Zero/unentered values, duplicate case references and unsupported currency remain unknown. No probability, expected-close schedule or historical snapshot exists for this measure. Current snapshot ignores date filters.',
            'route' => 'SalesGraph.php', 'source_route' => 'salescase/salescaseview.php', 'source_param' => 'salescaseref',
            'dimensions' => $caseDimensions,
            'sql' => "SELECT CAST(sc.salescaseindex AS CHAR) AS id, DATE(sc.commencementdate) AS date, sc.salescaseref AS reference,
       CASE WHEN sc.case_count=1 AND sc.enquiryvalue>0 AND dm.currcode='PKR' THEN sc.enquiryvalue ELSE NULL END AS amount,
       NULL AS quantity,
       CASE WHEN sc.case_count<>1 THEN 'Ambiguous case reference' WHEN COALESCE(dm.currcode,'')<>'PKR' THEN 'Unsupported currency'
            WHEN sc.enquiryvalue<=0 THEN 'Unvalued enquiry' ELSE 'Entered estimate' END AS status,
       CASE WHEN sc.case_count<>1 OR sc.enquiryvalue<=0 OR COALESCE(dm.currcode,'')<>'PKR' THEN 1 ELSE 0 END AS quality_issue,\n" . $caseFields . "\n" . $caseFrom,
        );

        $datasets['work_orders'] = array(
            'title' => 'Open work orders', 'unit' => 'count', 'kind' => 'snapshot', 'status' => 'actual',
            'source' => 'workorders current closed flag',
            'definition' => 'Open work-order numbers, counted once only where the source contains exactly one row for the work-order number.',
            'caveat' => 'Current manufacturing backlog, not completed production or utilization. Duplicate work-order numbers retain unknown amount. An empty source does not establish whether manufacturing is active. Current snapshot ignores date filters.',
            'route' => 'WorkOrderStatus.php', 'source_route' => 'WorkOrderStatus.php', 'source_param' => 'WO',
            'dimensions' => array('location' => 'Production location'),
            'sql' => <<<'SQL'
SELECT CAST(w.wo AS CHAR) AS id, w.startdate AS date, CONCAT('WO ',w.wo) AS reference,
       CASE WHEN w.row_count=1 THEN 1 ELSE NULL END AS amount, NULL AS quantity,
       CASE WHEN w.row_count<>1 THEN 'Ambiguous work order'
            WHEN w.requiredby IS NULL OR w.requiredby='0000-00-00' THEN 'Missing required date'
            WHEN w.requiredby<CURRENT_DATE() THEN 'Past required date' ELSE 'Open' END AS status,
       CASE WHEN w.row_count<>1 OR w.requiredby IS NULL OR w.requiredby='0000-00-00' THEN 1 ELSE 0 END AS quality_issue,
       CAST(w.wo AS CHAR) AS source_key, COALESCE(NULLIF(w.loccode,''),'[Unassigned]') AS location
FROM (SELECT wo,COUNT(*) AS row_count,MIN(startdate) AS startdate,MIN(requiredby) AS requiredby,
             MIN(loccode) AS loccode,MIN(closed) AS closed FROM workorders GROUP BY wo) w
WHERE w.closed=0
SQL
        );

        $datasets['pending_deliveries'] = array(
            'title' => 'Pending delivery challans', 'unit' => 'count', 'kind' => 'snapshot', 'status' => 'actual',
            'source' => 'dcs + dcgroups; canonical Sales Executive / Pending DC List status rules',
            'definition' => 'DCs with zero courier, invoice and GRB dates, no invoice group, and no membership in a dcgroups comma-separated DC list; one unique DC number.',
            'caveat' => 'Current operational pending state; not an unfulfilled customer order or additional sale. Mirrors existing status rules without inventing a completion meaning from inprogress. Duplicate DC numbers retain unknown count. Group-list lookup normalizes spaces. No historical status snapshots exist; date filters and prior comparisons do not apply.',
            'route' => 'SalesGraph.php', 'source_route' => 'dc/previewDC.php', 'source_param' => 'DCNo',
            'dimensions' => array('customer' => 'Customer', 'branch' => 'Customer branch', 'salesperson' => 'Case owner', 'location' => 'Dispatch location', 'project' => 'Sales case'),
            'sql' => <<<'SQL'
SELECT CAST(d.dcindex AS CHAR) AS id, d.orddate AS date, CONCAT('DC ',d.orderno) AS reference,
       CASE WHEN d.header_count=1 THEN 1 ELSE NULL END AS amount, NULL AS quantity,
       CASE WHEN d.header_count=1 THEN 'Pending' ELSE 'Ambiguous DC number' END AS status,
       CASE WHEN d.header_count<>1 THEN 1 ELSE 0 END AS quality_issue,
       CAST(d.orderno AS CHAR) AS source_key,
       CONCAT(d.debtorno,' · ',COALESCE(NULLIF(dm.name,''),'[Missing customer]')) AS customer,
       CONCAT(d.debtorno,' / ',COALESCE(NULLIF(d.branchcode,''),'[Unassigned]')) AS branch,
       COALESCE(NULLIF(sc.salesman,''),'[Unassigned]') AS salesperson,
       COALESCE(NULLIF(d.fromstkloc,''),'[Unassigned]') AS location,
       COALESCE(NULLIF(d.salescaseref,''),'[Unassigned]') AS project
FROM (SELECT orderno,COUNT(*) AS header_count,MIN(dcindex) AS dcindex,MIN(orddate) AS orddate,
             MIN(debtorno) AS debtorno,MIN(branchcode) AS branchcode,MIN(salescaseref) AS salescaseref,
             MIN(fromstkloc) AS fromstkloc,MIN(courierslipdate) AS courierslipdate,MIN(invoicedate) AS invoicedate,
             MIN(grbdate) AS grbdate,MAX(invoicegroupid) AS invoicegroupid
      FROM dcs GROUP BY orderno) d
LEFT JOIN debtorsmaster dm ON dm.debtorno=d.debtorno
LEFT JOIN (SELECT salescaseref,CASE WHEN COUNT(*)=1 THEN MIN(salesman) ELSE '[Ambiguous case]' END AS salesman
           FROM salescase GROUP BY salescaseref) sc ON sc.salescaseref=d.salescaseref
WHERE d.courierslipdate='0000-00-00 00:00:00' AND d.invoicedate='0000-00-00 00:00:00'
  AND d.grbdate='0000-00-00 00:00:00' AND d.invoicegroupid IS NULL
  AND NOT EXISTS (SELECT 1 FROM dcgroups g WHERE FIND_IN_SET(CAST(d.orderno AS CHAR), REPLACE(g.dcnos,' ',''))>0)
SQL
        );

        $datasets['legacy_po_commitments'] = array(
            'title' => 'Authorized legacy PO unreceived value', 'unit' => 'base', 'kind' => 'snapshot', 'status' => 'estimated',
            'source' => 'purchorders + purchorderdetails; authorized/printed incomplete lines',
            'definition' => 'Positive (quantity ordered − quantity received) × unit price / recorded PO rate for incomplete lines on Authorised or Printed legacy purchase orders.',
            'caveat' => 'Recorded legacy purchase-order commitment estimate, excluding Pending, Rejected and Cancelled orders. No tax/freight or unpriced amount is inferred. No combination with market slips or procurement plans: linkage between the streams is not reconciled. Quantity counters and current order status are not historical snapshots.',
            'route' => 'PO_SelectOSPurchOrder.php', 'source_route' => 'PO_OrderDetails.php', 'source_param' => 'OrderNo',
            'dimensions' => array('supplier' => 'Supplier', 'product' => 'Product', 'location' => 'Receiving location', 'project' => 'Job reference', 'account' => 'GL account'),
            'sql' => "SELECT CAST(d.podetailitem AS CHAR) AS id, p.orddate AS date,
                CONCAT('PO ',p.orderno,' / line ',d.podetailitem) AS reference,
                CASE WHEN p.rate>0 AND d.unitprice>0 THEN (d.quantityord-d.quantityrecd)*d.unitprice/p.rate ELSE NULL END AS amount,
                d.quantityord-d.quantityrecd AS quantity, p.status AS status,
                CASE WHEN p.rate<=0 OR d.unitprice<=0 THEN 1 ELSE 0 END AS quality_issue,
                p.orderno AS source_key, CONCAT(p.supplierno,' · ',COALESCE(s.suppname,'Missing supplier')) AS supplier,
                CONCAT(d.itemcode,' · ',d.itemdescription) AS product, p.intostocklocation AS location,
                COALESCE(NULLIF(d.jobref,''),'Unassigned') AS project, d.glcode AS account
                FROM purchorderdetails d INNER JOIN purchorders p ON p.orderno=d.orderno
                LEFT JOIN suppliers s ON s.supplierid=p.supplierno
                WHERE d.completed=0 AND d.quantityord>d.quantityrecd AND p.status IN ('Authorised','Printed')",
        );
        $datasets['procurement_pipeline'] = array(
            'title' => 'Open procurement planning value', 'unit' => 'PKR', 'kind' => 'snapshot', 'status' => 'estimated',
            'source' => 'procurement_document + procurement_document_details; current active documents',
            'definition' => 'Entered quantity × price for procurement-plan lines whose document is neither cancelled nor received; current stage and ETA remain visible.',
            'caveat' => 'Planning estimate, including unpublished and negotiation stages, not an approved PO, purchase, payable or cash forecast. Only positive priced lines for a currently PKR supplier are valued. Price currency is not captured on the plan, so supplier currency is an explicit assumption. Do not add this to market-slip or legacy-PO commitments without reconciling document linkage.',
            'route' => 'SupplierInquiry.php', 'source_route' => 'v2/bi/directors-record.php', 'source_param' => 'id',
            'dimensions' => array('supplier' => 'Supplier', 'product' => 'Product', 'stage' => 'Recorded stage', 'publication' => 'Publication status', 'eta' => 'ETA status'),
            'sql' => "SELECT CAST(d.id AS CHAR) AS id, p.commencement_date AS date,
                CONCAT('Plan ',p.id,' / line ',d.id) AS reference,
                CASE WHEN d.price>0 AND d.quantity>0 AND s.currcode='PKR' THEN d.quantity*d.price ELSE NULL END AS amount,
                d.quantity AS quantity, p.stage AS status, d.id AS source_key,
                CASE WHEN d.price<=0 OR d.quantity<=0 OR COALESCE(s.currcode,'')<>'PKR' THEN 1 ELSE 0 END AS quality_issue,
                CONCAT(p.supplierid,' · ',COALESCE(s.suppname,'Missing supplier')) AS supplier,
                CONCAT(d.stockid,' · ',COALESCE(sm.description,'Missing stock master')) AS product, p.stage,
                CASE WHEN p.publish_date IS NULL THEN 'Unpublished' ELSE 'Published' END AS publication,
                CASE WHEN p.eta_date IS NULL OR p.eta_date<'1900-01-01' THEN 'Unknown ETA'
                    WHEN p.eta_date<CURRENT_DATE() THEN 'Past ETA' ELSE 'Future ETA' END AS eta
                FROM procurement_document_details d INNER JOIN procurement_document p ON p.id=d.pdid
                LEFT JOIN suppliers s ON s.supplierid=p.supplierid LEFT JOIN stockmaster sm ON sm.stockid=d.stockid
                WHERE p.canceled_date IS NULL AND p.received_date IS NULL",
        );
        return $datasets;
    }
}
