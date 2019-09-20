<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><?php echo $title; ?></h5>
    </div>
    <div class="card-body">
        <p><?php echo $text; ?></p>
        <?php echo $form ? $this->output_select_form() : $this->output_export_item_options($options); ?>
    </div>
</div>
