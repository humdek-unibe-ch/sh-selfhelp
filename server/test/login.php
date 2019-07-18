<?php
require_once __DIR__ . "/../service/Login.php";
require_once __DIR__ . "/../service/BaseDb.php";
require_once __DIR__ . "/../service/globals_untracked.php";

$db = new BaseDb(DBSERVER, DBNAME, DBUSER, DBPW);
$login = new Login($db);
echo "<p>Test access denied bad password: ";
echo ($login->check_credentials("me@mydomain.com", "wotuele")) ? "failed" : "success";
echo "</p>";
echo "<p>Test access denied bad email: ";
echo ($login->check_credentials("you@yourdomain.com", "hanuele")) ? "failed" : "success";
echo "</p>";
echo "<p>Test access granted: ";
echo ($login->check_credentials("me@mydomain.com", "hanuele")) ? "success" : "failed";
echo "</p>";
?>
