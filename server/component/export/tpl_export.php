<div class="container mt-3">
    <div class="jumbotron">
        <h1><?php echo $title; ?></h1>
        <p><?php echo $text; ?></p>
    </div>
    <?php $this->output_export_item("user_input"); ?>
    <?php $this->output_export_item("user_input_form"); ?>
    <?php $this->output_export_item("user_activity"); ?>
    <?php $this->output_export_item("validation_codes"); ?>
</div>
