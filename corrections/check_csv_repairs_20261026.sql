-- Read-only checks; can also be run one SELECT at a time in phpMyAdmin.
-- Select the intended ERP database first. No stored procedure is called here.

-- Installation is confirmed only if all three repair routine names appear.
SELECT ROUTINE_NAME,ROUTINE_TYPE
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA=DATABASE()
  AND ROUTINE_NAME IN ('csvfix_9992_20261026','csvfix_9996_20261026','csvfix_10139_20261026')
ORDER BY ROUTINE_NAME;

-- A successful apply writes this log in the same transaction as its repair.
-- No rows is expected when only installation/previews have run.
SELECT id,voucher,action_name,changed_at,database_operator,details
FROM csvfix_20261026_log
ORDER BY id DESC LIMIT 20;

SELECT s.orderno,s.complete,l.id AS line_id,l.description,l.quantity AS line_quantity,
       i.id AS component_id,i.stockid,i.quantity AS component_quantity,i.rate,i.discountpercent
FROM shopsale s
JOIN shopsalelines l ON l.orderno=s.orderno
LEFT JOIN shopsalesitems i ON i.orderno=l.orderno AND i.lineno=l.id
WHERE s.orderno IN (9992,9996,10139)
ORDER BY s.orderno,l.id,i.id;

SELECT transno,stockid,SUM(qty) AS delivered_quantity,COUNT(*) AS stock_movement_count
FROM stockmoves
WHERE type=750 AND transno IN (9992,9996,10139)
GROUP BY transno,stockid;

SELECT transno,id,ovamount,alloc,settled,reversed
FROM debtortrans
WHERE type=750 AND transno IN (9992,9996,10139)
ORDER BY transno,id;
