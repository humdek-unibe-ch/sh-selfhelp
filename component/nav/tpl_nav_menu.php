<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle <?php echo $active; ?>" href="<?php echo $url; ?>" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expand="false">
        <?php echo $page_name; ?>
    </a>
    <div class="dropdown-menu <?php echo $align; ?>">
        <?php $this->output_nav_menu_items($children); ?>
    </div>
</li>
