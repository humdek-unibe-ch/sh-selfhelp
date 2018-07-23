<div class="container my-3">
    <?php $this->output_alert(); ?>
    <div class="row">
        <div class="col">
            <div class="d-flex align-items-end flex-column">
                <h1><?php echo $intro_title; ?></h1>
                <p><?php echo $intro_content; ?></p>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-0"><?php echo $login_title; ?></h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo $url; ?>" method="post">
                        <div class="form-group">
                            <input type="email" class="form-control" name="email" placeholder="<?php echo $user_label; ?>" required>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" name="password" placeholder="<?php echo $pw_label; ?>" required>
                        </div>
                        <button type="submit" class="w-100 btn btn-primary"><?php echo $login_label; ?></button>
                    </form>
                    <a href="#" class="small float-right"><?php echo $reset_label; ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
