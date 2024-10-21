<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="bg-light mb-4 rounded-2 py-5 px-3">
        <h1>Create New Page</h1>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Page Properties</h5>
        </div>
        <div class="card-body">
            <form action="<?php echo $action_url; ?>" method="post">
                <div class="mb-3">
                    <label class="form-label">Keyword</label>
                    <input type="text" class="form-control" name="keyword" placeholder="Enter keyword" required pattern="<?php echo NAME_PATTERN ?>">
                    <small class="form-text text-body-secondary">The page keyword must be unique, otherwise the page creation will fail. <b>Note that the page keyword can contain numbers, letters, - and _ characters</b></small>                    
                </div>
                <div class="mb-3">
                    <div>
                        <label class="form-label">Page Type</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" value="3" checked>
                        <label class="form-check-label form-label">Sections</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" value="4">
                        <label class="form-check-label form-label">Navigation</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" value="2" disabled>
                        <label class="form-check-label form-label">Component</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" value="1" disabled>
                        <label class="form-check-label form-label">Backend</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" value="5" disabled>
                        <label class="form-check-label form-label">Ajax</label>
                    </div>
                    <small class="d-block form-text text-body-secondary">The page type specified how the page content will be assembled. It is recommended to either use the type <code>Sections</code> or <code>Navigation</code>. Pages of type <code>Component</code> and <code>Custom</code> require PHP programming and cannot be handled by the CMS.</small>
                </div>
                <div id="header-position" class="mb-3 d-flex flex-column">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="set-position" value="<?php echo $this->position_value; ?>">
                        <label class="form-check-label text-body-secondary form-label">Header Position</label>
                    </div>
                    <div id="page-order-wrapper" class="d-none">
                    <?php $this->output_page_order(); ?>
                    </div>
                    <small class="form-text text-body-secondary">When activated, once the page title field is set, the page will appear in the header at the specified position (drag and drop). If not activated, the page will <strong>not</strong> appear in the header.</small>
                </div>
                <div id="headless-check" class="mb-3 d-none">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="set-headless" value="1">
                        <label class="form-check-label form-label">Headless Page</label>
                    </div>
                    <small class="form-text text-body-secondary">A headless page will <strong>not</strong> render any header or footer.</small>
                </div>
                <div class="mb-3">
                    <div class="form-check-inline w-100">
                        <?php $this->output_page_access_type(); ?>
                    </div>
                    <small class="form-text text-body-secondary">Page access type: mobile, web or mobile_and_web</small>
                </div>
                <div id="protocol-list" class="mb-3 d-none">
                    <div>
                        <label class="form-label">Protocol</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="protocol[]" value="GET" checked>
                        <label class="form-check-label form-label">GET</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="protocol[]" value="POST" checked>
                        <label class="form-check-label form-label">POST</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="protocol[]" value="PUT">
                        <label class="form-check-label form-label">PUT</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="protocol[]" value="PATCH">
                        <label class="form-check-label form-label">PATCH</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="protocol[]" value="DELETE">
                        <label class="form-check-label form-label">DELETE</label>
                    </div>
                    <small class="d-block form-text text-body-secondary">The protocol specifies how a page is accessed. <code>GET</code> is required to display the content of a page and <code>POST</code> is required to send forms to the page. <code>PUT</code>, <code>PATCH</code>, and <code>DELETE</code> may only become necessary for pages of type <code>Component</code> or <code>Custom</code>.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Url Pattern</label>
                    <input type="text" class="form-control" name="url" value="" placeholder="automatic" required readonly>
                    <small class="form-text text-body-secondary">This is set automatically. If you know what you are doing you may overwrite the value. Refer to the documentation of <a href="https://dannyvankooten.github.io/AltoRouter/usage/mapping-routes.html">Altorouter</a> for more information.</small>
                </div>
                <div class="mb-3 d-flex flex-column">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="set-open" value="1">
                        <label class="form-check-label">Open Access</label>
                    </div>
                    <small class="form-text text-body-secondary">When activated the page will be accessible by anyone without having to log in.</small>
                </div>
                <div class="mb-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="set-advanced" value="1">
                        <label class="form-check-label form-label">Advanced</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Create</button>
                <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary float-end">Cancel</a>
            </form>
        </div>
    </div>
</div>
