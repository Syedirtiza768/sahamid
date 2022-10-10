<?php

/* $Id: footer.inc 5784 2012-12-29 04:00:43Z daintree $*/

echo '</div>'; // BodyWrapDiv
echo '</div>'; // BodyDiv

echo '<div id="FooterDiv">';
echo '<div id="FooterWrapDiv">';

echo '<div id="FooterLogoDiv">';
	echo '<img src="'. $RootPath . '/' . $_SESSION['LogoFile'] . '" width="120" alt="webERP" title="webERP ' . _('Copyright') . ' &copy; weberp.org - ' . date('Y') . '" />';
echo '</div>';



echo '<div id="FooterTimeDiv">';
	echo DisplayDateTime();
echo '</div>';

echo '</div>'; // FooterWrapDiv
echo '</div>'; // FooterDiv
echo '</div>'; // Canvas

echo '</body>';
echo '</html>';

?>