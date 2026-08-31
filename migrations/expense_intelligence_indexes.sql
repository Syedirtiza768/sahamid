-- Expense Intelligence report indexes.
--
-- Additive and idempotent. Review/apply once per company database. The
-- report's primary predicate is the petty-cash claim date range; the legacy
-- pcashdetails table previously had only its counterindex primary key.

SET @expense_bi_index_sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE pcashdetails ADD INDEX idx_pcashdetails_bi_date (date)',
        'SELECT 1'
    )
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'pcashdetails'
      AND index_name = 'idx_pcashdetails_bi_date'
);

PREPARE expense_bi_index_stmt FROM @expense_bi_index_sql;
EXECUTE expense_bi_index_stmt;
DEALLOCATE PREPARE expense_bi_index_stmt;
