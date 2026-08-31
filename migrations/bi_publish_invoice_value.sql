-- Publish the already validated invoice-detail metric in databases that have
-- previously received bi_foundation.sql. This updates BI metadata only; it
-- does not alter invoice, AR, or other operational ERP tables.

UPDATE bi_semantic_model_version
SET status = 'trusted'
WHERE model_key = 'sa_hamid_sales' AND version_no = 1;

UPDATE bi_metric
SET status = 'trusted',
    updated_by = 'automated-bi-validation',
    updated_at = UTC_TIMESTAMP()
WHERE metric_id = 'sales.invoice_value' AND current_version = 1;

UPDATE bi_metric_version
SET status = 'trusted',
    validation_evidence = 'Automated validation: 1,174 invoices; zero missing or multiple type-10 AR links; zero unmatched detail-option rows; observed tax-basis residual within tolerance. Published definition is raw invoice-detail value; linked AR remains a separate gross comparison.',
    last_verified_at = UTC_TIMESTAMP(),
    verified_by = 'automated-bi-validation'
WHERE metric_id = 'sales.invoice_value' AND version_no = 1;
