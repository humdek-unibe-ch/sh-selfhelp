<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the group insert component.
 */
class CmsImportController extends BaseController
{
    /* Private Properties *****************************************************/

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
        if (isset($_POST['json'])) {
            if ($model->validate_and_set_json($_POST['json'])) {
                if ($model->type == 'section') {
                    // relation, page_id, parent_id and position tell where the section is attached. Without a
                    // relation the section is only created and stays unassigned.
                    $res = $model->import_section(
                        isset($_POST['relation']) && $_POST['relation'] !== '' ? $_POST['relation'] : null,
                        isset($_POST['page_id']) ? intval($_POST['page_id']) : null,
                        isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null,
                        isset($_POST['position']) && $_POST['position'] !== '' ? intval($_POST['position']) : null
                    );
                    $label = "Section: " . $model->json['section']['section_name'];
                } else {
                    $res = $model->import_page(isset($_POST['page_keyword']) ? trim($_POST['page_keyword']) : null);
                    $label = "Page: " . $model->json['page']['keyword'];
                }
                if ($res === true) {
                    $this->success = true;
                    $this->success_msgs[] = "Successfully parse JSON file: " . $this->escape_message($model->json['file_name']) . '.json';
                    $this->success_msgs[] = $this->escape_message($label) . ' was successfully imported.';
                } else {
                    $this->fail = true;
                    $this->error_msgs[] = "Error! " . $this->escape_message($label) . ' was not imported';
                    $this->error_msgs[] = $this->escape_message($res);
                }
                $model->get_services()->get_db()->clear_cache();
            } else {
                $this->fail = true;
                $this->error_msgs[] = $this->escape_message($model->json);
            }
        }
    }

    /* Private Methods ********************************************************/

    /**
     * Make a text which comes from the imported file safe for an alert. The
     * alerts are rendered as markdown with raw html allowed: the html is
     * encoded and so are the brackets and the "://" of urls, otherwise a name
     * like `[x](javascript:...)` or `https://...` would become a link (or an
     * image) in the alert.
     *
     * @param string $text
     *  The text to escape.
     * @retval string
     *  The escaped text.
     */
    private function escape_message($text)
    {
        return strtr(htmlspecialchars((string)$text), array('[' => '&#91;', ']' => '&#93;', '://' => '&#58;//'));
    }

    /* Public Methods *********************************************************/
}
?>
