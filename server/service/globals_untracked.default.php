<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
define('REDIRECT_ON_LOGIN', true);
define('PROJECT_NAME', '__project_name__');
define('DEBUG', 0);
define('CLOCKWORK_PROFILE', 0);
define('CORS', true);
// Origins that may call across origins when CORS is true (exact match: scheme, host, port).
// The default list is in globals.php; to replace it for this installation define it here:
// define('CORS_ALLOWED_ORIGINS', array('https://localhost:8100', 'https://app.example.org'));
define('LANGUAGE', 2); // German
#define('BASE_PATH', '/' . PROJECT_NAME);
define('BASE_PATH', '');
define('SHOW_PHP_INFO', false);
define('SESSION_TIMEOUT', 36000); // 1 hour

define('DBSERVER', 'localhost');
define('DBNAME', '__project_name__');
define('DBUSER', '__project_name__');
define('DBPW', '__password__');
?>
