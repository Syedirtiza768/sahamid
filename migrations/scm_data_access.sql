-- Restrict SCM data (v3/ib_form_sheet.php, its Excel template and api/ib_form_sheet_data.php) to named users.
-- Before this, every logged-in user could view, edit, delete and bulk-import SCM entries.
-- Users holding the '*' permission keep access without a row here.
-- Idempotent: re-running adds nothing that already exists.

INSERT INTO user_permission (userid, permission)
SELECT u.userid, 'scm_data'
FROM www_users u
WHERE u.userid IN ('ali_shabbar', 'imran_butt')
  AND NOT EXISTS (
      SELECT 1 FROM user_permission up
      WHERE up.userid = u.userid AND up.permission = 'scm_data'
  );

-- Review after running: everyone who can now open SCM data (explicit grant or '*').
-- SELECT userid, permission FROM user_permission WHERE permission IN ('scm_data', '*') ORDER BY userid;

-- Rollback (removes the grant; the code will then hide SCM data from these users too):
-- DELETE FROM user_permission WHERE permission = 'scm_data' AND userid IN ('ali_shabbar', 'imran_butt');
