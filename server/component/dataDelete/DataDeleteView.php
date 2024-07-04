<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";

/**
 * The view class of the asset select component.
 */
class DataDeleteView extends BaseView
{

    /* Private Properties *****************************************************/
    /**
     * The dataTable structure
     */
    private $dataTable;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->dataTable = $this->model->get_dataTable();
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    /**
     * Render the view.
     */
    public function output_content()
    {
        if (!$this->dataTable) {
            $this->output_missing();
            return;
        } else {
            $this->output_controller_alerts_success(true);
            $this->output_controller_alerts_fail(true);
            $dataTableColumnsCheckboxes = array();
            $dataTableColumns = $this->model->fetch_dataColumns();
            foreach ($dataTableColumns as $key => $value) {
                $dataTableColumnsCheckboxes[] = new BaseStyleComponent(
                    "checkbox",
                    array(
                        "name" => $value['id'],
                        "label" => $value['name'],
                        "css" => "w-100 d-flex justify-content-between",
                        "checkbox_value" => $value['name'],
                        "value" => '',
                        "toggle_switch" => 1,
                    )
                );
            }
            $dataTableColumnsCheckboxes[] = new BaseStyleComponent("input", array(
                "type_input" => "hidden",
                "name" => "DELETE_COLUMNS",
                "value" => 'DELETE_COLUMNS',
                "css" => "mb-3",
                "is_required" => true
            ));
            $holder = new BaseStyleComponent("div", array(
                "css" => 'container my-3',
                "children" => array(
                    new BaseStyleComponent("div", array(
                        "css" => "mb-3 d-flex justify-content-between p-3 border border-danger rounded",
                        "children" => array(
                            new BaseStyleComponent("markdown", array(
                                "text_md" => "**Name: `" .  $this->dataTable[0]['name_id'] . '`**'
                            )),
                            new BaseStyleComponent("markdown", array(
                                "text_md" => "**Display name: `" .  $this->dataTable[0]['name'] . '`**'
                            )),
                        )
                    )),
                    new BaseStyleComponent("card", array(
                        "css" => "mb-3 card card-danger",
                        "is_expanded" => false,
                        "is_collapsible" => true,
                        "title" => "<b>Delete dataTable</b>",
                        "children" => array(
                            new BaseStyleComponent("form", array(
                                "label" => "Delete",
                                "url" => "#",
                                "type" => "danger",
                                "label_cancel" => "Back",
                                "confirmation_title" => "Delete",
                                "confirmation_cancel" => "Cancel",
                                "confirmation_continue" => "Delete",
                                "confirmation_message" => "Are you sure that you want to delete dataTable: <code>" . $this->dataTable[0]['name'] . '</code>. This operation cannot be <code>undone</code> and it will <code>DELETE</code> the table and all its data!',
                                "url_cancel" => $this->model->get_link_url("data"),
                                "children" => array(
                                    new BaseStyleComponent("input", array(
                                        "type_input" => "hidden",
                                        "name" => "DELETE_DATATABLE",
                                        "value" => 'DELETE_DATATABLE',
                                        "css" => "mb-3",
                                        "is_required" => true
                                    )),
                                    new BaseStyleComponent("input", array(
                                        "type_input" => "hidden",
                                        "name" => "display_name",
                                        "value" => $this->dataTable[0]['name'],
                                        "css" => "mb-3",
                                        "is_required" => true
                                    )),
                                    new BaseStyleComponent("input", array(
                                        "type_input" => "text",
                                        "name" => "display_name_confirmation",
                                        "css" => "mb-3",
                                        "is_required" => true,
                                        "placeholder" => "Please enter the display name of the dataTable that you want to delete",
                                    )),
                                )
                            )),
                        )
                    )),
                    new BaseStyleComponent("card", array(
                        "css" => "mb-3 card card-danger",
                        "is_expanded" => false,
                        "is_collapsible" => true,
                        "title" => "<b>Delete columns</b>",
                        "children" => array(
                            new BaseStyleComponent("form", array(
                                "label" => "Delete",
                                "url" => "#",
                                "type" => "danger",
                                "label_cancel" => "Back",
                                "confirmation_title" => "Delete",
                                "confirmation_cancel" => "Cancel",
                                "confirmation_continue" => "Delete",
                                "confirmation_message" => "Are you sure that you want to delete selected columns for dataTable: <code>" . $this->dataTable[0]['name'] . '</code>. This operation cannot be <code>undone</code> and it will <code>DELETE</code> the selected columns and all the data associated with them.',
                                "url_cancel" => $this->model->get_link_url("data"),
                                "children" => $dataTableColumnsCheckboxes
                            )),
                        )
                    )),
                    new BaseStyleComponent("card", array(
                        "css" => "mb-3 card card-warning",
                        "is_expanded" => false,
                        "is_collapsible" => true,
                        "title" => "<b>Records</b>",
                        "children" => array(
                            new StyleComponent(
                                $this->model->get_services(),
                                -1,
                                array(),
                                -1,
                                array(),
                                array(
                                    "name" => "showUserInput",
                                    "type" => "component",
                                    "is_log" => 1,
                                    "data_table" => $this->dataTable[0]['table_id'],
                                    "label_delete" => "Delete",
                                    "own_entries_only" => 0,
                                    "css" => "dt-sortable dt-searching dt-bPaginate dt-bInfo",
                                    "delete_title" => "Delete record",
                                    "delete_content" => "Change the status of the record to `deleted`."
                                )
                            )
                        )
                    ))
                )
            ));
            $holder->output_content();
        }
    }

    public function output_content_mobile()
    {
        return;
    }
}
?>
