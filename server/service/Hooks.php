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
        // $this->db->clear_cache();
    }

    /* Private Methods *********************************************************/

    /**
     * Get hooks by hook type
     * @param string $hook_type
     * hook type code
     * @return array
     * Return the hooks
     */
    private function get_hooks($hook_type)
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_HOOKS, $this->db->get_cache()::CACHE_ALL, [__FUNCTION__, $hook_type]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = 'SELECT DISTINCT `class`, `function`
            FROM hooks h
            INNER JOIN  lookups l ON (l.id = h.id_hookTypes)
            WHERE l.lookup_code = :lookup_code';
            $res = $this->db->query_db($sql, array(":lookup_code" => $hook_type));
            $this->db->get_cache()->set($key, $res);
            return $res;
        }
    }

    /**
     * Check DB for entered functions to be watched and on entering the function execute the scheduled method in the scheduled class
     */
    private function schedule_hook_on_function_execute()
    {
        $hookService = $this;
        foreach ($this->get_hooks(hookTypes_hook_on_function_execute) as $key => $hook) {
            uopz_set_hook($hook['class'], $hook['function'], function () use ($hookService, $hook) {
                foreach ($hookService->get_hook_calls(hookTypes_hook_on_function_execute, $hook['class'], $hook['function']) as $key => $hook_method) {
                    if (class_exists($hook_method['exec_class'])) {
                        $hookClassInstance = new $hook_method['exec_class']($hookService->get_services());
                        if (method_exists($hookClassInstance, $hook_method['exec_function'])) {
                            $hookClassInstance->{$hook_method['exec_function']}();
                        }
                    }
                }
            });
        }
    }

    /**
     * Check DB for hooks from type `hookTypes_hook_overwrite_return`
     */
    private function schedule_hook_overwrite_return()
    {
        $hookService = $this;        
        foreach ($this->get_hooks(hookTypes_hook_overwrite_return) as $key => $hook) {
            $class = false;
            $func = false;
            foreach ($hookService->get_hook_calls(hookTypes_hook_overwrite_return, $hook['class'], $hook['function']) as $key => $hook_method) {
                if (!$class) {
                    $class = $hook['class'];
                }
                if (!$func) {
                    $func = $hook['function'];
                }
                uopz_set_return($class, $func, function (...$args) use ($hookService, $hook_method, $class, $func) {
                    if (class_exists($hook_method['exec_class'])) {
                        $hookClassInstance = new $hook_method['exec_class']($hookService->get_services());
                        if (method_exists($hookClassInstance, $hook_method['exec_function'])) {
                            // get the method parameters and pass them 
                            $reflector = new ReflectionClass($class);
                            $parameters = $reflector->getMethod($func)->getParameters();
                            $argsKeys = array();
                            foreach($parameters as $key => $parameter)
                            {
                                if(isset($args[$key])){
                                    $argsKeys[$parameter->name] = $args[$key];
                                }
                            }
                            $argsKeys['hookedClassInstance'] = $this;
                            $argsKeys['methodName'] = $func;
                            
                            $res = $hookClassInstance->{$hook_method['exec_function']}($argsKeys);
                            return $res;
                        }
                    }
                }, true);
                $class = $hook_method['exec_class'];
                $func = $hook_method['exec_function'];
            }
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Return all services
     * @return array
     * An associative array holding the different available services
     */
    public function get_services()
    {
        return $this->services;
    }

    /**
     * Get hook calls for given hook type, class and method
     * @param string $hook_type
     * The hook type
     * @param string $class
     * the class that will be hooked
     * @param string $func
     * the function that will be hooked
     * @return array
     * the hooked methods
     */
    public function get_hook_calls($hook_type, $class, $func)
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_HOOKS, $this->db->get_cache()::CACHE_ALL, [__FUNCTION__, $hook_type, $class, $func]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = 'SELECT `exec_class`, `exec_function`
            FROM hooks h
            INNER JOIN  lookups l ON (l.id = h.id_hookTypes)
            WHERE l.lookup_code = :lookup_code AND `class` = :class AND `function` = :func
            ORDER BY priority;';
            $res = $this->db->query_db($sql, array(
                ":lookup_code" => $hook_type,
                ":class" => $class,
                ":func" => $func
            ));
            $this->db->get_cache()->set($key, $res);
            return $res;
        }
    }
}
?>
