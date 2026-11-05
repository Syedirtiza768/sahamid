-- Additive, idempotent Directors Dashboard indexes. Apply once per company.
-- Does not alter business records, source status or financial definitions.
-- Check plans and index-build scheduling against the target company first.
-- The ERP's legacy tables carry zero-date defaults (for example gltrans.trandate). A server running a strict
-- SQL mode (NO_ZERO_DATE) refuses to rebuild such a table definition: "#1067 Invalid default value".
-- Relax the mode for THIS SESSION only while the indexes are added, then put it back. No data is touched.
SET @directors_saved_sql_mode := @@SESSION.sql_mode;
SET SESSION sql_mode = '';

SET @directors_index_sql := (SELECT IF(COUNT(*)=0,'ALTER TABLE `invoice` ADD INDEX `idx_directors_invoice_number` (invoiceno)','SELECT 1') FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='invoice' AND index_name='idx_directors_invoice_number');
PREPARE directors_index_stmt FROM @directors_index_sql;
EXECUTE directors_index_stmt;
DEALLOCATE PREPARE directors_index_stmt;

SET @directors_index_sql := (SELECT IF(COUNT(*)=0,'ALTER TABLE `invoiceoptions` ADD INDEX `idx_directors_invoice_option` (invoiceno,invoicelineno,invoiceoptionno)','SELECT 1') FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='invoiceoptions' AND index_name='idx_directors_invoice_option');
PREPARE directors_index_stmt FROM @directors_index_sql;
EXECUTE directors_index_stmt;
DEALLOCATE PREPARE directors_index_stmt;

SET @directors_index_sql := (SELECT IF(COUNT(*)=0,'ALTER TABLE `invoicedetails` ADD INDEX `idx_directors_invoice_detail` (invoiceno,invoicelineno,invoiceoptionno)','SELECT 1') FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='invoicedetails' AND index_name='idx_directors_invoice_detail');
PREPARE directors_index_stmt FROM @directors_index_sql;
EXECUTE directors_index_stmt;
DEALLOCATE PREPARE directors_index_stmt;

SET @directors_index_sql := (SELECT IF(COUNT(*)=0,'ALTER TABLE `shopsale` ADD INDEX `idx_directors_shop_order` (orderno)','SELECT 1') FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='shopsale' AND index_name='idx_directors_shop_order');
PREPARE directors_index_stmt FROM @directors_index_sql;
EXECUTE directors_index_stmt;
DEALLOCATE PREPARE directors_index_stmt;

SET @directors_index_sql := (SELECT IF(COUNT(*)=0,'ALTER TABLE `bpitems` ADD INDEX `idx_directors_market_slip` (parchino)','SELECT 1') FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='bpitems' AND index_name='idx_directors_market_slip');
PREPARE directors_index_stmt FROM @directors_index_sql;
EXECUTE directors_index_stmt;
DEALLOCATE PREPARE directors_index_stmt;

SET @directors_index_sql := (SELECT IF(COUNT(*)=0,'ALTER TABLE `dcs` ADD INDEX `idx_directors_dc_number` (orderno)','SELECT 1') FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='dcs' AND index_name='idx_directors_dc_number');
PREPARE directors_index_stmt FROM @directors_index_sql;
EXECUTE directors_index_stmt;
DEALLOCATE PREPARE directors_index_stmt;

SET @directors_index_sql := (SELECT IF(COUNT(*)=0,'ALTER TABLE `procurement_document_details` ADD INDEX `idx_directors_procurement_document` (pdid)','SELECT 1') FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='procurement_document_details' AND index_name='idx_directors_procurement_document');
PREPARE directors_index_stmt FROM @directors_index_sql;
EXECUTE directors_index_stmt;
DEALLOCATE PREPARE directors_index_stmt;

SET @directors_index_sql := (SELECT IF(COUNT(*)=0,'ALTER TABLE `pcashdetails` ADD INDEX `idx_directors_claim_date` (date)','SELECT 1') FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='pcashdetails' AND index_name='idx_directors_claim_date');
PREPARE directors_index_stmt FROM @directors_index_sql;
EXECUTE directors_index_stmt;
DEALLOCATE PREPARE directors_index_stmt;

SET @directors_index_sql := (SELECT IF(COUNT(*)=0,'ALTER TABLE `gltrans` ADD INDEX `idx_directors_posted_date_account` (posted,trandate,account)','SELECT 1') FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='gltrans' AND index_name='idx_directors_posted_date_account');
PREPARE directors_index_stmt FROM @directors_index_sql;
EXECUTE directors_index_stmt;
DEALLOCATE PREPARE directors_index_stmt;

SET SESSION sql_mode = @directors_saved_sql_mode;
