<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

/**
 * A class that handles transaction records
 */
class Transaction
{

    /* Constants ************************************************/

    /* Transaction Types */
    const TRAN_TYPE_INSERT = 'insert';
    const TRAN_TYPE_UPDATE = 'update';
    const TRAN_TYPE_DELETE = 'delete';
    const TRAN_TYPE_SELECT = 'select';
    const TRAN_TYPE_SEND_MAIL_OK = 'send_mail_ok';
    const TRAN_TYPE_SEND_MAIL_FAIL = 'send_mail_fail';
    const TRAN_TYPE_CHECK_MAILQUEUE = 'check_mailQueue';

    /* Transaction By */
    const TRAN_BY_USER = 'by_user';
    const TRAN_BY_MAIL_CRON = 'by_mail_cron';
    const TRAN_BY_QUALTRICS_CALLBACK = 'by_qualtrics_callback';

    /* Table names */
    const TABLE_PAGES = 'pages';
    const TABLE_MAILQUEUE = 'mailQueue';
    const TABLE_USERS = 'users';

    const TRANSACTIONS_TABLE = 'transactions';
    const LOOKUP_TRANSACTION_TYPE = 'transactionTypes';
    const LOOKUP_TRANSACTION_BY = 'transactionBy';



    /**
     * The db instance which grants access to the DB.
     */
    private $db;

    /**
     * Creating a PHPMailer Instance.
     *
     * @param object $db
     *  An instcance of the service class PageDb.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /* Public Methods *********************************************************/

    /**
     * Add a transaction which is used as log
     * 
     * @param string $tran_type
     * the trnsaction type
     * @param string $tran_by
     * action was triggered by
     * @param int $user_id default null
     * user id
     * @param string $table_name default null
     * the name of the table that is linked to the transaction
     * @param int $entry_id default null
     * the id of the row from the table that is linked to the transaction
     * @param $log_row default false
     * if log row is enabled then the row entry is kept in the log. It could be used for modifications of the row
     * @retval int
     *  The inserted id if succeded, false otherwise.
     */
    public function add_transaction($tran_type, $tran_by, $user_id = null, $table_name = null, $entry_id = null, $log_row = false, $verbal_log = null)
    {
        $log = array(
            "verbal_log" => $verbal_log ? $verbal_log : ('Transaction type: `' . $tran_type . '` from table: `' . $table_name . '` triggered ' . $tran_by),
            "url" => $user_id > 0 ? $_SERVER['REQUEST_URI'] : "",
            "session" => $user_id > 0 ? $_SESSION : ""
        );
        if ($table_name && $entry_id && $log_row) {
            $entry = $this->db->query_db_first('SELECT * FROM ' . $table_name . ' WHERE id = :id;', array(":id" => $entry_id));
            $log['table_row_entry'] = $entry;
        }
        return $this->db->insert(Transaction::TRANSACTIONS_TABLE, array(
            "id_transactionTypes" => $this->db->get_lookup_id_by_code(Transaction::LOOKUP_TRANSACTION_TYPE, $tran_type),
            "id_transactionBy" => $this->db->get_lookup_id_by_code(Transaction::LOOKUP_TRANSACTION_BY, $tran_by),
            "id_users" => $user_id,
            "table_name" => $table_name,
            "id_table_name" => $entry_id,
            "transaction_log" => json_encode($log)
        ));
    }
}
?>
