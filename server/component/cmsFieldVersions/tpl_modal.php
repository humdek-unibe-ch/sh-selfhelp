<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="modal fade" id="cms-field-versions-modal" tabindex="-1" role="dialog" aria-labelledby="cms-field-versions-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <div class="cms-fv-min0">
                    <h5 class="modal-title" id="cms-field-versions-title"><i class="fas fa-history text-muted mr-2"></i>History of <span class="cms-fv-title"></span></h5>
                    <small class="text-muted cms-fv-subtitle"></small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
    <template class="cms-fv-loading-template">
        <div class="cms-fv-loading"><i class="fas fa-spinner fa-pulse fa-lg"></i></div>
    </template>
    <template class="cms-fv-error-template">
        <div class="alert alert-danger m-3" role="alert"></div>
    </template>
</div>
