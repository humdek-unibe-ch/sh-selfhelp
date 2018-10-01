<?php
require_once __DIR__ . "/ext/Parsedown.php";

/**
 * Extension Class to the Parsedown service.
 */
class ParsedownExtension extends Parsedown
{
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
     * \<image_src\>:\<width\>x\<height\>:\<class_n\>,...,\<class_n\>
     */
    protected function inlineImage($excerpt)
    {
        $image = parent::inlineImage($excerpt);

        if ( ! isset($image))
        {
            return null;
        }

        $attrs = explode(':', $image['element']['attributes']['src']);
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


        $image['element']['attributes']['src'] = ASSET_PATH . '/'
            . $image['element']['attributes']['src'];

        return $image;
    }

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
