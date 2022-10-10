<?php
/* $Id: Login.php 7535 2016-05-20 13:43:16Z rchacon $*/
// Display demo user name and password within login form if $AllowDemoMode is true

//include ('LanguageSetup.php');
if ((isset($AllowDemoMode)) AND ($AllowDemoMode == True) AND (!isset($demo_text))) {
    $demo_text = _('Login as user') . ': <i>' . _('admin') . '</i><br />' . _('with password') . ': <i>' . _('weberp') . '</i>' .
        '<br /><a href="../">' . _('Return') . '</a>';// This line is to add a return link.
} elseif (!isset($demo_text)) {
    $demo_text = '';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title>webERP Login screen</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
        <link href="<?php echo $RootPath ?>/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="css/default/login-anim-bg.css" type="text/css"/>
        <style>
            .alert{
                -webkit-border-radius:0;
                -moz-border-radius:0;
                border-radius:0;
            }
            .main-logo{
                text-align: center;
            }
            .main-logo img{
                width: 60%;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        <div class="vid-container">
            <video id="Video1" class="bgvid back" autoplay="false" muted="muted" preload="auto" loop>
                <source src="video/login-bg.mp4" type="video/mp4">
            </video>
            <?php if (get_magic_quotes_gpc()) { ?>
                <div class="alert alert-warning"><?php  echo _('Your webserver is configured to enable Magic Quotes. This may cause problems if you use punctuation (such as quotes) when doing data entry. You should contact your webmaster to disable Magic Quotes'); ?></div>
            <?php } ?>
            <?php if ($demo_text) { ?>
                <div class="alert alert-danger text-center"><?php echo $demo_text; ?></div>
            <?php } ?>
            <div class="inner-container">
                <video id="Video2" class="bgvid inner" autoplay="false" muted="muted" preload="auto" loop>
                    <source src="video/login-bg.mp4" type="video/mp4">
                </video>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="post">
                    <div class="box">
                        <div class="main-logo">
                            <img src="<?php echo $RootPath ?>/css/webERP.gif" alt="">
                        </div>
                        <input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>"/>

                        <?php

                        if (isset($CompanyList) AND is_array($CompanyList)) {
                            foreach ($CompanyList as $key => $CompanyEntry) {
                                if ($DefaultDatabase == $CompanyEntry['database']) {
                                    $CompanyNameField = "$key";
                                    $DefaultCompany = $CompanyEntry['company'];
                                }
                            }
                            if ($AllowCompanySelectionBox === 'Hide') {
                                // do not show input or selection box
                                echo '<input type="hidden" name="CompanyNameField"  value="' . $CompanyNameField . '" />';
                            } elseif ($AllowCompanySelectionBox === 'ShowInputBox') {
                                // show input box
                                echo '<input type="text" name="DefaultCompany"  autofocus="autofocus" required="required" value="' . htmlspecialchars($DefaultCompany, ENT_QUOTES, 'UTF-8') . '" disabled="disabled"/>';//use disabled input for display consistency
                                echo '<input type="hidden" name="CompanyNameField"  value="' . $CompanyNameField . '" />';
                            } else {
                                // Show selection box ($AllowCompanySelectionBox == 'ShowSelectionBox')
                                echo '<select name="CompanyNameField">';
                                foreach ($CompanyList as $key => $CompanyEntry) {
                                    if (is_dir('companies/' . $CompanyEntry['database'])) {
                                        if ($CompanyEntry['database'] == $DefaultDatabase) {
                                            echo '<option selected="selected" label="' . htmlspecialchars($CompanyEntry['company'], ENT_QUOTES, 'UTF-8') . '" value="' . $key . '">' . htmlspecialchars($CompanyEntry['company'], ENT_QUOTES, 'UTF-8') . '</option>';
                                        } else {
                                            echo '<option label="' . htmlspecialchars($CompanyEntry['company'], ENT_QUOTES, 'UTF-8') . '" value="' . $key . '">' . htmlspecialchars($CompanyEntry['company'], ENT_QUOTES, 'UTF-8') . '</option>';
                                        }
                                    }
                                }
                                echo '</select>';
                            }
                        } else { //provision for backward compat - remove when we have a reliable upgrade for config.php
                            if ($AllowCompanySelectionBox === 'Hide') {
                                // do not show input or selection box
                                echo '<input type="hidden" name="CompanyNameField"  value="' . $DefaultCompany . '" />';
                            } else if ($AllowCompanySelectionBox === 'ShowInputBox') {
                                // show input box
                                echo '<input type="text" name="CompanyNameField"  autofocus="autofocus" required="required" value="' . $DefaultCompany . '" />';
                            } else {
                                // Show selection box ($AllowCompanySelectionBox == 'ShowSelectionBox')
                                echo '<select name="CompanyNameField">';
                                $Companies = scandir('companies/', 0);
                                foreach ($Companies as $CompanyEntry) {
                                    if (is_dir('companies/' . $CompanyEntry) AND $CompanyEntry != '..' AND $CompanyEntry != '' AND $CompanyEntry != '.svn' AND $CompanyEntry != '.') {
                                        if ($CompanyEntry == $DefaultDatabase) {
                                            echo '<option selected="selected" label="' . $CompanyEntry . '" value="' . $CompanyEntry . '">' . $CompanyEntry . '</option>';
                                        } else {
                                            echo '<option label="' . $CompanyEntry . '" value="' . $CompanyEntry . '">' . $CompanyEntry . '</option>';
                                        }
                                    }
                                }
                                echo '</select>';
                            }
                        } //end provision for backward compat
                        ?>
                        <input type="text" name="UserNameEntryField" required="required" autofocus="autofocus" maxlength="20" placeholder="<?php echo _('Username'); ?>"/>
                        <input type="password" required="required" name="Password" placeholder="<?php echo _('Password'); ?>"/>
                        <button type="submit" value="<?php echo _('Login'); ?>" name="SubmitUser">Login</button>
                    </div>
                </form>
            </div>
        </div>
        <script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
        <script src="javascript/login-anim-bg.js"></script>
    </body>
</html>