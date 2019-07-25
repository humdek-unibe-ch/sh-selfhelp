<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the interstedUserForm style component.
 */
class InterestedUserFormView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'label' ('Submit').
     * The label of the submit button.
     */
    private $label;

    /**
     * DB field 'placeholder' (empty string).
     * The placeholder of the email input field.
     */
    private $placeholder;

    /**
     * DB field 'type' ('primary').
     * The type of the submit button, e.g. 'primary', 'success', etc.
     */
    private $type;

    /**
     * DB field 'recepients' (empty string)
     * The recepients to receive an automatically sent email.
     */
    private $recepients;

    /**
     * DB field 'email_recepients' (empty string)
     * The email to be sent to the recepients.
     */
    private $email_recepients;

    /**
     * DB field 'email_recepients' (empty string)
     * The email to be sent to the email address that was entered to the form.
     */
    private $email_intersted_user;


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
            "name" => "email_intersted_user",
            "placeholder" => $this->placeholder,
            "is_required" => true,
        ));
        require __DIR__ . "/tpl_form.php";
    }
}
?>
