<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../progressBar/ProgressBarView.php";

/**
 * The view class of the userProgress style component.
 * This style components allows to represent the progress of a user.
 */
class UserProgressView extends ProgressBarView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->count_max = 100;
        $this->count = $model->get_user_progress();
    }
}
?>
