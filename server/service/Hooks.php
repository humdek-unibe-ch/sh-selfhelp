<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

/**
 * A class that handles all hooks
 */
class Hooks
{
    /* Constants ************************************************/

    /* Hooks */
    const HOOK_OUTPUT_STYLE_FIELD = 'outputStyleField';
    const HOOK_GET_CSP_RULES = 'getCspRules';

    /**
     * The db instance which grants access to the DB.
     */
    private $db;

    /**
     * An associative array holding the different available services. See the
     * class definition basepage for a list of all services.
     */
    private $services;

    /**
     * Creating a Transaction Instance.
     *
     * @param object $services
     *  The service handler instance which holds all services
     */
    public function __construct($services)
    {
        $this->db = $services->get_db();
        $this->services = $services;
        $this->schedule_hook_on_function_execute();
        $this->schedule_hook_overwrite_return();
    }

    /* Private Methods *********************************************************/


    /**
     * Check DB for entered functions to be watched and on entering the function execute the scheduled method in the scheduled class
     */
    private function schedule_hook_on_function_execute()
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_HOOKS, $this->db->get_cache()::CACHE_ALL, [__FUNCTION__, hookTypes_hook_on_function_execute]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result === false) {
            $sql = 'SELECT class, `function`, exec_class, exec_function
            FROM hooks h
            INNER JOIN  lookups l ON (l.id = h.id_hookTypes)
            WHERE l.lookup_code = :lookup_code';
            $get_result = $this->db->query_db($sql, array(":lookup_code" => hookTypes_hook_on_function_execute));
        }
        $services = $this->services;
        foreach ($get_result as $key => $hook) {
            if (class_exists($hook['exec_class'])) {
                $hookClassInstance = new $hook['exec_class']($services);
                if (method_exists($hookClassInstance, $hook['exec_function'])) {
                    uopz_set_hook($hook['class'], $hook['function'], function () use ($hookClassInstance, $hook) {
                        $hookClassInstance->{$hook['exec_function']}();
                    });
                }
            }
        }
    }

    /**
     * Check DB for hooks from type `hookTypes_hook_overwrite_return`
     */
    private function schedule_hook_overwrite_return()
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_HOOKS, $this->db->get_cache()::CACHE_ALL, [__FUNCTION__, hookTypes_hook_overwrite_return]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result === false) {
            $sql = 'SELECT class, `function`, exec_class, exec_function
            FROM hooks h
            INNER JOIN  lookups l ON (l.id = h.id_hookTypes)
            WHERE l.lookup_code = :lookup_code';
            $get_result = $this->db->query_db($sql, array(":lookup_code" => hookTypes_hook_overwrite_return));
        }
        $services = $this->services;
        foreach ($get_result as $key => $hook) {
            if (class_exists($hook['exec_class'])) {
                $hookClassInstance = new $hook['exec_class']($services);
                if (method_exists($hookClassInstance, $hook['exec_function'])) {
                    uopz_set_return($hook['class'], $hook['function'], function (...$args) use ($hookClassInstance, $hook) {
                        return $hookClassInstance->{$hook['exec_function']}($this, $hook['function'], $args);
                    }, true);
                }
            }
        }
    }

    /* Public Methods *********************************************************/

}
?>
