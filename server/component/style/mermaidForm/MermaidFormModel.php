<?php
require_once __DIR__ . "/../formUserInput/FormUserInputModel.php";
require_once __DIR__ . "/../StyleComponent.php";

/**
 * This class is used to prepare all data related to the mermaidForm style
 * components such that the data can easily be displayed in the view of the
 * component.
 */
class MermaidFormModel extends FormUserInputModel
{
    /* Private Properties *****************************************************/

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
    }

    /* Public Methods *********************************************************/

    /**
     * Get fields that will be editable by the mermaid, we return a json array.
     *
     * @param array $children
     *  The array of input field components
     * @retval jsonarray  of structure
     * name:{
     *   value: field_value,
     *   label: label_used_for_description
     * }
     */
    public function get_user_field_names_with_values($children)
    {
        $arrFields = [];
        foreach($children as $child)
        {
            if(is_a($child, "StyleComponent"))
            {
                $name = $child->get_style_instance()->get_view()
                    ->get_name_base();
                $value = $child->get_style_instance()->get_model()
                    ->get_form_field_value();
                if($this->is_cms_page()){
                    $arrFields[$name] = array(
                        "value" => '',
                        "label" => ''
                    );
                }else{
                    $arrFields[$name] = array(
                        "value" => $value,
                        "label" => ''
                    );
                }
                $arrFields[$name]['label'] = $child->get_style_instance()
                    ->get_view()->get_label();
            }
        }
        return $arrFields;
    }

    /**
     * Parse the code string and replace the labels with the values entered by
     * the subject.
     *
     * @param array $fields
     *  The array of input field values, see
     *  MermaidFormModel::get_user_field_names_with_values().
     * @param string $code
     *  The original code defined by the experimenter
     * @retval string
     *  The modified code where the editable node labels were replaced by the
     *  user input values stored in the DB.
     */
    public function replace_user_field_values_in_code($fields, $code)
    {
        if($this->is_cms_page())
            return $code;
        foreach($fields as $name => $field)
        {
            $pattern = "~" . $name . "(\[|\(\(|\(|{|>)(.*?)(\]|\)|\)\)|})~";
            preg_match_all($pattern, $code, $matches, PREG_PATTERN_ORDER);
            foreach($matches[0] as $match)
            {
                if($field['value'] == "") continue;
                $val = preg_replace("/[^a-zA-Z0-9_-]+/", "", $field['value']);
                $new = $name.$matches[1][0].$val.$matches[3][0];
                $code = str_replace($match, $new, $code);
            }
        }
        return $code;
    }
}
?>
