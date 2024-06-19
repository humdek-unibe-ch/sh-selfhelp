<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the formUserInput style component.
 */
class FormUserInputView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'anchor' (empty string).
     * The id of a anchor section to jump to on submit instead of the form.
     */
    protected $anchor;

    /**
     * DB field 'name' (empty string).
     * The name of the form. This will help to group all input data to
     * a specific set. If this field is not set, the style will not be rendered.
     */
    protected $name;

    /**
     * DB field 'label' ('Submit').
     * The label of the submit button.
     */
    protected $label;

    /**
     * DB field 'type' ('primary').
     * The type of the submit button, e.g. 'primary', 'success', etc.
     */
    protected $type;

    /**
     * DB field 'is_log' (false).
     * If set to true the form will save journal data, i.e. each data set is
     * stored individually with a timestamp. If set to false the form will save
     * persistent data which can be edited continuously by the user.
     */
    protected $is_log;

    /**
     * DB field 'ajax' (false).
     * If set to true the form will be sumbited via ajax call
     */
    protected $ajax;

    /**
     * DB field 'submit_and_send_email' (false).
     * If set to true the form will have one more submit button which will send an email with the form data to the user
     */
    protected $submit_and_send_email;

    /**
     * DB field 'submit_and_send_label' ('').
     * The label on the submit and send button
     */
    protected $submit_and_send_label;

    /**
     * Selected record_id if there is one selected
     */
    protected $selected_record_id;

    /**
     * Entry data if the style is used in entry visualization
     */
    protected $entry_data = null;

    /**
     * If true it loads only records created by the same user
     */
    protected $own_entries_only;    

    /**
     * Any path to whcih we want to redirect
     */
    protected $redirect_at_end;   

    /**
     * DB field 'confirmation_title' (empty string).
     * If set a modal is shown. This will be the header of the confirmation modal.
     */
    private $confirmation_title;

    /**
     * DB field 'confirmation_cancel' (empty string).
     */
    private $confirmation_cancel;

    /**
     * DB field 'confirmation_continue' (OK).
     */
    private $confirmation_continue;

    /**
     * DB field 'confirmation_message' ('Do you want to continue?').
     */
    private $confirmation_message;

    /**
     * DB field 'url_cancel' (empty string).
     * The target url when the cancel button is clicked.  If left empty, the
     * cancel button will not be rendered
     */
    private $url_cancel;

    /**
     * DB field 'label_cancel' ('Cancel').
     * The label of the cancel button.
     */
    private $label_cancel;

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
        $this->name = $this->model->get_db_field("name");
        $this->label = $this->model->get_db_field("label");
        $this->type = $this->model->get_db_field("type", "primary");
        $this->is_log = $this->model->get_db_field("is_log", false);
        $this->ajax = $this->model->get_db_field("ajax", 0);
        $this->anchor = $this->model->get_db_field("anchor");
        $this->submit_and_send_email = $this->model->get_db_field("submit_and_send_email", false);
        $this->submit_and_send_label = $this->model->get_db_field("submit_and_send_label", '');
        $this->own_entries_only = $this->model->get_db_field("own_entries_only", 1);
        $this->redirect_at_end = $this->model->get_db_field("redirect_at_end", "");
        $this->selected_record_id = $this->model->get_selected_record_id(); // if selected_record_id > 0 the form is in edit mode
        $this->confirmation_title = $this->model->get_db_field("confirmation_title", '');
        $this->confirmation_cancel = $this->model->get_db_field("label_cancel", '');
        $this->confirmation_continue = $this->model->get_db_field("label_continue", '');
        $this->confirmation_message = $this->model->get_db_field("label_message", '');
        $this->url_cancel = $this->model->get_db_field("url_cancel", '');
        $this->label_cancel = $this->model->get_db_field("label_cancel", '');
    }

    private function get_delete_url()
    {
        // implode string into array
        $url = explode('/', $_SERVER['REQUEST_URI']);
        //The array_filter() function filters the values of an array using a callback function.
        $url = array_filter($url);
        // remove the last element and return an array
        array_pop($url);
        // implode again into string
        return '/' . implode('/', $url);
    }

    /**
     * For each child of style formField enable the setting that the last db
     * entry is displayed in the form field. This is a recursive method.
     *
     * @param array $children
     *  The child component array of the current component.
     * @param bool $show_data
     *  If set to true, existing data is updated.
     *  If set to false, each data set is saved as a timestamped new entry.
     */
    protected function propagate_input_field_settings($children, $show_data)
    {
        foreach($children as $child)
        {
            $style = $child->get_style_instance();
            if(is_a($style, "FormFieldComponent"))
            {
                if($show_data) {
                    $style->enable_show_db_value();
                    $style->set_form_id($this->id_section);
                }
                $style->enable_user_input();
            }
            $this->propagate_input_field_settings($child->get_children(),
                $show_data);
        }
    }

    /**
     * For each child of style formField enable the value for the selected record
     * entry is displayed in the form field. 
     *
     * @param array $children
     *  The child component array of the current component.
     * @param array $entry_record
     *  The values of the selected record
     */
    protected function propagate_input_fields($children, $entry_record)
    {        
        foreach ($children as $child) {
            $style = $child->get_style_instance();
            if (is_a($style, "FormFieldComponent")) {
                if(isset($entry_record[$style->get_view()->get_name_base()])){                    
                    $style->get_view()->set_value($entry_record[$style->get_view()->get_name_base()]);
                }
            }
            $this->propagate_input_fields($child->get_children(), $entry_record);
        }
    }

    /**
     * For each child of style formField enable the value for the selected record
     * entry is displayed in the form field. 
     *
     * @param array $style
     *  The child component array of the current component.
     * @param array $entry_record
     *  The values of the selected record
     */
    protected function propagate_input_fields_mobile($style, $entry_record)
    {
        foreach ($style['children'] as $key => &$child) {
            if (isset($child["name"]["content"]) && isset($entry_record[$child["name"]["content"]])) {
                $child["value"]["content"] = $entry_record[$child["name"]["content"]];
            }
    
            if (isset($child['children']) && is_array($child['children'])) {
                $child = $this->propagate_input_fields_mobile($child, $entry_record);
            }
        }
        return $style;
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->name === "") return;
        $children = $this->model->get_children();        
        $this->propagate_input_field_settings($children, !$this->is_log);
        $children[] = new BaseStyleComponent("input", array(
            "type_input" => "hidden",
            "name" => "__form_name",
            "value" => htmlentities($this->name),
        ));
        $children[] = new BaseStyleComponent("input", array(
            "type_input" => "hidden",
            "name" => "ajax",
            "value" => $this->ajax,
        ));
        $children[] = new BaseStyleComponent("input", array(
            "type_input" => "hidden",
            "name" => "is_log",
            "value" => $this->is_log,
        ));
        $redirect_link = str_replace("/", "", $this->redirect_at_end);
        $redirect_link = $this->model->get_services()->get_router()->get_url($redirect_link);
        $children[] = new BaseStyleComponent("input", array(
            "type_input" => "hidden",
            "name" => "redirect_at_end",
            "value" => $redirect_link,
        ));
        if ($this->entry_data) {
            $children[] = new BaseStyleComponent("input", array(
                "type_input" => "hidden",
                "name" => ENTRY_RECORD_ID,
                "value" => $this->entry_data[ENTRY_RECORD_ID],
            ));
        }
        if ($this->selected_record_id > 0) {
            $children[] = new BaseStyleComponent("input", array(
                "type_input" => "hidden",
                "name" => SELECTED_RECORD_ID,
                "value" => $this->selected_record_id,
            ));
        }
        $url = $_SERVER['REQUEST_URI'] . '#section-'
                . ($this->anchor ? $this->anchor : $this->id_section);
        $form = new BaseStyleComponent("form", array(
            "label" => $this->label,
            "label_cancel" => $this->label_cancel,
            "type" => $this->type,
            "url" => $url,
            "children" => $children,
            "css" => $this->css,
            // "id" => ($this->id_section . isset($this->entry_data[ENTRY_RECORD_ID]) ? ' ' . $this->entry_data[ENTRY_RECORD_ID] : ''),
            "id" => $this->id_section,
            "submit_and_send_email" => $this->submit_and_send_email,
            "submit_and_send_label" => $this->submit_and_send_label,
            "confirmation_title" => $this->confirmation_title,
            "confirmation_cancel" => $this->confirmation_cancel,
            "confirmation_continue" => $this->confirmation_continue,
            "confirmation_message" => $this->confirmation_message,
            "url_cancel" => $this->model->get_services()->get_router()->get_url($this->url_cancel),
        ));
        require __DIR__ . "/tpl_form.php";
    }

    /**
     * Render the style view.
     */
    public function output_content_mobile()
    {
        $style = parent::output_content_mobile();   
        if ($this->selected_record_id > 0) {
            // edit mode; load the entry record value
            $entry_record = $this->model->get_form_entry_record($this->name, $this->selected_record_id, $this->own_entries_only);
        }
        if ($this->selected_record_id > 0) {
            $selected_record_id = new BaseStyleComponent("input", array(
                "type_input" => "hidden",
                "name" => SELECTED_RECORD_ID,
                "id" => SELECTED_RECORD_ID,
                "value" => $this->selected_record_id,
                "is_required" => 1
            ));
            $sel_field = $selected_record_id->output_content_mobile();
            if (!$sel_field['value']['content']) {
                $sel_field['value']['content'] = $this->selected_record_id;
            }
            $style['children'][] = $sel_field;
            $style = $this->propagate_input_fields_mobile($style, $entry_record);
        }
        $redirect_link = str_replace("/", "", $this->redirect_at_end);
        $redirect_link = $this->model->get_services()->get_router()->get_url($redirect_link);
        $style['redirect_at_end']['content'] = $redirect_link;
        $style['url_cancel']['content'] = $this->model->get_services()->get_router()->get_url($this->url_cancel);
        return $style;
    }
	
}
?>
