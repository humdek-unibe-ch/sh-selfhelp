<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Delete <?php echo ucfirst($this->target); ?></h1>
        <p>This will delete the <?php echo $this->target; ?> <code><?php echo $name; ?></code> and all the data associated to this <?php echo $this->target; ?>.</p>
        <p>Children elements are not affected.</p>
    </div>
    <?php $this->output_form(); ?>
</div>
