<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../graph/GraphBaseComponent.php";
require_once __DIR__ . "/GraphSankeyView.php";
require_once __DIR__ . "/GraphSankeyModel.php";

/**
 * A component class for a graphSankey style component. This style is
 * intended to display a Sankey diagram.
 */
class GraphSankeyComponent extends GraphBaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes the view instance to the constructor of the parent
     * class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of this component.
     * @param array $params
     *  An array of get parameters.
     * @param int $id_page
     *  The id of the parent page
     */
    public function __construct($services, $id, $params, $id_page)
    {
        $model = new GraphSankeyModel($services, $id);
        $view = new GraphSankeyView($model);

        parent::__construct($model, $view, $id_page);
    }
}
?>
