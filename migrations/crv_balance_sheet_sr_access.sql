-- Grant M-Asif access to CRV Balance Sheet rows for location=SR only.
INSERT INTO `permissions` (`name`, `slug`, `description`)
SELECT 'CRV Balance Sheet SR - All Salespeople',
       'CRVBalanceSheetSRAll',
       'View all CRV balances for the SR location'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `permissions`
    WHERE `slug` = 'CRVBalanceSheetSRAll'
);

INSERT INTO `user_permission` (`userid`, `permission`)
SELECT `www_users`.`userid`, 'CRVBalanceSheetSRAll'
FROM `www_users`
WHERE `www_users`.`userid` = 'M-Asif'
  AND NOT EXISTS (
      SELECT 1
      FROM `user_permission`
      WHERE `user_permission`.`userid` = `www_users`.`userid`
        AND `user_permission`.`permission` = 'CRVBalanceSheetSRAll'
  );
