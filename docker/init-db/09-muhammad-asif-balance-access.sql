-- ---------------------------------------------------------------------------
-- Grant Muhammad Asif (userid 'M-Asif') visibility into the SR balance reports:
--   reports/balance/crvbalance/CRVBalanceSheet.php?location=SR
--   reports/balance/custbalance/CustBalanceSheetSalesPerson.php?location=SR
--
-- Root cause: both pages opened but showed no rows because their data APIs
-- restrict a non-privileged user to their OWN salesman name, and "Muhammad Asif"
-- is not a salesman.
--   * CRVBalanceSheetAPI.php already unlocks full visibility for holders of
--     'executive_listing' (the app's executive "see-all balances" permission).
--   * CustBalanceSheetAPISalesPerson.php only unlocked for AccessLevel roles
--     8/10/22/23; it now also honours 'executive_listing' (code change).
--   * Opening the SalesPerson SR page additionally requires 'CustomerBalanceSheetSRSP'.
--
-- These two grants therefore let him open and see the data in both reports,
-- without changing his global role. Idempotent and non-destructive.
--
-- Run on production once (or import via phpMyAdmin):
--   docker exec -i sahamid-db sh -c 'mysql -uirtiza -pnetetech321 sahamid' < docker/init-db/09-muhammad-asif-balance-access.sql
-- ---------------------------------------------------------------------------

INSERT INTO user_permission (userid, permission)
SELECT u.userid, u.permission
FROM (
	SELECT 'M-Asif' AS userid, 'executive_listing'        AS permission
	UNION ALL
	SELECT 'M-Asif' AS userid, 'CustomerBalanceSheetSRSP' AS permission
) u
WHERE NOT EXISTS (
	SELECT 1 FROM user_permission x
	WHERE x.userid = u.userid AND x.permission = u.permission
);
