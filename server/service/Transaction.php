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

    /* Transaction Codes */
    const TRAN_CODE_INSERT = 'insert';
    const TRAN_CODE_UPDATE = 'update';
    const TRAN_CODE_DELETE = 'delete';
    const TRAN_CODE_SELECT = 'select';

    /* Table names */
    const TABLE_PAGES = 'pages';

    const TRANSACTIONS_TABLE = 'transactions';
    const LOOKUP_TRANSACTION_TYPE = 'transactionTypes';



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
     * Add transaction for the view page by the curent user. It record the table pages and the id of the page
     * @param array @page_info 
     * page info from fetch_page_info
     */
    public function add_page_view_transaction($page_info)
    {
        $log = array(
            "verbal_log" => 'UserID: ' . $_SESSION['id_user'] . ' visited page: ' . $_SERVER['REQUEST_URI'],
            "url" => $_SERVER['REQUEST_URI'],
            "session" => $_SESSION
        );
        $this->db->insert(Transaction::TRANSACTIONS_TABLE, array(
            "id_transactionTypes" => $this->db->get_lookup_id_by_code(Transaction::LOOKUP_TRANSACTION_TYPE, Transaction::TRAN_CODE_SELECT),
            "id_users" => $_SESSION['id_user'],
            "table_name" => Transaction::TABLE_PAGES,
            "id_table_name" => $page_info['id'],
            "transaction_log" => json_encode($log)
        ));
    }
}
?>
