<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="selfHelp-language-picker selfHelp-language-picker--<?php echo $style; ?> <?php echo $this->css; ?>"
     data-redirect="<?php echo htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8'); ?>">
<?php if ($this->label !== "") { ?>
    <span class="selfHelp-language-picker__label"><?php echo htmlspecialchars($this->label, ENT_QUOTES, 'UTF-8'); ?></span>
<?php } ?>
<?php if ($style === "select") { ?>
    <select class="form-control selfHelp-language-picker__select">
    <?php foreach ($languages as $language) { ?>
        <option value="<?php echo intval($language['id']); ?>"<?php echo ($current == $language['id']) ? ' selected' : ''; ?>>
            <?php echo htmlspecialchars($language['title'], ENT_QUOTES, 'UTF-8'); ?>
        </option>
    <?php } ?>
    </select>
<?php } else { ?>
    <?php foreach ($languages as $language) { ?>
        <button type="button" class="selfHelp-language-picker__button"
                data-language="<?php echo intval($language['id']); ?>"
                <?php echo ($current == $language['id']) ? 'aria-current="true"' : ''; ?>>
            <?php echo htmlspecialchars($language['title'], ENT_QUOTES, 'UTF-8'); ?>
        </button>
    <?php } ?>
<?php } ?>
</div>
