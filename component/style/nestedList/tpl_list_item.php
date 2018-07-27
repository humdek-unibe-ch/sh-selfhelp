<a href="#" class="list-group-item list-group-item-action p-1" <?php $this->output_collapse($id, $has_children); ?>>
    <?php $this->output_chevron($has_children); ?>
    <span class="label"><?php echo $name?></span>
</a>
<?php $this->output_children_container($id, $children); ?>
