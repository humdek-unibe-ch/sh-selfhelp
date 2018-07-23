<div class="container-fluid p-0">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="<?php echo $home_url; ?>">
            <?php echo $home; ?>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <?php
                    $this->output_nav_items();
                ?>
            </ul>
            <ul class="navbar-nav navbar-right">
                <?php
                    if($_SESSION['logged_in'])
                        $this->output_nav_menu('profile', $profile['title'], $profile['children'], true);
                    else
                        $this->output_nav_item('login', $login);
                ?>
            </ul>
        </div>
    </nav>
</div>
