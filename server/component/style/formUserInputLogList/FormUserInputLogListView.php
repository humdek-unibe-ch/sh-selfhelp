<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../formUserInput/FormUserInputView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * View for formUserInputLogList: list + modal/page form + delete confirm.
 */
class FormUserInputLogListView extends FormUserInputView
{
    private $show_form_in_modal;
    private $label_new;
    private $label_edit;
    private $label_delete;
    private $delete_title;
    private $delete_content;
    private $can_delete = false;
    private $modal_id;
    private $delete_modal_id;
    private $ful_confirmation_title;
    private $ful_confirmation_cancel;
    private $ful_confirmation_continue;
    private $ful_confirmation_message;
    private $ful_url_cancel;
    private $ful_label_cancel;

    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->is_log = true;
        $this->show_form_in_modal = (bool)$this->model->get_db_field("show_form_in_modal", 1);
        $this->label_new = $this->model->get_db_field("label_new", "New");
        $this->label_edit = $this->model->get_db_field("label_edit", "Edit");
        $this->label_delete = $this->model->get_db_field("label_delete", "");
        $this->delete_title = $this->model->get_db_field("delete_title", "Remove Entry");
        $this->delete_content = $this->model->get_db_field("delete_content", "Do you want to remove the entry?");
        $this->can_delete = $this->label_delete !== "";
        $this->modal_id = "formUserInputLogList-modal-" . $this->id_section;
        $this->delete_modal_id = "formUserInputLogList-delete-modal-" . $this->id_section;
        $this->ful_confirmation_title = $this->model->get_db_field("confirmation_title", '');
        $this->ful_confirmation_cancel = $this->model->get_db_field("label_cancel", '');
        $this->ful_confirmation_continue = $this->model->get_db_field("confirmation_continue", '');
        $this->ful_confirmation_message = $this->model->get_db_field("confirmation_message", '');
        $this->ful_label_cancel = $this->model->get_db_field("label_cancel", '');
        $this->ful_url_cancel = $this->model->get_db_field("url_cancel", '');
        if ($this->ful_url_cancel === '' && !$this->show_form_in_modal) {
            $this->ful_url_cancel = $this->model->get_list_url();
        }
    }

    public function output_content()
    {
        // CMS: keep a visible children holder. Runtime puts the form (and its
        // `.section-children-ui-cms`) inside a Bootstrap modal by default, so the
        // CMS "Add new section" button would never be usable for inputs.
        if ($this->model->is_cms_page()) {
            $root_css = trim($this->css . " formUserInputLogList");
            require __DIR__ . "/tpl/tpl_root_cms.php";
            return;
        }
        if ($this->name === "") {
            return;
        }
        if ($this->model->is_record_access_denied()) {
            // Denied deep-link (?record=…&sid=…): missing page only — never the list.
            $this->output_missing();
            return;
        }
        $root_css = trim($this->css . " formUserInputLogList");
        $config = $this->get_frontend_config();
        require __DIR__ . "/tpl/tpl_root.php";
    }

    public function output_content_mobile()
    {
        $style = parent::output_content_mobile();
        $style['list_rows'] = $this->model->get_list_rows();
        $style['list_headers'] = $this->model->get_list_headers();
        $style['can_delete'] = $this->can_delete;
        $style['show_form_in_modal'] = $this->show_form_in_modal;
        $style['label_new'] = $this->label_new;
        $style['label_edit'] = $this->label_edit;
        $style['label_delete'] = $this->label_delete;
        $style['url_param'] = $this->model->get_url_param_name();
        $style['record_access_denied'] = $this->model->is_record_access_denied();
        $style['is_log']['content'] = 1;
        return $style;
    }

    /**
     * DEBUG loads per-style js/css via BasePage::collect_style_includes().
     * Production uses gulp bundles (styles.min.js / styles.min.css).
     * Do not also push get_js_includes paths (would double-load in DEBUG).
     */

    protected function output_list_section()
    {
        if (!$this->model->should_show_list()) {
            return;
        }
        $rows = $this->model->get_list_rows();
        $headers = $this->model->get_list_headers();
        require __DIR__ . "/tpl/tpl_list.php";
    }

    /**
     * @param bool $for_modal
     */
    protected function output_form_section($for_modal = false)
    {
        if ($for_modal) {
            if (!$this->model->should_show_modal_form()) {
                return;
            }
        } else {
            if (!$this->model->should_show_page_form()) {
                return;
            }
        }
        $this->selected_record_id = $this->model->get_selected_record_id();
        $children = $this->model->get_children();
        $this->propagate_input_field_settings($children, false);
        if ($this->selected_record_id > 0) {
            $entry_record = $this->model->get_entry_record();
            if ($entry_record) {
                $this->propagate_input_fields($children, $entry_record);
            }
        }
        $children[] = new BaseStyleComponent("input", array(
            "type_input" => "hidden",
            "name" => "__form_name",
            "value" => htmlentities($this->name),
        ));
        $children[] = new BaseStyleComponent("input", array(
            "type_input" => "hidden",
            "name" => "ajax",
            "value" => $this->ajax,
        ));
        $children[] = new BaseStyleComponent("input", array(
            "type_input" => "hidden",
            "name" => "is_log",
            "value" => 1,
        ));
        $redirect_link = str_replace("/", "", $this->redirect_at_end);
        $redirect_link = $this->model->get_services()->get_router()->get_url($redirect_link);
        if ($redirect_link === '' || $redirect_link === false) {
            $redirect_link = '';
        }
        $children[] = new BaseStyleComponent("input", array(
            "type_input" => "hidden",
            "name" => "redirect_at_end",
            "value" => $redirect_link,
        ));
        if ($this->selected_record_id > 0) {
            $children[] = new BaseStyleComponent("input", array(
                "type_input" => "hidden",
                "name" => SELECTED_RECORD_ID,
                "value" => $this->selected_record_id,
            ));
        }
        // Form cancel button needs both label and url (ButtonView skips empty).
        // Modal: dismiss via JS; page: return to list / url_cancel.
        if ($for_modal) {
            $cancel_label = $this->ful_label_cancel !== '' ? $this->ful_label_cancel : 'Cancel';
            $cancel_url = '#';
        } else {
            $cancel_label = $this->ful_label_cancel;
            $cancel_url = $this->ful_url_cancel !== ''
                ? $this->model->get_services()->get_router()->get_url($this->ful_url_cancel)
                : $this->model->get_list_url();
        }
        $url = $_SERVER['REQUEST_URI'] . '#section-' . $this->id_section;
        $form = new BaseStyleComponent("form", array(
            "label" => $this->label,
            "label_cancel" => $cancel_label,
            "type" => $this->type,
            "url" => $url,
            "children" => $children,
            "css" => "selfHelp-form formUserInputLogList-form-inner",
            "id" => $this->id_section,
            "confirmation_title" => $this->ful_confirmation_title,
            "confirmation_cancel" => $this->ful_confirmation_cancel,
            "confirmation_continue" => $this->ful_confirmation_continue,
            "confirmation_message" => $this->ful_confirmation_message,
            "url_cancel" => $cancel_url,
        ));
        require __DIR__ . "/tpl/tpl_form.php";
    }

    protected function output_form_modal()
    {
        if (!$this->model->should_show_modal_form()) {
            return;
        }
        require __DIR__ . "/tpl/tpl_form_modal.php";
    }

    protected function output_delete_modal()
    {
        if (!$this->can_delete) {
            return;
        }
        $modal = new BaseStyleComponent('modal', array(
            'id' => $this->delete_modal_id,
            'title' => $this->delete_title,
            'css' => 'formUserInputLogList-delete-modal',
            'children' => array(
                new BaseStyleComponent('markdown', array(
                    "text_md" => $this->delete_content,
                )),
                new BaseStyleComponent('form', array(
                    "id" => $this->id_section . "-delete",
                    "type" => 'danger',
                    'url' => $_SERVER['REQUEST_URI'] . '#section-' . $this->id_section,
                    'label' => $this->label_delete,
                    'css' => 'formUserInputLogList-delete-form',
                    'children' => array(
                        new BaseStyleComponent('input', array(
                            'name' => '__form_name',
                            'type_input' => "hidden",
                            'value' => htmlentities($this->name),
                        )),
                        new BaseStyleComponent('input', array(
                            'name' => 'ajax',
                            'type_input' => "hidden",
                            'value' => $this->ajax,
                        )),
                        new BaseStyleComponent('input', array(
                            'name' => DELETE_RECORD_ID,
                            'type_input' => "hidden",
                        )),
                    ),
                )),
            ),
        ));
        $modal->output_content();
    }

    protected function output_list_header_cells($headers)
    {
        foreach ($headers as $title) {
            require __DIR__ . "/tpl/tpl_header_cell.php";
        }
    }

    protected function output_list_rows($rows, $headers)
    {
        foreach ($rows as $row) {
            require __DIR__ . "/tpl/tpl_row.php";
        }
    }

    protected function output_list_row_cells($row, $headers)
    {
        foreach ($headers as $header) {
            $value = isset($row[$header]) ? $row[$header] : '';
            $is_html = $this->model->is_list_header_html($header);
            require __DIR__ . "/tpl/tpl_cell.php";
        }
    }

    private function get_frontend_config()
    {
        return array(
            'sectionId' => $this->id_section,
            'modalId' => $this->modal_id,
            'deleteModalId' => $this->delete_modal_id,
            'showFormInModal' => $this->show_form_in_modal,
            'urlParam' => $this->model->get_url_param_name(),
            'urlSectionParam' => $this->model->get_url_section_param_name(),
            'urlRecordId' => $this->model->get_url_record_id(),
            'urlRecordRaw' => $this->model->get_url_record_raw(),
            'listUrl' => $this->model->get_list_url(),
            'newUrl' => $this->model->get_record_url('new'),
            'canDelete' => $this->can_delete,
            'labelNew' => $this->label_new,
            'labelEdit' => $this->label_edit,
        );
    }
}
?>
