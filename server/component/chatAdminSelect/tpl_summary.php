<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<p>There is a total of <strong><?php echo $this->user_count; ?></strong> user<?php echo ($this->user_count !== 1) ? "s" : ""; ?> in this room where <strong><?php echo $this->mod_count?></strong> user<?php echo ($this->mod_count !== 1) ? "s are" : " is a"; ?> <em><code>Therapist<?php echo ($this->mod_count !== 1) ? "s" : ""; ?></code></em> and <strong><?php echo $subj_count; ?></strong> user<?php echo ($subj_count !== 1) ? "s are" : " is a"; ?> <code>Subject<?php echo ($subj_count !== 1) ? "s" : ""; ?></code>.</p>
