<?php
/**
 * Small Composer-compatible bootstrap for the embedded BI module.
 *
 * The legacy application does not currently declare a project autoloader, so
 * this keeps the BI module usable before a composer dump-autoload is run while
 * still loading the repository Composer autoloader when it exists.
 */

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
	require_once dirname(__DIR__) . '/vendor/autoload.php';
}

spl_autoload_register(function ($class) {
	$prefix = 'SAHamid\\BI\\';
	$prefixLength = strlen($prefix);
	if (strncmp($class, $prefix, $prefixLength) !== 0) {
		return;
	}

	$relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, $prefixLength));
	$file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bi' . DIRECTORY_SEPARATOR . $relative . '.php';
	if (is_file($file)) {
		require_once $file;
	}
});
