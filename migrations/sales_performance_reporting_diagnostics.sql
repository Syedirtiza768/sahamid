-- Read-only diagnostics for a server where Sales Performance data does not load.
-- Select the same ERP application database used by config.php before running.
-- This script only reads metadata and the reporting settings; it changes nothing.

SET @sp_diag_query = IF(
    (SELECT COUNT(*) FROM information_schema.tables
     WHERE table_schema=DATABASE() AND table_name='sales_performance_schema_migrations') > 0,
    'SELECT version_no, migration_name, applied_at FROM sales_performance_schema_migrations ORDER BY version_no',
    'SELECT ''sales_performance_schema_migrations is missing'' AS migration_status'
);
PREPARE sp_diag_stmt FROM @sp_diag_query;
EXECUTE sp_diag_stmt;
DEALLOCATE PREPARE sp_diag_stmt;

SELECT required.table_name,
       IF(actual.table_name IS NULL, 'MISSING', 'OK') AS status
FROM (
    SELECT 'sales_performance_followups' AS table_name
    UNION ALL SELECT 'sales_performance_adjustments'
    UNION ALL SELECT 'sales_performance_settings'
    UNION ALL SELECT 'sales_performance_definitions'
    UNION ALL SELECT 'sales_performance_scopes'
    UNION ALL SELECT 'sales_performance_scope_grants'
    UNION ALL SELECT 'sales_performance_enquiry_events'
    UNION ALL SELECT 'sales_performance_quotation_issues'
    UNION ALL SELECT 'sales_performance_po_receipts'
    UNION ALL SELECT 'sales_performance_po_bridges'
    UNION ALL SELECT 'sales_performance_team_membership'
    UNION ALL SELECT 'sales_performance_calendar_days'
    UNION ALL SELECT 'sales_performance_coverage'
    UNION ALL SELECT 'sales_performance_normalization_locks'
    UNION ALL SELECT 'sales_performance_reference_observations'
    UNION ALL SELECT 'sales_performance_reconciliation'
    UNION ALL SELECT 'sales_performance_reconciliation_history'
    UNION ALL SELECT 'sales_performance_reference_observation_history'
    UNION ALL SELECT 'sales_performance_report_runs'
    UNION ALL SELECT 'sales_performance_report_run_facts'
    UNION ALL SELECT 'sales_performance_team_membership_history'
    UNION ALL SELECT 'sales_performance_calendar_day_history'
    UNION ALL SELECT 'sales_performance_coverage_history'
    UNION ALL SELECT 'sales_performance_scope_history'
) AS required
LEFT JOIN information_schema.tables AS actual
  ON actual.table_schema=DATABASE() AND actual.table_name=required.table_name
ORDER BY required.table_name;

SELECT required.table_name, required.column_name,
       IF(actual.column_name IS NULL, 'MISSING', 'OK') AS status
FROM (
    SELECT 'sales_performance_adjustments' AS table_name, 'adjustment_type' AS column_name
    UNION ALL SELECT 'sales_performance_adjustments', 'count_effect'
    UNION ALL SELECT 'sales_performance_adjustments', 'policy_version'
    UNION ALL SELECT 'sales_performance_adjustments', 'reversal_of_id'
    UNION ALL SELECT 'sales_performance_followups', 'corrected_from_id'
    UNION ALL SELECT 'sales_performance_followups', 'voided_at'
    UNION ALL SELECT 'sales_performance_followups', 'voided_by'
    UNION ALL SELECT 'sales_performance_followups', 'void_reason'
    UNION ALL SELECT 'sales_performance_enquiry_events', 'correction_reason'
    UNION ALL SELECT 'sales_performance_enquiry_events', 'void_reason'
    UNION ALL SELECT 'sales_performance_po_receipts', 'correction_reason'
    UNION ALL SELECT 'sales_performance_po_receipts', 'voided_at'
    UNION ALL SELECT 'sales_performance_po_receipts', 'voided_by'
    UNION ALL SELECT 'sales_performance_po_receipts', 'void_reason'
    UNION ALL SELECT 'sales_performance_po_receipts', 'void_idempotency_key'
    UNION ALL SELECT 'sales_performance_reference_observations', 'value_currency'
    UNION ALL SELECT 'sales_performance_reference_observations', 'verification_note'
    UNION ALL SELECT 'sales_performance_reference_observation_history', 'observation_id'
    UNION ALL SELECT 'sales_performance_reference_observation_history', 'previous_state'
    UNION ALL SELECT 'sales_performance_reference_observation_history', 'new_state'
    UNION ALL SELECT 'sales_performance_reference_observation_history', 'review_note'
    UNION ALL SELECT 'sales_performance_reference_observation_history', 'changed_by'
    UNION ALL SELECT 'sales_performance_reference_observation_history', 'changed_at'
    UNION ALL SELECT 'sales_performance_team_membership_history', 'membership_id'
    UNION ALL SELECT 'sales_performance_team_membership_history', 'action_type'
    UNION ALL SELECT 'sales_performance_team_membership_history', 'salesmancode'
    UNION ALL SELECT 'sales_performance_team_membership_history', 'team_id'
    UNION ALL SELECT 'sales_performance_team_membership_history', 'valid_from'
    UNION ALL SELECT 'sales_performance_team_membership_history', 'previous_valid_to'
    UNION ALL SELECT 'sales_performance_team_membership_history', 'new_valid_to'
    UNION ALL SELECT 'sales_performance_team_membership_history', 'changed_by'
    UNION ALL SELECT 'sales_performance_calendar_day_history', 'calendar_day_id'
    UNION ALL SELECT 'sales_performance_calendar_day_history', 'calendar_version'
    UNION ALL SELECT 'sales_performance_calendar_day_history', 'business_date'
    UNION ALL SELECT 'sales_performance_calendar_day_history', 'previous_workday'
    UNION ALL SELECT 'sales_performance_calendar_day_history', 'new_workday'
    UNION ALL SELECT 'sales_performance_calendar_day_history', 'new_reason'
    UNION ALL SELECT 'sales_performance_calendar_day_history', 'changed_by'
    UNION ALL SELECT 'sales_performance_reconciliation_history', 'reconciliation_id'
    UNION ALL SELECT 'sales_performance_reconciliation_history', 'action_type'
    UNION ALL SELECT 'sales_performance_reconciliation_history', 'classification'
    UNION ALL SELECT 'sales_performance_reconciliation_history', 'status'
    UNION ALL SELECT 'sales_performance_reconciliation_history', 'owner_user_id'
    UNION ALL SELECT 'sales_performance_reconciliation_history', 'resolution_note'
    UNION ALL SELECT 'sales_performance_reconciliation_history', 'changed_by'
    UNION ALL SELECT 'sales_performance_reconciliation_history', 'changed_at'
    UNION ALL SELECT 'sales_performance_coverage', 'review_snapshot_json'
    UNION ALL SELECT 'sales_performance_coverage_history', 'coverage_id'
    UNION ALL SELECT 'sales_performance_coverage_history', 'action_type'
    UNION ALL SELECT 'sales_performance_coverage_history', 'previous_state'
    UNION ALL SELECT 'sales_performance_coverage_history', 'new_state'
    UNION ALL SELECT 'sales_performance_coverage_history', 'previous_note'
    UNION ALL SELECT 'sales_performance_coverage_history', 'new_note'
    UNION ALL SELECT 'sales_performance_coverage_history', 'previous_evidence_reference'
    UNION ALL SELECT 'sales_performance_coverage_history', 'new_evidence_reference'
    UNION ALL SELECT 'sales_performance_coverage_history', 'previous_reporter_user_id'
    UNION ALL SELECT 'sales_performance_coverage_history', 'new_reporter_user_id'
    UNION ALL SELECT 'sales_performance_coverage_history', 'previous_business_cutoff'
    UNION ALL SELECT 'sales_performance_coverage_history', 'new_business_cutoff'
    UNION ALL SELECT 'sales_performance_coverage_history', 'previous_submitted_at'
    UNION ALL SELECT 'sales_performance_coverage_history', 'new_submitted_at'
    UNION ALL SELECT 'sales_performance_coverage_history', 'previous_review_snapshot_json'
    UNION ALL SELECT 'sales_performance_coverage_history', 'new_review_snapshot_json'
    UNION ALL SELECT 'sales_performance_coverage_history', 'changed_by'
    UNION ALL SELECT 'sales_performance_coverage_history', 'changed_at'
    UNION ALL SELECT 'sales_performance_scope_history', 'scope_id'
    UNION ALL SELECT 'sales_performance_scope_history', 'scope_key'
    UNION ALL SELECT 'sales_performance_scope_history', 'scope_version'
    UNION ALL SELECT 'sales_performance_scope_history', 'action_type'
    UNION ALL SELECT 'sales_performance_scope_history', 'previous_label'
    UNION ALL SELECT 'sales_performance_scope_history', 'new_label'
    UNION ALL SELECT 'sales_performance_scope_history', 'previous_team_ids_text'
    UNION ALL SELECT 'sales_performance_scope_history', 'new_team_ids_text'
    UNION ALL SELECT 'sales_performance_scope_history', 'previous_include_unassigned'
    UNION ALL SELECT 'sales_performance_scope_history', 'new_include_unassigned'
    UNION ALL SELECT 'sales_performance_scope_history', 'previous_validation_status'
    UNION ALL SELECT 'sales_performance_scope_history', 'new_validation_status'
    UNION ALL SELECT 'sales_performance_scope_history', 'change_note'
    UNION ALL SELECT 'sales_performance_scope_history', 'changed_by'
    UNION ALL SELECT 'sales_performance_scope_history', 'changed_at'
) AS required
LEFT JOIN information_schema.columns AS actual
  ON actual.table_schema=DATABASE()
 AND actual.table_name=required.table_name
 AND actual.column_name=required.column_name
ORDER BY required.table_name, required.column_name;

SELECT required.table_name, required.index_name,
       IF(actual.index_name IS NULL, 'MISSING', 'OK') AS status
FROM (
    SELECT 'sales_performance_followups' AS table_name, 'idx_sp_followup_correction' AS index_name
    UNION ALL SELECT 'sales_performance_enquiry_events', 'idx_sp_enquiry_correction'
    UNION ALL SELECT 'sales_performance_enquiry_events', 'idx_sp_enquiry_void'
    UNION ALL SELECT 'sales_performance_po_receipts', 'idx_sp_po_receipt_correction'
    UNION ALL SELECT 'sales_performance_po_receipts', 'uq_sp_po_receipt_void_idempotency'
    UNION ALL SELECT 'sales_performance_team_membership_history', 'idx_sp_team_history_item'
    UNION ALL SELECT 'sales_performance_calendar_day_history', 'idx_sp_calendar_history_item'
    UNION ALL SELECT 'sales_performance_reference_observation_history', 'idx_sp_reference_history_item'
    UNION ALL SELECT 'sales_performance_reconciliation_history', 'idx_sp_recon_history_item'
    UNION ALL SELECT 'sales_performance_coverage_history', 'idx_sp_coverage_history_item'
    UNION ALL SELECT 'sales_performance_scope_history', 'idx_sp_scope_history_item'
) AS required
LEFT JOIN information_schema.statistics AS actual
  ON actual.table_schema=DATABASE()
 AND actual.table_name=required.table_name
 AND actual.index_name=required.index_name
ORDER BY required.table_name, required.index_name;

SET @sp_diag_query = IF(
    (SELECT COUNT(*) FROM information_schema.tables
     WHERE table_schema=DATABASE() AND table_name='sales_performance_settings') > 0,
    'SELECT ''report_calendar_version'' AS setting_key, IF(COUNT(*) > 0, ''OK'', ''MISSING'') AS status FROM sales_performance_settings WHERE setting_key=''report_calendar_version'' AND LENGTH(TRIM(setting_value)) > 0',
    'SELECT ''sales_performance_settings'' AS setting_key, ''TABLE MISSING'' AS status'
);
PREPARE sp_diag_stmt FROM @sp_diag_query;
EXECUTE sp_diag_stmt;
DEALLOCATE PREPARE sp_diag_stmt;

SELECT 'salesPerformanceReport.php' AS required_script,
       IF(script IS NULL, 'MISSING', 'OK') AS status
FROM (SELECT 1 AS singleton) AS one_row
LEFT JOIN scripts ON scripts.script='salesPerformanceReport.php';
