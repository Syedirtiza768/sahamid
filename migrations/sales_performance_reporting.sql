-- Sales performance reporting support tables.
-- Additive migration: operational quotation, OC and DC records are unchanged.

CREATE TABLE IF NOT EXISTS sales_performance_schema_migrations (
    version_no INT NOT NULL,
    migration_name VARCHAR(120) NOT NULL,
    applied_at DATETIME NOT NULL,
    PRIMARY KEY (version_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_followups (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    activity_date DATE NOT NULL,
    salesman VARCHAR(100) NOT NULL,
    salescaseref VARCHAR(40) NOT NULL DEFAULT '',
    activity_type VARCHAR(24) NOT NULL,
    outcome VARCHAR(255) NOT NULL DEFAULT '',
    next_action_date DATE DEFAULT NULL,
    notes TEXT NOT NULL,
    created_by VARCHAR(50) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_sp_followup_date_salesman (activity_date, salesman),
    KEY idx_sp_followup_case (salescaseref),
    KEY idx_sp_followup_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_adjustments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    metric_key VARCHAR(32) NOT NULL,
    source_key VARCHAR(100) NOT NULL,
    excluded_amount DECIMAL(24,6) NOT NULL,
    reason VARCHAR(500) NOT NULL,
    status VARCHAR(16) NOT NULL DEFAULT 'pending',
    requested_by VARCHAR(50) NOT NULL DEFAULT '',
    requested_at DATETIME NOT NULL,
    reviewed_by VARCHAR(50) DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    review_note VARCHAR(500) DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_sp_adjustment_source (metric_key, source_key, status),
    KEY idx_sp_adjustment_status (status, requested_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_settings (
    setting_key VARCHAR(64) NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO sales_performance_settings (setting_key, setting_value)
SELECT 'followup_start_date', DATE_FORMAT(CURDATE(), '%Y-%m-%d') FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM sales_performance_settings WHERE setting_key = 'followup_start_date'
);

-- Seed permission names so administrators can grant the controls through the
-- existing user permissions screen. Grants are intentionally left to admins.
INSERT INTO permissions (name, slug, description)
SELECT 'Manage Sales Performance Adjustments', 'sales_performance_adjustments',
       'Request normalized-value exclusions for sales performance reports' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE slug = 'sales_performance_adjustments'
);

INSERT INTO permissions (name, slug, description)
SELECT 'Approve Sales Performance Adjustments', 'sales_performance_adjustments_approve',
       'Approve or reject normalized-value exclusions for sales performance reports' FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE slug = 'sales_performance_adjustments_approve'
);

INSERT INTO sales_performance_schema_migrations (version_no, migration_name, applied_at)
VALUES (1, 'sales_performance_reporting', NOW())
ON DUPLICATE KEY UPDATE migration_name=VALUES(migration_name);
