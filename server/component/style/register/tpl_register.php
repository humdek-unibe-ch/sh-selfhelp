<div class="card <?php echo $this->css; ?>">
    <div class="card-header">
        <h5 class="m-0"><?php echo $this->title; ?></h5>
    </div>
    <div class="card-body">
        <?php $this->output_alert(); ?>
        <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
            <div class="form-group">
                <input type="email" class="form-control" name="email" placeholder="<?php echo $this->user_label; ?>" required>
            </div>
            <div class="form-group">
                <input type="text" class="form-control" name="code" placeholder="<?php echo $this->code_label; ?>" required>
            </div>
            <button type="submit" class="w-100 btn btn-primary"><?php echo $this->submit_label; ?></button>
        </form>
    </div>
</div>
