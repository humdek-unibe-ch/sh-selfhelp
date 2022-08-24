<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";
require_once __DIR__ . "/FormulaParser.php";
require_once __DIR__ . "/../../../service/ext/math-executor/vendor/autoload.php";

use FormulaParser\FormulaParser;
use NXP\MathExecutor;

/**
 * The base view class of form field style components.
 * This class provides common functionality that is used for all for field style
 * components.
 */
class FormulaParserView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'formula' (empty string).
     * The formula definition
     */
    private $formula;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of a base style component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->formula = $this->model->get_db_field("formula", array());
    }

    /* Private Methods ********************************************************/


    /* Public Methods ********************************************************/

    public function test()
    {
        echo "test";
    }

    /**
     * Render the style view.
     */
    public function output_content()
    {
        // $fp = new FormulaParser($this->formula['formula']);
        // $fp->setVariables($this->formula['variables']);
        // $result =  $fp->getResult();
        $executor = new MathExecutor();
        $executor->addFunction('sum', function ($arr) {

            return array_sum($arr);
        });
        foreach ($this->formula['variables'] as $var => $value) {
            if(is_array($value)){
                $executor->setVar($var, $value);
            }else{
                $executor->setVar($var, $value);
            }            
        }
        $result = $executor->execute($this->formula['formula']);
        // $result ='';
        require __DIR__ . "/tpl_formula.php";
    }
}
?>
