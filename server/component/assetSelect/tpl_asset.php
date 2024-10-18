<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <div class="bg-light mb-4 rounded-2 py-5 px-3">
        <h1>Manage Assets and CSS Files</h1>
        <p>Manage asset and CSS files. Asset files can be referenced in the CMS and CSS files can be used to customize the look and feel of the webpage.</p>
    </div>
    <?php $this->output_assets(assetTypes_asset); ?>
    <?php $this->output_assets(assetTypes_css); ?>
    <?php $this->output_assets(assetTypes_static); ?>
</div>
