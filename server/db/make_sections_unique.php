<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

function make_sections_unique($db)
{
    $sql = "SELECT name, COUNT(*) FROM sections GROUP BY name HAVING COUNT(*) > 1";
    $multiples = $db->query_db($sql);
    foreach($multiples as $multiple)
    {
        $sql = "SELECT * FROM sections WHERE name = :name";
        $res = $db->query_db($sql, array(':name' => $multiple['name']));
        foreach($res as $index => $item)
        {
            if($index === 0) continue;
            $db->update_by_ids('sections',
                array('name' => $multiple['name']. '-' . $index),
                array('id' => $item['id'])
            );
        }
    }
}

?>
