<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="<?php echo $this->css; ?>">
    <div class="card">
        <div class="card-header">
            Style Signature
        </div>
        <div class="card-body">
            <div class="card card-body mb-3 bg-light">
                <dl class="mb-0">
                    <dt>Name</dt>
                    <dd><code><?php echo $name; ?></code></dd>
                    <dt>Group</dt>
                    <dd><code><?php echo $group; ?></code></dd>
                    <dt>Type</dt>
                    <dd><code><?php echo $type; ?></code></dd>
                </dl>
            </div>
            <?php echo $description; ?>
        </div>
    </div>
    <div class="card mt-3">
        <div class="card-header">
            Style Fields
        </div>
        <ul class="list-group list-group-flush">
            <?php $this->output_style_fields($fields); ?>
        </ul>
    </div>
</div>
