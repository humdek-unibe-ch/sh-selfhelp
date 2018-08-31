<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Delete Group</h1>
        <p>This will delete the group <code><?php echo $this->selected_group['name']; ?></code> and all the data associated to this group.</p>
    </div>
    <?php $this->output_form(); ?>
</div>
