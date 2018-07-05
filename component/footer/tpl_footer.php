<div class="container-fluid bg-light p-3">
    <div class="footer">
        <a class="text-dark small <?php echo $this->get_active_css( 'impressum' ); ?>" href="<?php echo $this->router->generate('impressum'); ?>">
            <?php echo $impressum; ?>
        </a> |
        <a class="text-dark small <?php echo $this->get_active_css( 'disclaimer' ); ?>" href="<?php echo $this->router->generate('disclaimer'); ?>">
            <?php echo $disclaimer; ?>
        </a> |
        <a class="text-dark small <?php echo $this->get_active_css( 'agb' ); ?>" href="<?php echo $this->router->generate('agb'); ?>">
            <?php echo $agb; ?>
        </a>
    </div>
</div>
