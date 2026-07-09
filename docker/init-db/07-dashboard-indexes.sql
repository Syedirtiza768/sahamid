-- ---------------------------------------------------------------------------
-- Performance indexes for the Sales Main Dashboard (v2/salesMainDashboard.php)
--
-- The dashboard's metrics join invoice / invoicedetails / invoiceoptions /
-- custbranch / debtortrans / salesorders / shopsale / dcs and filter by date.
-- Several of these join/filter columns are UNINDEXED in the base schema
-- (e.g. invoice.invoicesdate, invoice.invoiceno, invoicedetails.invoiceno),
-- forcing full table scans on large tables — the dominant cause of slow loads.
--
-- SAFE TO RUN ON A LIVE DATABASE: adding a secondary index is non-destructive.
-- This script is idempotent — it skips any index that already exists (by name),
-- so it can be re-run safely. On a large table each ADD INDEX can take a while
-- and briefly increase load; prefer a low-traffic window.
--
-- To run against the running container:
--   docker exec -i sahamid-db sh -c 'mysql -uroot -prootpassword sahamid' < docker/init-db/07-dashboard-indexes.sql
-- (It also runs automatically on a fresh `docker compose up` DB init.)
-- ---------------------------------------------------------------------------

-- Production servers often run a strict sql_mode (NO_ZERO_DATE / STRICT_TRANS_TABLES;
-- the MySQL 5.7/8.0 defaults). ADD INDEX revalidates the table, and legacy
-- '0000-00-00' column defaults (e.g. invoicedetails.actualdispatchdate) then raise
-- "#1067 Invalid default value". Relax sql_mode for THIS session so the ALTERs succeed
-- without altering any column. A stored routine captures the sql_mode in force when it
-- is created and runs under it, so this must be set before CREATE PROCEDURE. Restored
-- at the very end; it only affects this connection.
SET @dash_old_sql_mode = @@SESSION.sql_mode;
SET SESSION sql_mode = '';

DELIMITER //
DROP PROCEDURE IF EXISTS _dash_add_index //
CREATE PROCEDURE _dash_add_index(IN tbl VARCHAR(64), IN idx VARCHAR(64), IN cols VARCHAR(255))
BEGIN
	SET SESSION sql_mode = '';   -- belt-and-suspenders: keep the ALTER permissive
	IF EXISTS (SELECT 1 FROM information_schema.tables
	           WHERE table_schema = DATABASE() AND table_name = tbl)
	AND NOT EXISTS (SELECT 1 FROM information_schema.statistics
	                WHERE table_schema = DATABASE() AND table_name = tbl AND index_name = idx) THEN
		SET @s = CONCAT('ALTER TABLE `', tbl, '` ADD INDEX `', idx, '` (', cols, ')');
		PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
	END IF;
END //
DELIMITER ;

-- invoice: date filter + join keys
CALL _dash_add_index('invoice',          'idx_dash_invoicesdate', '`invoicesdate`');
CALL _dash_add_index('invoice',          'idx_dash_invoiceno',    '`invoiceno`');
CALL _dash_add_index('invoice',          'idx_dash_branchcode',   '`branchcode`');

-- invoice line tables: join back to invoice by (invoiceno, line, option)
CALL _dash_add_index('invoicedetails',   'idx_dash_inv_line',     '`invoiceno`,`invoicelineno`,`invoiceoptionno`');
CALL _dash_add_index('invoicedetails',   'idx_dash_stkcode',      '`stkcode`');
CALL _dash_add_index('invoiceoptions',   'idx_dash_inv_line',     '`invoiceno`,`invoicelineno`,`invoiceoptionno`');

-- customer branch: join keys
CALL _dash_add_index('custbranch',       'idx_dash_branchcode',   '`branchcode`');
CALL _dash_add_index('custbranch',       'idx_dash_debtorno',     '`debtorno`');
CALL _dash_add_index('custbranch',       'idx_dash_salesman',     '`salesman`');

-- debtor transactions: outstanding + shop-sale lookups
CALL _dash_add_index('debtortrans',      'idx_dash_transno',      '`transno`');
CALL _dash_add_index('debtortrans',      'idx_dash_type_flags',   '`type`,`settled`,`reversed`');
CALL _dash_add_index('debtortrans',      'idx_dash_debtorno',     '`debtorno`');

-- sales orders (PO / OC values)
CALL _dash_add_index('salesorders',      'idx_dash_orddate',      '`orddate`');
CALL _dash_add_index('salesorders',      'idx_dash_flags',        '`quotation`,`poplaced`');
CALL _dash_add_index('salesorders',      'idx_dash_salesperson',  '`salesperson`');
CALL _dash_add_index('salesorderdetails','idx_dash_orderno',      '`orderno`');

-- shop sales (CRV / CSV / Shop DC)
CALL _dash_add_index('shopsale',         'idx_dash_orderno',      '`orderno`');
CALL _dash_add_index('shopsale',         'idx_dash_orddate',      '`orddate`');
CALL _dash_add_index('shopsale',         'idx_dash_branchcode',   '`branchcode`');

-- delivery challans (Pending DC)
CALL _dash_add_index('dcs',              'idx_dash_orddate',      '`orddate`');
CALL _dash_add_index('dcs',              'idx_dash_salescaseref', '`salescaseref`');
CALL _dash_add_index('dcdetails',        'idx_dash_orderno',      '`orderno`');
CALL _dash_add_index('dcoptions',        'idx_dash_orderno',      '`orderno`,`lineno`');

-- stock issuance (Cart Value)
CALL _dash_add_index('stockissuance',    'idx_dash_salesperson',  '`salesperson`');

DROP PROCEDURE IF EXISTS _dash_add_index;

-- restore the caller's original sql_mode
SET SESSION sql_mode = @dash_old_sql_mode;
