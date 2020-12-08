<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the plaintext style component.
 * A plaintext style supports the following fields:
 *  'text': The text to be rendered.
 */
class SearchView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'label' (Search).
     * The label for the search button
     */
    private $label;

    /**
     * DB field 'placeholder' (empty string).
     * The placeholder text in the input field
     */
    private $placeholder;

    /**
     * Prefix string for the search
     */
    private $prefix;

    /**
     * Suffix string for the search
     */
    private $suffix;

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
        $this->label = $this->model->get_db_field('label', 'Search');
        $this->placeholder = $this->model->get_db_field('placeholder', '');
        $this->prefix = $this->model->get_db_field('prefix', '');
        $this->suffix = $this->model->get_db_field('suffix', '');
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {

        $style = new BaseStyleComponent("form", array(
            "css" => "d-flex " . $this->css,
            "label" => $this->label,
            "url" => $this->model->get_services()->get_router()->get_url('#self'),
            'children' => array(
                new BaseStyleComponent("input", array(
                    "type_input" => "text",
                    "name" => "search_param",
                    "is_required" => true,
                    "placeholder" => $this->placeholder,
                    "css" => "w-auto mr-3",
                ))
            )
        ));
        $style->output_content();
    }
}
?>
