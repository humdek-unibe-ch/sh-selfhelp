<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="modal fade" id="ui-add-section-modal" tabindex="-1" role="dialog" aria-labelledby="ui-add-section-title" aria-hidden="true">
    <div id="ui-add-section" class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div>
                    <h5 class="modal-title" id="ui-add-section-title">Add section</h5>
                    <div id="ui-add-section-target" class="small text-muted"></div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-link active" id="nav-new-section-tab" data-toggle="tab" href="#nav-new-section" role="tab" aria-controls="nav-new-section" aria-selected="true">New Section</a>
                        <a class="nav-link" id="nav-unassigned-section-tab" data-toggle="tab" href="#nav-unassigned-section" role="tab" aria-controls="nav-unassigned-section" aria-selected="false">Unassigned Section</a>
                        <a class="nav-link" id="nav-reference-section-tab" data-toggle="tab" href="#nav-reference-section" role="tab" aria-controls="nav-reference-section" aria-selected="false">Reference Section</a>
                        <?php if ($this->model->can_import_section()) { ?>
                            <a class="nav-link" id="nav-import-section-tab" data-toggle="tab" href="#nav-import-section" role="tab" aria-controls="nav-import-section" aria-selected="false">Import Section</a>
                        <?php } ?>
                    </div>
                </nav>
                <div class="tab-content pt-3" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-new-section" role="tabpanel" aria-labelledby="nav-new-section-tab">
                        <?php $this->output_add_new_section(); ?>
                    </div>
                    <div class="tab-pane fade" id="nav-unassigned-section" role="tabpanel" aria-labelledby="nav-unassigned-section-tab">
                        <p class="small text-muted">Sections which are not used on any page.</p>
                        <div class="d-flex">
                            <?php $this->output_add_unassigned_section(); ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-reference-section" role="tabpanel" aria-labelledby="nav-reference-section-tab">
                        <p class="small text-muted">Use a section which already exists on another page. Changes of the section are visible everywhere it is used.</p>
                        <div class="d-flex">
                            <?php $this->output_add_reference_section(); ?>
                        </div>
                    </div>
                    <?php if ($this->model->can_import_section()) { ?>
                        <div class="tab-pane fade" id="nav-import-section" role="tabpanel" aria-labelledby="nav-import-section-tab">
                            <p class="small text-muted">Import a section (with all its children) which was exported with <em>Export section</em>.</p>
                            <form id="cmsImportJson" class="cms-import-form d-flex w-100" data-ajax="1" action="<?php echo $this->esc($import_url); ?>" method="post" enctype='multipart/form-data'>
                                <div class="flex-grow-1 mr-2">
                                    <div class="custom-file">
                                        <input id="file" type="file" class="custom-file-input" name="file" required accept=".json">
                                        <label class="custom-file-label text-muted">Choose a JSON file</label>
                                    </div>
                                </div>
                                <input id='json' name='json' type="hidden" />
                                <input id='dbVersion' class="cms-import-db-version" type="hidden" value='<?php $this->get_db_version(); ?>' />
                                <input id='appVersion' class="cms-import-app-version" type="hidden" value='<?php $this->get_app_version(); ?>' />
                                <button id="ui-import-section-btn" type="submit" class="btn btn-primary">Import</button>
                            </form>
                            <div id="ui-import-section-error" class="alert alert-danger d-none mt-3 mb-0" role="alert"></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
