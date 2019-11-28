<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";
/**
 * This class is used to prepare all data related to the chat component such
 * that the data can easily be displayed in the view of the component.
 */
class JsonModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all chat related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The id of the section id of the chat wrapper.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
    }

    /* Private Methodes *******************************************************/

    /**
     * Return a markdown style when an error ocurs in json style parsing.
     *
     * @param bool $is_child
     *  True if the content must be returned as style object, false if the
     *  contnet must be returned as HTML string.
     * @param string $msg
     *  The error message.
     * @retval mixed
     *  Either an HTML string or a style object (see param $is_child).
     */
    private function json_style_return_error($is_child, $msg)
    {
        $style = new BaseStyleComponent('markdownInline', array(
            "text_md_inline" => "**ERROR**: " . $msg
        ));
        if(!$is_child)
            return $this->json_style_to_html($style);
        else
            return $style;
    }

    /**
     * Take a style object, render it and return the HTML code.
     *
     * @param object $style
     *  A style component to render.
     * @retval string
     *  An HTML string
     */
    private function json_style_to_html($style)
    {
        ob_start();
        $style->output_content();
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /* Public Methods *********************************************************/

    /**
     * Parses a json array to find `baseStyle` keys. Such keys are then
     * transformed to HTML string to be rendered on the screen. This is a
     * recursive function.
     *
     * @param array $j_array
     *  The json array to be parsed.
     * @param string $parent_key
     *  The key of the parent element.
     * @param bool $requires_base_style
     *  If set to true the child obejct must have at least one base style key.
     * @param bool $is_child
     *  A flag indicating whether children are processed or the final root
     *  element (the root needs to perform an output buffering of the style).
     * @retval mixed
     *  The parsed element.
     */
    public function json_style_parse($j_array, $parent_key="root",
        $requires_base_style=false, $is_child=false)
    {
        if(!is_array($j_array))
            return $j_array;

        $arr = array();
        $has_base_style = false;
        foreach($j_array as $key => $item)
        {
            // check if a json style key was misspelled
            if($key[0] === "_" && $key !== "_baseStyle"
                && $key !== "_name" && $key !== "_fields")
            {
                return $this->json_style_return_error($is_child,
                    "unknown field name `" . $key . "` in style `json`");
            }
            // distinguish between a children field and any other
            if($key === "children")
            {
                $is_child = true;
                $children = array();
                if(!is_array($item))
                    $children[] = $this->json_style_return_error($is_child,
                        "the field `children` must have an array value");
                else
                    foreach($item as $child_key => $child)
                    {
                        if(!is_numeric($child_key))
                        {
                            $children[] = $this->json_style_return_error($is_child,
                                "the field `children` must have an array value, object detected");
                            break;
                        }
                        $children[] = $this->json_style_parse($child, $child_key, true, $is_child);
                    }
                $item = $children;
            }
            else
                $item = $this->json_style_parse($item, $key, false, $is_child);
            // process style fields
            if($key === "_baseStyle")
            {
                $has_base_style = true;
                if(!isset($item['_name']))
                    return $this->json_style_return_error($is_child,
                        "invalid baseStyle definition: key `_name` is undefined");
                if(!isset($item['_fields']))
                    return $this->json_style_return_error($is_child,
                        "invalid `" . $item['_name'] . "` baseStyle definition: key `_fields` is undefined");
                $style = new BaseStyleComponent($item['_name'], $item['_fields']);
                if(!$is_child)
                    return $this->json_style_to_html($style);
                else
                    return $style;
            }
            $arr[$key] = $item;
        }
        if(!$has_base_style && $requires_base_style)
        {
            return $this->json_style_return_error($is_child,
                "field `_baseStyle` was expected but not found in key `" . $parent_key . "`");
        }
        return $arr;
    }
}
?>
