<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the asset select component.
 */
class QualtricsSurveyView extends StyleView
{

    /* Private Properties *****************************************************/

    /**
     * Markdown text that is shown if the survey is done and it can be filled only once per schedule.
     */
    private $label_survey_done;

    /**
     * Markdown text that is shown if the survey is not active right now.
     */
    private $label_survey_not_active;

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
        $this->label_survey_done = $this->model->get_db_field('label_survey_done', '');
        $this->label_survey_not_active = $this->model->get_db_field('label_survey_not_active', '');
    }

    /* Private Methods ********************************************************/

    /**
     * Render the asset list.
     *
     * @param string $mode
     *  Specifies the insert mode (either 'css' or 'asset').
     */
    private function output($mode)
    {
        echo $mode;
    }

    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        if ($this->model->is_survey_active()) {
            if ($this->model->is_survey_done()) {
                if ($this->label_survey_done != '') {
                    $alert = new BaseStyleComponent("alert", array(
                        "type" => "danger",
                        "is_dismissable" => false,
                        "children" => array(new BaseStyleComponent("markdown", array(
                            "text_md" => $this->label_survey_done,
                        )))
                    ));
                    $alert->output_content();
                }
            } else {
                require __DIR__ . "/tpl_qualtricsSurvey.php";
            }
        } else {
            if ($this->label_survey_not_active != '') {
                $alert = new BaseStyleComponent("alert", array(
                    "type" => "danger",
                    "is_dismissable" => false,
                    "children" => array(new BaseStyleComponent("markdown", array(
                        "text_md" => $this->label_survey_not_active,
                    )))
                ));
                $alert->output_content();
            }
        }
    }

    /**
     * Load the survey link for the iFrame
     */
    public function get_survey_link()
    {
        return $this->model->get_survey_link();
    }

    public function output_content_mobile()
    {
        $style = parent::output_content_mobile();
        $style['qualtrics_url'] = $this->model->get_survey_link();
        $style['alert'] = '';
        $style['show_survey'] = false;
        if ($this->model->is_survey_active()) {
            if ($this->model->is_survey_done()) {
                $style['alert'] = $this->label_survey_done;
                $style['show_survey'] = true;
            }
        } else {
            $style['alert'] = $this->label_survey_not_active;
            $style['show_survey'] = true;
        }
        return $style;
    }
}
?>
