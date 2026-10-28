<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
// Variables: $node (see CmsView::output_outline_actions).
?>
<span class="ui-outline-actions">
    <?php if ($node['pinned']) { ?>
        <?php if ($node['list']) { ?>
            <button type="button" class="ui-outline-btn text-success" data-action="add-inside" title="Add a section inside">
                <i class="fas fa-plus-circle"></i>
            </button>
        <?php } ?>
    <?php } else { ?>
        <span class="dropdown">
            <button type="button" class="ui-outline-btn text-success" data-toggle="dropdown"
                data-boundary="window" aria-haspopup="true" aria-expanded="false" title="Add a section">
                <i class="fas fa-plus-circle"></i>
            </button>
            <span class="dropdown-menu dropdown-menu-right">
                <button type="button" class="dropdown-item" data-action="add-above">
                    <i class="fas fa-level-up-alt fa-fw mr-1"></i>Add section above
                </button>
                <button type="button" class="dropdown-item" data-action="add-below">
                    <i class="fas fa-level-down-alt fa-fw mr-1"></i>Add section below
                </button>
                <?php if ($node['list']) { ?>
                    <button type="button" class="dropdown-item" data-action="add-inside">
                        <i class="fas fa-sign-in-alt fa-fw mr-1"></i>Add section inside
                    </button>
                <?php } ?>
            </span>
        </span>
        <button type="button" class="ui-outline-btn text-primary" data-action="move-up" title="Move up">
            <i class="fas fa-arrow-up"></i>
        </button>
        <button type="button" class="ui-outline-btn text-primary" data-action="move-down" title="Move down">
            <i class="fas fa-arrow-down"></i>
        </button>
        <button type="button" class="ui-outline-btn text-danger" data-action="remove" title="Remove the section from the page">
            <i class="fas fa-times-circle"></i>
        </button>
    <?php } ?>
</span>
