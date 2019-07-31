<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Remove File <code><?php echo $this->file_name; ?></code> from the Server</h1>
        <p>Removing a file from the server will delete this file permanently. This cannot be undone.</p>
    </div>
    <?php $this->output_form_rm_file(); ?>
</div>
