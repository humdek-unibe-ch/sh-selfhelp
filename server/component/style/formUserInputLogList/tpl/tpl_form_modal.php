<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="modal fade formUserInputLogList-modal" id="<?php echo htmlspecialchars($this->modal_id); ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title formUserInputLogList-modal-title">
                    <?php
                    echo htmlspecialchars(
                        $this->model->get_url_record_id() > 0 ? $this->label_edit : $this->label_new
                    );
                    ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php $this->output_form_section(true); ?>
            </div>
        </div>
    </div>
</div>
