<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The base view class of a emailForm style component.
 */
class EmailFormBaseView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'label' ('Submit').
     * The label of the submit button.
     */
    protected $label;

    /**
     * DB field 'placeholder' (empty string).
     * The placeholder of the email input field.
     */
    protected $placeholder;

    /**
     * DB field 'type' ('primary').
     * The type of the submit button, e.g. 'primary', 'success', etc.
     */
    protected $type;


    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of a base style component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->label = $this->model->get_db_field("label", "Submit");
        $this->type = $this->model->get_db_field("type", "primary");
        $this->placeholder = $this->model->get_db_field("placeholder");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $url = $_SERVER['REQUEST_URI'] . '#section-' . $this->id_section;
        $input = new BaseStyleComponent('input', array(
            "type_input" => "email",
            "name" => "email_user",
            "placeholder" => $this->placeholder,
            "is_required" => true,
        ));
        require __DIR__ . "/tpl_form.php";
    }
	
	public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
