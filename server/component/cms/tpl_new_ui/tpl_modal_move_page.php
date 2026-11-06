<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="modal fade" id="ui-move-page-modal" tabindex="-1" role="dialog" aria-labelledby="ui-move-page-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="ui-move-page-form" action="<?php echo $this->esc($move_url); ?>" method="post"
                data-page-id="<?php echo intval($page['id']); ?>"
                data-page-keyword="<?php echo $this->esc($page['keyword']); ?>"
                data-parent="<?php echo intval($page['parent']); ?>"
                data-in-menu="<?php echo $page['nav_position'] !== null && $page['nav_position'] !== '' ? '1' : '0'; ?>"
                data-menus="<?php echo $this->esc(json_encode($menus)); ?>">
                <div class="modal-header py-2">
                    <div>
                        <h5 class="modal-title" id="ui-move-page-title">Move page</h5>
                        <div class="small text-muted"><code><?php echo $this->esc($page['keyword']); ?></code></div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if ($has_children) { ?>
                        <div class="alert alert-info small py-2">This page has child pages, so it stays a root page: the page tree has two levels. Move its child pages first if it should become a child page.</div>
                    <?php } ?>
                    <div class="form-group">
                        <label class="mb-1">New parent</label>
                        <?php
                        $picker_id = "ui-move-page-parent";
                        $picker_name = "parent";
                        $selected = intval($page['parent']);
                        require __DIR__ . "/tpl_parent_page_picker.php";
                        ?>
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="ui-move-page-in-menu">
                            <label class="custom-control-label" for="ui-move-page-in-menu">Show in the header menu</label>
                        </div>
                        <div id="ui-move-page-menu" class="mt-2 d-none">
                            <small class="form-text text-muted mt-0 mb-1" id="ui-move-page-menu-help"></small>
                            <ul class="list-group ui-move-page-menu-list" id="ui-move-page-menu-list"></ul>
                        </div>
                    </div>
                    <div id="ui-move-page-error" class="alert alert-danger d-none mt-3 mb-0" role="alert"></div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button id="ui-move-page-submit" type="submit" class="btn btn-primary">Move page</button>
                </div>
            </form>
        </div>
    </div>
</div>
