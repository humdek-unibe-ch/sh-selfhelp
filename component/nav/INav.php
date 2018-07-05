<?php
/**
 * The interface definition for the naviagion component view.
 */
interface INav
{
    /**
     * Return the home string
     *
     * @retval string
     *  The login string.
     */
    public function get_home();

    /**
     * Return the login string.
     *
     * @retval string
     *  The login string.
     */
    public function get_login();

    /**
     * Return the pages array.
     *
     * @retval array
     *  An associative array of the form {string $key => string $name}
     */
    public function get_pages();
}
?>
