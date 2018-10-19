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
     * The error message to be set if a failure occurred.
     */
    protected $error_msg = "";

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
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $number = substr($from, 0, -2);
        $suffix = strtoupper(substr($from,-2));

        //B or no suffix
        if(is_numeric(substr($suffix, 0, 1))) {
            return preg_replace('/[^\d]/', '', $from);
        }

        $exponent = array_flip($units)[$suffix] ?? null;
        if($exponent === null) {
            return null;
        }

        return $number * (1024 ** $exponent);
    }

    /* Public Methods *********************************************************/

    /**
     * Return the error message string
     *
     * @retval string
     *  The error message.
     */
    public function get_error_msg()
    {
        return $this->error_msg;
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
