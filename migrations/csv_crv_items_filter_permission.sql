-- Permission for the CSV / CRV shop-sale items report (csvcrvitemsfilter.php, csvcrvitems.php).
-- Grant it per user from the user permissions screen, or with an INSERT into user_permission.
INSERT INTO `permissions` (`name`, `slug`, `description`)
SELECT 'csv crv items filter',
       'csv_crv_items_filter',
       'csvcrvitemsfilter.php'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM `permissions`
    WHERE `slug` = 'csv_crv_items_filter'
);
