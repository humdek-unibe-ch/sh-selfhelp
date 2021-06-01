<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the container style component.
 * Containers allow to wrap content into a div tag.
 */
class ContainerView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'is_fluid' (false).
     * If set to true the container spand to whole page. If set to false the
     * container only uses a part of the page.
     */
    private $is_fluid;

    /**
     * DB field 'export_pdf' (false).
     * If set to true the container offers export PDF functionality. All children in the container will be exported in a PDF file with javascript
     */
    private $export_pdf;

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
        $this->is_fluid = $this->model->get_db_field('is_fluid', false);
        $this->export_pdf = $this->model->get_db_field('export_pdf', false);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $fluid = ($this->is_fluid) ? "-fluid" : "";
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
        $fluid = ($this->is_fluid) ? "-fluid" : "";
        require __DIR__ . "/tpl_container_entryValue.php";
    }

    public function output_export_pdf_btn()
    {
        if ($this->export_pdf) {
            require __DIR__ . "/tpl_exportPDF_btn.php";
        }
    }

    public function output_content_mobile()
    {
        $style = parent::output_content_mobile();        
        $style['css'] = $style['css'] . ' ' . ($this->is_fluid ? "mobile-container-fluid" : "mobile-container");
        return $style;
    }

    /**
     * Render output as an entry for mobile
     * @param array $entry_value
     * the data for the entry value
     */
    public function output_content_mobile_entry($entry_value)
    {
        $style = parent::output_content_mobile_entry($entry_value);        
        $style['css'] = $style['css'] . ' ' . ($this->is_fluid ? "mobile-container-fluid" : "mobile-container");
        return $style;
    }
}
?>
