-- ---------------------------------------------------------------------------
-- Grant Muhammad Asif (userid 'M-Asif') access to the SR balance reports:
--   reports/balance/crvbalance/CRVBalanceSheet.php?location=SR
--   reports/balance/custbalance/CustBalanceSheetSalesPerson.php?location=SR
--
-- CRV access is scoped to the SR balance sheet. It does not grant the global
-- 'executive_listing' permission.
-- ---------------------------------------------------------------------------

INSERT INTO permissions (`name`, `slug`, `description`)
SELECT 'CRV Balance Sheet SR - All Salespeople',
       'CRVBalanceSheetSRAll',
       'View all CRV balances for the SR location'
FROM DUAL
WHERE NOT EXISTS (
	SELECT 1 FROM permissions WHERE slug = 'CRVBalanceSheetSRAll'
);

INSERT INTO user_permission (userid, permission)
SELECT u.userid, u.permission
FROM (
	SELECT 'M-Asif' AS userid, 'CRVBalanceSheetSRAll' AS permission
	UNION ALL
	SELECT 'M-Asif' AS userid, 'CustomerBalanceSheetSRSP' AS permission
) u
WHERE NOT EXISTS (
	SELECT 1 FROM user_permission x
	WHERE x.userid = u.userid AND x.permission = u.permission
);
