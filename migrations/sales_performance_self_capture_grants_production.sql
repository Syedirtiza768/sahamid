-- Production: grant salespeople the permissions to record their own Sales Performance activity.
-- Run against the ERP database (the one the application uses, e.g. sahamid), step by step.
-- Permissions live in user_permission, which is MyISAM (no transactions), so Step 1 takes a snapshot
-- that the rollback script uses to remove only the rows added here.
--
-- Scope: www_users accounts whose real name matches a salesman record.
-- Excluded: the 'mgmttest' test account. Remove the exclusion line in Step 3 to include it.

-- Step 1: snapshot current permissions (fails if the snapshot table already exists, protecting an earlier snapshot)
CREATE TABLE user_permission_pre_self_capture AS SELECT * FROM user_permission;

-- Step 2: review the accounts that will be granted
SELECT u.userid, u.realname
FROM www_users u
INNER JOIN salesman s ON s.salesmanname = u.realname
WHERE u.userid <> 'mgmttest'
ORDER BY u.realname, u.userid;

-- Step 3: grant the three permissions, skipping any the account already has
INSERT INTO user_permission (userid, permission)
SELECT u.userid, p.slug
FROM www_users u
INNER JOIN salesman s ON s.salesmanname = u.realname
CROSS JOIN (
    SELECT 'salesperson_daily_activity_report' AS slug
    UNION ALL SELECT 'sales_performance_view_self'
    UNION ALL SELECT 'sales_performance_capture_events'
) p
WHERE u.userid <> 'mgmttest'
  AND NOT EXISTS (
    SELECT 1 FROM user_permission up
    WHERE up.userid = u.userid AND up.permission = p.slug
);

-- Step 4: verify. Counts all accounts holding each permission, including accounts granted before this migration.
SELECT permission, COUNT(*) AS users
FROM user_permission
WHERE permission IN ('salesperson_daily_activity_report', 'sales_performance_view_self', 'sales_performance_capture_events')
GROUP BY permission;

-- Rows added by this migration (should equal the number of new grants):
SELECT COUNT(*) AS rows_added
FROM user_permission up
WHERE NOT EXISTS (SELECT 1 FROM user_permission_pre_self_capture p WHERE p.id = up.id);
