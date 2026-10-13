-- Sales reporting v5: add reasoned PO receipt correction and void audit metadata.
-- Apply after sales_performance_reporting.sql and v2 through v4.
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='sales_performance_po_receipts' AND column_name='correction_reason')=0,
    'ALTER TABLE sales_performance_po_receipts ADD COLUMN correction_reason VARCHAR(500) DEFAULT NULL', 'SELECT 1');
PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='sales_performance_po_receipts' AND column_name='voided_at')=0,
    'ALTER TABLE sales_performance_po_receipts ADD COLUMN voided_at DATETIME DEFAULT NULL', 'SELECT 1');
PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='sales_performance_po_receipts' AND column_name='voided_by')=0,
    'ALTER TABLE sales_performance_po_receipts ADD COLUMN voided_by VARCHAR(50) DEFAULT NULL', 'SELECT 1');
PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='sales_performance_po_receipts' AND column_name='void_reason')=0,
    'ALTER TABLE sales_performance_po_receipts ADD COLUMN void_reason VARCHAR(500) DEFAULT NULL', 'SELECT 1');
PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='sales_performance_po_receipts' AND column_name='void_idempotency_key')=0,
    'ALTER TABLE sales_performance_po_receipts ADD COLUMN void_idempotency_key VARCHAR(80) DEFAULT NULL', 'SELECT 1');
PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=DATABASE() AND table_name='sales_performance_po_receipts' AND index_name='idx_sp_po_receipt_correction')=0,
    'ALTER TABLE sales_performance_po_receipts ADD KEY idx_sp_po_receipt_correction (correction_of_id)', 'SELECT 1');
PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=DATABASE() AND table_name='sales_performance_po_receipts' AND index_name='uq_sp_po_receipt_void_idempotency')=0,
    'ALTER TABLE sales_performance_po_receipts ADD UNIQUE KEY uq_sp_po_receipt_void_idempotency (void_idempotency_key)', 'SELECT 1');
PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

INSERT INTO sales_performance_schema_migrations (version_no, migration_name, applied_at)
VALUES (5, 'sales_performance_reporting_v5', NOW())
ON DUPLICATE KEY UPDATE migration_name=VALUES(migration_name);
