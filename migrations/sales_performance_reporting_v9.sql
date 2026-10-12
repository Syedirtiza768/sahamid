-- Sales reporting v9: auditable, two-person review for named team population configuration.
-- Apply after sales_performance_reporting.sql and v2 through v8.

CREATE TABLE IF NOT EXISTS sales_performance_scope_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    scope_id INT UNSIGNED NOT NULL,
    scope_key VARCHAR(64) NOT NULL,
    scope_version INT NOT NULL,
    action_type VARCHAR(24) NOT NULL,
    previous_label VARCHAR(120) DEFAULT NULL,
    new_label VARCHAR(120) NOT NULL,
    previous_team_ids_text TEXT DEFAULT NULL,
    new_team_ids_text TEXT NOT NULL,
    previous_include_unassigned TINYINT(1) DEFAULT NULL,
    new_include_unassigned TINYINT(1) NOT NULL,
    previous_validation_status VARCHAR(24) DEFAULT NULL,
    new_validation_status VARCHAR(24) NOT NULL,
    change_note VARCHAR(1000) NOT NULL,
    changed_by VARCHAR(50) NOT NULL,
    changed_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_sp_scope_history_item (scope_key, scope_version, id),
    KEY idx_sp_scope_history_date (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO permissions (name, slug, description)
SELECT 'Approve Named Sales Reporting Scope', 'sales_performance_scopes_approve', 'Independently review and approve versioned named commercial team population mappings' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug='sales_performance_scopes_approve');

INSERT INTO sales_performance_schema_migrations (version_no, migration_name, applied_at)
VALUES (9, 'sales_performance_reporting_v9', NOW())
ON DUPLICATE KEY UPDATE migration_name=VALUES(migration_name);
