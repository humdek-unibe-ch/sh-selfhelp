<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php // Variable: $groups (see CmsModel::get_grouped_style_list) ?>
<p class="small text-muted">Choose the style of the new section. Type to search by name or description.</p>
<div class="d-flex">
    <select id="ui-new-section-select" class="ui-select-picker border rounded flex-grow-1" data-live-search="true" data-size="10">
        <option disabled selected value>-- select a style --</option>
        <?php foreach ($groups as $group) { ?>
            <optgroup label="<?php echo $this->esc($group['name']); ?>">
                <?php foreach ($group['styles'] as $style) { ?>
                    <option value="<?php echo intval($style['value']); ?>"
                        data-tokens="<?php echo $this->esc($style['text'] . ' ' . $style['description']); ?>"
                        data-description-html="<?php echo $this->esc($style['description_html']); ?>"><?php echo $this->esc($style['text']); ?></option>
                <?php } ?>
            </optgroup>
        <?php } ?>
    </select>
    <button id="ui-new-section-btn" type="button" class="btn btn-primary ml-2">Add</button>
</div>
<div id="ui-new-section-description" class="ui-add-section-description d-none" aria-live="polite"></div>
