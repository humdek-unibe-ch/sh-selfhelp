<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/ext/Parsedown.php";

/**
 * Extension Class to the Parsedown service.
 */
class ParsedownExtension extends Parsedown
{
    /**
     * The private attribute to access the instance of the service class
     * UserInput.
     */
    private $user_input;

    /**
     * The private attribute to access the instance of the service class
     * Router.
     */
    private $router;

    /**
     * The constructor to add the new inline parser for user form fields.
     *
     * @param instance $user_input
     *  An instance of the service class UserInput.
     * @param instance $router
     *  An instance of the service class Router.
     */
    function __construct($user_input = null, $router = null)
    {
        $this->user_input = $user_input;
        $this->router = $router;

        $this->InlineTypes['@'][]= 'UserFormField';
        $this->inlineMarkerList .= '@';
    }

    /**
     * Extend the parsedown parser with user form fields. Using the syntax
     * defined in UserInput::get_input_value_pattern() user form fields can be
     * placed in markdown and, thus, allow to format user input fileds in any
     * way imaginable (as HTML is allowed in markdown).
     */
    protected function inlineUserFormField($excerpt)
    {
        if($this->user_input === null)
            return null;
        $pattern = '/' . $this->user_input->get_input_value_pattern() . '/';
        if (preg_match($pattern, $excerpt['text'], $matches))
        {
            $value = $this->user_input->get_input_value_by_pattern($matches[0],
                $_SESSION['id_user']);
            return array(

                // How many characters to advance the Parsedown's
                // cursor after being done processing this tag.
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'span',
                    'text' => $value ?? "Bad Syntax",
                ),
            );
        }
    }

    /**
     * Extende the inlineImage parser by changing the base path of image
     * sources. Further, allow to specify the image width and height as well
     * as css classes that will be attached to the image. By default the classes
     *  - img-fluid
     *  - img-thumbnail
     *
     * are assigned to each image.
     *
     * The size and class attributes are postfixe to the filename, delimited by
     * a colon:
     * \<image_src\>|\<width\>x\<height\>|\<class_n\>,...,\<class_n\>
     */
    protected function inlineImage($excerpt)
    {
        $image = parent::inlineImage($excerpt);

        if ( ! isset($image))
        {
            return null;
        }

        $attrs = explode('|', $image['element']['attributes']['src']);
        $image['element']['attributes']['src'] = $attrs[0];
        if(count($attrs) > 1)
        {
            $size = explode('x', $attrs[1]);
            if(count($size) > 1)
            {
                $image['element']['attributes']['width'] = $size[0];
                $image['element']['attributes']['height'] = $size[1];
            }
        }
        if(count($attrs) > 2)
        {
            $class = implode(' ', explode(',', $attrs[2]));
            $image['element']['attributes']['class'] =
                "img-fluid img-thumbnail " . $class;
        }

        return $image;
    }

    /**
     * Extends the inlineLink parser to allow the same behaviour as the link
     * syles, with one addition:
     *
     * - prepending a link url with '!' will open the link in a new tab
     */
    protected function inlineLink($excerpt)
    {
        $link = parent::inlineLink($excerpt);

        if($link['element']['attributes']['href'][0] === '!')
        {
            $link['element']['attributes']['target'] = '_blank';
            $link['element']['attributes']['href'] =
                substr($link['element']['attributes']['href'], 1);
        }
        if($link !== null)
            $link['element']['attributes']['href'] =
                $this->router->get_url($link['element']['attributes']['href']);

        return $link;
    }

    /**
     * Extends the blockTable parser by adding bootstrap classes to render
     * a table responsive.
     */
    protected function blockTable($Line, array $Block = null)
    {
        $table = parent::blockTable($Line, $Block);

        if(isset($table['element']))
            $table['element']['attributes'] = array(
                'class' => "table table-responsive-sm",
            );

        return $table;
    }
}
?>
