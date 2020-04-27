<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<nav class="navbar navbar-expand-lg <?php echo $css ?>">
  <a class="navbar-brand hidden" href="<?php echo $leadingLink['url'] ?>"><?php echo $leadingLink['title'] ?></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
    <i class="fas fa-bars"></i>
  </button>
  <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
    <div class="navbar-nav">      
      <?php $this->output_navbar_links($items); ?>
    </div>
  </div>
</nav>