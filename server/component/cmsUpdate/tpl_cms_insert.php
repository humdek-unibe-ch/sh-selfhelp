<div class="container-fluid mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Add Section</h1>
        <p>Add a section to the <?php echo $child; ?> list of <?php echo $target; ?>
        Either a new section can be created or an already existing section can be chosen.</p>
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
                        <div class="form-group form-check">
                            <input type="checkbox" name="new-section" class="form-check-input" checked disabled>
                            <label class="form-check-label">Create New Section</label>
                        </div>
                        <div class="row">
                            <div class="col form-group">
                                <label>Name Prefix</label>
                                <input type="text" name="section-name-prefix" class="form-control" placeholder="Enter Section Name" required>
                            </div>
                            <div class="col form-group">
                                <label>Name</label>
                                <input type="text" name="section-name" class="form-control" value="-" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Style</label>
                            <select class="form-control" name="section-style" required>
                                <option disabled selected value>-- select a style --</option>
                                <?php $this->output_style_list(); ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Section</button>
                        <a href="<?php echo $url_cancel; ?>" class="btn btn-secondary float-right">Cancel</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-6 order-md-3 order-xl-2">
                <div class="card mb-3">
                <div class="card-header">
                    Style Selection Helper
                </div>
                <div class="card-body">
                    <?php $this->output_style_tabs(); ?>
                </div>
                </div>
            </div>
            <div class="col col-xl-auto order-md-2 order-xl-3 select-section-list">
                <?php $this->output_section_search_list(); ?>
            </div>
        </div>
    </form>
</div>
