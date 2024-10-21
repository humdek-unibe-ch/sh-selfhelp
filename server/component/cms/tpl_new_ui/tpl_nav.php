<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container-fluid p-0">
    <nav class="navbar navbar-expand-lg bg-body-tertiary" id="nav-menu">
        <a class="navbar-brand" href="<?php echo $home_url; ?>">
            <?php echo $home['title']; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto">
                <?php
                    $this->output_nav_items();
                ?>
            </ul>
            <ul class="navbar-nav">
                <?php
                    if($_SESSION['logged_in'])
                    {
                        $this->output_profile();
                    }
                    else if($login)
                        $this->output_login();
                ?>
            </ul>
        </div>
    </nav>
</div>
