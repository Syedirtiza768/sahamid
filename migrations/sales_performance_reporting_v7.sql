-- Sales reporting v7: preserve every reporting-coverage submission change.
-- Apply after sales_performance_reporting.sql and v2 through v6.
CREATE TABLE IF NOT EXISTS sales_performance_coverage_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    coverage_id BIGINT UNSIGNED NOT NULL,
    action_type VARCHAR(16) NOT NULL,
    previous_state VARCHAR(24) DEFAULT NULL,
    new_state VARCHAR(24) NOT NULL,
    previous_note VARCHAR(1000) DEFAULT NULL,
    new_note VARCHAR(1000) NOT NULL,
    previous_evidence_reference VARCHAR(500) DEFAULT NULL,
    new_evidence_reference VARCHAR(500) DEFAULT NULL,
    previous_reporter_user_id VARCHAR(50) DEFAULT NULL,
    new_reporter_user_id VARCHAR(50) NOT NULL,
    previous_business_cutoff DATETIME DEFAULT NULL,
    new_business_cutoff DATETIME NOT NULL,
    previous_submitted_at DATETIME DEFAULT NULL,
    new_submitted_at DATETIME NOT NULL,
    changed_by VARCHAR(50) NOT NULL,
    changed_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_sp_coverage_history_item (coverage_id, id),
    KEY idx_sp_coverage_history_date (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO sales_performance_schema_migrations (version_no, migration_name, applied_at)
VALUES (7, 'sales_performance_reporting_v7', NOW())
ON DUPLICATE KEY UPDATE migration_name=VALUES(migration_name);
