-- Select the ERP database before running this script.
-- Read-only audit of every Cash Sale Voucher, with no date restriction.
-- An obsolete item already attached successfully is not by itself an error.
START TRANSACTION READ ONLY;

-- audit_scope
SELECT DATABASE() AS database_name, NOW() AS checked_at,
       COUNT(*) AS csv_count, MIN(orddate) AS first_csv_date,
       MAX(orddate) AS latest_csv_date, MAX(orderno) AS latest_csv_number,
       SUM(complete=1) AS completed_csv_count,
       SUM(complete=0) AS open_csv_count
FROM shopsale WHERE payment='csv';

-- missing_or_zero_quantity_attachments
SELECT s.orderno, s.orddate, s.salesman, s.complete,
       COALESCE(p.active_postings,0) AS active_postings,
       l.id AS line_id, l.description, l.notes,
       l.quantity AS customer_quantity, l.uom,
       l.price, l.quantity*l.price AS line_value,
       CASE WHEN COALESCE(i.attached_count,0)=0 THEN 'MISSING_ATTACHMENT'
            ELSE 'NO_POSITIVE_COMPONENT_QUANTITY' END AS issue
FROM shopsale s
JOIN shopsalelines l ON l.orderno=s.orderno
LEFT JOIN (SELECT orderno,lineno,COUNT(*) AS attached_count,
                  SUM(CASE WHEN quantity>0 THEN 1 ELSE 0 END) AS positive_count
           FROM shopsalesitems GROUP BY orderno,lineno) i
       ON i.orderno=l.orderno AND i.lineno=l.id
LEFT JOIN (SELECT transno,COUNT(*) AS active_postings
           FROM debtortrans WHERE type=750 AND reversed=0
           GROUP BY transno) p ON p.transno=s.orderno
WHERE s.payment='csv'
  AND (COALESCE(i.attached_count,0)=0 OR i.positive_count=0)
ORDER BY s.complete DESC,COALESCE(p.active_postings,0) DESC,s.orddate DESC,s.orderno DESC,l.id;

-- hidden_or_unpriced_unattached_stock
-- Allocation records identify candidates, not the exact customer line supplied.
-- A remaining allocation above zero is stronger evidence of an attachment
-- blocker. A zero balance can also reflect returns, reallocations or old edits.
SELECT s.orderno,s.orddate,s.salesman,s.complete,
       COALESCE(p.active_postings,0) AS active_postings,
       r.stockid,m.mnfpno,m.description,m.units,m.discontinued,
       m.materialcost AS current_catalog_rate,
       r.remaining_allocation,r.dispatch_ids,
       CASE WHEN m.stockid IS NULL THEN 'STOCK_CODE_MISSING_FROM_CATALOG'
            WHEN m.discontinued<>0 THEN 'OBSOLETE_STOCK_NOT_ATTACHED'
            ELSE 'ZERO_CATALOG_RATE_STOCK_NOT_ATTACHED' END AS issue
FROM shopsale s
JOIN (SELECT CAST(csv AS UNSIGNED) AS orderno,stockid,
             SUM(COALESCE(quantity,0)) AS remaining_allocation,
             GROUP_CONCAT(DISTINCT NULLIF(dispatchid,'') ORDER BY dispatchid) AS dispatch_ids
      FROM ogpcsvref WHERE csv REGEXP '^[0-9]+$' GROUP BY csv,stockid) r
     ON r.orderno=s.orderno
LEFT JOIN stockmaster m ON m.stockid=r.stockid
LEFT JOIN (SELECT orderno,stockid,COUNT(*) AS attached_count
           FROM shopsalesitems GROUP BY orderno,stockid) i
       ON i.orderno=s.orderno AND i.stockid=r.stockid
LEFT JOIN (SELECT transno,COUNT(*) AS active_postings
           FROM debtortrans WHERE type=750 AND reversed=0
           GROUP BY transno) p ON p.transno=s.orderno
WHERE s.payment='csv' AND COALESCE(r.stockid,'')<>''
  AND (m.stockid IS NULL OR m.discontinued<>0 OR m.materialcost<=0)
  AND i.attached_count IS NULL
ORDER BY s.complete DESC,remaining_allocation DESC,s.orddate DESC,s.orderno DESC,r.stockid;

-- reopened_posted_csvs
-- These require reconciliation before any ordinary finalization is attempted.
SELECT s.orderno,s.orddate,s.salesman,s.complete,
       COUNT(d.id) AS active_postings,SUM(d.ovamount) AS posted_amount
FROM shopsale s
JOIN debtortrans d ON d.type=750 AND d.transno=s.orderno AND d.reversed=0
WHERE s.payment='csv' AND s.complete=0
GROUP BY s.orderno,s.orddate,s.salesman,s.complete
ORDER BY s.orddate DESC,s.orderno DESC;

ROLLBACK;
