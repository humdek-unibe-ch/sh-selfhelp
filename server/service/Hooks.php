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
     * Static registry of hook chains for hook_overwrite_return.
     * Key: "ClassName::methodName" (target) => array with 'hooks' and metadata.
     * Used by BaseHooks::execute_private_method for chain-aware traversal.
     */
    private static $hook_chains = array();

    /**
     * Creating a Transaction Instance.
     *
     * @param object $services
     *  The service handler instance which holds all services
     */
    public function __construct($services)
    {
        $this->db = $services->get_db();
        // $this->db->clear_cache();
        $this->services = $services;
        $this->schedule_hook_on_function_execute();
        $this->schedule_hook_overwrite_return();        
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
            if (class_exists($hook['class']) && method_exists($hook['class'], $hook['function'])) {
                // add hooks only if the class and the method exists
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
    }

    /**
     * Check DB for hooks from type `hookTypes_hook_overwrite_return`.
     *
     * Sets a single uopz_set_return on each TARGET method only. The hook
     * chain is managed internally via args-based context so that
     * intermediate hook methods are never overridden with uopz. This
     * prevents cross-chain collisions when the same exec_function appears
     * in multiple chains (e.g. shared edit/view hook methods).
     *
     * Execution order is preserved: the last hook (highest priority value)
     * runs first as the outermost wrapper; each execute_private_method call
     * peels one layer toward the original method.
     */
    private function schedule_hook_overwrite_return()
    {
        $hookService = $this;
        foreach ($this->get_hooks(hookTypes_hook_overwrite_return) as $key => $hook) {
            $hook_methods = $hookService->get_hook_calls(
                hookTypes_hook_overwrite_return,
                $hook['class'],
                $hook['function']
            );
            if (empty($hook_methods)) {
                continue;
            }

            $target_class = $hook['class'];
            $target_func  = $hook['function'];

            if (!class_exists($target_class) || !method_exists($target_class, $target_func)) {
                continue;
            }

            $valid_hooks = array();
            foreach ($hook_methods as $hm) {
                if (class_exists($hm['exec_class']) && method_exists($hm['exec_class'], $hm['exec_function'])) {
                    $valid_hooks[] = $hm;
                }
            }
            if (empty($valid_hooks)) {
                continue;
            }

            $chain_key = $target_class . '::' . $target_func;
            self::$hook_chains[$chain_key] = array(
                'hooks'        => $valid_hooks,
                'target_class' => $target_class,
                'target_func'  => $target_func
            );

            $new_func = function (...$args) use ($hookService, $valid_hooks, $target_class, $target_func, $chain_key) {
                $reflector  = new ReflectionClass($target_class);
                $parameters = $reflector->getMethod($target_func)->getParameters();
                $argsKeys   = array();
                foreach ($parameters as $k => $parameter) {
                    if (array_key_exists($k, $args)) {
                        $argsKeys[$parameter->name] = $args[$k];
                    }
                }
                $argsKeys['hookedClassInstance'] = $this;
                $argsKeys['methodName']          = $target_func;
                $argsKeys['_hook_chain_key']     = $chain_key;
                $argsKeys['_hook_chain_index']   = count($valid_hooks) - 1;

                $last = $valid_hooks[count($valid_hooks) - 1];
                $inst = new $last['exec_class']($hookService->get_services());
                return $inst->{$last['exec_function']}($argsKeys);
            };

            uopz_set_return($target_class, $target_func, $new_func, true);
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
     * Retrieve the hook chain registered for a given target.
     * @param string $chain_key  "ClassName::methodName"
     * @return array|null  Chain data or null if not found
     */
    public static function get_hook_chain($chain_key)
    {
        return isset(self::$hook_chains[$chain_key])
            ? self::$hook_chains[$chain_key]
            : null;
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
