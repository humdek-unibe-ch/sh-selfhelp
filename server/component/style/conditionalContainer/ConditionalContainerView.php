<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the conditional container style component.
 * A conditional containers wraps its content into a div tag but only displays
 * the content if a given condition is true.
 */
class ConditionalContainerView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'condition' (empty string).
     * A condition string that needs to be parsed and evaluated.
     */
    private $condition;

    /**
     * DB field 'debug' (false).
     * If set, debug information is printed out.
     */
    private $debug;

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
        $this->condition = $this->model->get_db_field('condition');
        $this->debug = $this->model->get_db_field('debug', false);
    }

    private function get_entry_values($entry_value){
        $cond = json_encode($this->model->get_db_field('condition'));
        $cond = $this->model->get_entry_value($entry_value, $cond);
        $this->condition = json_decode($cond, true);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $res = $this->model->compute_condition($this->condition);
        if($this->debug)
        {
            echo '<pre class="alert alert-warning">';
                var_dump($res);
            echo "</pre>";
        }
        if($this->model->is_cms_page() || $res['result'])
            require __DIR__ . "/tpl_container.php";
    }

    /**
     * Render the style view.
     * @param array $entry_value
     * the data for the entry value
     */
    public function output_content_entry($entry_value)
    {
        $entry_data = $entry_value;
        $this->get_entry_values($entry_value);

        $res = $this->model->compute_condition($this->condition);
        if($this->debug)
        {
            echo '<pre class="alert alert-warning">';
                var_dump($res);
            echo "</pre>";
        }
        if($this->model->is_cms_page() || $res['result'])
            require __DIR__ . "/tpl_container_entryValue.php";
    }

    public function output_content_mobile()
    {
        $res = $this->model->compute_condition($this->condition);
        if ($res['result']) {
            return parent::output_content_mobile();
        }
    }

    /**
     * Render output as an entry for mobile
     * @param array $entry_value
     * the data for the entry value
     */
    public function output_content_mobile_entry($entry_value)
    {        
        $this->get_entry_values($entry_value);
        $res = $this->model->compute_condition($this->condition);
        if ($res['result']) {
            return parent::output_content_mobile_entry($entry_value);
        }
    }
}
?>
