<?php

/**
 * Clockwork PHP Debugging Integration
 * 
 * This file provides a wrapper for the Clockwork debugging tool in vanilla PHP.
 * It allows for performance monitoring, logging, and debugging of PHP applications.
 */

// Load Clockwork via Composer autoloader
require_once __DIR__ . '/server/service/ext/clockwork/vendor/autoload.php';


$clockwork = Clockwork\Support\Vanilla\Clockwork::init([
    'storage_files_path' => __DIR__ . '/data/clockwork',
]);

return $clockwork->handleMetadata();
