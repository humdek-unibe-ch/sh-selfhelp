<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <?php echo $this->text; ?>
            <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
                <div class="form-group">
                    <input type="text" class="form-control" name="email" placeholder="<?php echo $this->placeholder; ?>" required>
                </div>
                <div class="form-group d-none">
                    <label>Leave this field empty</label>
                    <input type="text" class="form-control" name="phone7h92jP" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-primary"><?php echo $this->reset_label; ?></button>
            </form>
    </div>
</div>
