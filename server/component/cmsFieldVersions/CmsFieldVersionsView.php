<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";

/**
 * The view of the field version history. The CMS opens it in a modal
 * (js/field_versions.js loads the element `.cms-field-versions` of this page);
 * it also works as a page of its own.
 */
class CmsFieldVersionsView extends BaseView
{
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
    }

    /* Private Methods ********************************************************/

    /**
     * Render the alert messages of the controller.
     */
    private function output_alert()
    {
        $this->output_controller_alerts_fail();
        $this->output_controller_alerts_success();
    }

    /**
     * Render the list of versions.
     *
     * @param array $versions
     *  See CmsFieldVersionsModel::get_versions().
     * @param array|false $selected
     *  The selected version.
     */
    private function output_versions($versions, $selected)
    {
        foreach ($versions as $version) {
            $is_selected = $selected && intval($selected['id']) === intval($version['id']);
            $url = $this->model->get_version_url(intval($version['version_no']));
            require __DIR__ . "/tpl_version_item.php";
        }
    }

    /**
     * Render the selected version: its details, the restore button and the
     * changes compared to the current content.
     *
     * @param array|false $selected
     *  The selected version.
     */
    private function output_selected($selected)
    {
        if (!$selected) {
            echo '<div class="cms-fv-empty">This field has no content yet.</div>';
            return;
        }
        $rows = $this->model->get_diff($selected['content'], $this->model->get_current_content());
        $added = count(array_filter($rows, function ($row) { return $row['type'] === 'add'; }));
        $removed = count(array_filter($rows, function ($row) { return $row['type'] === 'del'; }));
        $confirmation = array(
            "confirmation_title" => "Restore version " . intval($selected['version_no']) . "?",
            "confirmation_message" => "The content of version " . intval($selected['version_no'])
                . " becomes the current content of the field right away. The current content stays in the history.",
            "confirmation_continue" => "Restore",
            "confirmation_cancel" => "Cancel"
        );
        require __DIR__ . "/tpl_selected.php";
    }

    /**
     * Render the changes from the selected version to the current content.
     *
     * @param array $rows
     *  See CmsFieldVersionsModel::get_diff().
     */
    private function output_diff($rows)
    {
        if (count($rows) === 0) {
            echo '<div class="cms-fv-empty">No changes: this version is the same as the current content.</div>';
            return;
        }
        require __DIR__ . "/tpl_diff.php";
    }

    /**
     * Format a date of a version for the list.
     */
    private function format_date($date)
    {
        return htmlspecialchars(date('Y-m-d H:i', strtotime($date)));
    }

    /* Public Methods *********************************************************/

    /**
     * Render the page.
     */
    public function output_content()
    {
        $labels = $this->model->get_labels();
        $versions = $this->model->get_versions();
        $selected = $this->model->get_selected_version();
        $back_url = $this->model->get_back_url();
        require __DIR__ . "/tpl_cmsFieldVersions.php";
    }

    public function output_content_mobile()
    {
        echo 'mobile';
    }

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        $local = array(__DIR__ . "/css/field_versions.css");
        return parent::get_css_includes($local);
    }
}
?>
