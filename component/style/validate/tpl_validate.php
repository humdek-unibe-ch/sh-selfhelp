<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1><?php echo $this->title; ?></h1>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $this->subtitle; ?></h5>
        </div>
        <div class="card-body">
            <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
                <div class="form-group">
                    <label><?php echo $this->name_label; ?></label>
                    <input type="text" class="form-control" name="name" placeholder="<?php echo $this->name_placeholder; ?>" required>
                    <small class="form-text text-muted"><?php echo $this->name_description; ?></small>
                </div>
                <div class="form-group">
                    <label><?php echo $this->pw_label; ?></label>
                    <input type="password" class="form-control mb-1" name="pw" placeholder="<?php echo $this->pw_placeholder; ?>" required>
                    <input type="password" class="form-control" name="pw_verify" placeholder="<?php echo $this->pw_confirm_label; ?>" required>
                </div>
                <div class="form-group">
                    <div>
                    <label><?php echo $this->gender_label; ?></label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" value="1" required>
                        <label class="form-check-label"><?php echo $this->gender_male; ?></label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" value="2" required>
                        <label class="form-check-label"><?php echo $this->gender_female; ?></label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo $this->activate_label; ?></button>
            </form>
        </div>
    </div>
</div>
