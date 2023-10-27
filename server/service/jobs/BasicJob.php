<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

require_once __DIR__ . "/../ext/PHPMailer.php";
require_once __DIR__ . "/../ext/PHPMailer_Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class BasicJob extends PHPMailer
{

    /**
     * The db instance which grants access to the DB.
     */
    protected $db;

    /**
     * The transaction instance that log to DB.
     */
    protected $transaction;

    /**
     * The condtion instance.
     */
    protected $condition;


    /**
     * Creating a PHPMailer Instance.
     *
     * @param object $db
     *  An instcance of the service class PageDb.
     */
    public function __construct($db, $transaction, $condition)
    {
        $this->db = $db;
        $this->transaction = $transaction;
        $this->condition = $condition;
        parent::__construct(false);
    }

    protected function check_condition($condition, $id_users)
    {
        $condition_object =  json_decode($condition, true);
        return $this->condition->compute_condition($condition_object, $id_users, 'jobScheduler')['result'];
    }

}

?>