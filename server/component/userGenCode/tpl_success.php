<div class="container mt-3">
    <div class="jumbotron">
    <h1>Success</h1>
        <p>A total of <code><?php echo $count; ?></code> validation codes were successfully created and added to the database.</p>
        <?php $this->output_collision(); ?>
        <?php $this->output_export_buttons(); ?>
        <a href="<?php echo $url; ?>" class="btn btn-secondary">Create More Validation Codes</a>
    </div>
</div>

