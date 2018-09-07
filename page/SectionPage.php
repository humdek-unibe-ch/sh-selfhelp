<?php
require_once __DIR__ . "/BasePage.php";
require_once __DIR__ . "/InternalPage.php";
require_once __DIR__ . "/../component/style/StyleComponent.php";
require_once __DIR__ . "/../component/style/BaseStyleComponent.php";

/**
 * This class maps the section structure of the DB. A section page consists
 * solely of a collection of sections as defined in the database.
 */
class SectionPage extends BasePage
{
    /* Private Properties *****************************************************/

    private $sections;
    private $errors;
    private $nav_section_id;
    private $user_input;

    /* Constructors ***********************************************************/

    /**
     * The constructor of this class. It calls the constructor of the parent
     * class and collects all sections that are allocated to the current page.
     * For each section, a StyleComponent is created and added to the component
     * list of the page.
     *
     * @param object $router
     *  The router instance is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param string $keyword
     *  The identification name of the page.
     */
    public function __construct($router, $db, $keyword, $params=array())
    {
        parent::__construct($router, $db, $keyword);
        $this->nav_section_id = isset($params['id']) ? $params['id'] : null;

        $this->errors = array();
        $this->user_input = null;
        if($this->has_user_input && count($_POST) > 0)
        {
            $this->user_input = $this->check_user_input();
            $this->errors = $this->services['gump']->get_errors_array(true);
            foreach($this->errors as $index => $error)
                $this->add_component("alert-" . $index,
                    new BaseStyleComponent("alert", array(
                        "type" => "danger",
                        "is_dismissable" => true,
                        "children" => array(
                            new BaseStyleComponent("plaintext", array(
                                "text" => $error,
                            ))
                        ),
                        "css" => "mx-3 mt-3",
                    ))
                );
        }

        $this->sections = $db->fetch_page_sections($keyword);
        foreach($this->sections as $section)
            $this->add_component("section-" . $section['id'],
                new StyleComponent($this->services, intval($section['id'])));

        if($this->nav_section_id != null)
        {
            $this->services['nav']->set_current_index($this->nav_section_id);
            $this->add_component("navigation", new StyleComponent(
                $this->services, $this->id_navigation_section,
                $this->nav_section_id));
        }
    }

    /**
     *
     */
    private function check_user_input()
    {
        $validation_rules = array();
        $filter_rules = array();
        $field_names = array();
        foreach($_POST as $name => $value)
        {
            $name_pieces = explode('-', $name);
            if(count($name_pieces) <= 1 || !is_numeric($name_pieces[0]))
                continue;
            $id_section = intval($name_pieces[0]);
            $sql = "SELECT sft.content
                FROM sections_fields_translation AS sft
                LEFT JOIN fields AS f ON f.id = sft.id_fields
                WHERE f.name = 'label' AND sft.id_sections = :id";
            $label = $this->services['db']->query_db_first($sql,
                array(":id" => $id_section));
            $field_names[$name] = ($label) ? $label["content"]
                : implode('-', array_slice($name_pieces, 1));
            // determine the type of the field
            $sql = "SELECT st.name FROM styles AS st
                LEFT JOIN sections AS s ON s.id_styles = st.id
                WHERE s.id = :id";
            $style = $this->services['db']->query_db_first($sql,
                array(":id" => $id_section));
            if($style)
            {
                if($style['name'] == "slider")
                {
                    $validation_rules[$name] = "integer";
                    $filter_rules[$name] = "sanitize_numbers";
                }
                else if($style['name'] == "textarea")
                    $filter_rules[$name] = "sanitize_string";
                else if($style['name'] == "select")
                {
                    $validation_rules[$name] = "alpha_dash";
                    $filter_rules[$name] = "trim|sanitize_string";
                }
                else if($style['name'] == "input")
                {
                    $sql = "SELECT sft.content
                        FROM sections_fields_translation AS sft
                        LEFT JOIN fields AS f ON f.id = sft.id_fields
                        WHERE f.name = 'type-input' AND sft.id_sections = :id";
                    $type_db = $this->services['db']->query_db_first($sql,
                        array(":id" => $id_section));
                    $type = "";
                    if($type_db) $type = $type_db["content"];
                    if($type == "text" || $type == "checkbox" || $type == "month"
                        || $type == "week" || $type == "search" || $type == "tel")
                        $filter_rules[$name] = "trim|sanitize_string";
                    else if($type == "color")
                        $validation_rules[$name] = "regex,/#[a-fA-F0-9]{6}/";
                    else if($type == "date")
                        $validation_rules[$name] = "date";
                    else if($type == "email")
                        $validation_rules[$name] = "valid_email";
                    else if($type == "number" || $type == "range")
                        $validation_rules[$name] = "numeric";
                    else if($type == "time")
                        $validation_rules[$name] = "regex,/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/";
                    else if($type == "url")
                        $validation_rules[$name] = "valid_url";
                }
            }
        }
        $this->services['gump']->validation_rules($validation_rules);
        $this->services['gump']->filter_rules($filter_rules);
        $this->services['gump']->set_field_names($field_names);
        return $this->services['gump']->run($_POST);
    }

    private function output_alerts()
    {
        foreach($this->errors as $index => $error)
            $this->output_component("alert-" . $index);
    }

    private function output_sections()
    {
        $was_section_rendered = false;
        foreach($this->sections as $section)
        {
            $comp = $this->get_component("section-" . $section['id']);
            if($comp->has_access())
            {
                $comp->output_content();
                $was_section_rendered = true;
            }
        }
        if($this->nav_section_id)
        {
            $sql = "SELECT * FROM sections_navigation
                WHERE child = :id AND id_pages = :pid";
            if($this->services['db']->query_db_first($sql, array(
                    ":id" => $this->nav_section_id, ":pid" => $this->id_page)))
            {
                $this->output_component("navigation");
                $was_section_rendered = true;
            }
        }

        if((count($this->sections) > 0 || $this->nav_section_id)
            && !$was_section_rendered)
        {
            $page = new InternalPage($this, "missing");
            $page->output_content();
        }
    }

    /* Protected Methods ******************************************************/

    /**
     * See BasePage::output_content(). This implementation renders all
     * components that are assigned to the current page (as specified in the
     * DB).
     */
    protected function output_content()
    {
        if($this->user_input === null)
            $this->output_sections();
        else if($this->user_input === false)
        {
            $this->output_alerts();
            $this->output_sections();
        }
        else
        {
            $page = new InternalPage($this, "user_input_success");
            $page->output_content();
        }
    }

    /**
     * See BasePage::output_meta_tags()
     * The current implementation is not doing anything.
     */
    protected function output_meta_tags() {}
}
?>
