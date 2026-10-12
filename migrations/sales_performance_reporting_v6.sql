-- Sales reporting v6: add auditable team/calendar setup, reference review and discrepancy resolution support.
-- Apply after sales_performance_reporting.sql and v2 through v5.
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='sales_performance_reference_observations' AND column_name='value_currency')=0,
    'ALTER TABLE sales_performance_reference_observations ADD COLUMN value_currency VARCHAR(10) DEFAULT NULL AFTER value_basis', 'SELECT 1');
PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

INSERT INTO sales_performance_settings (setting_key, setting_value)
SELECT 'report_calendar_version', 'weekday-mon-sat-v1' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM sales_performance_settings WHERE setting_key='report_calendar_version');

SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema=DATABASE() AND table_name='sales_performance_reference_observations' AND column_name='verification_note')=0,
    'ALTER TABLE sales_performance_reference_observations ADD COLUMN verification_note TEXT DEFAULT NULL AFTER verification_state', 'SELECT 1');
PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

CREATE TABLE IF NOT EXISTS sales_performance_team_membership_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    membership_id BIGINT UNSIGNED NOT NULL,
    action_type VARCHAR(24) NOT NULL,
    salesmancode VARCHAR(20) NOT NULL,
    team_id INT NOT NULL,
    valid_from DATE NOT NULL,
    previous_valid_to DATE DEFAULT NULL,
    new_valid_to DATE DEFAULT NULL,
    reporting_group VARCHAR(64) NOT NULL,
    provenance VARCHAR(120) NOT NULL,
    changed_by VARCHAR(50) NOT NULL,
    changed_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_sp_team_history_item (membership_id, id),
    KEY idx_sp_team_history_date (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_calendar_day_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    calendar_day_id BIGINT UNSIGNED NOT NULL,
    calendar_version VARCHAR(40) NOT NULL,
    business_date DATE NOT NULL,
    previous_workday TINYINT(1) DEFAULT NULL,
    new_workday TINYINT(1) NOT NULL,
    previous_reason VARCHAR(255) DEFAULT NULL,
    new_reason VARCHAR(255) NOT NULL,
    changed_by VARCHAR(50) NOT NULL,
    changed_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_sp_calendar_history_item (calendar_day_id, id),
    KEY idx_sp_calendar_history_date (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_reference_observation_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    observation_id BIGINT UNSIGNED NOT NULL,
    previous_state VARCHAR(24) NOT NULL,
    new_state VARCHAR(24) NOT NULL,
    review_note TEXT NOT NULL,
    changed_by VARCHAR(50) NOT NULL,
    changed_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_sp_reference_history_item (observation_id, id),
    KEY idx_sp_reference_history_date (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_reconciliation_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    reconciliation_id BIGINT UNSIGNED NOT NULL,
    action_type VARCHAR(16) NOT NULL,
    classification VARCHAR(40) NOT NULL,
    status VARCHAR(20) NOT NULL,
    owner_user_id VARCHAR(50) DEFAULT NULL,
    resolution_note TEXT,
    changed_by VARCHAR(50) NOT NULL,
    changed_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_sp_recon_history_item (reconciliation_id, id),
    KEY idx_sp_recon_history_date (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO sales_performance_schema_migrations (version_no, migration_name, applied_at)
VALUES (6, 'sales_performance_reporting_v6', NOW())
ON DUPLICATE KEY UPDATE migration_name=VALUES(migration_name);
