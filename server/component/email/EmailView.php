<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the email component.
 */
class EmailView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * The id of the currently selected email.
     */
    private $active_id;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the user insert component.
     * @param object $controller
     *  The controller instance of the user insert component.
     * @param int $id
     *  The currently active email id (null if no id is selected)
     */
    public function __construct($model, $controller, $id=null)
    {
        parent::__construct($model, $controller);
        $this->active_id = $id;
    }

    /* Private Methods ********************************************************/

    /**
     * Render the fail alerts.
     */
    private function output_alert()
    {
        $this->output_controller_alerts_fail();
    }

    /**
     * Render the list of emails.
     */
    private function output_emails()
    {
        $emails = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Registered Users",
            "children" => array(new BaseStyleComponent("nestedList", array(
                "items" => $this->model->get_emails(),
                "id_prefix" => "emails",
                "is_collapsible" => false,
                "id_active" => $this->active_id,
            )))
        ));
        $emails->output_content();
    }

    /**
     * Render either the intro or a specific email form.
     */
    private function output_email()
    {
        if($this->active_id === null)
        {
            $cms_url = $this->model->get_link_url('cmsSelect');
            require __DIR__ . "/tpl_intro.php";
        }
        else
        {
            $target = $_SERVER['REQUEST_URI'];
            $langs = $this->model->get_email($this->active_id);
            require __DIR__ . "/tpl_form.php";
        }

    }

    /**
     * Render the email forms for each language.
     *
     * @param array $langs
     *  An array of emails in different languages (see EmailModel::get_email()).
     */
    private function output_form_items($langs)
    {
        foreach($langs as $lang)
        {
            $textarea = new BaseStyleComponent("textarea", array(
                "label" => $lang['locale'],
                "name" => intval($lang['p_id']) . "-" . $this->active_id
                    . "-". intval($lang['l_id']),
                "value" => $lang['content'],
            ));
            $textarea->output_content();
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_email.php";
    }
}
?>
