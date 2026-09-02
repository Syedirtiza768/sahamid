-- Read-only BI expense report support indexes.
--
-- The expense report reads pcashdetails at claim grain and applies the same
-- expense_listing_access tab scope as the operational listing. These indexes
-- are additive and do not change accounting data or posting behavior.

SET @bi_expense_sql = IF(
    EXISTS(
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'pcashdetails'
          AND index_name = 'idx_pcashdetails_bi_date'
    ),
    'SELECT 1',
    'ALTER TABLE pcashdetails ADD INDEX idx_pcashdetails_bi_date (date, codeexpense, tabcode)'
);
PREPARE bi_expense_statement FROM @bi_expense_sql;
EXECUTE bi_expense_statement;
DEALLOCATE PREPARE bi_expense_statement;

SET @bi_expense_sql = IF(
    EXISTS(
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'pcashdetails'
          AND index_name = 'idx_pcashdetails_bi_tab_date'
    ),
    'SELECT 1',
    'ALTER TABLE pcashdetails ADD INDEX idx_pcashdetails_bi_tab_date (tabcode, date)'
);
PREPARE bi_expense_statement FROM @bi_expense_sql;
EXECUTE bi_expense_statement;
DEALLOCATE PREPARE bi_expense_statement;

SET @bi_expense_sql = IF(
    EXISTS(
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'expense_listing_access'
          AND index_name = 'idx_expense_access_bi_user_tab'
    ),
    'SELECT 1',
    'ALTER TABLE expense_listing_access ADD INDEX idx_expense_access_bi_user_tab (`user`, can_access)'
);
PREPARE bi_expense_statement FROM @bi_expense_sql;
EXECUTE bi_expense_statement;
DEALLOCATE PREPARE bi_expense_statement;
