<?php
require_once __DIR__ . "/../BaseComponent.php";

/**
 * The component class for a Config Component. This is a quick and dirty 
 * implementation to render markdown files.
 */
class ConfigComponent extends BaseComponent
{
    /* Pivate Properties *****************************************************/

    /**
     * The target markdown file to load.
     */
    private $target;

    /**
     * An associative array holding the different available services. See the
     * class definition basepage for a list of all services.
     */
    private $services;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes the view instance to the constructor of the parent
     * class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param array $params
     *  The get params passed by the url.
     */
    public function __construct($services, $params)
    {
	$this->services = $services;
        $this->target = isset($params['target']) ? $params['target'] : null;
        parent::__construct(null, null);
    }

    /* Pivate Methods *********************************************************/

    /**
     * Render the markdown file if available.
     */
    private function output_md()
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . "/doc/server/" . $this->target . ".md";
        if(file_exists($path))
            echo $this->services->get_parsedown()->text(file_get_contents($path));
    }

    /* Public Methods *********************************************************/

    /**
     * Overwrite the method BaseComponent::output_content to bypass the MCV
     * concept.
     */
    public function output_content()
    {
        $url = $this->services->get_router()->generate("configs");
        require __DIR__ . "/tpl_config.php";
    }
}
?>
