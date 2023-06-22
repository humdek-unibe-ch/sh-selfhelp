<?php
header("X-XSS-Protection: 1; mode=block");
header("X-Frame-Options: SAMEORIGIN");
$_SERVER['DOCUMENT_ROOT'] = __DIR__;
require_once "./Selfhelp.php";


if (defined('SHOW_PHP_INFO') && SHOW_PHP_INFO) {
    echo phpinfo();
    return;
}

/**
 * Helper function to show stacktrace also of warnings.
 */
function exception_error_handler($severity, $message, $file, $line)
{
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}

$selfhelp = new Selfhelp();
