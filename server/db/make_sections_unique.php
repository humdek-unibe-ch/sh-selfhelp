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
