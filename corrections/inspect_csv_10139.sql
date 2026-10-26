-- Run against the production ERP database selected by the administrator.
-- Read-only diagnosis of CSV 10139. This script does not repair or post a sale.
START TRANSACTION READ ONLY;

-- Header, customer bill total, and existing active accounting posting count.
SELECT s.orderno, s.orddate, s.payment, s.salesman, s.complete,
       s.discount, s.discountPKR,
       (SELECT SUM(l.quantity * l.price) FROM shopsalelines l
        WHERE l.orderno=s.orderno) * (1-s.discount/100) - s.discountPKR AS bill_total,
       (SELECT COUNT(*) FROM debtortrans d
        WHERE d.type=750 AND d.transno=s.orderno AND d.reversed=0) AS active_postings
FROM shopsale s
WHERE s.orderno=10139;

-- Includes unattached lines and the saved component rates used by reports.
-- Component quantity is per customer-line unit: multiply by line quantity.
SELECT l.id AS line_id, l.description AS customer_description,
       l.quantity AS customer_quantity, l.uom AS customer_unit,
       l.price AS customer_unit_price, l.quantity*l.price AS line_total,
       i.id AS component_id, i.stockid, m.mnfpno,
       m.description AS stock_description, m.units AS stock_unit,
       i.quantity AS component_per_line_unit,
       i.quantity*l.quantity AS total_stock_quantity,
       i.rate AS saved_component_rate, i.discountpercent,
       i.quantity*i.rate*(1-i.discountpercent/100)*l.quantity AS component_total,
       CASE WHEN i.id IS NULL THEN 'MISSING ATTACHMENT'
            WHEN m.stockid IS NULL THEN 'STOCK CODE NOT FOUND'
            WHEN i.quantity<=0 THEN 'INVALID COMPONENT QUANTITY'
            ELSE 'ATTACHED' END AS attachment_status
FROM shopsalelines l
LEFT JOIN shopsalesitems i ON i.orderno=l.orderno AND i.lineno=l.id
LEFT JOIN stockmaster m ON m.stockid=i.stockid
WHERE l.orderno=10139
ORDER BY l.id, i.id;

-- Stock allocations and original outward dispatch quantities, including an
-- SPD allocated to the voucher but missing from its attached component rows.
SELECT r.id AS allocation_id, r.stockid, m.mnfpno,
       m.description, m.units, r.salesman, r.dispatchid,
       r.quantity AS remaining_allocation,
       p.quantity AS original_dispatch_quantity,
       h.despatchdate, h.deliveredto
FROM ogpcsvref r
LEFT JOIN stockmaster m ON m.stockid=r.stockid
LEFT JOIN posdispatchitems p ON p.dispatchid=r.dispatchid AND p.stockid=r.stockid
LEFT JOIN posdispatch h ON h.dispatchid=r.dispatchid
WHERE r.csv='10139'
ORDER BY r.id;

-- Existing delivery movements: reconcile these before adding any deduction.
SELECT stkmoveno, stockid, type, transno, loccode, trandate,
       reference, qty, newqoh, hidemovt
FROM stockmoves
WHERE type=750 AND transno=10139
ORDER BY stkmoveno;

-- Relevant salesperson balances. Access is through the administrator's own
-- database permissions rather than changing cart access in the application.
SELECT si.salesperson, si.stockid, m.mnfpno, m.description, m.units,
       si.issued, si.dc, si.returned, si.adjusted
FROM stockissuance si
LEFT JOIN stockmaster m ON m.stockid=si.stockid
WHERE si.salesperson IN (SELECT salesman FROM shopsale WHERE orderno=10139)
  AND (EXISTS (SELECT 1 FROM ogpcsvref r
               WHERE r.csv='10139' AND r.stockid=si.stockid)
       OR EXISTS (SELECT 1 FROM shopsalesitems i
                  WHERE i.orderno=10139 AND i.stockid=si.stockid))
ORDER BY si.stockid;

-- Existing accounting entries and settlement state, retained during repair.
SELECT id, type, transno, trandate, ovamount, alloc, settled, reversed
FROM debtortrans
WHERE type=750 AND transno=10139
ORDER BY id;

ROLLBACK;
