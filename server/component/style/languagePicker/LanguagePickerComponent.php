<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/../StyleModel.php";
require_once __DIR__ . "/LanguagePickerView.php";

/**
 * A component class for the language picker style component.
 *
 * Renders the languages configured in the CMS and sets the session language to
 * the one chosen, the same thing the footer picker does. It exists as a style
 * so it can be placed on pages the footer does not reach - headless pages, and
 * login and register, which come before a user has a profile to read a
 * preference from.
 */
class LanguagePickerComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes the view instance to the constructor of the parent
     * class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of this component.
     * @param array $params
     *  The list of get parameters to propagate.
     * @param number $id_page
     *  The id of the parent page
     * @param array $entry_record
     *  An array that contains the entry record information.
     */
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        $model = new StyleModel($services, $id, $params, $id_page, $entry_record);
        $view = new LanguagePickerView($model);
        parent::__construct($model, $view);
    }
}
?>
