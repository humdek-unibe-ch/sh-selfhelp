<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";
require_once __DIR__ . "/../StyleComponent.php";
require_once __DIR__ . "/../../user/UserModel.php";

/**
 * This class is used to prepare all data related to the emailFormBase style
 * components such that the data can easily be displayed in the view of the
 * component.
 */
abstract class EmailFormBaseModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'is_html' (false)
     * If set to true the email will be sent with an html body.
     */
    protected $is_html;

    /**
     * DB field 'email_user' (empty string)
     * The email to be sent to the email address that was entered to the form.
     */
    protected $email_user;

    /**
     * DB field 'subject_user' (empty string)
     * The subject of the email to be sent to the email address that was
     * entered to the form.
     */
    protected $subject_user;

    /**
     * DB field 'attachments_user' (empty string)
     * The assets to be attached to the email that will be sent to the address
     * entered to the form.
     */
    protected $attachments_user;

    /**
     * The instance of the user model.
     */
    protected $user;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
     */
    public function __construct($services, $id, $params=array())
    {
        parent::__construct($services, $id, $params);
        $this->email_user = $this->get_db_field("email_user");
        $this->subject_user = $this->get_db_field("subject_user");
        $this->attachments_user = $this->get_db_field(
            "attachments_user", array());
        $this->is_html = $this->get_db_field('is_html', false);
        $this->user = new UserModel($services);
    }

    /* Public Methods *********************************************************/

    /**
     * Defines the actions once a user has entered a valid email address.
     */
    abstract public function perform_email_actions($email);
}
?>
