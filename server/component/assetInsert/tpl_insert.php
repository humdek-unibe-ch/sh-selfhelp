<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="card card-header mb-4 rounded-2 py-5 px-3">
        <h1><?php echo $title[$this->mode]; ?></h1>
        <p>Browse for the file you want to upload and provide a name under which the file will be stored on the server.</p>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Upload</h5>
        </div>
        <div class="card-body">
            <div class="d-none"><i class="fas fa-spinner fa-pulse fa-lg me-3"></i>Uploading the file to the server</div>
            <form id="asset-upload-form" action="<?php echo $action_url; ?>" method="post" enctype='multipart/form-data'>
                <div class="row">
                    <?php $this->output_folder(); ?>
                    <div class="mb-3 col">
                        <label>Name</label>
                        <input id="assetsFileName" type="text" class="form-control" name="name" placeholder="Enter a file name" required>
                    </div>
                    <div class="mb-3 col">
                        <label>File</label>
                        <input type="file" class="form-control" name="file" required>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="overwrite">
                    <label class="form-check-label">Overwrite a file with the same name.</label>
                </div>
                <button id="asset-upload-button" type="submit" class="btn btn-primary">Upload</button>
                <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary float-end">Cancel</a>
            </form>
        </div>
    </div>
</div>