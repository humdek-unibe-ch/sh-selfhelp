<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
// Variables: $node, $index, $editable, $depth, $auto_name, $label, $secondary,
// $has_children, $classes, $search (see CmsView::output_outline_node).
?>
<li class="<?php echo $classes; ?>"
    data-id="<?php echo intval($node['id']); ?>"
    data-index="<?php echo intval($index); ?>"
    data-position="<?php echo intval($node['position']); ?>"
    data-name="<?php echo $this->esc($node['name']); ?>"
    data-style="<?php echo $this->esc($node['style']); ?>"
    data-search="<?php echo $this->esc($search); ?>">
    <div class="ui-outline-row<?php echo $node['selected'] ? ' is-selected' : ''; ?>">
        <?php if ($has_children) { ?>
            <button type="button" class="ui-outline-twisty" data-action="toggle"
                aria-label="Show or hide the sections inside">
                <i class="fas fa-chevron-down"></i>
            </button>
        <?php } else { ?>
            <span class="ui-outline-twisty is-empty"></span>
        <?php } ?>
        <?php if ($editable && !$node['pinned']) { ?>
            <i class="fas fa-grip-vertical ui-outline-handle" title="Drag to move the section"></i>
        <?php } ?>
        <a class="ui-outline-link"
           href="<?php echo $this->esc($node['url']); ?>"
           title="<?php echo $this->esc($node['name'] . ' (' . $node['style'] . ', id ' . $node['id'] . ')'); ?>">
            <span class="badge badge-secondary ui-outline-style"><?php echo $this->esc($node['style']); ?></span>
            <span class="ui-outline-name<?php echo $auto_name ? ' is-auto' : ''; ?>"><?php echo $this->esc($label); ?></span>
            <?php if ($secondary !== '') { ?>
                <span class="ui-outline-snippet"><?php echo $this->esc($secondary); ?></span>
            <?php } ?>
        </a>
        <span class="ui-outline-id">#<?php echo intval($node['id']); ?></span>
        <?php if ($editable) {
            $this->output_outline_actions($node);
        } ?>
    </div>
    <?php if ($node['list']) {
        $this->output_outline_list($node['list'], $editable, $depth + 1);
    } ?>
</li>
