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
     * DB field 'submit_and_send_lable' ('').
     * The label on the submit and send button
     */
    protected $submit_and_send_lable;

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

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of a base style component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $controller, $selected_record_id = null)
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
        $this->selected_record_id = $selected_record_id; // if selected_record_id > 0 the form is in edit mode
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
        foreach ($style['children'] as $key => $child) {
            if (isset($child["name"]) && isset($entry_record[$child["name"]["content"]])) {
                $style['children'][$key]["value"]["content"] = $entry_record[$child["name"]["content"]];
            }
            // if($style['children'])
            $style['children'][$key] = $this->propagate_input_fields_mobile($style['children'][$key], $entry_record);
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

        if ($this->selected_record_id > 0) {
            // edit mode; load the entry record value
            $entry_record = $this->model->get_form_entry_record($this->name, $this->selected_record_id, $this->own_entries_only);
            if (!$entry_record) {
                // no data for that record
                $this->sections = $this->model->get_services()->get_db()->fetch_page_sections('missing');
                foreach ($this->sections as $section) {
                    $missing_styles =  new StyleComponent($this->model->get_services(), intval($section['id']));
                    $missing_styles->output_content();
                }
                return;
            }
        }

        $children = $this->model->get_children();        
        $this->propagate_input_field_settings($children, !$this->is_log);
        if($this->selected_record_id > 0){
            $this->propagate_input_fields($children, $entry_record);
        }
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
        $redirect_link = $this->model->get_link_url($redirect_link);
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
                "name" => "selected_record_id",
                "value" => $this->selected_record_id,
            ));
        }
        $url = $_SERVER['REQUEST_URI'] . '#section-'
                . ($this->anchor ? $this->anchor : $this->id_section);
        $form = new BaseStyleComponent("form", array(
            "label" => $this->selected_record_id > 0 ? 'Update' : $this->label,
            "type" => $this->type,
            "url" => $url,
            "children" => $children,
            "css" => $this->css,
            "id" => $this->id_section . isset($this->entry_data[ENTRY_RECORD_ID]) ? ' ' . $this->entry_data[ENTRY_RECORD_ID] : '',
            "submit_and_send_email" => $this->submit_and_send_email,
            "submit_and_send_label" => $this->submit_and_send_label
        ));
        require __DIR__ . "/tpl_form.php";
        if ($this->selected_record_id > 0) {
            // update mode; show delete button

            $form_delete = new BaseStyleComponent("card", array(
                "css" => "mt-3 mb-3",
                "id" => "delete-card-" . $this->id_section,
                "is_expanded" => false,
                "type" => 'danger',
                "is_collapsible" => true,
                "title" => "Delete Entry",
                "children" => array(
                    new BaseStyleComponent("form", array(
                        "label" => 'Delete',
                        "type" => 'danger',
                        "url" => $this->get_delete_url(),
                        "children" => array(
                            new BaseStyleComponent("input", array(
                                "type_input" => "hidden",
                                "name" => "delete_record_id",
                                "value" => $this->selected_record_id,
                            )),
                            new BaseStyleComponent("input", array(
                                "type_input" => "hidden",
                                "name" => "__form_name",
                                "value" => htmlentities($this->name),
                            ))
                        )
                    ))
                )
            ));

            $form_delete->output_content();
        }        
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
                "name" => "selected_record_id",
                "id" => "selected_record_id",
                "value" => $this->selected_record_id,
                "is_required" => 1
            ));
            $style['children'][] = $selected_record_id->output_content_mobile();
            $style = $this->propagate_input_fields_mobile($style, $entry_record);
        }
        if (isset($style['label'])) {
            $style['label']['content'] = $this->selected_record_id > 0 ? 'Update' : $style['label']['content'];
        }
        if ($this->selected_record_id > 0) {
            $form_delete = new BaseStyleComponent("card", array(
                "css" => "mobile-mt-3 mobile-mb-3",
                "is_expanded" => false,
                "id" => "delete-card-" . $this->id_section,
                "type" => 'danger',
                "is_collapsible" => true,                
                "title" => "Delete Entry",
                "children" => array(
                    new BaseStyleComponent("form", array(
                        "label" => 'Delete',
                        "type" => 'danger',
                        "close_modal_at_end" => 1,
                        "redirect_at_end" => $this->model->get_db_field("redirect_at_end"),
                        "url" => $this->get_delete_url(),
                        "children" => array(
                            new BaseStyleComponent("input", array(
                                "type_input" => "hidden",
                                "name" => "delete_record_id",
                                "id" => "delete_record_id",
                                "value" => $this->selected_record_id,
                                "is_required" => 1
                            )),
                            new BaseStyleComponent("input", array(
                                "type_input" => "hidden",
                                "name" => "__form_name",
                                "id" => "__form_name",
                                "value" => htmlentities($this->name),
                                "is_required" => 1
                            ))
                        )
                    ))
                )
            ));
            if(!$entry_record){
                // no data for that record or no access
                $no_access = new BaseStyleComponent("markdown", array(
                    "text_md" => 'No access or no data for that record',
                ));
                return $no_access->output_content_mobile();
            }
            $holder = new BaseStyleComponent("div", array(
                "css" => ""                
            ));            
            $new_style = $holder->output_content_mobile();
            $new_style['children'][] = $style;
            $new_style['children'][] = $form_delete->output_content_mobile();            
            return $new_style;
        }
        return $style;
    }
	
}
?>
