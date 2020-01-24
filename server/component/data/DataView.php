<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

/**
 * The view class of the data component. Extends UserSelectView. It needs the user table
 */
class DataView extends UserSelectView
{
    /* Private Properties *****************************************************/

    /**
     * An array of all user input forms.
     */
    private $forms;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the user insert component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->forms = $this->model->get_forms();
    }

    /* Private Methods ********************************************************/

    /**
     * Render the show data
     */
    private function output_data()
    {
        require __DIR__ . "/tpl_show_data.php";
    }

    /**
     * Render input forms as datatable
     */
    private function output_tables_data()
    {
        foreach ($this->forms as $form) {
            $formFields = $this->model->getFormFields($form['form_id']);
            // loop over the rows, outputting them
            $tableHead = '<thead><tr>';
            $tableBody = '<tbody>';
            if (count($formFields) > 0) {
                foreach (array_keys($formFields[0]) as $key => $value) {
                    $tableHead = $tableHead . '<th>' . $value . '</th>';
                }
            }
            foreach ($formFields as $field) {
                $tableBody = $tableBody . '<tr>';
                foreach (array_values($field) as $key => $value) {
                    $tableBody = $tableBody . '<td>' . $value . '</td>';
                }
                $tableBody = $tableBody . '</tr>';
            }
            $tableHead = $tableHead . '</tr></thead>';
            $tableBody = $tableBody . '</tbody>';
            $formName = $form['form_name'];

            $card = new BaseStyleComponent("card", array(
                "css" => "mb-3 card card-success",
                "is_expanded" => true,
                "is_collapsible" => true,
                "title" => $formName,
                "children" => array(
                    new BaseStyleComponent("markdown", array(
                        "text_md" => '<table class="adminData w-100 table">' . $tableHead . $tableBody . '</table>'
                    ))
                )
            ));
            $card->output_content();
        }
    }

    /**
     * Render configuration panel
     */
    private function output_config_panel()
    {
        $this->output_user_activity();
    }
    
    /**
     * Show info for the selected user
     */
    public function get_selected_user(){
        if($this->model->get_uid()){
            $user = $this->model->get_selected_user();
            return $user['code'] . ' ' . $user['email'];
        }else{
            return 'All';
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        $local = array(__DIR__ . "/js/data.js");
        return parent::get_js_includes($local);
    }

    /**
     * Render the cms view.
     */
    public function output_content()
    {        
        require __DIR__ . "/tpl_data.php";
    }
}
?>
