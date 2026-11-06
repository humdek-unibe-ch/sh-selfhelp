<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
// Variables: $picker_id (unique prefix), $picker_name (name of the radios),
// $candidates (CmsModel::get_parent_page_candidates()), $selected (id of the checked parent, 0 = root)
?>
<div class="ui-parent-picker" id="<?php echo $this->esc($picker_id); ?>">
    <div class="ui-parent-picker-search">
        <i class="fas fa-search"></i>
        <input type="search" class="form-control form-control-sm ui-parent-picker-filter" placeholder="Search pages" aria-label="Search pages" autocomplete="off">
    </div>
    <div class="list-group ui-parent-picker-list">
        <label class="list-group-item list-group-item-action ui-parent-picker-item" data-keyword="">
            <input type="radio" class="mr-2" name="<?php echo $this->esc($picker_name); ?>" value="0" <?php echo intval($selected) === 0 ? 'checked' : ''; ?>>
            <i class="fas fa-sitemap fa-fw text-muted mr-1"></i><strong>Root</strong><span class="small text-muted ml-1">(no parent)</span>
        </label>
        <?php foreach ($candidates as $candidate) { ?>
            <label class="list-group-item list-group-item-action ui-parent-picker-item pl-4" data-keyword="<?php echo $this->esc(strtolower($candidate['keyword'])); ?>">
                <input type="radio" class="mr-2" name="<?php echo $this->esc($picker_name); ?>" value="<?php echo intval($candidate['id']); ?>" <?php echo intval($selected) === intval($candidate['id']) ? 'checked' : ''; ?>>
                <i class="far fa-file fa-fw text-muted mr-1"></i><?php echo $this->esc($candidate['keyword']); ?>
            </label>
        <?php } ?>
        <div class="list-group-item small text-muted ui-parent-picker-empty d-none">No page matches the search.</div>
    </div>
</div>
