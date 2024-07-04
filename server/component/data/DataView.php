<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";

/**
 * The view class of the data component. Extends UserSelectView. It needs the user table
 */
class DataView extends BaseView
{
    /* Private Properties *****************************************************/

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the user insert component.
     * @param object $controller
     *  The controller instance of the user insert component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
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
        $exclude_columns = ['user_name'];
        $forms = $this->model->get_selected_forms();
        if ($forms) {
            if ($forms[0] == 'all') {
                $forms = $this->model->get_dataTables();
            }
            foreach ($forms as $keyForm => $form) {
                $formId = isset($form['id']) ? $form['id'] : $form;
                $formFields = $this->model->getFormFields($formId, $this->model->get_selected_users());
                if (!empty($formFields)) {
                    // Initialize an array to store table rows
                    $tableRows = [];
                
                    // Build table head
                    $tableHead = '<thead><tr>';
                    if (count($formFields) > 0) {
                        foreach (array_keys($formFields[0]) as $key => $value) {
                            if (!in_array($value, $exclude_columns)) {
                                // show columns which are not excluded
                                $tableHead .= '<th>' . $value . '</th>';
                            }
                        }
                    }
                    $tableHead .= '</tr></thead>';
                
                    // Build table body
                    $tableBody = '<tbody>';
                    foreach ($formFields as $field) {
                        $row = '<tr>';
                        foreach ($field as $key => $value) {
                            if (!in_array($key, $exclude_columns)) {
                                // show columns which are not excluded
                                $row .= '<td>' . $value . '</td>';
                            }
                        }
                        $row .= '</tr>';
                        $tableRows[] = $row; // Add the row to the array
                    }
                    $tableBody .= implode('', $tableRows);
                    $tableBody .= '</tbody>';
                
                    $formDisplayName = $this->model->get_user_input()->get_dataTable_displayName($formId);
                    $formName = isset($form['name']) ? $form['name'] : $this->model->get_user_input()->get_dataTable_name($formId);
                
                    // Build the card content
                    $table = '<table class="adminData w-100 table dataTable table-sm table-hover">' . $tableHead . $tableBody . '</table>';
                    $card = new BaseStyleComponent("card", array(
                        "css" => "mb-3 card card-success",
                        "is_expanded" => true,
                        "is_collapsible" => true,
                        "url_edit"=> $this->model->get_link_url('dataDelete', array("id_dataTables" => $formId)),
                        "title" => "Name: <code class='ml-1 mr-1'> " . $formName . " </code> Display name: <code class='ml-1 mr-1'>" . $formDisplayName .'</code>',
                        "children" => array(
                            new BaseStyleComponent("markdown", array(
                                "text_md" => $table
                            ))
                        )
                    ));
                    $card->output_content();
                }
                
            }
        }
    }

    /**
     * Render configuration panel
     */
    private function output_config_panel()
    {
        $forms = $this->model->get_dataTables();
        $options = array();
        $options[] = array(
            "value" => 'all',
            "text" => 'All'
        );
        $options = array_merge($options, $forms);
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3 card",
            "is_expanded" => true,
            "type" => 'primary',
            "is_collapsible" => false,
            "title" => 'Search Panel',            
            "children" => array(
                new BaseStyleComponent("form", array(
                    "label" => "Search",
                    "url" => $this->model->get_link_url("data"),
                    "url_cancel" => $this->model->get_link_url("data"),
                    "children" => array(
                        new BaseStyleComponent("select", array(
                            "label" => "Select user",
                            "value" => $this->model->get_selected_users(),
                            "name" => "users",
                            "css" => 'mb-3',
                            "live_search" => true,
                            "is_multiple" => false,
                            "items" => $this->model->get_users(),
                        )),
                        new BaseStyleComponent("select", array(
                            "label" => "Select form",
                            "value" => $this->model->get_selected_forms(),
                            "name" => "forms",
                            "css" => 'mb-3',
                            "live_search" => true,
                            "is_multiple" => true,
                            "items" => $options,
                        )),
                        new BaseStyleComponent("input", array(
                            "label" => "Filter",
                            "type_input" => "text",
                            "name" => "filter",
                            "id" => "dataFilter",
                            "placeholder" => "Type to filter all forms' data simultaneously",
                        )),
                    )
                )),
            )
        ));
        $card->output_content();
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

    public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
