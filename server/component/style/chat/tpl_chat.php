<div class="card card-body">
    <div class="chat">
    <?php $this->output_msgs(); ?>
    </div>
</div>
    <form action="<?php echo $url; ?>" method="post">
    <div class="row mt-2">
        <div class="col">
            <textarea class="form-control" name="msg"></textarea>
        </div>
        <div class="col-auto align-self-center">
            <button type="submit" class="btn btn-primary"><?php echo $this->label; ?>
        </div>
    </div>
</form>
