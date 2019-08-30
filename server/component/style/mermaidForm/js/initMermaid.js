$(document).ready(function () {
   mermaid.mermaidAPI.initialize({
      startOnLoad: false
   });
   $(".mermaidForm").each(function () {
      var $element = $(this).children('div.mermaidCode:first');
      var formName = $(this).children('div.mermaidFormName:first').text();
      var j_editableFields = $(this).children('div.mermaidFormEditableFields:first').text();

      var insertSvg = function (svgCode) {
         var $svgCode = $(svgCode);
         addNodeIdPrefix(formName, $svgCode);
         $element.html($svgCode);
         addPointerClassAndClickEvent(formName, j_editableFields);
      };
      var graph = mermaid.mermaidAPI.render('graph-' + formName, $element.text(), insertSvg);
      growNodesToFitText($('#graph-' + formName).find('.node'));
   });
});

function addNodeIdPrefix(formName, $svgCode) {
   //prefix each node with formName for uniquness
   var allNodes = $svgCode.find('.node');
   allNodes.each(function () {
      if (!$(this).attr('id').includes(formName)) {
         $(this).attr('id', formName + '_' + $(this).attr('id'));
      }
   });
}

function growNodesToFitText($nodes) {
   // grow the bounding box of the text of a node to not crop the text.
   $nodes.each(function () {
      var $foreignObject = $(this).find('foreignObject:first');
      var $div = $foreignObject.children('div:first');
      var delta = $foreignObject.find('div:first').width()
         - $foreignObject.attr('width');
      $foreignObject.attr('width', parseInt($foreignObject.attr('width')) + delta);
   });
}

function addPointerClassAndClickEvent(formName, j_editableFields) {
   // add pointer class to editable fields
   var editableFields = parseEditableFields(j_editableFields);
   for (var name in editableFields) {
      $('#' + formName + '_' + name).addClass('pointer');
      $('#' + formName + '_' + name).click((function (_name) {
         return function () {
            onClickToEdit($(this).attr('id'), formName, editableFields[_name]);
         }
      })(name));
   }
}

function onClickToEdit(id, formName, editableField) {
   // fire modal form on clik on editable field
   $("#" + formName).find(":input").each(function () {
      if (formName + "_" + $(this).attr('name') == id + "[value]") {
         //add label as title and if it is empty put a default one
         if (editableField.label != '') {
            $('#' + formName).find('.modal-title').html(editableField.label);
         } else {
            $(this).addClass('mb-3');
         }
         //add visble class to the field that the user wants to edit
         $(this).addClass('mermaidFieldVisible');
      }
      else
         $(this).prop('required', false);
   });
   $("#" + formName).modal();
   $("#" + formName).on('hidden.bs.modal', function () {
      //on modal close make all visible fields hidden
      $('.mermaidFieldVisible').each(function () {
         $(this).removeClass('mermaidFieldVisible');
      });

   });
}

function parseEditableFields(j_editableFields) {
   // get editable fields and values and return them as object
   try {
      return JSON.parse(j_editableFields);
   }
   catch (e) {
      console.log(e);
      return {};
   }
}
