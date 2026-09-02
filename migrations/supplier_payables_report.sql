-- Register the Supplier & Payables report with the existing AP report security level.
-- Run once per company database after deploying the PHP files.
INSERT INTO scripts (script, pagesecurity, description)
VALUES ('SupplierPayablesReport.php', 2, 'Executive supplier relationship and accounts payable reporting')
ON DUPLICATE KEY UPDATE
    pagesecurity = VALUES(pagesecurity),
    description = VALUES(description);

-- Targeted indexes for the report’s payable and historical-allocation paths.
-- Run once against each company database during a low-traffic window.
SET @supplier_payables_report_old_sql_mode = @@SESSION.sql_mode;
SET SESSION sql_mode = '';

DELIMITER //
DROP PROCEDURE IF EXISTS _supplier_payables_report_add_indexes //
CREATE PROCEDURE _supplier_payables_report_add_indexes()
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = 'supptrans'
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'supptrans'
          AND index_name = 'idx_supplier_payables_report_type_date_id'
    ) THEN
        ALTER TABLE `supptrans`
            ADD INDEX `idx_supplier_payables_report_type_date_id`
                (`type`, `trandate`, `id`);
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = 'suppallocs'
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'suppallocs'
          AND index_name = 'idx_supplier_payables_report_date_alloc'
    ) THEN
        ALTER TABLE `suppallocs`
            ADD INDEX `idx_supplier_payables_report_date_alloc`
                (`datealloc`, `transid_allocfrom`, `transid_allocto`);
    END IF;
END //
DELIMITER ;

CALL _supplier_payables_report_add_indexes();
DROP PROCEDURE IF EXISTS _supplier_payables_report_add_indexes;

SET SESSION sql_mode = @supplier_payables_report_old_sql_mode;
