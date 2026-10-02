-- Grant M-Asif access to all salesperson rows in the SR customer balance sheet.
INSERT INTO `permissions` (`name`, `slug`, `description`)
SELECT 'Customer Balance Sheet SR SP - All Salespeople',
       'CustomerBalanceSheetSRSPAll',
       'View all salesperson balances in the SR customer balance sheet'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `permissions`
    WHERE `slug` = 'CustomerBalanceSheetSRSPAll'
);

INSERT INTO `user_permission` (`userid`, `permission`)
SELECT `www_users`.`userid`, `permissions`.`slug`
FROM `www_users`
INNER JOIN `permissions`
    ON `permissions`.`slug` = 'CustomerBalanceSheetSRSPAll'
WHERE `www_users`.`userid` = 'M-Asif'
  AND NOT EXISTS (
      SELECT 1
      FROM `user_permission`
      WHERE `user_permission`.`userid` = `www_users`.`userid`
        AND `user_permission`.`permission` = `permissions`.`slug`
  );
