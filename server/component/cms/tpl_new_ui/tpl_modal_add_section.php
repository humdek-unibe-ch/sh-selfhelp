<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="modal" id="ui-add-section-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div id="ui-add-section" class="modal-dialog position-absolute mt-0 mb-0" role="document">
        <div class="modal-content">
            <div class="modal-header pt-1 pb-1">
                <h5 class="modal-title" id="staticBackdropLabel">Add Section</h5>
                <button type="button" class="btn-close p-1 m-0" data-bs-dismiss="modal" aria-label="Close">
                    
                </button>
            </div>
            <div class="modal-body p-2">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-link active" id="nav-new-section-tab" data-bs-toggle="tab" href="#nav-new-section" role="tab" aria-controls="nav-new-section" aria-selected="true">New Section</a>
                        <a class="nav-link" id="nav-unassigned-section-tab" data-bs-toggle="tab" href="#nav-unassigned-section" role="tab" aria-controls="nav-unassigned-section" aria-selected="false">Unassigned Section</a>
                        <a class="nav-link" id="nav-reference-section-tab" data-bs-toggle="tab" href="#nav-reference-section" role="tab" aria-controls="nav-reference-section" aria-selected="false">Reference Section</a>
                        <a class="nav-link" id="nav-import-section-tab" data-bs-toggle="tab" href="#nav-import-section" role="tab" aria-controls="nav-import-section" aria-selected="false">Import Section</a>
                    </div>
                </nav>
                <div class="tab-content pt-2" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-new-section" role="tabpanel" aria-labelledby="nav-new-section-tab">
                        <div class="d-flex">
                            <?php $this->output_add_new_section(); ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-unassigned-section" role="tabpanel" aria-labelledby="nav-unassigned-section-tab">
                        <div class="d-flex">
                            <?php $this->output_add_unassigned_section(); ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-reference-section" role="tabpanel" aria-labelledby="nav-reference-section-tab">
                        <div class="d-flex">
                            <?php $this->output_add_reference_section(); ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-import-section" role="tabpanel" aria-labelledby="nav-import-section-tab">
                        <div class="d-flex">
                            <form id="cmsImportJson" action="<?php echo $import_url; ?>" class="d-flex w-100" method="post" enctype='multipart/form-data'>
                                    <div class="flex-grow-1 me-2">
                                        <div class="custom-file">
                                            <input id="file" type="file" class="form-control" name="file" required accept=".json">
                                            <label class="form-label text-muted">Choose a JSON file</label>
                                        </div>
                                    </div>
                                    <input id='json' name='json' type="hidden" />
                                    <input id='dbVersion' type="hidden" value='<?php $this->get_db_version(); ?>' />
                                    <input id='appVersion' type="hidden" value='<?php $this->get_app_version(); ?>' />
                                    <button id="ui-import-section-btn" type="submit" class="btn btn-primary ">Import</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>