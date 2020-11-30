<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../navigation/NavigationView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the navigation bar component.
 */
class NavigationBarView extends NavigationView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the session navigation component.
     */
    protected function output_nav()
    {
        $this->output_local_component("nav");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $items = $this->model->get_db_field("items", array());
        $css = $this->css;
        
        if(count($items) > 0){
            $leadingLink = array_shift($items);
            require __DIR__ . "/tpl_navigationBar.php";
        }
        
    }
	
	public function output_content_mobile()
    {
        echo 'mobile';
    }

    /**
     * Render the navbar links.
     */
    public function output_navbar_links($links){
        foreach($links as $link){
            require __DIR__ . "/tpl_navigationBarLink.php";
        }
    }
}
?>
