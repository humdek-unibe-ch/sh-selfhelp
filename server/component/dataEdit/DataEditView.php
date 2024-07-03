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
class DataEditView extends BaseView
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
            $holder = new BaseStyleComponent("div", array(
                "css" => 'container my-3',
                "children" => array(
                    new BaseStyleComponent("card", array(
                        "css" => "mb-3 card card-warning",
                        "is_expanded" => true,
                        "is_collapsible" => true,
                        "title" => "Name: <code class='ml-1 mr-1'> " . $this->dataTable[0]['name_id'] . " </code> Display name: <code class='ml-1 mr-1'>" . $this->dataTable[0]['name'] . '</code>',
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
                                    "data_table" => 91,
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
