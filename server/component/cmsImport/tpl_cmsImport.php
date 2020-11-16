<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Import <?php echo $this->type; ?></h1>
        <p>Browse for the JSON file you want to import in the CMS.</p>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Import</h5>
        </div>
        <div class="card-body">
            <div class="d-none"><i class="fas fa-spinner fa-pulse fa-lg mr-3"></i>Uploading the file to the server</div>
            <form id="asset-upload-form" action="<?php echo $action_url; ?>" method="post" enctype='multipart/form-data'>
                <div class="row">
                    <div class="form-group col">
                        <label>File</label>
                        <div class="custom-file">
                            <input id="file" type="file" class="custom-file-input" name="file" required accept=".json">
                            <label class="custom-file-label text-muted">Choose a JSON file</label>
                        </div>
                    </div>
                </div>
                <input id='json' name='json' type="hidden" />
                <button id="asset-upload-button" type="submit" class="btn btn-primary">Import</button>
                <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary float-right">Cancel</a>
            </form>
        </div>
    </div>
</div>