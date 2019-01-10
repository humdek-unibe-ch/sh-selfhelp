<div class="card <?php echo $this->css; ?>">
    <div class="card-header">
        <h5 class="m-0"><?php echo $this->login_title; ?></h5>
    </div>
    <div class="card-body">
        <form action="<?php echo $url; ?>" method="post">
            <div class="form-group">
                <input type="text" class="form-control" name="email" placeholder="<?php echo $this->user_label; ?>" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="password" placeholder="<?php echo $this->pw_label; ?>" required>
            </div>
            <button type="submit" class="w-100 btn btn-primary"><?php echo $this->login_label; ?></button>
        </form>
        <a href="<?php echo $reset_url; ?>" class="small float-right"><?php echo $this->reset_label; ?></a>
    </div>
</div>
