<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="modal fade" id="ui-import-page-modal" tabindex="-1" role="dialog" aria-labelledby="ui-import-page-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="ui-import-page-form" class="cms-import-form" data-ajax="1" action="<?php echo $this->esc($import_url); ?>" method="post" enctype="multipart/form-data"
                data-taken-keywords="<?php echo $this->esc(json_encode($taken_keywords)); ?>">
                <div class="modal-header py-2">
                    <h5 class="modal-title" id="ui-import-page-title">Import page</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Import a page which was exported with <em>Export page</em>. A new page with a copy of all its sections is created; child pages in the file are imported as its child pages.</p>
                    <div class="form-group">
                        <label>JSON file</label>
                        <div class="custom-file">
                            <input id="ui-import-page-file" type="file" class="custom-file-input" name="file" required accept=".json">
                            <label class="custom-file-label text-muted" for="ui-import-page-file">Choose a JSON file</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ui-import-page-keyword">Keywords of the new pages</label>
                        <div class="ui-import-page-list">
                            <div class="input-group input-group-sm mb-1">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="far fa-file fa-fw"></i></span></div>
                                <input id="ui-import-page-keyword" type="text" class="form-control ui-import-page-keyword" name="page_keyword" pattern="[a-zA-Z0-9_\-]+" maxlength="100" required autocomplete="off" aria-label="Keyword of the new page">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div id="ui-import-page-children"></div>
                        </div>
                        <small class="form-text text-muted">Only letters, numbers, <code>-</code> and <code>_</code>. A keyword must not be used by another page.</small>
                    </div>
                    <div class="form-group mb-0">
                        <label class="mb-1">Parent page</label>
                        <div id="ui-import-page-parent-note" class="small text-muted mb-1 d-none">This file contains child pages, so it can only be imported as a root page: the page tree has two levels.</div>
                        <?php
                        $picker_id = "ui-import-page-parent";
                        $picker_name = "parent";
                        $selected = 0;
                        require __DIR__ . "/tpl_parent_page_picker.php";
                        ?>
                    </div>
                    <input name="json" type="hidden" />
                    <input class="cms-import-db-version" type="hidden" value="<?php $this->get_db_version(); ?>" />
                    <input class="cms-import-app-version" type="hidden" value="<?php $this->get_app_version(); ?>" />
                    <div id="ui-import-page-error" class="alert alert-danger d-none mt-3 mb-0" role="alert"></div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button id="ui-import-page-submit" type="submit" class="btn btn-primary">Import page</button>
                </div>
            </form>
        </div>
    </div>
</div>
