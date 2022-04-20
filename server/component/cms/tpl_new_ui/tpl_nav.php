<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container-fluid p-0">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="<?php echo $home_url; ?>">
            <?php echo $home; ?>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <?php
                    $this->output_nav_items();
                ?>
            </ul>
            <ul class="navbar-nav navbar-right">
                <?php
                    if($_SESSION['logged_in'])
                    {
                        $this->output_nav_chat();
                        $this->output_profile();
                    }
                    else if($login)
                        $this->output_nav_item('login', $login, null, $login_is_active);
                ?>
            </ul>
        </div>
    </nav>
</div>
