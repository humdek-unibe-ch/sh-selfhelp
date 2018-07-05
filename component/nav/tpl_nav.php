<div class="container-fluid p-0">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="<?php echo $this->router->generate( 'home' ); ?>">
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
                    $this->output_nav_item('login', $login);
                ?>
            </ul>
            </a>
        </div>
    </nav>
</div>
