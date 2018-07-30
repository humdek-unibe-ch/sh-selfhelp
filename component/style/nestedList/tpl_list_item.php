<a href="<?php echo $url; ?>" class="list-group-item list-group-item-action p-1 <?php echo $active; ?>" <?php $this->output_collapse($id, $has_children, $is_expanded); ?>>
    <?php $this->output_chevron($has_children, $is_expanded); ?>
    <span class="label"><?php echo $name?></span>
</a>
<?php $this->output_children_container($id, $children, $is_expanded); ?>
