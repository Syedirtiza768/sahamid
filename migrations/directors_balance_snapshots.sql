-- Directors' dashboard: one row per snapshot measure per day.
-- Additive only. It captures balances from the day the job first runs; it never rebuilds history,
-- because allocations and costs can be edited after the fact.
-- Idempotent: re-running the capture for the same day updates that day's row.
CREATE TABLE IF NOT EXISTS `directors_balance_snapshots` (
  `snapshot_date` date NOT NULL,
  `measure` varchar(40) NOT NULL,
  `value` decimal(20,2) DEFAULT NULL COMMENT 'NULL when every source amount was unavailable',
  `overdue` decimal(20,2) DEFAULT NULL,
  `row_count` int(11) NOT NULL DEFAULT 0,
  `invalid_amounts` int(11) NOT NULL DEFAULT 0,
  `captured_at` datetime NOT NULL,
  PRIMARY KEY (`snapshot_date`,`measure`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- Reason for the key: reads are always "one measure over a date range" or "all measures for a day",
-- both served by the primary key; no secondary index is needed at one row per measure per day.
