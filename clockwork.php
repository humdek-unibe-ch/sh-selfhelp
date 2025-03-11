<?php

/**
 * Clockwork PHP Debugging Integration
 * 
 * This file serves as the API endpoint for the Clockwork browser extension.
 * @see https://underground.works/clockwork/
 */

// Load Clockwork via Composer autoloader
require_once __DIR__ . '/server/service/ext/clockwork/vendor/autoload.php';

// Initialize Clockwork
$clockwork = Clockwork\Support\Vanilla\Clockwork::init([
    'storage_files_path' => __DIR__ . '/data/clockwork',
    'register_helpers' => true
]);

// Handle the Clockwork request and return the appropriate response
return $clockwork->handleMetadata();
