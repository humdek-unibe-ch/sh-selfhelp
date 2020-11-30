<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the graphLegend style component.
 * This style components allows to render one commun legend for multiple graphs.
 */
class GraphLegendView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'value_types' (empty string)
     * A JSON string to define a label and a color for each distinct data value.
     */
    private $value_types;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->value_types = $this->model->get_db_field("value_types");
    }

    /* Private Methods ********************************************************/

    /**
     * Checks wether the types array provided through the CMS contains all
     * required fields.
     *
     * @retval boolean
     *  True on success, false on failure.
     */
    private function check_value_types() {
        if(!is_array($this->value_types) || count($this->value_types) === 0)
            return false;
        foreach($this->value_types as $idx => $item)
        {
            if(!isset($item["label"]))
                return false;
            if(!isset($item["color"]))
                return false;
        }
        return true;
    }

    /**
     * Render the list of legend items.
     */
    private function output_list_items() {
        foreach($this->value_types as $item) {
            $key = $item['key'];
            $color = $item['color'];
            $label = $item['label'];
            require __DIR__ . "/tpl_graphLegend_item.php";
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if(!$this->check_value_types()) {
            echo "parse error in <code>value_types</code>";
        } else {
            require __DIR__ . "/tpl_graphLegend.php";
        }
    }
	public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
