-- Sales reporting v10: register the report in the ERP Reports page security map.
-- Apply after sales_performance_reporting.sql and v2 through v9.

INSERT INTO scripts (script, pagesecurity, description)
VALUES ('salesPerformanceReport.php', 2, 'Sales Performance reporting by month, week and team')
ON DUPLICATE KEY UPDATE
    pagesecurity = VALUES(pagesecurity),
    description = VALUES(description);

INSERT INTO sales_performance_schema_migrations (version_no, migration_name, applied_at)
VALUES (10, 'sales_performance_reporting_v10', NOW())
ON DUPLICATE KEY UPDATE migration_name=VALUES(migration_name);
