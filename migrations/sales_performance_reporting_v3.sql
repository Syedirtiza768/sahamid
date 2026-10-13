-- Sales reporting v3: index the follow-up correction chain for safe row-locked replacement checks.
-- Apply after sales_performance_reporting.sql and sales_performance_reporting_v2.sql.
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND index_name='idx_sp_followup_correction')=0,
    'ALTER TABLE sales_performance_followups ADD KEY idx_sp_followup_correction (corrected_from_id)', 'SELECT 1');
PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

INSERT INTO sales_performance_schema_migrations (version_no, migration_name, applied_at)
VALUES (3, 'sales_performance_reporting_v3', NOW())
ON DUPLICATE KEY UPDATE migration_name=VALUES(migration_name);
