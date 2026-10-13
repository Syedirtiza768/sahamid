-- Sales reporting v4: retain reasons and index the enquiry correction/void ledger.
-- Apply after sales_performance_reporting.sql, sales_performance_reporting_v2.sql, and sales_performance_reporting_v3.sql.
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='sales_performance_enquiry_events' AND column_name='correction_reason')=0,
    'ALTER TABLE sales_performance_enquiry_events ADD COLUMN correction_reason VARCHAR(500) DEFAULT NULL', 'SELECT 1');
PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='sales_performance_enquiry_events' AND column_name='void_reason')=0,
    'ALTER TABLE sales_performance_enquiry_events ADD COLUMN void_reason VARCHAR(500) DEFAULT NULL', 'SELECT 1');
PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=DATABASE() AND table_name='sales_performance_enquiry_events' AND index_name='idx_sp_enquiry_correction')=0,
    'ALTER TABLE sales_performance_enquiry_events ADD KEY idx_sp_enquiry_correction (corrects_event_id)', 'SELECT 1');
PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=DATABASE() AND table_name='sales_performance_enquiry_events' AND index_name='idx_sp_enquiry_void')=0,
    'ALTER TABLE sales_performance_enquiry_events ADD KEY idx_sp_enquiry_void (voids_event_id)', 'SELECT 1');
PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

INSERT INTO sales_performance_schema_migrations (version_no, migration_name, applied_at)
VALUES (4, 'sales_performance_reporting_v4', NOW())
ON DUPLICATE KEY UPDATE migration_name=VALUES(migration_name);
