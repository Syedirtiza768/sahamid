-- Performance index for reports/balance/suppstatement/SupplierStatement.php.
-- Run once against each company database during a low-traffic window.
-- The statement uses supplierno + trandate for both the opening balance and
-- transaction range query, and orders by trandate.

SET @supplier_statement_old_sql_mode = @@SESSION.sql_mode;
SET SESSION sql_mode = '';

DELIMITER //
DROP PROCEDURE IF EXISTS _supplier_statement_add_index //
CREATE PROCEDURE _supplier_statement_add_index()
BEGIN
    IF EXISTS (
        SELECT 1
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
          AND table_name = 'supptrans'
    ) AND NOT EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'supptrans'
          AND index_name = 'idx_supplier_statement_supplier_date'
    ) THEN
        ALTER TABLE `supptrans`
            ADD INDEX `idx_supplier_statement_supplier_date` (`supplierno`, `trandate`);
    END IF;
END //
DELIMITER ;

CALL _supplier_statement_add_index();
DROP PROCEDURE IF EXISTS _supplier_statement_add_index;

SET SESSION sql_mode = @supplier_statement_old_sql_mode;
