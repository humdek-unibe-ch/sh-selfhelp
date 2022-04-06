<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="modal" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div id="ui-add-style" class="modal-dialog position-absolute mt-0 mb-0" role="document">
        <div class="modal-content">
            <div class="modal-header pt-1 pb-1">
                <h5 class="modal-title" id="staticBackdropLabel">Add Section</h5>
                <button type="button" class="close p-1 m-0" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-2">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-link active" id="nav-new-section-tab" data-toggle="tab" href="#nav-new-section" role="tab" aria-controls="nav-new-section" aria-selected="true">New Section</a>
                        <a class="nav-link" id="nav-unassigned-section-tab" data-toggle="tab" href="#nav-unassigned-section" role="tab" aria-controls="nav-unassigned-section" aria-selected="false">Unassigned Section</a>
                        <a class="nav-link" id="nav-reference-section-tab" data-toggle="tab" href="#nav-reference-section" role="tab" aria-controls="nav-reference-section" aria-selected="false">Reference Section</a>
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
                </div>
            </div>
        </div>
    </div>
</div>