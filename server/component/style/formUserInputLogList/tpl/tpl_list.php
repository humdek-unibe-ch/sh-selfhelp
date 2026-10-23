<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
$is_empty = !$rows || count($rows) === 0;
$show_toolbar = !$is_empty;
?>
<?php if ($show_toolbar) { ?>
<div class="formUserInputLogList-toolbar mb-3">
    <?php $this->output_new_button(); ?>
</div>
<?php } ?>
<div class="formUserInputLogList-list">
    <?php if ($is_empty) { ?>
        <?php require __DIR__ . "/tpl_empty.php"; ?>
    <?php } else if ($this->is_cards_layout()) { ?>
        <div class="formUserInputLogList-cards">
            <?php $this->output_list_rows($rows, $headers); ?>
        </div>
    <?php } else { ?>
        <div class="table-responsive formUserInputLogList-table-wrap">
            <table class="table formUserInputLogList-table <?php echo htmlspecialchars($this->css); ?>">
                <thead>
                    <tr>
                        <?php if ($this->has_list_actions() && $this->is_actions_left()) { ?>
                            <th class="formUserInputLogList-col formUserInputLogList-actions-col"></th>
                        <?php } ?>
                        <?php $this->output_list_header_cells($headers); ?>
                        <?php if ($this->has_list_actions() && !$this->is_actions_left()) { ?>
                            <th class="formUserInputLogList-col formUserInputLogList-actions-col"></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $this->output_list_rows($rows, $headers); ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>
