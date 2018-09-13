<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Create New Page</h1>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Page Properties Details</h5>
        </div>
        <div class="card-body">
            <form action="<?php echo $action_url; ?>" method="post">
                <div class="form-group">
                    <label>Keyword</label>
                    <input type="text" class="form-control" name="keyword" placeholder="Enter keyword" required>
                    <small class="form-text text-muted">The page keyword must be unique.</small>
                </div>
                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="url-manual" value="1">
                        <label class="text-muted">Url Pattern</label>
                    </div>
                    <input type="text" class="form-control" name="url" value="" placeholder="automatic" required readonly>
                    <small class="form-text text-muted">This is set automatically. If you know what you are doing you may overwrite the value. Refer to the documentation of <a href="http://altorouter.com/usage/mapping-routes.html">Altorouter</a> for more information.</small>
                </div>
                <div class="form-group">
                    <div>
                        <label>Protocol</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="protocol[]" value="GET" checked>
                        <label class="form-check-label">GET</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="protocol[]" value="POST">
                        <label class="form-check-label">POST</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="protocol[]" value="PUT" disabled>
                        <label class="form-check-label">PUT</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="protocol[]" value="PATCH" disabled>
                        <label class="form-check-label">PATCH</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="protocol[]" value="DELETE" disabled>
                        <label class="form-check-label">DELETE</label>
                    </div>
                    <small class="form-text text-muted">The protocol specifies how a page is accessed. <code>GET</code> is required to display the content of a page and <code>POST</code> is required to send forms to the page.</small>
                </div>
                <div class="form-group">
                    <div>
                        <label>Page Type</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" value="3" checked>
                        <label class="form-check-label">Sections</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" value="4">
                        <label class="form-check-label">Navigation</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" value="2" disabled>
                        <label class="form-check-label text-muted">Component</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" value="1"i disabled>
                        <label class="form-check-label text-muted">Custom</label>
                    </div>
                    <small class="form-text text-muted">The page type specified how the page content will be assembled. It is recommended to either use the type <code>Sections</code> or <code>Navigation</code> (no PHP programming required).</small>
                </div>
                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="set-position" value="<?php echo $this->position_value; ?>">
                        <label class="text-muted">Header Position</label>
                    </div>
                    <div id="page-order-wrapper" class="d-none">
                    <?php $this->output_page_order(); ?>
                    </div>
                    <small class="form-text text-muted">When activated, once the page title field is set, the page will appear in the header at the specified position (drag and drop). If not activated, the page will <strong>not</strong> appear in the header.</small>
                </div>
                <button type="submit" class="btn btn-primary">Create</button>
                <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary float-right">Cancel</a>
            </form>
        </div>
    </div>
</div>
