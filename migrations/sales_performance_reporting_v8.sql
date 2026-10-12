-- Sales reporting v8: bind daily completeness submissions to signed, server-calculated review snapshots.
-- Apply after sales_performance_reporting.sql and v2 through v7.
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='sales_performance_coverage' AND column_name='review_snapshot_json')=0,
    'ALTER TABLE sales_performance_coverage ADD COLUMN review_snapshot_json MEDIUMTEXT DEFAULT NULL', 'SELECT 1');
PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='sales_performance_coverage_history' AND column_name='previous_review_snapshot_json')=0,
    'ALTER TABLE sales_performance_coverage_history ADD COLUMN previous_review_snapshot_json MEDIUMTEXT DEFAULT NULL', 'SELECT 1');
PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='sales_performance_coverage_history' AND column_name='new_review_snapshot_json')=0,
    'ALTER TABLE sales_performance_coverage_history ADD COLUMN new_review_snapshot_json MEDIUMTEXT DEFAULT NULL', 'SELECT 1');
PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

INSERT INTO sales_performance_schema_migrations (version_no, migration_name, applied_at)
VALUES (8, 'sales_performance_reporting_v8', NOW())
ON DUPLICATE KEY UPDATE migration_name=VALUES(migration_name);
