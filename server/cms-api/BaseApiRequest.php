<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

abstract class BaseApiRequest
{
    protected $router;
    protected $db;
    protected $acl;
    protected $login;
    protected $nav;
    protected $parsedown;
    protected $user_input;
    protected $keyword;
    protected $response = array();

    public function __construct($services, $keyword)
    {
        $this->router = $services->get_router();
        $this->db = $services->get_db();
        $this->acl = $services->get_acl();
        $this->login = $services->get_login();
        $this->nav = $services->get_nav();
        $this->parsedown = $services->get_parsedown();
        $this->user_input = $services->get_user_input();
        $this->keyword = $keyword;
    }

    private function has_access()
    {
        $page_id = $this->db->fetch_page_id_by_keyword($this->keyword);
        return $this->acl->has_access($_SESSION['id_user'], $page_id, 'select');
    }

    private function get_response_code_message($code)
    {
        $messages = [
            200 => 'OK', 201 => 'Created', 400 => 'Bad Request', 401 => 'Unauthorized', 403 => 'Forbidden',
            404 => 'Not Found', 405 => 'Method Not Allowed', 500 => 'Internal Server Error'
        ];
        return $messages[$code] ?? 'Unknown status code';
    }

    public function authorizeUser()
    {
        return $this->has_access()
            ? array("timestamp" => date("Y-m-d H:i:s"), "status" => 200, "message" => $this->get_response_code_message(200))
            : array("timestamp" => date("Y-m-d H:i:s"), "status" => 401, "message" => $this->get_response_code_message(401));
    }

    public function init_response($response)
    {
        $this->response = $response;
    }

    public function get_response()
    {
        return $this->response;
    }

    public function set_response($response)
    {
        $this->response['response'] = $response;
    }

    public function set_status($status)
    {
        $this->response['status'] = $status;
        $this->response['message'] = $this->get_response_code_message($status);
    }

    public function set_error_message($error_message)
    {
        $this->response['error_message'] = $error_message;
    }

    public function return_response()
    {
        header('Content-Type: application/json');
        echo json_encode($this->response);
    }
}
?>
