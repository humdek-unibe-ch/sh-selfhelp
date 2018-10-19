<button class="nested-list-menu-responsive collapsed d-md-none d-auto btn btn-secondary w-100 rounded-0 text-truncate <?php echo $this->css; ?>" type="button">
    <i class="fas fa-bars mr-2"></i><?php echo $title; ?>
</button>
<div class="list-group collapse d-md-block nested-list-menu-collapsible border border-secondary rounded-bottom border-top-0 p-2 w-100">
    <?php $this->output_list(); ?>
</div>
