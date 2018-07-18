<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle <?php echo $this->get_active_css($key); ?>" href="<?php echo $this->router->generate($key); ?>" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expand="false">
        <?php echo $page_name; ?>
    </a>
    <div class="dropdown-menu <?php echo $align; ?>">
        <?php $this->output_nav_menu_items($children); ?>
    </div>
</li>
