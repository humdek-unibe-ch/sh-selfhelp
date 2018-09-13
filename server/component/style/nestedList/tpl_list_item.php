<div class="list-group-item list-group-item-action p-0 <?php echo $collapsible; ?>">
    <?php $this->output_chevron($has_children, $is_expanded); ?>
    <?php $this->output_list_item_name($item, $active, $id_html); ?>
</div>
<?php $this->output_children_container($children, $is_expanded); ?>
