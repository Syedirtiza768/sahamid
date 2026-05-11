-- IB form sheet (from "ib form sheet.xlsx" header row)
-- Run once against the company database (e.g. sah_saherp / sah_amid per your install).

CREATE TABLE IF NOT EXISTS ib_form_sheet_entries (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  period_month DATE NOT NULL COMMENT 'First day of calendar month',
  total_payment_gst DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  total_payment_nongst_cash DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  total_payment_international DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  total_payment_freightward DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  total_advance_payment DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  entered_by VARCHAR(20) NOT NULL DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_ib_form_period (period_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
