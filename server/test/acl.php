<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../service/Acl.php";
require_once __DIR__ . "/../service/BaseDb.php";
require_once __DIR__ . "/../service/globals_untracked.php";

$db = new BaseDb(DBSERVER, DBNAME, DBUSER, DBPW);
$acl = new Acl($db);
$id_user = 1;
$id_page = 1;

function print_res($assert, $res)
{
    if($assert == $res) echo '<span style="color:green">success</span>';
    else echo '<span style="padding:2px; background-color:red; color:white">fail</span>';
}
function assert_all_access_levels($assert)
{
    global $acl, $id_user, $id_page;
    print_res($assert[0], $acl->has_access_select($id_user, $id_page));
    echo ", ";
    print_res($assert[1], $acl->has_access_insert($id_user, $id_page));
    echo ", ";
    print_res($assert[2], $acl->has_access_update($id_user, $id_page));
    echo ", ";
    print_res($assert[3], $acl->has_access_delete($id_user, $id_page));
}
echo "<p>Check all access rights: ";
assert_all_access_levels(array(false, false, false, false));
echo "</p>";
echo "<p>Grant select access: ";
print_res(true, $acl->grant_access_select($id_user, $id_page));
echo "</p>";
echo "<p>Grant insert access: ";
print_res(true, $acl->grant_access_insert($id_user, $id_page));
echo "</p>";
echo "<p>Grant update access: ";
print_res(true, $acl->grant_access_update($id_user, $id_page));
echo "</p>";
echo "<p>Grant delete access: ";
print_res(true, $acl->grant_access_delete($id_user, $id_page));
echo "</p>";
echo "<p>Check all access rights: ";
assert_all_access_levels(array(true, true, true, true));
echo "</p>";
echo "<p>Revoke select access: ";
print_res(true, $acl->revoke_access_select($id_user, $id_page));
echo "</p>";
echo "<p>Revoke insert access: ";
print_res(true, $acl->revoke_access_insert($id_user, $id_page));
echo "</p>";
echo "<p>Revoke update access: ";
print_res(true, $acl->revoke_access_update($id_user, $id_page));
echo "</p>";
echo "<p>Revoke delete access: ";
print_res(true, $acl->revoke_access_delete($id_user, $id_page));
echo "</p>";
echo "<p>Check all access rights: ";
assert_all_access_levels(array(false, false, false, false));
echo "</p>";


echo "<p>Grant level 4 access: ";
print_res(true, $acl->grant_access_levels($id_user, $id_page, 4));
echo "</p>";
echo "<p>Check all access rights: ";
assert_all_access_levels(array(true, true, true, true));
echo "</p>";
echo "<p>Revoke level 4 access: ";
print_res(true, $acl->revoke_access_levels($id_user, $id_page, 1));
echo "</p>";
echo "<p>Check all access rights: ";
assert_all_access_levels(array(false, false, false, false));
echo "</p>";
echo "<p>Grant level 10 access: ";
print_res(true, $acl->grant_access_levels($id_user, $id_page, 10));
echo "</p>";
echo "<p>Check all access rights: ";
assert_all_access_levels(array(true, true, true, true));
echo "</p>";
echo "<p>Revoke level 8 access: ";
print_res(true, $acl->revoke_access_levels($id_user, $id_page, 8));
echo "</p>";
echo "<p>Check all access rights: ";
assert_all_access_levels(array(true, true, true, true));
echo "</p>";
echo "<p>Revoke level 4 access: ";
print_res(true, $acl->revoke_access_levels($id_user, $id_page, 4));
echo "</p>";
echo "<p>Check all access rights: ";
assert_all_access_levels(array(true, true, true, false));
echo "</p>";
echo "<p>Grant level 3 access: ";
print_res(true, $acl->grant_access_levels($id_user, $id_page, 3));
echo "</p>";
echo "<p>Check all access rights: ";
assert_all_access_levels(array(true, true, true, false));
echo "</p>";
echo "<p>Revoke level 2 access: ";
print_res(true, $acl->revoke_access_levels($id_user, $id_page, 2));
echo "</p>";
echo "<p>Check all access rights: ";
assert_all_access_levels(array(true, false, false, false));
echo "</p>";
echo "<p>Revoke level -10 access: ";
print_res(true, $acl->revoke_access_levels($id_user, $id_page, -10));
echo "</p>";
echo "<p>Check all access rights: ";
assert_all_access_levels(array(false, false, false, false));
echo "</p>";
echo "<p>Grant level 0 access: ";
print_res(true, $acl->grant_access_levels($id_user, $id_page, 0));
echo "</p>";
echo "<p>Check all access rights: ";
assert_all_access_levels(array(false, false, false, false));
echo "</p>";

?>
