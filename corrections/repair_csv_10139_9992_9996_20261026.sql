-- Select the PRODUCTION ERP database in your SQL client before loading this file.
-- MariaDB 10.3+ / MySQL 5.7+. Run outside any existing transaction.
-- This file installs narrowly scoped repair routines and first-state backups.
-- INSTALL ONLY: no procedure is executed while this file is imported.
-- Older phpMyAdmin releases can fail when an import ends with CALL result sets.
-- Back up the database, preview, then use the APPLY calls shown at the bottom.
SET time_zone = '+05:00';

CREATE TABLE IF NOT EXISTS csvfix_20261026_shopsale LIKE shopsale;
CREATE TABLE IF NOT EXISTS csvfix_20261026_shopsalelines LIKE shopsalelines;
CREATE TABLE IF NOT EXISTS csvfix_20261026_shopsalesitems LIKE shopsalesitems;
CREATE TABLE IF NOT EXISTS csvfix_20261026_stockissuance LIKE stockissuance;
CREATE TABLE IF NOT EXISTS csvfix_20261026_stockmoves LIKE stockmoves;
CREATE TABLE IF NOT EXISTS csvfix_20261026_ogpcsvref LIKE ogpcsvref;
ALTER TABLE csvfix_20261026_ogpcsvref MODIFY quantity DECIMAL(18,6) DEFAULT NULL;
CREATE TABLE IF NOT EXISTS csvfix_20261026_debtortrans LIKE debtortrans;
CREATE TABLE IF NOT EXISTS csvfix_20261026_snapshots (
    voucher INT NOT NULL PRIMARY KEY,
    captured_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    database_operator VARCHAR(255) NOT NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS csvfix_20261026_log (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    voucher INT NOT NULL, action_name VARCHAR(40) NOT NULL,
    changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    database_operator VARCHAR(255) NOT NULL, details TEXT NOT NULL
) ENGINE=InnoDB;

DROP PROCEDURE IF EXISTS csvfix_assert_20261026;
DROP PROCEDURE IF EXISTS csvfix_snapshot_20261026;
DROP PROCEDURE IF EXISTS csvfix_posting_20261026;
DROP PROCEDURE IF EXISTS csvfix_verify_posting_20261026;
DROP PROCEDURE IF EXISTS csvfix_9992_20261026;
DROP PROCEDURE IF EXISTS csvfix_9996_20261026;
DROP PROCEDURE IF EXISTS csvfix_10139_20261026;
DELIMITER $$

CREATE PROCEDURE csvfix_assert_20261026(IN ok BOOLEAN, IN explanation VARCHAR(128))
SQL SECURITY INVOKER
BEGIN
    IF NOT COALESCE(ok,FALSE) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT=explanation;
    END IF;
END$$

-- A committed snapshot is frozen, including which rows existed originally.
CREATE PROCEDURE csvfix_snapshot_20261026(IN n INT)
SQL SECURITY INVOKER
capture_block: BEGIN
    DECLARE captured INT;
    SELECT COUNT(*) INTO captured FROM csvfix_20261026_snapshots WHERE voucher=n;
    IF captured>0 THEN LEAVE capture_block; END IF;
    INSERT IGNORE INTO csvfix_20261026_shopsale SELECT * FROM shopsale WHERE orderno=n;
    INSERT IGNORE INTO csvfix_20261026_shopsalelines SELECT * FROM shopsalelines WHERE orderno=n;
    INSERT IGNORE INTO csvfix_20261026_shopsalesitems SELECT * FROM shopsalesitems WHERE orderno=n;
    INSERT IGNORE INTO csvfix_20261026_stockmoves SELECT * FROM stockmoves
      WHERE (type=750 AND transno=n) OR (n=9996 AND stkmoveno=811519);
    INSERT IGNORE INTO csvfix_20261026_ogpcsvref SELECT * FROM ogpcsvref WHERE csv=CAST(n AS CHAR);
    INSERT IGNORE INTO csvfix_20261026_debtortrans SELECT * FROM debtortrans WHERE type=750 AND transno=n;
    INSERT IGNORE INTO csvfix_20261026_stockissuance
      SELECT si.* FROM stockissuance si
      WHERE si.salesperson IN (SELECT salesman FROM shopsale WHERE orderno=n)
        AND si.stockid IN (
          SELECT stockid FROM shopsalesitems WHERE orderno=n
          UNION SELECT stockid FROM ogpcsvref WHERE csv=CAST(n AS CHAR)
          UNION SELECT stockid FROM stockmoves WHERE type=750 AND transno=n
        );
    INSERT INTO csvfix_20261026_snapshots(voucher,database_operator) VALUES(n,USER());
END$$

-- Lock the header and its one active accounting posting. No accounting write.
CREATE PROCEDURE csvfix_posting_20261026(
    IN n INT, IN expected_total DOUBLE, OUT owner VARCHAR(50),
    OUT posting_id INT, OUT allocated DOUBLE, OUT settlement INT)
SQL SECURITY INVOKER
BEGIN
    DECLARE k INT; DECLARE header_id INT; DECLARE channel VARCHAR(3);
    DECLARE d DOUBLE; DECLARE fixed_d DOUBLE; DECLARE bill DOUBLE; DECLARE amount DOUBLE;
    SELECT COUNT(*) INTO k FROM information_schema.TABLES
      WHERE TABLE_SCHEMA=DATABASE()
        AND TABLE_NAME IN ('shopsale','shopsalelines','shopsalesitems','stockmoves',
                           'stockissuance','ogpcsvref','debtortrans') AND ENGINE='InnoDB';
    CALL csvfix_assert_20261026(k=7,'All seven operational tables must use InnoDB; review storage engines first');
    SELECT COUNT(*),MAX(id) INTO k,header_id FROM shopsale WHERE orderno=n;
    CALL csvfix_assert_20261026(k=1,'Voucher not found or duplicate voucher headers; no repair performed');
    SELECT salesman,payment,discount,discountPKR INTO owner,channel,d,fixed_d
      FROM shopsale WHERE id=header_id FOR UPDATE;
    CALL csvfix_assert_20261026(channel='csv','The requested voucher is not a CSV');
    SELECT SUM(quantity*price)*(1-d/100)-fixed_d INTO bill FROM shopsalelines WHERE orderno=n;
    CALL csvfix_assert_20261026(ABS(bill-expected_total)<0.00001,'Customer bill total changed; review current voucher before repair');
    SELECT COUNT(*),MAX(id) INTO k,posting_id FROM debtortrans
      WHERE type=750 AND transno=n AND reversed=0;
    CALL csvfix_assert_20261026(k=1,'Expected exactly one active type-750 posting; no repair performed');
    SELECT ovamount,alloc,settled INTO amount,allocated,settlement
      FROM debtortrans WHERE id=posting_id FOR UPDATE;
    CALL csvfix_assert_20261026(ABS(amount-expected_total)<0.00001,'Accounting amount differs from the expected bill total');
END$$

CREATE PROCEDURE csvfix_verify_posting_20261026(
    IN n INT,IN posting_id INT,IN expected_total DOUBLE,IN allocated DOUBLE,IN settlement INT)
SQL SECURITY INVOKER
BEGIN
    DECLARE k INT;
    SELECT COUNT(*) INTO k FROM debtortrans
      WHERE type=750 AND transno=n AND reversed=0;
    CALL csvfix_assert_20261026(k=1,'Unexpected accounting posting count; transaction rolled back');
    SELECT COUNT(*) INTO k FROM debtortrans WHERE id=posting_id AND type=750 AND transno=n
      AND reversed=0 AND ABS(ovamount-expected_total)<0.00001
      AND ABS(alloc-allocated)<0.00001 AND settled=settlement;
    CALL csvfix_assert_20261026(k=1,'Accounting or settlement state changed; transaction rolled back');
END$$

CREATE PROCEDURE csvfix_9992_20261026(IN apply_changes BOOLEAN)
SQL SECURITY INVOKER
repair_block: BEGIN
    DECLARE owner VARCHAR(50); DECLARE posting_id INT; DECLARE allocated DOUBLE;
    DECLARE settlement INT; DECLARE k INT; DECLARE x DOUBLE;
    DECLARE item_id INT; DECLARE line_qty DOUBLE; DECLARE line_price DOUBLE;
    DECLARE unit_name VARCHAR(10); DECLARE code VARCHAR(40); DECLARE component_qty DOUBLE;
    DECLARE component_rate DOUBLE; DECLARE component_discount DOUBLE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
    START TRANSACTION;
    CALL csvfix_posting_20261026(9992,28000,owner,posting_id,allocated,settlement);
    CALL csvfix_assert_20261026(owner='Mazhar Iqbal','CSV 9992 salesperson changed; review current state');
    SELECT COUNT(*) INTO k FROM shopsalelines WHERE orderno=9992;
    CALL csvfix_assert_20261026(k=1,'CSV 9992 no longer has its expected single customer line');
    SELECT quantity,price,uom INTO line_qty,line_price,unit_name
      FROM shopsalelines WHERE id=25948 AND orderno=9992 FOR UPDATE;
    CALL csvfix_assert_20261026(line_qty=4 AND line_price=7000 AND unit_name='each','CSV 9992 fuse line changed');
    SELECT COUNT(*),SUM(qty) INTO k,x FROM stockmoves
      WHERE type=750 AND transno=9992 AND stockid='3802FUSE170L4961';
    CALL csvfix_assert_20261026(k=1 AND ABS(x-4)<0.00001,'CSV 9992 must already have one stock posting for four fuses');
    SELECT COUNT(*) INTO k FROM stockmaster WHERE stockid='3802FUSE170L4961'
      AND mnfpno='170L4961' AND units='each';
    CALL csvfix_assert_20261026(k=1,'CSV 9992 original fuse code is missing or has different units');
    SELECT COUNT(*),MAX(id) INTO k,item_id FROM shopsalesitems WHERE orderno=9992 AND lineno=25948;
    CALL csvfix_assert_20261026(k<=1,'CSV 9992 has multiple components; manual reconciliation needed');
    IF k=1 THEN
      SELECT stockid,quantity,rate,discountpercent INTO code,component_qty,component_rate,component_discount
        FROM shopsalesitems WHERE id=item_id FOR UPDATE;
      CALL csvfix_assert_20261026(code='3802FUSE170L4961' AND component_qty=1
        AND ABS(component_rate*(1-component_discount/100)-7000)<0.00001,
        'CSV 9992 existing component differs; no replacement was made');
    END IF;
    SELECT 9992 AS voucher,IF(apply_changes,'APPLY','PREVIEW') AS mode,
      '3802FUSE170L4961' AS stockid,1 AS component_per_each,4 AS total_fuses,
      28000 AS bill_total,'Restore attachment/completed flag; retain all original stock/accounting postings' AS plan;
    IF NOT COALESCE(apply_changes,FALSE) THEN ROLLBACK; LEAVE repair_block; END IF;
    CALL csvfix_snapshot_20261026(9992);
    IF k=0 THEN
      INSERT INTO shopsalesitems(orderno,lineno,stockid,quantity,rate,discountpercent)
        VALUES(9992,25948,'3802FUSE170L4961',1,7000,0);
    ELSE
      UPDATE shopsalesitems SET rate=7000,discountpercent=0 WHERE id=item_id;
    END IF;
    UPDATE shopsale SET complete=1 WHERE orderno=9992;
    CALL csvfix_verify_posting_20261026(9992,posting_id,28000,allocated,settlement);
    INSERT INTO csvfix_20261026_log(voucher,action_name,database_operator,details)
      VALUES(9992,'RESTORE_ATTACHMENT',USER(),'Fuse component and completion reconciled. No stock/accounting repost.');
    COMMIT;
END$$

CREATE PROCEDURE csvfix_9996_20261026(IN apply_changes BOOLEAN)
SQL SECURITY INVOKER
repair_block: BEGIN
    DECLARE owner VARCHAR(50); DECLARE posting_id INT; DECLARE allocated DOUBLE;
    DECLARE settlement INT; DECLARE k INT; DECLARE item_id INT; DECLARE cart_id INT;
    DECLARE line_qty DOUBLE; DECLARE line_price DOUBLE; DECLARE unit_name VARCHAR(10);
    DECLARE component_qty DOUBLE; DECLARE component_rate DOUBLE; DECLARE component_discount DOUBLE;
    DECLARE code VARCHAR(40); DECLARE dispatched DOUBLE; DECLARE delivered DOUBLE;
    DECLARE adjustment DOUBLE; DECLARE held DOUBLE; DECLARE cart_delivered DOUBLE;
    DECLARE allocation_balance DOUBLE; DECLARE movement_qty DOUBLE;
    DECLARE location_code VARCHAR(10); DECLARE current_period INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
    START TRANSACTION;
    CALL csvfix_posting_20261026(9996,2400,owner,posting_id,allocated,settlement);
    CALL csvfix_assert_20261026(owner='Jalal Nasir','CSV 9996 salesperson changed');
    SELECT quantity,price,uom INTO line_qty,line_price,unit_name
      FROM shopsalelines WHERE id=25957 AND orderno=9996 FOR UPDATE;
    CALL csvfix_assert_20261026(line_qty=24 AND line_price=100 AND unit_name='feet','CSV 9996 customer line changed');
    SELECT COUNT(*),MAX(id) INTO k,item_id FROM shopsalesitems WHERE orderno=9996 AND lineno=25957;
    CALL csvfix_assert_20261026(k=1,'CSV 9996 must have exactly one pipe component');
    SELECT stockid,quantity,rate,discountpercent INTO code,component_qty,component_rate,component_discount
      FROM shopsalesitems WHERE id=item_id FOR UPDATE;
    CALL csvfix_assert_20261026(code='3111CONPIPGI-Flexible-Pipe-1/2-Inch','CSV 9996 has a different stock code');
    SELECT COUNT(*) INTO k FROM stockmaster WHERE stockid=code AND units IN ('meters','metres','m');
    CALL csvfix_assert_20261026(k=1,'CSV 9996 stock catalogue no longer uses metres');
    CALL csvfix_assert_20261026(ABS(component_qty-0.03045)<0.00000001 OR ABS(component_qty*24-7.31)<0.00001,
      'CSV 9996 component quantity is neither the known old nor corrected value');
    CALL csvfix_assert_20261026(ABS(component_qty*component_rate*(1-component_discount/100)*24-2400)<0.0001,
      'CSV 9996 component selling value changed');
    SELECT COUNT(*),SUM(quantity) INTO k,dispatched FROM posdispatchitems
      WHERE dispatchid=274539 AND stockid=code;
    CALL csvfix_assert_20261026(k=1 AND ABS(dispatched-7.31)<0.00001,'Dispatch 274539 no longer records the expected 7.31 metres');
    SELECT COUNT(*) INTO k FROM ogpcsvref WHERE csv='9996' AND stockid=code
      AND salesman=owner AND dispatchid='274539';
    CALL csvfix_assert_20261026(k>=1,'CSV 9996 original dispatch link is missing');
    SELECT COUNT(*) INTO k FROM ogpcsvref WHERE csv='9996' AND stockid=code
      AND salesman=owner AND NULLIF(dispatchid,'') IS NOT NULL AND dispatchid<>'274539';
    CALL csvfix_assert_20261026(k=0,'CSV 9996 has another pipe dispatch; review its allocation');
    SELECT stockid,qty,loccode INTO code,movement_qty,location_code FROM stockmoves
      WHERE stkmoveno=811519 AND type=511 AND transno=274539 FOR UPDATE;
    CALL csvfix_assert_20261026(code='3111CONPIPGI-Flexible-Pipe-1/2-Inch'
      AND (ABS(movement_qty-7)<0.00001 OR ABS(movement_qty-7.31)<0.00001),'Original outward movement differs');
    SELECT COUNT(*) INTO k FROM stockmoves WHERE stkmoveno=812083 AND type=750 AND transno=9996
      AND stockid=code AND (ABS(qty-0.7308)<0.00001 OR ABS(qty-7.31)<0.00001);
    CALL csvfix_assert_20261026(k=1,'CSV 9996 original delivery movement changed or was removed');
    SELECT COUNT(*),SUM(qty) INTO k,delivered FROM stockmoves WHERE type=750 AND transno=9996 AND stockid=code;
    CALL csvfix_assert_20261026((k=1 AND (ABS(delivered-0.7308)<0.00001 OR ABS(delivered-7.31)<0.00001))
       OR (k=2 AND ABS(delivered-7.31)<0.00001),'CSV 9996 stock delivery is not a known repair state');
    IF k=2 THEN
      SELECT COUNT(*) INTO k FROM stockmoves WHERE type=750 AND transno=9996 AND stockid=code
        AND reference='CSV9996 repair 20261026';
      CALL csvfix_assert_20261026(k=1,'CSV 9996 has an unrecognized second stock movement');
    END IF;
    SELECT COUNT(DISTINCT loccode) INTO k FROM stockmoves
      WHERE type=750 AND transno=9996 AND stockid=code AND loccode<>location_code;
    CALL csvfix_assert_20261026(k=0,'CSV 9996 existing stock location differs from its outward dispatch');
    SELECT stockissuanceindex,issued,dc INTO cart_id,held,cart_delivered FROM stockissuance
      WHERE salesperson=owner AND stockid=code FOR UPDATE;
    CALL csvfix_assert_20261026(cart_id IS NOT NULL,'CSV 9996 salesperson cart row is missing');
    CALL csvfix_assert_20261026(cart_delivered+0.00001>=delivered,'CSV 9996 delivered cart balance was reduced; review returns first');
    SELECT SUM(COALESCE(quantity,0)) INTO allocation_balance FROM ogpcsvref
      WHERE csv='9996' AND stockid=code AND salesman=owner FOR UPDATE;
    SET adjustment=7.31-delivered;
    CALL csvfix_assert_20261026(adjustment>=-0.00001 AND held+0.00001>=adjustment,
      'CSV 9996 does not have sufficient current cart stock for the correction');
    IF adjustment>0.00001 THEN
      CALL csvfix_assert_20261026(ABS(allocation_balance-6)<0.00001 OR ABS(allocation_balance-adjustment)<0.00001,
        'CSV 9996 remaining allocation differs from the known rounding error');
      SELECT MIN(periodno) INTO current_period FROM periods WHERE lastdate_in_period=LAST_DAY(CURDATE());
      CALL csvfix_assert_20261026(current_period IS NOT NULL,'The current accounting period is missing; create it through ERP setup');
    ELSE
      CALL csvfix_assert_20261026(ABS(allocation_balance)<0.00001 OR ABS(allocation_balance-6)<0.00001,
        'CSV 9996 already-delivered allocation has an unexpected balance');
    END IF;
    SELECT 9996 AS voucher,IF(apply_changes,'APPLY','PREVIEW') AS mode,
      7.31E0/24 AS component_metres_per_displayed_foot,2400E0/7.31E0 AS saved_rate_per_metre,
      delivered AS already_delivered_metres,adjustment AS extra_stock_to_deduct,
      2400 AS unchanged_bill_total;
    IF NOT COALESCE(apply_changes,FALSE) THEN ROLLBACK; LEAVE repair_block; END IF;
    CALL csvfix_snapshot_20261026(9996);
    UPDATE shopsalesitems SET quantity=7.31E0/24,rate=2400E0/7.31E0,discountpercent=0 WHERE id=item_id;
    UPDATE stockmoves SET qty=7.31 WHERE stkmoveno=811519;
    IF adjustment>0.00001 THEN
      UPDATE stockissuance SET issued=GREATEST(0,issued-adjustment),dc=dc+adjustment WHERE stockissuanceindex=cart_id;
      -- Append a dated correction; retain the original historical delivery row.
      INSERT INTO stockmoves(stockid,type,transno,loccode,trandate,reference,qty,prd,newqoh)
        VALUES(code,750,9996,location_code,CURDATE(),'CSV9996 repair 20261026',adjustment,current_period,GREATEST(0,held-adjustment));
    END IF;
    UPDATE ogpcsvref SET quantity=0 WHERE csv='9996' AND stockid=code AND salesman=owner;
    SELECT SUM(qty) INTO delivered FROM stockmoves WHERE type=750 AND transno=9996 AND stockid=code;
    CALL csvfix_assert_20261026(ABS(delivered-7.31)<0.00001,'CSV 9996 stock total did not reconcile; rolled back');
    CALL csvfix_verify_posting_20261026(9996,posting_id,2400,allocated,settlement);
    INSERT INTO csvfix_20261026_log(voucher,action_name,database_operator,details)
      VALUES(9996,'RECONCILE_PIPE_QUANTITY',USER(),CONCAT('Recorded dispatch 7.31 m. Added stock correction: ',adjustment,' m.'));
    COMMIT;
END$$

CREATE PROCEDURE csvfix_10139_20261026(IN apply_changes BOOLEAN,IN confirmed_stockid VARCHAR(40))
SQL SECURITY INVOKER
repair_block: BEGIN
    DECLARE owner VARCHAR(50); DECLARE posting_id INT; DECLARE allocated DOUBLE;
    DECLARE settlement INT; DECLARE k INT; DECLARE line_id INT; DECLARE item_id INT;
    DECLARE cart_id INT; DECLARE current_period INT; DECLARE held DOUBLE; DECLARE cart_delivered DOUBLE;
    DECLARE available DOUBLE; DECLARE delivered DOUBLE; DECLARE dispatch_quantity DOUBLE;
    DECLARE attached_qty DOUBLE; DECLARE attached_rate DOUBLE; DECLARE attached_discount DOUBLE;
    DECLARE attached_code VARCHAR(40); DECLARE location_code VARCHAR(10); DECLARE movement_count INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
    START TRANSACTION;
    CALL csvfix_posting_20261026(10139,21830,owner,posting_id,allocated,settlement);
    CALL csvfix_assert_20261026(owner='Jalal Nasir','CSV 10139 salesperson changed');
    SELECT COUNT(*),MAX(id) INTO k,line_id FROM shopsalelines WHERE orderno=10139
      AND description LIKE '%AC SPD%40%2%Himel%' AND quantity=1 AND price=6000 AND uom='each';
    CALL csvfix_assert_20261026(k=1,'CSV 10139 SPD line is missing or no longer matches the reviewed bill');
    SELECT id INTO line_id FROM shopsalelines WHERE id=line_id AND orderno=10139 FOR UPDATE;
    IF confirmed_stockid IS NULL OR confirmed_stockid='' THEN
      CALL csvfix_assert_20261026(NOT COALESCE(apply_changes,FALSE),
        'Confirm the SPD stock code from its dispatch and pass it explicitly; nothing was changed');
      SELECT r.id,r.stockid,m.mnfpno,m.description,r.quantity AS remaining_allocation,
        r.dispatchid,p.quantity AS original_dispatch_quantity
        FROM ogpcsvref r LEFT JOIN stockmaster m ON m.stockid=r.stockid
        LEFT JOIN posdispatchitems p ON p.dispatchid=r.dispatchid AND p.stockid=r.stockid
        WHERE r.csv='10139' AND r.salesman=owner;
      SELECT stkmoveno,stockid,qty,loccode,trandate,reference FROM stockmoves WHERE type=750 AND transno=10139;
      SELECT 'PREVIEW: confirm the supplied AC SPD code, then preview/apply again with that code' AS next_step;
      ROLLBACK; LEAVE repair_block;
    END IF;
    CALL csvfix_assert_20261026(confirmed_stockid IN ('9381SURGPRHDY3402275','9381SURGPRHDY3N402275'),
      'The supplied code is not one of the reviewed matching AC SPD models');
    SELECT COUNT(*) INTO k FROM stockmaster WHERE stockid=confirmed_stockid AND units='each';
    CALL csvfix_assert_20261026(k=1,'Confirmed SPD stock code is not in the each-unit catalogue');
    SELECT COUNT(*),MAX(id) INTO k,item_id FROM shopsalesitems WHERE orderno=10139 AND lineno=line_id;
    CALL csvfix_assert_20261026(k<=1,'CSV 10139 SPD line already has multiple components');
    IF k=1 THEN
      SELECT stockid,quantity,rate,discountpercent INTO attached_code,attached_qty,attached_rate,attached_discount
        FROM shopsalesitems WHERE id=item_id FOR UPDATE;
      CALL csvfix_assert_20261026(attached_code=confirmed_stockid AND attached_qty=1
        AND ABS(attached_rate*(1-attached_discount/100)-6000)<0.00001,
        'CSV 10139 already has a different component; no replacement was made');
    END IF;
    SELECT COUNT(*),COALESCE(SUM(qty),0) INTO movement_count,delivered FROM stockmoves
      WHERE type=750 AND transno=10139 AND stockid=confirmed_stockid;
    CALL csvfix_assert_20261026(movement_count=0 OR (movement_count=1 AND ABS(delivered-1)<0.00001),
      'CSV 10139 SPD has an unexpected existing stock posting');
    IF movement_count=0 THEN
      SELECT stockissuanceindex,issued,dc INTO cart_id,held,cart_delivered FROM stockissuance
        WHERE salesperson=owner AND stockid=confirmed_stockid FOR UPDATE;
      CALL csvfix_assert_20261026(cart_id IS NOT NULL AND held>=1,'Confirmed SPD is not available in the salesperson cart');
      SELECT COALESCE(SUM(quantity),0) INTO available FROM ogpcsvref
        WHERE csv='10139' AND salesman=owner AND stockid=confirmed_stockid FOR UPDATE;
      CALL csvfix_assert_20261026(available>=1,'Confirmed SPD is not allocated to CSV 10139');
      SELECT COALESCE(SUM(di.quantity),0) INTO dispatch_quantity FROM posdispatchitems di
        JOIN posdispatch h ON h.dispatchid=di.dispatchid
        WHERE di.stockid=confirmed_stockid AND h.deliveredto=owner
          AND EXISTS (SELECT 1 FROM ogpcsvref r WHERE r.csv='10139' AND r.salesman=owner
                      AND r.stockid=confirmed_stockid AND r.dispatchid=CAST(di.dispatchid AS CHAR));
      CALL csvfix_assert_20261026(dispatch_quantity>=1,'No original SPD dispatch supports this voucher allocation');
      SELECT COUNT(DISTINCT loccode),MAX(loccode) INTO k,location_code FROM stockmoves
        WHERE type=750 AND transno=10139;
      CALL csvfix_assert_20261026(k=1 AND location_code<>'','CSV 10139 original stock location is ambiguous');
      SELECT MIN(periodno) INTO current_period FROM periods WHERE lastdate_in_period=LAST_DAY(CURDATE());
      CALL csvfix_assert_20261026(current_period IS NOT NULL,'The current accounting period is missing; create it through ERP setup');
    END IF;
    SELECT 10139 AS voucher,IF(apply_changes,'APPLY','PREVIEW') AS mode,
      confirmed_stockid AS stockid,1 AS total_spd_quantity,21830 AS unchanged_bill_total,
      IF(movement_count=0,'Add missing stock deduction ONCE and attach the SPD','Restore attachment only; stock already deducted') AS plan;
    IF NOT COALESCE(apply_changes,FALSE) THEN ROLLBACK; LEAVE repair_block; END IF;
    CALL csvfix_snapshot_20261026(10139);
    IF item_id IS NULL THEN
      INSERT INTO shopsalesitems(orderno,lineno,stockid,quantity,rate,discountpercent)
        VALUES(10139,line_id,confirmed_stockid,1,6000,0);
    ELSE
      UPDATE shopsalesitems SET rate=6000,discountpercent=0 WHERE id=item_id;
    END IF;
    IF movement_count=0 THEN
      UPDATE stockissuance SET issued=issued-1,dc=dc+1 WHERE stockissuanceindex=cart_id;
      UPDATE ogpcsvref SET quantity=0 WHERE csv='10139' AND salesman=owner AND stockid=confirmed_stockid;
      INSERT INTO ogpcsvref(stockid,csv,salesman,quantity) VALUES(confirmed_stockid,'10139',owner,available-1);
      INSERT INTO stockmoves(stockid,type,transno,loccode,trandate,reference,qty,prd,newqoh)
        VALUES(confirmed_stockid,750,10139,location_code,CURDATE(),'CSV10139 repair 20261026',1,current_period,held-1);
    END IF;
    SELECT SUM(qty) INTO delivered FROM stockmoves WHERE type=750 AND transno=10139 AND stockid=confirmed_stockid;
    CALL csvfix_assert_20261026(ABS(delivered-1)<0.00001,'CSV 10139 SPD stock total did not reconcile; rolled back');
    UPDATE shopsale SET complete=1 WHERE orderno=10139;
    CALL csvfix_verify_posting_20261026(10139,posting_id,21830,allocated,settlement);
    INSERT INTO csvfix_20261026_log(voucher,action_name,database_operator,details)
      VALUES(10139,'RESTORE_SPD_ATTACHMENT',USER(),CONCAT('Confirmed SKU ',confirmed_stockid,'. Existing stock postings: ',movement_count));
    COMMIT;
END$$
DELIMITER ;

-- PREVIEW separately after installation. Check result sets and SIGNAL errors.
-- Prefer the mysql client, or phpMyAdmin Database > Routines > Execute.
-- Older phpMyAdmin SQL/import screens may report #2014 after a CALL has run.
-- CALL csvfix_9992_20261026(0);
-- CALL csvfix_9996_20261026(0);
-- CALL csvfix_10139_20261026(0,NULL);

-- APPLY individually after reviewing previews and taking a database backup:
-- CALL csvfix_9992_20261026(1);
-- CALL csvfix_9996_20261026(1);
-- First confirm the actual SPD code using CSV 10139's dispatch result above.
-- The old matching code is a candidate, not an assumption made by this script:
-- CALL csvfix_10139_20261026(0,'9381SURGPRHDY3402275');
-- CALL csvfix_10139_20261026(1,'9381SURGPRHDY3402275');

-- These procedures never INSERT/UPDATE debtortrans, gltrans, or payments.
-- Changing rate and discount together preserves the effective selling price.
-- 7.31 m is the RECORDED dispatch, rounded to two decimal places; it is not an
-- assertion that 24 international feet equals exactly 7.31 metres.
