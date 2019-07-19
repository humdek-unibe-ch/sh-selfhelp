$(document).ready(function () {
   mermaid.mermaidAPI.initialize({
      startOnLoad: true,
      mermaid: {
         callback: preload // call fucntion preload after mermaid initialize the svg objects
      }
   });  
   mermaid.init(undefined, $('.mermaidDiagram'))  
});

function addPointerClassAndClickEvent(){
   // add pointer class to editable fields
   var editableFields = getEditableFields();         
   var formName = $('#mermaidFormName').text();
   for (var name in editableFields){
      $('#' + formName + '_' + name).addClass('pointer');
      $('#' + formName + '_' + name).click(function() {
         onClickToEdit($(this).attr('id'));
      });
   }
}

function getEditableFields(){
   // get editable fields and values and return them as object
   try{
      return JSON.parse($('#mermaidFormEditableFields').text());      
   }
   catch(e){
      console.log(e);
      return {};
   }
}

function onClickToEdit(id){ 
   // fire modal form on clik on editable field 
   var formName = $('#mermaidFormName').text();
   var editableFields = getEditableFields();         
   $("form input[type=text]").each(function(){
      if (formName + "_" + $(this).attr('name') == id+"[value]") {
         //add label as title and if it is empty put a default one
         var label = editableFields[id.replace(formName + '_', '')].label;
         $('#modalFormTitle').html(label != '' ? label : 'Please enter your input');
         //add visble class to the field that the user wants to edit
         $(this).addClass('mermaidFieldVisible');
      }
   });   
   $("#"+ formName).modal();  
   $("#"+ formName).on('hidden.bs.modal', function () {
      //on modal close make all visible fields hidden
      $('.mermaidFieldVisible').each(function(){
        $(this).removeClass('mermaidFieldVisible');
      });

   });
}

function preload(){
   //preload the id of mermaid and assign them formName as a prefix for uniquness
   // 
   var allNodes = $('.node');
   var formName = $('#mermaidFormName').text();
   var editableFields = getEditableFields();  
   allNodes.each(function(){
      var orgId = $(this).attr('id');
      if(!$(this).attr('id').includes(formName)){
         $(this).attr('id', formName + '_' + $(this).attr('id'));         
         if(editableFields[orgId] && editableFields[orgId].value){
            if(editableFields[orgId].value != ''){
               $(this).find('foreignObject div').html(editableFields[orgId].value);
            }
         }         
      }
   });   
   addPointerClassAndClickEvent();
}