-- Directors dashboard: two more additive, idempotent indexes found by EXPLAIN on a realistic copy.
-- Apply once per company, outside peak hours (index builds lock writes on the table briefly).
-- Does not alter business records, source status or financial definitions.
--
-- gltrans (account, trandate): the P&L measures read journals per account within a date window.
--   Measured on the local copy: net profit 0.7 s -> 0.22 s, operating costs 0.3 s -> 0.1 s.
-- ocoptions (orderno, lineno, optionno): the order-confirmation value lookup used by "PO received"
--   (also by the Sales Executive Report) scanned all of ocoptions for every order.
-- The ERP's legacy tables carry zero-date defaults (for example gltrans.trandate). A server running a strict
-- SQL mode (NO_ZERO_DATE) refuses to rebuild such a table definition: "#1067 Invalid default value".
-- Relax the mode for THIS SESSION only while the indexes are added, then put it back. No data is touched.
SET @directors_saved_sql_mode := @@SESSION.sql_mode;
SET SESSION sql_mode = '';

SET @directors_index_sql := (SELECT IF(COUNT(*)=0,'ALTER TABLE `gltrans` ADD INDEX `idx_directors_account_date` (account,trandate)','SELECT 1') FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'gltrans' AND index_name = 'idx_directors_account_date');
PREPARE directors_index_stmt FROM @directors_index_sql;
EXECUTE directors_index_stmt;
DEALLOCATE PREPARE directors_index_stmt;

SET @directors_index_sql := (SELECT IF(COUNT(*)=0,'ALTER TABLE `ocoptions` ADD INDEX `idx_directors_oc_option` (orderno,lineno,optionno)','SELECT 1') FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'ocoptions' AND index_name = 'idx_directors_oc_option');
PREPARE directors_index_stmt FROM @directors_index_sql;
EXECUTE directors_index_stmt;
DEALLOCATE PREPARE directors_index_stmt;

SET SESSION sql_mode = @directors_saved_sql_mode;
