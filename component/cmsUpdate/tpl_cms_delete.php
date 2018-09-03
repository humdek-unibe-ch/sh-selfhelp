<div class="container mt-3">
    <div class="jumbotron">
        <h1>Remove Section</h1>
        <p>This will remove the section <code><?php echo $del_section; ?></code> from the <?php echo $child; ?> list of <?php echo $target; ?>
 However, it will not delete the section. All data of the section and its subsections will remain intact.</p>
    </div>
    <form action="<?php echo $url; ?>" method="post">
        <input type="hidden" value="<?php echo $did; ?>" name="remove-section-link">
        <input type="hidden" value="delete" name="mode">
        <input type="hidden" value="<?php echo $relation; ?>" name="relation">
        <div class="card mb-3">
            <div class="card-header">
                Remove Section
            </div>
            <div class="card-body">
                <button type="submit" class="btn btn-danger">Remove Section</button>
                <a href="<?php echo $url_cancel; ?>" class="btn btn-secondary float-right">Cancel</a>
            </div>
        </div>
    </form>
</div>
