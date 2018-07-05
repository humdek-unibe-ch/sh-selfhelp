<li class="nav-item">
    <a class="nav-link <?php echo $this->get_active_css( $key ); ?>" href="<?php echo $this->router->generate( $key ); ?>">
        <?php echo $page_name; ?>
    </a>
</li>
