<div>
    <div class="px-1 session-nav-link <?php echo $active; ?>">
        <a href="<?php echo $child['url']; ?>">
            <?php echo $child['title']; ?>
        </a>
    </div>
    <div class="ml-3">
        <?php $this->output_nav_children($child['children']); ?>
    </div>
</div>
