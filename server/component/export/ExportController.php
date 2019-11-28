<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the export component.
 */
class ExportController extends BaseController
{
   /* Constructors ***********************************************************/

   /**
    * The constructor.
    *
    * @param object $model
    *  The model instance of the export component.
    */
   public function __construct($model)
   {
      parent::__construct($model);
      if (isset($_POST['export_form_name_select'])) {
         $parameters['id'] = $_POST['export_form_name_select'];
         $parameters['option'] = 'all';
         $parameters['selector'] = 'user_input_form';
         header('Location: ' . $model->get_user_export_form_url($parameters));
      }
   }
}
