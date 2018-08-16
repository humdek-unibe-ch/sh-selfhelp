<ul class="children-list list-group <?php echo $sortable; ?>">
    <?php $this->output_list_new_button($is_sortable); ?>
    <?php $this->output_list_items($items, $is_sortable); ?>
</ul>
