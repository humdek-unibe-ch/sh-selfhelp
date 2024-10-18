<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the asset select component.
 */
class ModuleScheduledJobsCalendarView extends BaseView
{
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
    }

    /* Private Methods ********************************************************/


    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_schedule_jobs_calendar_view.php";
    }

    public function output_calendar()
    {
        $container = new BaseStyleComponent("container", array(
            "css" => "mt-3",
            "is_fluid" => false,
            "children" => array(new BaseStyleComponent("card", array(
                "css" => "mb-3",
                "title" => 'Job schedule calendar view',
                "type" => "light",
                "is_expanded" => true,
                "is_collapsible" => false,
                "children" => array(
                    new BaseStyleComponent("div", array(
                        "css" => "d-flex align-items-end",
                        "children" => array(
                            new BaseStyleComponent("select", array(
                                "label" => "Select user",
                                "value" => $this->model->get_selected_user(),
                                "id" => "scheduled-jobs-calendar-selected-user",
                                "name" => "users",
                                "css" => 'flex-grow-1 me-3',
                                "live_search" => true,
                                "is_multiple" => false,
                                "items" => $this->model->get_users(),
                            )),
                            new BaseStyleComponent("div", array(
                                "children" => array(new BaseStyleComponent("button", array(
                                    "label" => "View",
                                    "css" => "flex-grow-0 mb-3",
                                    "id" => "scheduled-jobs-view-calendar-btn",
                                    "url" => $this->model->get_link_url("moduleScheduledJobsCalendar", array("uid" => ":uid", "aid"=>":aid")),
                                    "type" => "primary",
                                )))
                            ))
                        )
                    )),
                    new BaseStyleComponent("select", array(
                        "label" => "Filter for action",
                        "value" => $this->model->get_selected_action(),
                        "id" => "scheduled-jobs-calendar-selected-action",
                        "name" => "action",
                        "css" => '',
                        "live_search" => true,
                        "is_multiple" => false,
                        "items" => $this->model->get_actions(),
                    )),
                    new BaseStyleComponent("div", array(
                        "css" => "scheduled-jobs-calendar-view"
                    ))
                )
            )))
        ));
        $container->output_content();
    }

    public function output_content_mobile()
    {
        echo 'mobile';
    }

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @return array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        if (empty($local)) {
            $local = array(
                __DIR__ . "/js/full-calendar-v6-1-5.min.js",
                __DIR__ . "/js/bootstrap-full-calendar-v6-1-5.global.min.js",
                __DIR__ . "/js/jquery.contextMenu.min.js",
                __DIR__ . "/js/moduleScheduledJobsCalendar.js"
            );
        }
        return parent::get_js_includes($local);
    }

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @return array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        $local = array(
            __DIR__ . "/css/event-calendar.min.css",
            __DIR__ . "/css/moduleScheduledJobsCalendar.css",
            __DIR__ . "/css/jquery.contextMenu.min.css"
        );
        return parent::get_css_includes($local);
    }
}
?>
