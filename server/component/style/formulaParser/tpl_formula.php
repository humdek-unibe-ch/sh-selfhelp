<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div>
    Formula <br>
    Variables <?php echo var_dump($this->formula['variables'])?> <br>
    <?php echo $this->formula['formula'] . " = " . $result ?>
    <br>
    stats_cdf_normal(5, 3, 2, 1) * 100 <br>
    CDF <?php echo $cdf ?><br>
    CDF2 <?php echo $cdf2 *100 ?>
</div>
