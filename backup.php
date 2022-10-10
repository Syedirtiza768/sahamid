<?php
// This script was created by phpMyBackupPro v.2.4 (http://www.phpMyBackupPro.net)
// In order to work probably, it must be saved in the directory C:/xampp/htdocs/saherp/.
$_POST['db']=array("sah_saherp", );
$_POST['tables']="on";
$_POST['data']="on";
$_POST['drop']="on";
$period=(3600*24)*0;
$security_key="0f0d9fd57b0f4af815fad029141d0fac";
// switch to the phpMyBackupPro v.2.4 directory
@chdir("C:/xampp/htdocs/phpBackup");
@include("backup.php");
// switch back to the directory containing this script
@chdir("C:/xampp/htdocs/saherp/");
?>