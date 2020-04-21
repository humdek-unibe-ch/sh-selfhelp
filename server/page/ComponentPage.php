<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/InternalPage.php";
require_once __DIR__ . "/BasePage.php";

/**
 * This class creates a page from a single component where the component class
 * matches with the page keyword according to the following naming conventions:
 * A page with the keyword "foo" will search for the component class
 * "component/foo/FooComponent.php".
 */
class ComponentPage extends BasePage
{
    /* Private Properties *****************************************************/

    /**
     * The instance of the component to be rendered to the page.
     */
    private $componentInstance;

    /* Constructors ***********************************************************/

    /**
     * The constructor of this class. It calls the constructor of the parent
     * class and instanciates the specified component.
     *
     * @param object $services
     *  The service handler instance which holds all services
     * @param string $keyword
     *  The identification name of the page.
     * @param array $params
     *  The get parameters to be propagated to the component.
     */
    public function __construct($services, $keyword, $params)
    {
        parent::__construct($services, $keyword);
        $componentClass = ucfirst($keyword) . "Component";
        if(class_exists($componentClass))
        {
            $this->componentInstance = new $componentClass($this->services,
                $params, $this->id_page);
            $this->add_component("comp", $this->componentInstance);
        }
    }

    /* Protected Methods ******************************************************/

    /**
     * See BasePage::output_content(). This implementation renders all
     * components that are assigned to the current page (as specified in the
     * DB).
     */
    protected function output_content()
    {
        if($this->componentInstance && $this->componentInstance->has_access())
            $this->output_component("comp");
        else
        {
            $page = new InternalPage($this, "missing");
            $page->output_content();
        }
    }
}
?>
