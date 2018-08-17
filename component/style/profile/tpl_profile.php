<div class="container-fluid my-3">
    <div class="row">
        <div class="col">
            <?php $this->output_alert_pw_change(); ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="m-0"><?php echo $this->pw_change_title; ?></h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo $url; ?>" method="post">
                        <div class="form-group">
                            <input type="password" class="form-control" name="password" placeholder="<?php echo $this->pw_label; ?>" required>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" name="verification" placeholder="<?php echo $this->pw_confirm_label; ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary"><?php echo $this->pw_change_action_label; ?></button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col">
            <?php $this->output_alert_delete(); ?>
            <div class="card danger">
                <div class="card-header">
                    <h5 class="m-0"><?php echo $this->delete_title; ?></h5>
                </div>
                <div class="card-body">
                    <?php echo $this->delete_content; ?>
                    <div class="mt-3">
                        <button class="btn btn-danger" data-toggle="collapse" data-target="#confirmation" aria-expanded="false" aria-controls="confirmation">
                            <?php echo $this->delete_label; ?>
                        </button>
                    </div>
                    <div class="collapse" id="confirmation">
                        <div class="card card-body">
                            <?php echo $this->delete_confirm_content; ?>
                            <form method="post">
                                <div class="form-group">
                                    <input type="email" class="form-control" name="email" placeholder="<?php echo $this->user_label; ?>" required>
                                </div>
                                <button class="btn btn-danger" >
                                    <?php echo $this->delete_confirm_label; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
