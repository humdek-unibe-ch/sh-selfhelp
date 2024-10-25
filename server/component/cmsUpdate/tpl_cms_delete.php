<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <div class="card card-header mb-4 rounded-2 py-5 px-3">
        <h1>Remove Section Relation</h1>
        <p>This will remove the section <code><?php echo $del_section; ?></code> from the <?php echo $child; ?> list of <?php echo $target; ?>
 However, it will not delete the section. All data of the section and its subsections will remain intact.</p>
    </div>
    <form action="<?php echo $url; ?>" method="post">
        <input type="hidden" value="<?php echo $did; ?>" name="remove-section-link">
        <input type="hidden" value="delete" name="mode">
        <input type="hidden" value="<?php echo $relation; ?>" name="relation">
        <div class="card mb-3 card-warning">
            <div class="card-header">
                Remove Section Relation
            </div>
            <div class="card-body">
                <button type="submit" class="btn btn-warning">Remove Section Relation</button>
                <a href="<?php echo $url_cancel; ?>" class="btn btn-secondary float-end">Cancel</a>
            </div>
        </div>
    </form>
</div>
