#!/bin/bash
#http://agateau.com/2014/template-for-shell-based-command-line-scripts/
set -e

PROGNAME=$(basename $0)

die() {
    echo "$PROGNAME: $*" >&2
    exit 1
}

usage() {
    if [ "$*" != "" ] ; then
        echo "Error: $*"
    fi

    cat << EOF

Usage: $PROGNAME [OPTION ...] STYLE_NAME
<Program description>.

Options:
-h, --help          display this usage message and exit
-p, --path [PATH]   path to the target folder where the style folder will be produced

EOF

    exit 1
}

name=""
path="."
while [ $# -gt 0 ] ; do
    case "$1" in
        -h|--help)
            usage
            ;;
        -p|--path)
            path="$2"
            shift
            ;;
        -*)
            usage "Unknown option '$1'"
            ;;
        *)
            if [ -z "$name" ] ; then
                name="$1"
            else
                usage "Too many arguments"
            fi
            ;;
    esac
    shift
done

if [ -z "$name" ] ; then
    usage "missing style name"
fi

mkdir $path/$name
prefix="${name^}"
cat << EOF > $path/$name/${prefix}Model.php
<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";
/**
 * This class is used to prepare all data related to the ${name} style
 * component such that the data can easily be displayed in the view of the
 * component.
 */
class ${prefix}Model extends StyleModel
{
    /* Private Properties *****************************************************/

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/
}
EOF

cat << EOF > $path/$name/${prefix}View.php
<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the ${name} style component.
 */
class ${prefix}View extends StyleView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
    }

    /* Private Methods ********************************************************/


    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
    }
}
?>
EOF

cat << EOF > $path/$name/${prefix}Controller.php
<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseController.php";
/**
 * The controller class of the ${name} style component.
 */
class ${prefix}Controller extends BaseController
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
    }

    /* Public Methods *********************************************************/

}
?>
EOF

cat << EOF > $path/$name/${prefix}Component.php
<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/${prefix}View.php";
require_once __DIR__ . "/${prefix}Model.php";
require_once __DIR__ . "/${prefix}Controller.php";

/**
 * The class to define the component of the style ${name}.
 */
class ${prefix}Component extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class, the View
     * class and the Controller class and passes them to the constructor of the
     * parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The section id of this component.
     */
    public function __construct($services, $id)
    {
        $model = new ${prefix}Model($services, $id);
        $controller = new ${prefix}Controller($model);
        $view = new ${prefix}View($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
EOF
