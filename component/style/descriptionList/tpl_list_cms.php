<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
    <?php $this->output_field_items($fields, $mode); ?>
    <button type="submit" class="btn btn-dark">Submit Changes</button>
</form>
