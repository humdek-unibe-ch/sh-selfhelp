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

    

    /**
     * Render the style view for mobile.
     */
    private function output_conditional_content_mobile()
    {
        $style = $this->model->get_db_fields();
        $style['style_name'] = $this->style_name;
        $style['css'] = $this->css;
        $children = [];
        foreach ($this->children as $child) {
            if ($child instanceof StyleComponent || $child instanceof BaseStyleComponent) {
                if($child->get_view() instanceof ConditionFailedView){
                    // do nothhing. This child is shown only if the condition fails
                }else{
                    $children[] = $child->output_content_mobile();
                }                
            } 
        }
        $style['children'] = $children;
        return $style;
    }

    /**
     * Render the style view for mobile.
     */
    private function output_conditional_content_mobile_entry($entry_value)
    {
        $style = $this->model->get_db_fields();
        $style['style_name'] = $this->style_name;
        $style['css'] = $this->css;
        $children = [];
        foreach ($this->children as $child) {
            if ($child instanceof StyleComponent || $child instanceof BaseStyleComponent) {
                if($child->get_view() instanceof ConditionFailedView){
                    // do nothhing. This child is shown only if the condition fails
                }else{
                    if (method_exists($child, 'output_content_mobile_entry')) {
                        $children[]  = $child->output_content_mobile_entry($entry_value);
                    } else {
                        $children[]  = $child->output_content_mobile();
                    }
                }                
            } 
        }
        $style['children'] = $children;
        return $style;
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $res = $this->model->get_condition_result();
        if($this->debug)
        {
            echo '<pre class="alert alert-warning">';
                var_dump($res);
            echo "</pre>";
        }
        if(
        $this->model->is_cms_page() || $res['result']) {
            require __DIR__ . "/tpl_container.php";
        } else {
            require __DIR__ . "/tpl_failed_container.php";
        }
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

        $res = $this->model->get_condition_result();
        if($this->debug)
        {
            echo '<pre class="alert alert-warning">';
                var_dump($res);
            echo "</pre>";
        }
        if ($this->model->is_cms_page() || $res['result']) {
            require __DIR__ . "/tpl_container_entryValue.php";
        } else {
            require __DIR__ . "/tpl_failed_container_entryValue.php";
        }
    }

    /**
     * Render the style view for mobile.
     */
    public function output_content_mobile()
    {
        $res = $this->model->get_condition_result();
        if ($res['result']) {
            return $this->output_conditional_content_mobile();
        } else {
            foreach ($this->children as $child) {
                if ($child instanceof StyleComponent || $child instanceof BaseStyleComponent) {
                    if ($child->get_view() instanceof ConditionFailedView) {
                        return $child->output_content_mobile();
                    } else {
                        // do nothhing condition failed
                    }
                }
            }
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
        $res = $this->model->get_condition_result();
        if ($res['result']) {
            return $this->output_conditional_content_mobile_entry($entry_value);
        } else {
            foreach ($this->children as $child) {
                if ($child instanceof StyleComponent || $child instanceof BaseStyleComponent
                ) {
                    if ($child->get_view() instanceof ConditionFailedView) {
                        return $child->output_content_mobile_entry($entry_value);
                    } else {
                        // do nothhing condition failed
                    }
                }
            }
        }
    }

    /**
     * Render the content of all children of this view instance.
     * Overwrite the basic function as we do not show the style which are in conditionFailed
     */
    public function output_children()
    {
        foreach ($this->children as $child) {
            if ( $child instanceof StyleComponent || $child instanceof BaseStyleComponent) {
                if($child->get_view() instanceof ConditionFailedView){
                    // do nothhing. This child is shown only if the condition fails
                }else{
                    $child->output_content();
                }
            } else {
                echo "invalid child element of type '" . gettype($child) . "'";
            }
        }
    }

    /**
     * Render the content of all children of this view instance as entries
     * * Overwrite the basic function as we do not show the style which are in conditionFailed
     * @param array $entry_value
     * the data for the entry value
     */
    public function output_children_entry($entry_data)
    {
        foreach ($this->children as $child) {
            if ($child instanceof StyleComponent || $child instanceof BaseStyleComponent) {
                if ($child->get_view() instanceof ConditionFailedView) {
                    // do nothhing. This child is shown only if the condition fails
                } else {
                    if (method_exists($child, 'output_content_entry')) {
                        $child->output_content_entry($entry_data);
                    } else {
                        $child->output_content();
                    }
                }
            } else {
                echo "invalid child element of type '" . gettype($child) . "'";
            };
        }
    }

    /**
     * Render the content conditionFailed
     */
    public function output_failed_children()
    {
        foreach ($this->children as $child) {
            if ( $child instanceof StyleComponent || $child instanceof BaseStyleComponent) {
                if($child->get_view() instanceof ConditionFailedView){
                    $child->output_content();
                }else{
                    // do nothhing condition failed
                }
            } else {
                echo "invalid child element of type '" . gettype($child) . "'";
            }
        }
    }

    /**
     * Render the content of conditionfailed
     * @param array $entry_value
     * the data for the entry value
     */
    public function output_failed_children_entry($entry_data)
    {
        foreach ($this->children as $child) {
            if ($child instanceof StyleComponent || $child instanceof BaseStyleComponent) {
                if ($child->get_view() instanceof ConditionFailedView) {
                    
                    if (method_exists($child, 'output_content_entry')) {
                        $child->output_content_entry($entry_data);
                    } else {
                        $child->output_content();
                    }
                } else {
                    // do nothhing condition failed
                }
            } else {
                echo "invalid child element of type '" . gettype($child) . "'";
            };
        }
    }
}
?>
