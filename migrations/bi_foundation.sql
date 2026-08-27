-- Embedded BI foundation metadata and cache tables.
--
-- This migration is additive and idempotent. It does not alter operational ERP
-- tables. Apply it once per company database after reviewing the rollout plan in
-- docs/BI_PHASE0_DISCOVERY.md.

CREATE TABLE IF NOT EXISTS bi_semantic_model_version (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    model_key VARCHAR(100) NOT NULL,
    version_no INT UNSIGNED NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    description TEXT NOT NULL,
    created_by VARCHAR(50) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_bi_semantic_model_version (model_key, version_no),
    KEY idx_bi_semantic_model_status (model_key, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS bi_metric (
    metric_id VARCHAR(100) NOT NULL,
    owning_module VARCHAR(100) NOT NULL,
    business_owner VARCHAR(150) NOT NULL DEFAULT '',
    current_version INT UNSIGNED NOT NULL DEFAULT 1,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    created_by VARCHAR(50) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL,
    updated_by VARCHAR(50) NOT NULL DEFAULT '',
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (metric_id),
    KEY idx_bi_metric_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS bi_metric_version (
    metric_id VARCHAR(100) NOT NULL,
    version_no INT UNSIGNED NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    handler_key VARCHAR(100) DEFAULT NULL,
    business_name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    formula TEXT NOT NULL,
    source_lineage TEXT NOT NULL,
    grain VARCHAR(255) NOT NULL,
    date_role VARCHAR(100) DEFAULT NULL,
    dimensions TEXT NOT NULL,
    caveats TEXT NOT NULL,
    validation_evidence TEXT NOT NULL,
    freshness_sla VARCHAR(100) DEFAULT NULL,
    last_verified_at DATETIME DEFAULT NULL,
    verified_by VARCHAR(50) DEFAULT NULL,
    created_by VARCHAR(50) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL,
    PRIMARY KEY (metric_id, version_no),
    KEY idx_bi_metric_version_status (status),
    KEY idx_bi_metric_version_handler (handler_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS bi_metric_validation (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    metric_id VARCHAR(100) NOT NULL,
    version_no INT UNSIGNED NOT NULL,
    validation_type VARCHAR(64) NOT NULL,
    status VARCHAR(32) NOT NULL,
    source_total DECIMAL(24,6) DEFAULT NULL,
    comparison_total DECIMAL(24,6) DEFAULT NULL,
    variance DECIMAL(24,6) DEFAULT NULL,
    tolerance DECIMAL(24,6) DEFAULT NULL,
    evidence TEXT NOT NULL,
    run_at DATETIME NOT NULL,
    run_by VARCHAR(50) NOT NULL DEFAULT '',
    PRIMARY KEY (id),
    KEY idx_bi_metric_validation_metric (metric_id, version_no),
    KEY idx_bi_metric_validation_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS bi_query_cache (
    cache_key CHAR(64) NOT NULL,
    payload LONGTEXT NOT NULL,
    created_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    PRIMARY KEY (cache_key),
    KEY idx_bi_query_cache_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS bi_query_log (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    database_name VARCHAR(100) NOT NULL,
    user_id VARCHAR(50) NOT NULL DEFAULT '',
    metric_ids VARCHAR(1000) NOT NULL,
    request_fingerprint CHAR(64) NOT NULL,
    scope_fingerprint CHAR(64) NOT NULL,
    result_status VARCHAR(32) NOT NULL,
    error_code VARCHAR(64) DEFAULT NULL,
    elapsed_ms INT UNSIGNED DEFAULT NULL,
    returned_rows INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_bi_query_log_created (created_at),
    KEY idx_bi_query_log_metric (metric_ids(191)),
    KEY idx_bi_query_log_status (result_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS bi_audit_event (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    database_name VARCHAR(100) NOT NULL,
    user_id VARCHAR(50) NOT NULL DEFAULT '',
    event_type VARCHAR(64) NOT NULL,
    object_type VARCHAR(64) NOT NULL,
    object_key VARCHAR(191) NOT NULL,
    details TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_bi_audit_event_created (created_at),
    KEY idx_bi_audit_event_object (object_type, object_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Seed only immutable catalog identities/versions. Business status remains
-- Awaiting Validation until the documented reconciliation is approved.
INSERT INTO bi_semantic_model_version
    (model_key, version_no, status, description, created_at)
VALUES
    ('sa_hamid_sales', 1, 'awaiting_validation', 'Starter sales semantic model for the embedded BI query boundary.', UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE model_key = VALUES(model_key);

INSERT INTO bi_metric
    (metric_id, owning_module, business_owner, current_version, status, created_at, updated_at)
VALUES
    ('sales.invoice_value', 'sales', 'Finance / Sales owner', 1, 'awaiting_validation', UTC_TIMESTAMP(), UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE metric_id = VALUES(metric_id);

INSERT INTO bi_metric_version
    (metric_id, version_no, status, handler_key, business_name, description, formula, source_lineage, grain, date_role, dimensions, caveats, validation_evidence, freshness_sla, created_at)
VALUES
    ('sales.invoice_value', 1, 'awaiting_validation', 'sales_invoice_value', 'Invoice Value',
     'Invoice line value for posted, non-returned operational invoices.',
     'invoicedetails.unitprice * (1 - invoicedetails.discountpercent) * invoicedetails.quantity * invoiceoptions.quantity',
     'invoice.invoiceno, invoice.invoicesdate, invoice.returned, invoice.inprogress, invoiceoptions.invoiceno/invoicelineno/invoiceoptionno, invoicedetails matching keys and value fields, salescase.salesman mapped through salesman.salesmanname/salesmancode for scope',
     'one row per invoice detail and invoice option combination', 'invoice.invoicesdate', 'salesperson',
     'Linked debtortrans.ovamount is gross for exclusive invoices and reconciles only after the date/versioned tax policy is applied. invoice.salesperson is blank in the live dataset, so salesperson scope must use salescase.salesman mapped through salesman.salesmanname/salesmancode. Currency and GL posting-date behavior are not yet modeled.',
     'Local MariaDB comparison on 2026-08-27: raw invoice-line formula 235839785.97447327 versus linked debtor transaction ovamount 267153957.4547138 for 2026-01-01 through 2026-08-27; the 31314171.480240 variance is explained by 18% gross-up for exclusive services=0 and 16% gross-up for exclusive services=1 in this window.',
     '15 minutes after approval', UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE metric_id = VALUES(metric_id), version_no = VALUES(version_no);
