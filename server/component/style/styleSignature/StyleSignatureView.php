<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the styleSignature style component.
 * This style component allows to render meta data about a style.
 */
class StyleSignatureView extends StyleView
{
    /* Private Properties******************************************************/

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
    }

    /* Private  Methods *******************************************************/

    /* Public Methods *********************************************************/

    /**
     * Render the style fields.
     *
     * @param array $fields
     *  The fields of the style. Refer to
     *  StyleSignatureModel::get_styke_fields() for more information on the
     *  available keys.
     */
    public function output_style_fields($fields)
    {
        foreach($fields as $field) {
            $name = $field['name'];
            $description = $this->model->text_md($field['description']);
            $type = $field['type'];
            require __DIR__ . "/tpl_styleSignature_field.php";
        }
    }

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $info = $this->model->get_style_info();
        if($info) {
            $fields = $this->model->get_style_fields($info['id']);
            $tabs = new BaseStyleComponent("tabs", array(
                "css" => $this->css,
                "children" => array(
                    new BaseStyleComponent("tab", array(
                        "id" => $this->id_section . "-0",
                        "label" => "Style Signature",
                        "is_expanded" => true,
                        "children" => array(
                            new BaseStyleComponent("template", array(
                                "path" => __DIR__ . "/tpl_styleSignature.php",
                                "items" => $info
                            ))
                        )
                    )),
                    new BaseStyleComponent("tab", array(
                        "id" => $this->id_section . "-1",
                        "label" => "Style Fields",
                        "children" => array(
                            new BaseStyleComponent("template", array(
                                "path" => __DIR__ . "/tpl_styleSignature_fields.php",
                                "items" => array(
                                    "fields" => $fields,
                                    "parent" => $this
                                )
                            ))
                        )
                    ))
                )
            ));
            $tabs->output_content();
        }
    }
}
?>
