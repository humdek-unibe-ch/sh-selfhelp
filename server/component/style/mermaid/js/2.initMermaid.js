$(document).ready(function () {
   var obj = $(".mermaidDiagram");
   console.log(obj);
   var callback = function(){
        alert('A callback was triggered');
    }
   mermaid.init(undefined, obj);



   

   // console.log($(".mermaidDiagram"));

   // var graphDefinition = 'graph LR A[Square Rect Biger] -- Link text --> B((Circle)) A --> C(Round Rect) B --> D{Rhombus} C --> D ';

   // for (var i = 0; i < $(".mermaidDiagram").length; i++) {
   //    var element = $(".mermaidDiagram")[i];
   //    var id = 'theGraph' + i;
   //    var insertSvg = function (svgCode, bindFunctions) {
   //       element.innerHTML = svgCode;
   //       if (typeof callback !== 'undefined') {
   //          callback(id);
   //       }
   //       bindFunctions(element);
   //    };
   //    mermaid.mermaidAPI.render(id, $(element).text(), insertSvg, element);
   // }



});