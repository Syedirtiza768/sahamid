-- Performance indexes for shop/voucher/api/voucherList.php.
--
-- Run this script once against the production company database during a
-- low-traffic window. The ALTER statements are intentionally explicit so the
-- script can be run from phpMyAdmin or the mysql client without Docker.

ALTER TABLE `voucher`
    ADD INDEX `idx_voucher_list_type_created` (`type`, `created_at`),
    ADD INDEX `idx_voucher_list_user_created` (`type`, `user_name`, `created_at`),
    ADD INDEX `idx_voucher_list_pid_created` (`type`, `pid`, `created_at`),
    ADD INDEX `idx_voucher_list_salesman_created` (`type`, `salesman`(100), `created_at`);

ALTER TABLE `vendor_permission`
    ADD INDEX `idx_vendor_permission_user_permission` (`userid`, `permission`);
