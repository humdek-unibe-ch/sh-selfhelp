<div class="card mb-3">
    <div class="card-body">
        <p>The database holds <code><?php echo $count; ?></code> validation codes. <code><?php echo $count_consumed; ?></code> of those are consumed which leaves <strong><code><?php echo $count_open; ?></code> open for assignment</strong>.</p>
        <?php $this->output_export_buttons(); ?>
    </div>
</div>
