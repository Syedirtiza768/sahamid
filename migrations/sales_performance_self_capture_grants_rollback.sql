-- Rollback for sales_performance_self_capture_grants_production.sql.
-- Removes only the permission rows added after the Step 1 snapshot table was created.
-- Permissions that existed before the migration (they are in the snapshot) are kept.
-- Run against the same ERP database. Requires user_permission_pre_self_capture to exist.

DELETE FROM user_permission
WHERE permission IN ('salesperson_daily_activity_report', 'sales_performance_view_self', 'sales_performance_capture_events')
  AND id NOT IN (SELECT id FROM user_permission_pre_self_capture);

-- Verify: rows added since the snapshot should now be zero.
SELECT COUNT(*) AS rows_added_since_snapshot
FROM user_permission up
WHERE NOT EXISTS (SELECT 1 FROM user_permission_pre_self_capture p WHERE p.id = up.id);
