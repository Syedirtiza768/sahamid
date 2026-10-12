-- Sales performance reporting: additive audit/capture and reproducibility layer.
-- Supports MariaDB 10.3 / MySQL 5.7-compatible SQL. Apply after a reviewed preflight.
-- Run this script with a client that stops on the first SQL error. DDL is not transactional.
-- No operational source rows are backfilled and no permissions are granted here.

CREATE TABLE IF NOT EXISTS sales_performance_schema_migrations (
    version_no INT NOT NULL,
    migration_name VARCHAR(120) NOT NULL,
    applied_at DATETIME NOT NULL,
    PRIMARY KEY (version_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Repeatable foundation tables. Existing v1 follow-ups/adjustments are extended below.
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
    actual_activity_at DATETIME DEFAULT NULL,
    quotation_orderno INT DEFAULT NULL,
    credited_salesman_code VARCHAR(20) DEFAULT NULL,
    actor_user_id VARCHAR(50) DEFAULT NULL,
    entered_by_user_id VARCHAR(50) DEFAULT NULL,
    event_team_id INT DEFAULT NULL,
    attribution_basis VARCHAR(50) NOT NULL DEFAULT 'legacy_name_or_current_owner',
    outcome_code VARCHAR(40) DEFAULT NULL,
    next_action VARCHAR(500) DEFAULT NULL,
    idempotency_key VARCHAR(80) DEFAULT NULL,
    voided_at DATETIME DEFAULT NULL,
    voided_by VARCHAR(50) DEFAULT NULL,
    void_reason VARCHAR(500) DEFAULT NULL,
    corrected_from_id BIGINT UNSIGNED DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sp_followup_idempotency (idempotency_key),
    KEY idx_sp_followup_date_salesman (activity_date, salesman),
    KEY idx_sp_followup_case (salescaseref),
    KEY idx_sp_followup_created_by (created_by),
    KEY idx_sp_followup_event_at (actual_activity_at, credited_salesman_code),
    KEY idx_sp_followup_quote (quotation_orderno)
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
    adjustment_type VARCHAR(32) NOT NULL DEFAULT 'value_exclusion',
    policy_version VARCHAR(40) NOT NULL DEFAULT 'normalization-v1-draft',
    target_issue_id BIGINT UNSIGNED DEFAULT NULL,
    canonical_source_key VARCHAR(100) DEFAULT NULL,
    count_effect VARCHAR(20) NOT NULL DEFAULT 'retain',
    source_amount_basis DECIMAL(24,6) DEFAULT NULL,
    source_version VARCHAR(100) DEFAULT NULL,
    reversal_of_id BIGINT UNSIGNED DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_sp_adjustment_source (metric_key, source_key, status),
    KEY idx_sp_adjustment_status (status, requested_at),
    KEY idx_sp_adjustment_policy (source_key, policy_version, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_settings (
    setting_key VARCHAR(64) NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Guarded column additions make this safe over the initial draft migration.
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND column_name='actual_activity_at')=0,
    'ALTER TABLE sales_performance_followups ADD COLUMN actual_activity_at DATETIME DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND column_name='quotation_orderno')=0,
    'ALTER TABLE sales_performance_followups ADD COLUMN quotation_orderno INT DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND column_name='credited_salesman_code')=0,
    'ALTER TABLE sales_performance_followups ADD COLUMN credited_salesman_code VARCHAR(20) DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND column_name='actor_user_id')=0,
    'ALTER TABLE sales_performance_followups ADD COLUMN actor_user_id VARCHAR(50) DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND column_name='entered_by_user_id')=0,
    'ALTER TABLE sales_performance_followups ADD COLUMN entered_by_user_id VARCHAR(50) DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND column_name='event_team_id')=0,
    'ALTER TABLE sales_performance_followups ADD COLUMN event_team_id INT DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND column_name='attribution_basis')=0,
    'ALTER TABLE sales_performance_followups ADD COLUMN attribution_basis VARCHAR(50) NOT NULL DEFAULT ''legacy_name_or_current_owner''', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND column_name='outcome_code')=0,
    'ALTER TABLE sales_performance_followups ADD COLUMN outcome_code VARCHAR(40) DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND column_name='next_action')=0,
    'ALTER TABLE sales_performance_followups ADD COLUMN next_action VARCHAR(500) DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND column_name='idempotency_key')=0,
    'ALTER TABLE sales_performance_followups ADD COLUMN idempotency_key VARCHAR(80) DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND column_name='voided_at')=0,
    'ALTER TABLE sales_performance_followups ADD COLUMN voided_at DATETIME DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND column_name='voided_by')=0,
    'ALTER TABLE sales_performance_followups ADD COLUMN voided_by VARCHAR(50) DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND column_name='void_reason')=0,
    'ALTER TABLE sales_performance_followups ADD COLUMN void_reason VARCHAR(500) DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND column_name='corrected_from_id')=0,
    'ALTER TABLE sales_performance_followups ADD COLUMN corrected_from_id BIGINT UNSIGNED DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND index_name='uq_sp_followup_idempotency')=0,
    'ALTER TABLE sales_performance_followups ADD UNIQUE KEY uq_sp_followup_idempotency (idempotency_key)', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND index_name='idx_sp_followup_event_at')=0,
    'ALTER TABLE sales_performance_followups ADD KEY idx_sp_followup_event_at (actual_activity_at, credited_salesman_code)', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='sales_performance_followups' AND index_name='idx_sp_followup_quote')=0,
    'ALTER TABLE sales_performance_followups ADD KEY idx_sp_followup_quote (quotation_orderno)', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_adjustments' AND column_name='adjustment_type')=0,
    'ALTER TABLE sales_performance_adjustments ADD COLUMN adjustment_type VARCHAR(32) NOT NULL DEFAULT ''value_exclusion''', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_adjustments' AND column_name='policy_version')=0,
    'ALTER TABLE sales_performance_adjustments ADD COLUMN policy_version VARCHAR(40) NOT NULL DEFAULT ''normalization-v1-draft''', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_adjustments' AND column_name='target_issue_id')=0,
    'ALTER TABLE sales_performance_adjustments ADD COLUMN target_issue_id BIGINT UNSIGNED DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_adjustments' AND column_name='canonical_source_key')=0,
    'ALTER TABLE sales_performance_adjustments ADD COLUMN canonical_source_key VARCHAR(100) DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_adjustments' AND column_name='count_effect')=0,
    'ALTER TABLE sales_performance_adjustments ADD COLUMN count_effect VARCHAR(20) NOT NULL DEFAULT ''retain''', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_adjustments' AND column_name='source_amount_basis')=0,
    'ALTER TABLE sales_performance_adjustments ADD COLUMN source_amount_basis DECIMAL(24,6) DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_adjustments' AND column_name='source_version')=0,
    'ALTER TABLE sales_performance_adjustments ADD COLUMN source_version VARCHAR(100) DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='sales_performance_adjustments' AND column_name='reversal_of_id')=0,
    'ALTER TABLE sales_performance_adjustments ADD COLUMN reversal_of_id BIGINT UNSIGNED DEFAULT NULL', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;
SET @sp_ddl = IF((SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='sales_performance_adjustments' AND index_name='idx_sp_adjustment_reversal')=0,
    'ALTER TABLE sales_performance_adjustments ADD KEY idx_sp_adjustment_reversal (reversal_of_id, status)', 'SELECT 1'); PREPARE sp_stmt FROM @sp_ddl; EXECUTE sp_stmt; DEALLOCATE PREPARE sp_stmt;

CREATE TABLE IF NOT EXISTS sales_performance_definitions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    metric_key VARCHAR(64) NOT NULL,
    definition_version INT NOT NULL,
    validation_status VARCHAR(24) NOT NULL DEFAULT 'draft',
    effective_from DATE DEFAULT NULL,
    effective_to DATE DEFAULT NULL,
    definition_text TEXT NOT NULL,
    decision_references VARCHAR(500) NOT NULL DEFAULT '',
    scope_policy_version VARCHAR(40) DEFAULT NULL,
    calendar_policy_version VARCHAR(40) DEFAULT NULL,
    normalization_policy_version VARCHAR(40) DEFAULT NULL,
    approved_by VARCHAR(50) DEFAULT NULL,
    approved_at DATETIME DEFAULT NULL,
    created_by VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sp_definition_version (metric_key, definition_version),
    KEY idx_sp_definition_status (validation_status, effective_from)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_scopes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    scope_key VARCHAR(64) NOT NULL,
    label VARCHAR(120) NOT NULL,
    scope_version INT NOT NULL DEFAULT 1,
    team_ids_text TEXT NOT NULL,
    include_unassigned TINYINT(1) NOT NULL DEFAULT 1,
    validation_status VARCHAR(24) NOT NULL DEFAULT 'needs_setup',
    effective_from DATE DEFAULT NULL,
    effective_to DATE DEFAULT NULL,
    updated_by VARCHAR(50) DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sp_scope_key_version (scope_key, scope_version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_scope_grants (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id VARCHAR(50) NOT NULL,
    capability VARCHAR(32) NOT NULL,
    scope_key VARCHAR(64) NOT NULL DEFAULT '',
    team_id INT DEFAULT NULL,
    granted_by VARCHAR(50) NOT NULL,
    granted_at DATETIME NOT NULL,
    revoked_by VARCHAR(50) DEFAULT NULL,
    revoked_at DATETIME DEFAULT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_sp_scope_grant_active (user_id, capability, revoked_at),
    KEY idx_sp_scope_grant_team (team_id, capability, revoked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO sales_performance_scopes (scope_key, label, team_ids_text, include_unassigned, validation_status)
SELECT 'erp_company', 'ERP company scope', '', 1, 'provisional' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM sales_performance_scopes WHERE scope_key='erp_company' AND scope_version=1);
INSERT INTO sales_performance_scopes (scope_key, label, team_ids_text, include_unassigned, validation_status)
SELECT 'core_sales_teams', 'North, South, Central and Projects', '', 1, 'needs_setup' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM sales_performance_scopes WHERE scope_key='core_sales_teams' AND scope_version=1);

CREATE TABLE IF NOT EXISTS sales_performance_enquiry_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    salescaseref VARCHAR(40) NOT NULL,
    episode_no INT UNSIGNED NOT NULL DEFAULT 1,
    event_type VARCHAR(32) NOT NULL,
    business_at DATETIME NOT NULL,
    recorded_at DATETIME NOT NULL,
    actor_user_id VARCHAR(50) DEFAULT NULL,
    entered_by_user_id VARCHAR(50) NOT NULL,
    credited_salesman_code VARCHAR(20) DEFAULT NULL,
    event_team_id INT DEFAULT NULL,
    attribution_basis VARCHAR(50) NOT NULL DEFAULT 'current_owner_at_entry',
    response_code VARCHAR(40) DEFAULT NULL,
    reason_code VARCHAR(40) DEFAULT NULL,
    notes TEXT,
    evidence_reference VARCHAR(500) DEFAULT NULL,
    idempotency_key VARCHAR(80) DEFAULT NULL,
    corrects_event_id BIGINT UNSIGNED DEFAULT NULL,
    voids_event_id BIGINT UNSIGNED DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sp_enquiry_event_idempotency (idempotency_key),
    KEY idx_sp_enquiry_case_time (salescaseref, business_at, id),
    KEY idx_sp_enquiry_time_type (event_type, business_at),
    KEY idx_sp_enquiry_team_time (event_team_id, business_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_quotation_issues (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    orderno INT NOT NULL,
    issue_sequence INT UNSIGNED NOT NULL,
    source_hash CHAR(64) NOT NULL,
    salescaseref VARCHAR(40) NOT NULL,
    debtorno VARCHAR(10) NOT NULL,
    branchcode VARCHAR(10) NOT NULL,
    canonical_family_key VARCHAR(80) NOT NULL,
    preceding_issue_id BIGINT UNSIGNED DEFAULT NULL,
    issue_kind VARCHAR(16) NOT NULL,
    issued_at DATETIME NOT NULL,
    recorded_at DATETIME NOT NULL,
    actor_user_id VARCHAR(50) DEFAULT NULL,
    entered_by_user_id VARCHAR(50) NOT NULL,
    credited_salesman_code VARCHAR(20) DEFAULT NULL,
    event_team_id INT DEFAULT NULL,
    attribution_basis VARCHAR(50) NOT NULL,
    line_snapshot LONGTEXT NOT NULL,
    raw_amount DECIMAL(24,6) DEFAULT NULL,
    net_amount DECIMAL(24,6) DEFAULT NULL,
    tax_amount DECIMAL(24,6) DEFAULT NULL,
    currency CHAR(3) DEFAULT NULL,
    tax_basis VARCHAR(40) NOT NULL,
    valuation_policy_version VARCHAR(60) NOT NULL,
    evidence_reference VARCHAR(500) DEFAULT NULL,
    idempotency_key VARCHAR(80) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sp_quote_issue_document_sequence (orderno, issue_sequence),
    UNIQUE KEY uq_sp_quote_issue_idempotency (idempotency_key),
    KEY idx_sp_quote_issue_date_owner (issued_at, credited_salesman_code),
    KEY idx_sp_quote_issue_case (salescaseref),
    KEY idx_sp_quote_issue_family (canonical_family_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_po_receipts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    debtorno VARCHAR(10) NOT NULL,
    branchcode VARCHAR(10) NOT NULL,
    customer_reference_original VARCHAR(150) NOT NULL,
    customer_reference_normalized VARCHAR(150) NOT NULL,
    received_at DATETIME NOT NULL,
    recorded_at DATETIME NOT NULL,
    salescaseref VARCHAR(40) DEFAULT NULL,
    entered_by_user_id VARCHAR(50) NOT NULL,
    credited_salesman_code VARCHAR(20) DEFAULT NULL,
    event_team_id INT DEFAULT NULL,
    attribution_basis VARCHAR(50) NOT NULL,
    stated_amount DECIMAL(24,6) DEFAULT NULL,
    net_amount DECIMAL(24,6) DEFAULT NULL,
    currency CHAR(3) DEFAULT NULL,
    tax_basis VARCHAR(40) NOT NULL DEFAULT 'unknown',
    evidence_reference VARCHAR(500) DEFAULT NULL,
    receipt_status VARCHAR(20) NOT NULL DEFAULT 'active',
    idempotency_key VARCHAR(80) NOT NULL,
    correction_of_id BIGINT UNSIGNED DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sp_po_receipt_idempotency (idempotency_key),
    KEY idx_sp_po_customer_ref (debtorno, branchcode, customer_reference_normalized),
    KEY idx_sp_po_received_owner (received_at, credited_salesman_code),
    KEY idx_sp_po_case (salescaseref)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_po_bridges (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    receipt_id BIGINT UNSIGNED NOT NULL,
    source_type VARCHAR(20) NOT NULL,
    source_key VARCHAR(100) NOT NULL,
    created_by VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sp_po_bridge (receipt_id, source_type, source_key),
    KEY idx_sp_po_bridge_source (source_type, source_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_team_membership (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    salesmancode VARCHAR(20) NOT NULL,
    team_id INT NOT NULL,
    valid_from DATE NOT NULL,
    valid_to DATE DEFAULT NULL,
    reporting_group VARCHAR(64) NOT NULL DEFAULT 'commercial_sales',
    provenance VARCHAR(120) NOT NULL,
    recorded_by VARCHAR(50) NOT NULL,
    recorded_at DATETIME NOT NULL,
    supersedes_id BIGINT UNSIGNED DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_sp_team_member_dates (salesmancode, valid_from, valid_to),
    KEY idx_sp_team_id_dates (team_id, valid_from, valid_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_calendar_days (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    calendar_version VARCHAR(40) NOT NULL,
    business_date DATE NOT NULL,
    planned_workday TINYINT(1) NOT NULL,
    override_reason VARCHAR(255) DEFAULT NULL,
    recorded_by VARCHAR(50) NOT NULL,
    recorded_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sp_calendar_date_version (calendar_version, business_date),
    KEY idx_sp_calendar_workday (calendar_version, planned_workday, business_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_coverage (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    scope_key VARCHAR(64) NOT NULL,
    team_id INT DEFAULT NULL,
    metric_key VARCHAR(64) NOT NULL,
    coverage_date DATE NOT NULL,
    coverage_state VARCHAR(24) NOT NULL,
    business_cutoff DATETIME DEFAULT NULL,
    reporter_user_id VARCHAR(50) DEFAULT NULL,
    submitted_at DATETIME DEFAULT NULL,
    reviewed_by VARCHAR(50) DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    evidence_reference VARCHAR(500) DEFAULT NULL,
    note VARCHAR(1000) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sp_coverage_slice (scope_key, team_id, metric_key, coverage_date),
    KEY idx_sp_coverage_date_metric (coverage_date, metric_key, coverage_state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_normalization_locks (
    source_type VARCHAR(32) NOT NULL,
    source_key VARCHAR(100) NOT NULL,
    policy_version VARCHAR(40) NOT NULL,
    revision_no BIGINT UNSIGNED NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (source_type, source_key, policy_version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_reference_observations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    source_name VARCHAR(255) NOT NULL,
    source_sha256 CHAR(64) NOT NULL,
    source_version VARCHAR(80) NOT NULL,
    evidence_location VARCHAR(120) NOT NULL,
    observation_key VARCHAR(255) NOT NULL,
    period_from DATE NOT NULL,
    period_through DATE NOT NULL,
    cutoff_at DATETIME DEFAULT NULL,
    granularity VARCHAR(20) NOT NULL,
    scope_key VARCHAR(64) DEFAULT NULL,
    team_name VARCHAR(120) DEFAULT NULL,
    salesperson_name VARCHAR(120) DEFAULT NULL,
    metric_key VARCHAR(64) NOT NULL,
    observed_count DECIMAL(24,6) DEFAULT NULL,
    observed_value DECIMAL(24,6) DEFAULT NULL,
    value_basis VARCHAR(50) DEFAULT NULL,
    denominator DECIMAL(24,6) DEFAULT NULL,
    verification_state VARCHAR(24) NOT NULL DEFAULT 'transcribed_unverified',
    notes TEXT,
    imported_by VARCHAR(50) NOT NULL,
    imported_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sp_reference_source_observation (source_sha256, observation_key),
    KEY idx_sp_reference_period_metric (period_from, period_through, metric_key),
    KEY idx_sp_reference_team_metric (team_name, metric_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_reconciliation (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    run_id BIGINT UNSIGNED DEFAULT NULL,
    reference_observation_id BIGINT UNSIGNED DEFAULT NULL,
    metric_key VARCHAR(64) NOT NULL,
    scope_key VARCHAR(64) DEFAULT NULL,
    period_from DATE NOT NULL,
    period_through DATE NOT NULL,
    erp_count DECIMAL(24,6) DEFAULT NULL,
    erp_value DECIMAL(24,6) DEFAULT NULL,
    reference_count DECIMAL(24,6) DEFAULT NULL,
    reference_value DECIMAL(24,6) DEFAULT NULL,
    difference_count DECIMAL(24,6) DEFAULT NULL,
    difference_value DECIMAL(24,6) DEFAULT NULL,
    classification VARCHAR(40) NOT NULL DEFAULT 'unresolved',
    source_basis TEXT NOT NULL,
    supporting_documents TEXT,
    owner_user_id VARCHAR(50) DEFAULT NULL,
    resolution_note TEXT,
    status VARCHAR(20) NOT NULL DEFAULT 'open',
    created_by VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_sp_recon_metric_period (metric_key, period_from, period_through),
    KEY idx_sp_recon_status (status, classification)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_report_runs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    run_key CHAR(36) NOT NULL,
    run_state VARCHAR(20) NOT NULL DEFAULT 'building',
    requested_by VARCHAR(50) NOT NULL,
    company_name VARCHAR(120) NOT NULL,
    database_name VARCHAR(64) NOT NULL,
    authorized_scope_text TEXT NOT NULL,
    scope_fingerprint CHAR(64) NOT NULL,
    filters_text TEXT NOT NULL,
    business_cutoff DATETIME NOT NULL,
    recorded_data_cutoff DATETIME NOT NULL,
    report_timezone VARCHAR(40) NOT NULL,
    definition_version VARCHAR(80) NOT NULL,
    calendar_version VARCHAR(40) NOT NULL,
    attribution_version VARCHAR(40) NOT NULL,
    normalization_version VARCHAR(40) NOT NULL,
    generated_at DATETIME NOT NULL,
    validation_state VARCHAR(24) NOT NULL,
    completeness_text TEXT NOT NULL,
    totals_text LONGTEXT,
    export_manifest_text TEXT,
    content_hash CHAR(64) DEFAULT NULL,
    ready_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sp_run_key (run_key),
    KEY idx_sp_run_requester (requested_by, generated_at),
    KEY idx_sp_run_state (run_state, generated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sales_performance_report_run_facts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    run_id BIGINT UNSIGNED NOT NULL,
    fact_id VARCHAR(120) NOT NULL,
    metric_family VARCHAR(64) NOT NULL,
    source_type VARCHAR(40) NOT NULL,
    source_key VARCHAR(120) NOT NULL,
    source_version VARCHAR(120) DEFAULT NULL,
    salescaseref VARCHAR(40) DEFAULT NULL,
    customer VARCHAR(40) DEFAULT NULL,
    branch VARCHAR(10) DEFAULT NULL,
    actor_user_id VARCHAR(50) DEFAULT NULL,
    entered_by_user_id VARCHAR(50) DEFAULT NULL,
    credited_salesperson_code VARCHAR(20) DEFAULT NULL,
    credited_team_id INT DEFAULT NULL,
    attribution_basis VARCHAR(60) DEFAULT NULL,
    attribution_version VARCHAR(40) DEFAULT NULL,
    business_at DATETIME DEFAULT NULL,
    recorded_at DATETIME DEFAULT NULL,
    date_basis VARCHAR(100) DEFAULT NULL,
    raw_amount DECIMAL(24,6) DEFAULT NULL,
    net_amount DECIMAL(24,6) DEFAULT NULL,
    normalized_amount DECIMAL(24,6) DEFAULT NULL,
    currency CHAR(3) DEFAULT NULL,
    reporting_currency CHAR(3) DEFAULT NULL,
    tax_basis VARCHAR(50) DEFAULT NULL,
    tax_rule_version VARCHAR(60) DEFAULT NULL,
    raw_count_eligible TINYINT(1) DEFAULT NULL,
    normalized_count_eligible TINYINT(1) DEFAULT NULL,
    value_status VARCHAR(40) NOT NULL,
    coverage_status VARCHAR(40) NOT NULL,
    eligibility_status VARCHAR(40) NOT NULL,
    warnings_text TEXT,
    normalization_references TEXT,
    source_evidence LONGTEXT,
    calculation_version VARCHAR(80) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sp_run_fact (run_id, fact_id),
    KEY idx_sp_run_fact_metric_date (run_id, metric_family, business_at),
    KEY idx_sp_run_fact_owner (run_id, credited_team_id, credited_salesperson_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Seed definitions only; application users must not infer approval from the presence of a row.
INSERT INTO sales_performance_definitions (metric_key, definition_version, validation_status, definition_text, decision_references, created_by, created_at)
SELECT 'quotation_initial_issues', 1, 'provisional', 'Legacy saved initial quotation documents by document-date proxy; quick and ordinary option-zero valuation; net amount uses provisional ERP PDF tax treatment.', 'DEC-06,DEC-08,DEC-10,DEC-11', 'migration', NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM sales_performance_definitions WHERE metric_key='quotation_initial_issues' AND definition_version=1);
INSERT INTO sales_performance_definitions (metric_key, definition_version, validation_status, definition_text, decision_references, created_by, created_at)
SELECT 'quotation_revision_issues', 1, 'provisional', 'Legacy saved revision quotation documents by document-date proxy; full current net value; not an as-issued snapshot.', 'DEC-06,DEC-08,DEC-09,DEC-10,DEC-11', 'migration', NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM sales_performance_definitions WHERE metric_key='quotation_revision_issues' AND definition_version=1);
INSERT INTO sales_performance_definitions (metric_key, definition_version, validation_status, definition_text, decision_references, created_by, created_at)
SELECT 'customer_po_receipt', 1, 'draft', 'Count canonical customer PO receipts; OC figures are a separate provisional proxy.', 'DEC-12', 'migration', NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM sales_performance_definitions WHERE metric_key='customer_po_receipt' AND definition_version=1);

-- Capability definitions are seeded without user_permission grants.
INSERT INTO permissions (name, slug, description)
SELECT 'View Sales Performance for Self', 'sales_performance_view_self', 'View sales performance facts within the signed-in salesperson scope' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug='sales_performance_view_self');
INSERT INTO permissions (name, slug, description)
SELECT 'View Sales Performance Teams', 'sales_performance_view_teams', 'View only explicitly authorized sales team reporting scopes' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug='sales_performance_view_teams');
INSERT INTO permissions (name, slug, description)
SELECT 'View Sales Performance Company', 'sales_performance_view_all', 'View the configured all-company sales performance scope' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug='sales_performance_view_all');
INSERT INTO permissions (name, slug, description)
SELECT 'Capture Sales Performance Events', 'sales_performance_capture_events', 'Capture scoped enquiry, follow-up and customer PO reporting events' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug='sales_performance_capture_events');
INSERT INTO permissions (name, slug, description)
SELECT 'Administer Sales Performance Setup', 'sales_performance_admin', 'Configure sales reporting definitions, scope, calendar and coverage' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug='sales_performance_admin');
INSERT INTO permissions (name, slug, description)
SELECT 'Create Frozen Sales Performance Runs', 'sales_performance_create_run', 'Create reproducible sales reporting runs within current authorization' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug='sales_performance_create_run');

INSERT INTO sales_performance_settings (setting_key, setting_value)
SELECT 'report_timezone', 'Asia/Karachi' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM sales_performance_settings WHERE setting_key='report_timezone');

INSERT INTO sales_performance_schema_migrations (version_no, migration_name, applied_at)
VALUES (2, 'sales_performance_reporting_v2', NOW())
ON DUPLICATE KEY UPDATE migration_name=VALUES(migration_name);
