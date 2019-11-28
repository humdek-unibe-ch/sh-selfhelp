<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
    function print_pw_hash($pw)
    {
        echo "<p><strong>" . $pw . "</strong>: " . password_hash($pw, PASSWORD_DEFAULT) . "</p>";
    }
    print_pw_hash("hanuele");
    print_pw_hash("admin");
    print_pw_hash("experimenter");
    print_pw_hash("subject");
?>
