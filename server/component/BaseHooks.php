<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/BaseModel.php";

/**
 * The class to define the hooks.
 */
class BaseHooks extends BaseModel
{
    /* Constructors ***********************************************************/

    /* Protected Properties *****************************************************/

    /**
     * Various params
     */
    protected $params;

    /**
     * The constructor creates an instance of the hooks.
     * @param object $services
     *  The service handler instance which holds all services
     * @param object $params
     *  Various params
     */
    public function __construct($services, $params = array())
    {
        $this->params = $params;
        parent::__construct($services);
    }

    /**
     * Execute private method with reflection
     * @param object hookedClassInstance
     * The class which was hooked
     * @param string $methodName
     * The name of the method that we want to execute
     * @param object $params = null
     * Params passed to the method
     * @return object
     * Return the method result
     */
    protected function execute_private_method($args = array())
    {
        $reflector = new ReflectionObject($args['hookedClassInstance']);
        $method = $reflector->getMethod($args['methodName']);
        $method->setAccessible(true);
        $parameters = $method->getParameters();

        $params = array();
        foreach ($parameters as $key => $parameter) {
            if (isset($args[$parameter->name])) {
                $params[] = $args[$parameter->name];
            }
        }
        if (count($parameters) == 0) {
            $res = $method->invoke($args['hookedClassInstance']);
        } else {
            $res = $method->invoke($args['hookedClassInstance'], ...$params);
        }
        $method->setAccessible(false);
        return $res;
    }

    /**
     * Set value to private property with reflection
     * @param object hookedClassInstance
     * The class which was hooked
     * @param string $propertyName
     * The name of the property that we want to set
     * @param object $propertyNewValue
     * The new value
     */
    protected function set_private_property($args = array())
    {
        $reflector = new ReflectionObject($args['hookedClassInstance']);
        $property = $reflector->getProperty($args['propertyName']);
        if (isset($args['arrayKey'])) {
            $arr_value = $this->get_private_property($args);
            $arr_value[$args['arrayKey']] = $args['propertyNewValue'];
            $args['propertyNewValue'] = $arr_value;
        }
        $property->setAccessible(true);
        $property->setValue($args['hookedClassInstance'], $args['propertyNewValue']);
        $property->setAccessible(false);
    }

    /**
     * Гet value to private property with reflection
     * @param object hookedClassInstance
     * The class which was hooked
     * @param string $propertyName
     * The name of the property that we want to set
     * @return object $propertyValue
     * Return property value
     */
    protected function get_private_property($args = array())
    {
        $reflector = new ReflectionObject($args['hookedClassInstance']);
        $property = $reflector->getProperty($args['propertyName']);
        $property->setAccessible(true);
        $propertyValue = $property->getValue($args['hookedClassInstance']);
        $property->setAccessible(false);
        return $propertyValue;
    }

    /**
     * Get the parameter value of the function by parameter name
     * The function is called recursively until it finds the parameter
     * @param array $args
     * all the arguments
     * @param string $param_name
     * the name of the parameter that we search
     * @return any
     * Return the value
     */
    protected function get_param_by_name($args, $param_name)
    {
        if (isset($args[$param_name])) {
            return $args[$param_name];
        } else if (isset($args['args'])) {
            return $this->get_param_by_name($args['args'], $param_name);
        } else {
            throw new Exception('Missing parameter');
        }
    }

    /**
     * Execute parent method with reflection
     * @param object hookedClassInstance
     * The class which was hooked
     * @param string $methodName
     * The name of the method that we want to execute
     * @param object $params = null
     * Params passed to the method
     * @return object
     * Return the method result
     */
    protected function execute_parent_method($args = array())
    {
        $reflector = new ReflectionObject($args['hookedClassInstance']);
        $parent_class = $reflector->getParentClass();
        $method = $parent_class->getMethod($args['methodName']);
        $parameters = $method->getParameters();

        $params = array();
        foreach ($parameters as $key => $parameter) {
            if (isset($args[$parameter->name])) {
                $params[] = $args[$parameter->name];
            }
        }
        if (count($parameters) == 0) {
            $res = $method->invoke($args['hookedClassInstance']);
        } else {
            $res = $method->invoke($args['hookedClassInstance'], ...$params);
        }
        $method->setAccessible(false);
        return $res;
    }

    /**
     * Get the plugin version
     */
    public function get_plugin_db_version($plugin_name = null)
    {
        $res = $this->services->get_db()->query_db_first(
            'SELECT `version` FROM `plugins` WHERE `name` = :plugin_name',
            array(
                ":plugin_name" => $plugin_name
            )
        );
        return $res && $res['version']  ? $res['version'] : 'no data';
    }
}
?>
