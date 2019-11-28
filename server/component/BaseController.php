<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
/**
 * The class to define the basic functionality of a controller.
 */
abstract class BaseController
{
    /* Protected Properties ***************************************************/

    /**
     * The model instance of the component.
     */
    protected $model;

    /**
     * The success status.
     */
    protected $success;

    /**
     * The fail status.
     */
    protected $fail;

    /**
     * The error messages to be set if a failure occurred.
     */
    protected $error_msgs = array();

    /**
     * The success messages to be set if the oeration was successful.
     */
    protected $success_msgs = array();

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        $this->model = $model;
        $this->success = false;
        $this->fail = false;
    }

    /* Protected Methods ******************************************************/

    /**
     * Convert an abreviated file size string (e.g. '5MB') into bytes.
     *
     * @retval int
     *  The file size in bytes
     */
    protected function convert_to_bytes($from) {
        $units = ['', 'K', 'M', 'G', 'T'];
        $number = intval(substr($from, 0, -1));
        $suffix = substr($from,-1);

        $exponent = array_flip($units)[$suffix] ?? null;
        if($exponent === null) {
            return null;
        }

        return $number * (1024 ** $exponent);
    }

    /* Public Methods *********************************************************/

    /**
     * Return the error message array
     *
     * @retval array
     *  The error messages.
     */
    public function get_error_msgs()
    {
        return $this->error_msgs;
    }

    /**
     * Return the success message array
     *
     * @retval array
     *  The success messages.
     */
    public function get_success_msgs()
    {
        return $this->success_msgs;
    }

    /**
     * Returns the failure status
     *
     * @retval bool
     *  true if the operation has failed, false otherwise.
     */
    public function has_failed()
    {
        return $this->fail;
    }

    /**
     * Returns the success status
     *
     * @retval bool
     *  true if the operation has succeeded, false otherwise.
     */
    public function has_succeeded()
    {
        return $this->success;
    }
}
?>
