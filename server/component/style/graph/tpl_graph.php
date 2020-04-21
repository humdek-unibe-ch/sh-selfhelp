<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="position-relative graph-<?php echo $this->graph_type; ?> <?php echo $this->css; ?>">
    <div class="graph-data d-none"><?php echo $this->output_graph_data(); ?></div>
    <div class="graph-opts d-none"><?php echo $this->output_graph_opts(); ?></div>
    <div class="graph-pending d-none text-center fa-5x"><i class="text-muted fas fa-spinner fa-spin align-middle"></i></div>
    <div class="graph-plot"></div>
</div>
