<div class="container mt-3">
    <div class="jumbotron">
        <h1>Add Navigation Section</h1>
        <p>Add a navigation section to the <?php echo $child; ?> list of <?php echo $target; ?></p>
        <p>The root section of a navigation page must always be a <code>navigationContainer</code>, thus, no other option is available.</p>
    </div>
    <form action="<?php echo $url; ?>" method="post">
        <input type="hidden" value="" name="add-section-link">
        <input type="hidden" value="insert" name="mode">
        <input type="hidden" value="<?php echo $relation; ?>" name="relation">
        <div class="row">
            <div class="col">
                <div class="card mb-3">
                    <div class="card-header">
                        Add Section
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col form-group">
                                <label>Name Prefix</label>
                                <input type="text" name="section-name-prefix" class="form-control" placeholder="Enter Section Name" required>
                            </div>
                            <div class="col form-group">
                                <label>Name</label>
                                <input type="text" name="section-name" class="form-control" value="-navigationContainer" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Style</label>
                            <input type="hidden" name="section-style" value="<?php echo NAVIGATION_CONTAINER_STYLE_ID; ?>" readonly>
                            <input class="form-control" name="section-style-dummy" value="navigationContainer" disabled>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Section</button>
                        <a href="<?php echo $url_cancel; ?>" class="btn btn-secondary float-right">Cancel</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-auto select-section-list">
                <?php $this->output_section_search_list(); ?>
            </div>
        </div>
    </form>
</div>
