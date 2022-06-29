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
    const HOOK_OUTPUT_NAV_RIGHT = 'outputNavRight';
    const HOOK_OUTPUT_NAV = 'outputNav';

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
        // $this->db->get_cache()->clear_cache();
    }

    /* Private Methods *********************************************************/

    /**
     * Get all hooks - outputStyleField
     * @param int $id_fieldType
     * the id of the field type that will be presented
     * @return array
     * Array with plugins that are registered for that fieldType
     */
    private function getHooks_outputStyleField($id_fieldType)
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_HOOKS, $id_fieldType, [__FUNCTION__, Hooks::HOOK_OUTPUT_STYLE_FIELD]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = 'SELECT p.name AS plugin_name
                FROM hooks h
                INNER JOIN hooks_fieldTypes hft ON (hft.id_hooks = h.id)
                INNER JOIN `plugins` p ON (hft.id_plugins = p.id)
                WHERE hft.id_fieldType = :id_fieldType AND h.name = :hook_name';
            return $this->db->query_db($sql, array(":id_fieldType" => $id_fieldType, ":hook_name" => Hooks::HOOK_OUTPUT_STYLE_FIELD));
        }
    }

    /**
     * Get all hooks - getCspRules
     * @return array
     * Array with plugins that register csp rules
     */
    private function getHooks_getCspRules()
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_HOOKS, $this->db->get_cache()::CACHE_ALL, [__FUNCTION__, Hooks::HOOK_GET_CSP_RULES]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = 'SELECT p.name AS plugin_name
                FROM hooks h
                INNER JOIN hooks_plugins hp ON (hp.id_hooks = h.id)
                INNER JOIN `plugins` p ON (hp.id_plugins = p.id)
                WHERE h.name = :hook_name';
            return $this->db->query_db($sql, array(":hook_name" => Hooks::HOOK_GET_CSP_RULES));
        }
    }

    /**
     * Get all hooks - outputNavRight
     * @return array
     */
    private function getHooks_outputNavRight()
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_HOOKS, $this->db->get_cache()::CACHE_ALL, [__FUNCTION__, Hooks::HOOK_OUTPUT_NAV_RIGHT]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = 'SELECT p.name AS plugin_name
                FROM hooks h
                INNER JOIN hooks_plugins hp ON (hp.id_hooks = h.id)
                INNER JOIN `plugins` p ON (hp.id_plugins = p.id)
                WHERE h.name = :hook_name';
            return $this->db->query_db($sql, array(":hook_name" => Hooks::HOOK_OUTPUT_NAV_RIGHT));
        }
    }

    /**
     * Get all hooks - outputNav
     * @return array
     */
    private function getHooks_outputNav()
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_HOOKS, $this->db->get_cache()::CACHE_ALL, [__FUNCTION__, Hooks::HOOK_OUTPUT_NAV]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = 'SELECT p.name AS plugin_name
                FROM hooks h
                INNER JOIN hooks_plugins hp ON (hp.id_hooks = h.id)
                INNER JOIN `plugins` p ON (hp.id_plugins = p.id)
                WHERE h.name = :hook_name';
            return $this->db->query_db($sql, array(":hook_name" => Hooks::HOOK_OUTPUT_NAV));
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Load the plugin and execute the outputStyleField method from the hook class
     * @param object
     * Various params
     * @return object
     * Return a BaseStyleComponent object
     */
    public function outputStyleField($params)
    {
        foreach ($this->getHooks_outputStyleField($params['field']['id_fieldType']) as $key => $plugin) {
            $class_name = ucfirst($plugin['plugin_name']) . 'Hooks';
            if (class_exists($class_name)) {
                $hooks = new $class_name($this->services, $params);
                if (method_exists($hooks, Hooks::HOOK_OUTPUT_STYLE_FIELD)) {
                    return $hooks->outputStyleField();
                }
            }
        }
        return array();
    }

    /**
     * Load the plugin and execute the outputStyleField method from the hook class     
     * @return string
     * Return all plugins csp_rules
     */
    public function getCspRules()
    {
        $csp_rules = '';
        foreach ($this->getHooks_getCspRules() as $key => $plugin) {
            $class_name = ucfirst($plugin['plugin_name']) . 'Hooks';
            if (class_exists($class_name)) {
                $hooks = new $class_name($this->services);
                if (method_exists($hooks, Hooks::HOOK_GET_CSP_RULES)) {
                    $csp_rules = $csp_rules . $hooks->getCspRules();
                }
            }
        }
        return $csp_rules;
    }

    /**
     * Output an item in nav-right
     * @param object
     * Various params
     */
    public function outputNavRight()
    {
        foreach ($this->getHooks_outputNavRight() as $key => $plugin) {
            $class_name = ucfirst($plugin['plugin_name']) . 'Hooks';
            if (class_exists($class_name)) {
                $hooks = new $class_name($this->services);
                if (method_exists($hooks, Hooks::HOOK_OUTPUT_NAV_RIGHT)) {
                    $hooks->outputNavRight();
                }
            }
        }
    }

    /**
     * Output an item in nav-right
     * @param object
     * Various params
     */
    public function outputNav()
    {
        foreach ($this->getHooks_outputNav() as $key => $plugin) {
            $class_name = ucfirst($plugin['plugin_name']) . 'Hooks';
            if (class_exists($class_name)) {
                $hooks = new $class_name($this->services);
                if (method_exists($hooks, Hooks::HOOK_OUTPUT_NAV)) {
                    $hooks->outputNav();
                }
            }
        }
    }
}
?>
