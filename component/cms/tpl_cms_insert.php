<div class="container mt-3">
    <div class="jumbotron">
        <h1>Add Section</h1>
        <p>Add a section to the <?php echo $child; ?> list of <?php echo $target; ?></p>
        <p>Either a new section can be created or an already existing section can be chosen.</p>
        <p><strong>Note, a sections refers to a single set of section fields. This is important when using the same section in different places as changes to section fields will affect all views of the section.</strong></p>
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
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="section-name" class="form-control" placeholder="Enter Section Name" required>
                        </div>
                        <div class="form-group">
                            <label>Style</label>
                            <select class="form-control" name="section-style" required>
                                <option disabled selected value>-- select an option --</option>
                                <?php $this->output_style_list(); ?>
                            </select>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" name="new-section" class="form-check-input" checked disabled>
                            <label class="text-muted">Create New Section</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Section</button>
                        <a href="<?php echo $url; ?>" class="btn btn-secondary float-right">Cancel</a>
                    </div>
                </div>
            </div>
            <div class="col-auto select-section-list">
                <?php $this->output_section_search_list(); ?>
            </div>
        </div>
    </form>
</div>
