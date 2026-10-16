-- Speeds up the Sales Performance report, which reads every quotation in the reporting period.
-- Without these indexes the report spends most of its time scanning salesorderoptions and quotation issues.
-- Safe to run more than once. Each index can be removed again with DROP INDEX if needed.

-- Quotation line options are joined on order, line and option number. Without this index each join scans the whole table.
CREATE INDEX IF NOT EXISTS idx_sp_options_order_line_option ON salesorderoptions (orderno, lineno, optionno);

-- The report lists quotations that already have an issued snapshot, filtered by issue kind.
CREATE INDEX IF NOT EXISTS idx_sp_quote_issue_kind_order ON sales_performance_quotation_issues (issue_kind, orderno);
