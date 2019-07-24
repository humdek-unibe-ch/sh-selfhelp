<div id="<?php echo $this->id_section; ?>" class="modal fade <?php echo $this->css ?>">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <?php $this->output_title(); ?>
            <div class="modal-body">
                <?php $this->output_children(); ?>
            </div>
        </div>
    </div>
</div>
