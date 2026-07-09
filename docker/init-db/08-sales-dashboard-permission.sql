-- ---------------------------------------------------------------------------
-- Grant the 'sales_dashboard' permission (v2/salesMainDashboard.php).
--
-- The dashboard is gated on userHasPermission($db,'sales_dashboard'), which is
-- also satisfied by the '*' super-permission — so ADMINS ALREADY HAVE ACCESS and
-- do NOT need a row here. This script grants the slug to the current directors
-- (everyone who holds 'directorreports') so they keep access after the switch
-- from the shared 'directorreports' gate to this dedicated slug.
--
-- Idempotent and non-destructive — safe to run more than once.
--
-- Run on production once:
--   docker exec -i sahamid-db sh -c 'mysql -uirtiza -pnetetech321 sahamid' < docker/init-db/08-sales-dashboard-permission.sql
--   (or import via phpMyAdmin)
-- ---------------------------------------------------------------------------

INSERT INTO user_permission (userid, permission)
SELECT DISTINCT dp.userid, 'sales_dashboard'
FROM   user_permission dp
WHERE  dp.permission = 'directorreports'
AND NOT EXISTS (
	SELECT 1 FROM user_permission x
	WHERE x.userid = dp.userid AND x.permission = 'sales_dashboard'
);

-- ---------------------------------------------------------------------------
-- Managing access afterwards:
--
--   Grant to one person:
--     INSERT INTO user_permission (userid, permission) VALUES ('some_userid', 'sales_dashboard');
--
--   Revoke from one person:
--     DELETE FROM user_permission WHERE userid = 'some_userid' AND permission = 'sales_dashboard';
--
--   See who currently has explicit access (admins with '*' are implicit and not listed):
--     SELECT userid FROM user_permission WHERE permission = 'sales_dashboard';
-- ---------------------------------------------------------------------------
