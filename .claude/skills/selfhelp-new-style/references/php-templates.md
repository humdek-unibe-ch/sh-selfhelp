# PHP skeletons

Replace `progressCard` / `ProgressCard` with the style name. Keep the MPL header on every file.

## View (all styles) — `ProgressCardView.php`

```php
<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";
// plugin style: require_once __DIR__ . "/../../../../../../component/style/StyleView.php";

/**
 * The view class of the progressCard style component.
 * <one or two lines on what it renders>
 */
class ProgressCardView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'title' (empty string).
     * The heading shown on top of the card.
     */
    private $title;

    /**
     * DB field 'value' (0).
     * Current progress in percent.
     */
    private $value;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the progressCard component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->title = $this->model->get_db_field("title", "");
        $this->value = (int)$this->model->get_db_field("value", 0);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_progressCard.php";
    }

    /**
     * Render the mobile view. Only needed when adding computed values.
     */
    public function output_content_mobile()
    {
        $style = parent::output_content_mobile();
        $style['value']['content'] = $this->value;
        return $style;
    }
}
?>
```

## Template — `tpl_progressCard.php`

```php
<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div id="section-<?php echo $this->id_section; ?>" class="card progress-card <?php echo $this->css; ?>">
    <h5 class="card-title"><?php echo htmlspecialchars($this->title); ?></h5>
    <?php $this->output_children(); ?>
</div>
```

Helper methods called from a tpl (e.g. `$this->output_close_button()`) are private methods on the
View that `require __DIR__ . "/tpl_<part>.php";` — see `alert/AlertView.php`.

## Component (component type only) — `ProgressCardComponent.php`

```php
<?php
/* MPL header */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/../StyleModel.php";          // or "/ProgressCardModel.php"
require_once __DIR__ . "/ProgressCardView.php";
// require_once __DIR__ . "/ProgressCardController.php";
// plugin style: __DIR__ . "/../../../../../../component/BaseComponent.php"
//               __DIR__ . "/../../../../../../component/style/StyleModel.php"

/**
 * The progressCard style component.
 */
class ProgressCardComponent extends BaseComponent
{
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        $model = new StyleModel($services, $id, $params, $id_page, $entry_record);
        $controller = null;
        // if(!$model->is_cms_page())
        //     $controller = new ProgressCardController($model);
        $view = new ProgressCardView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
```

## Model (only when StyleModel isn't enough) — `ProgressCardModel.php`

```php
<?php
require_once __DIR__ . "/../StyleModel.php";

class ProgressCardModel extends StyleModel
{
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        parent::__construct($services, $id, $params, $id_page, $entry_record);
    }

    // Data access goes here, via $this->db / $this->user_input (services), never in the view.
}
?>
```

## Controller (POST/AJAX only) — `ProgressCardController.php`

Extends `BaseController` (`require_once __DIR__ . "/../../BaseController.php";`). Constructor takes
the model, calls `parent::__construct($model)`, then checks `$_POST` and acts. Use
`$this->success` / `$this->fail` with `$this->success_msgs[]` / `$this->error_msgs[]` so the view can
render alerts via `output_controller_alerts_success()` / `_fail()`. Model `FormUserInputController`
is the reference for validation (GUMP) and saving via `UserInput::save_data()`.

## Plugin assets in the View

```php
public function get_css_includes($local = array())
{
    if (empty($local)) {
        $css_file = __DIR__ . "/../../../../css/ext/<plugin>.min.css";
        $version = DEBUG ? (filemtime($css_file) ?: time())
                         : rtrim(shell_exec("git describe --tags") ?: "");
        $local = array($css_file . "?v=" . $version);
    }
    return parent::get_css_includes($local);
}
// get_js_includes() is identical with the js/ext file.
```
