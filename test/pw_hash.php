<?php
    function print_pw_hash($pw)
    {
        echo "<p><strong>" . $pw . "</strong>: " . password_hash($pw, PASSWORD_DEFAULT) . "</p>";
    }
    print_pw_hash("hanuele");
    print_pw_hash("tpf-admin");
?>
