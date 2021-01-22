<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="mb-3">
    <table id="mailQueue" class="table table-sm table-hover" data-transactions = '<?php echo $this->output_mail_queue_transactions(); ?>'>
        <thead>
            <tr>
                <th scope="col"></th>
                <th scope="col">ID</th>
                <th scope="col">Status</th>
                <th scope="col">Type</th>
                <th scope="col">Entry date</th>
                <th scope="col">Date to be sent</th>
                <th scope="col">Sent date</th>
                <th scope="col">From email</th>
                <th scope="col">From name</th>
                <th scope="col">Reply to</th>
                <th scope="col">Recipent emails</th>
                <th scope="col">CC</th>
                <th scope="col">BCC</th>
                <th scope="col">Subject</th>
                <th scope="col">Is HTML</th>
            </tr>
        </thead>
        <tbody>
            <?php $this->output_mail_queue_rows(); ?>
        </tbody>
    </table>
</div>
