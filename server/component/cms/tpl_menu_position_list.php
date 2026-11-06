<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
// The position of a page in the header menu: the page is highlighted and can be dragged or moved with the arrows,
// the other menu pages are fixed. Used by the Header Position of the page properties, Create New Page and (built in
// JS with the same markup) the Move Page modal.
// Variables: $menu_items (keys 'id', 'title' and 'is_self'), $menu_list_id (optional id of the list).
// data-index is the position of the item when the list was rendered: the order is saved as `set-position`.
if (!isset($menu_items)) {
    // rendered by the `template` style: its items are in $fields
    $menu_items = $fields['items'];
}
?>
<ul class="list-group children-list ui-menu-position-list"<?php echo isset($menu_list_id) ? ' id="' . htmlspecialchars($menu_list_id) . '"' : ''; ?>>
    <?php foreach ($menu_items as $index => $item) { ?>
        <?php if (!empty($item['is_self'])) { ?>
            <li class="list-group-item py-1 d-flex align-items-center ui-menu-position-self list-group-item-primary" data-index="<?php echo intval($index); ?>">
                <i class="fas fa-grip-vertical mr-2"></i>
                <strong class="flex-grow-1 label"><?php echo htmlspecialchars($item['title']); ?></strong>
                <button type="button" class="btn btn-sm btn-link py-0 ui-menu-position-step" data-step="-1" aria-label="Move up"><i class="fas fa-arrow-up"></i></button>
                <button type="button" class="btn btn-sm btn-link py-0 ui-menu-position-step" data-step="1" aria-label="Move down"><i class="fas fa-arrow-down"></i></button>
            </li>
        <?php } else { ?>
            <li class="list-group-item py-1 d-flex align-items-center fixed text-muted" data-index="<?php echo intval($index); ?>">
                <span><?php echo htmlspecialchars($item['title']); ?></span>
            </li>
        <?php } ?>
    <?php } ?>
</ul>
