<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-auto">
            <?php $this->output_lists(); ?>
        </div>
        <div class="col">
            <div class="row">
                <?php $this->output_alerts(); ?>
                <?php $this->output_fields(); ?>
                <div class="col">
                    <?php $this->output_page_content(); ?>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <?php $this->output_controls(); ?>
        </div>
    </div>
</div>
<div id="create-new-section" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Section</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" value="" name="add-section-link">
                    <div class="select-section-list">
                        <?php $this->output_section_search_list(); ?>
                    </div>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Insert Section</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="remove-section-association" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remove Section From List</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post">
                <input type="hidden" value="" name="remove-section-link">
                <div class="modal-body">
                    <p>
                        This will remove the section from the current list but not delete the section.
                        All the sections data and its subsections will remain intact.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Remove Section</button>
                </div>
            </form>
        </div>
    </div>
</div>
