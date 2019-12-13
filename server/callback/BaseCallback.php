<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

/**
 * The base class for callback requests.
 */
abstract class BaseCallback
{
    /**
     *  The router instance is used to generate valid links.
     */
    protected $router;

    /**
     *  The db instance which grants access to the DB.
     */
    protected $db;

    /**
     * The instance to the navigation service which allows to switch between
     * sections, associated to a specific page.
     */
    protected $nav;

    /**
     * The login instance that allows to check user credentials.
     */
    protected $login;

    /**
     * The instnce of the access control layer (ACL) which allows to decide
     * which links to display.
     */
    protected $acl;

    /**
     * The instance of the parsedown service.
     */
    protected $parsedown;

    /**
     * User input handler.
     */
    protected $user_input;

    /**
     * The constructor.
     *
     * @param object $services
     *  The service handler instance which holds all services
     */
    public function __construct($services)
    {
        $this->router = $services->get_router();
        $this->db = $services->get_db();
        $this->acl = $services->get_acl();
        $this->login = $services->get_login();
        $this->nav = $services->get_nav();
        $this->parsedown = $services->get_parsedown();
        $this->user_input = $services->get_user_input();
    }

    public function insert_callback_log($log, $params)
    { 
        $callback_id = $this->db->insert("callbackLogs", array(
            "callback_date" => 'NOW()',
            "remote_addr" => $log['REMOTE_ADDR'],
            "callback_params" => json_encode($params),
            "status" => "init",
            "log" => json_encode($log)
        ));
        return $callback_id;
    }
}
?>
