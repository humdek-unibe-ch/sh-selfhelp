<div class="section-children-ui-cms">
        <?php if (isset($this->model->get_params()['title'])) {
            // if we have that parameter we load from cms, show the children
            $this->output_children();
        } ?>
    </div>
<div class="<?php echo $this->css; ?>">
    <?php $this->output_messages(); ?>
</div>
