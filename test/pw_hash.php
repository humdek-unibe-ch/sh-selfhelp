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
