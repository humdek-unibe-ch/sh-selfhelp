<div class="container mt-3">
    <div class="jumbotron">
    <h1>Success</h1>
        <p>A total of <code><?php echo $count; ?></code> validation codes were successfully created.</p>
        <?php $this->output_collision(); ?>
        <a href="<?php echo $url; ?>" class="btn btn-primary">Download Validation Codes</a>
    </div>
</div>

