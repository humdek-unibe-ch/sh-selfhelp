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
    protected function execute_private_method($hookedClassInstance, $methodName, $params = null)
    {
        $reflector = new ReflectionObject($hookedClassInstance);
        $method = $reflector->getMethod($methodName);
        $method->setAccessible(true);
        if ($params == null) {
            $res = $method->invoke($hookedClassInstance);
        } else {
            $res = $method->invoke($hookedClassInstance, $params);
        }
        $method->setAccessible(false);
        return $res;
    }
}
?>
