<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>

<?php
require_once __DIR__ . "/../service/UserInput.php";
require_once __DIR__ . "/../service/BaseDb.php";
require_once __DIR__ . "/../service/globals_untracked.php";

$db = new BaseDb(DBSERVER, DBNAME, DBUSER, DBPW);
$ui = new UserInput($db);

function print_fields($rows)
{
    if(count($rows) < 1) return;
    echo "<table>";
    echo "<tr><th>" . implode('</th><th>', array_keys($rows[0])) . "</th></tr>";
    foreach($rows as $row)
        echo "<tr><td>" . implode('</td><td>', $row) . "</td></tr>";
    echo "</table>";
}
echo "<h3>Print All fields</h3>";
print_fields($ui->get_input_fields());

echo "<h3>Print fields of female users</h3>";
print_fields($ui->get_input_fields_by_gender_female());

echo "<h3>Print fields of male users</h3>";
print_fields($ui->get_input_fields_by_gender_male());

echo "<h3>Print fields of 'protocol' page</h3>";
print_fields($ui->get_input_fields_by_page("protocol"));

echo "<h3>Print fields of 'gedanken' nav</h3>";
print_fields($ui->get_input_fields_by_nav("gedanken"));

echo "<h3>Print 'my_number' fields</h3>";
print_fields($ui->get_input_fields_by_field_name("my_number"));

echo "<h3>Print 'my_number' fields of 'protocol' page and 'protocol1' nav from female users</h3>";
print_fields($ui->get_input_fields(array("gender" => "female", "page" => "protocol", "nav" => "protocol1", "field_name" => "my_number")));
?>
