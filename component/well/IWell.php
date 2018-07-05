<?php
/**
 * The interface definition for the naviagion component view.
 */
interface IWell
{
    /**
     * Return the title string
     *
     * @retval string
     *  The title string.
     */
    public function get_title();

    /**
     * Return the content string.
     *
     * @retval string
     *  The content string.
     */
    public function get_content();
}
?>
