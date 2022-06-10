$(document).ready(function () {
	initMermaidForm()
});

function initMermaidForm() {
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
		if ($('#graph-mermaid').length == 0) {
			var graph = mermaid.mermaidAPI.render('graph-' + formName, $element.text(), insertSvg);
			growNodesToFitText($('#graph-' + formName));
		}
	});
}

function addNodeIdPrefix(formName, $svgCode) {
	//prefix each node with formName for uniquness
	var allNodes = $svgCode.find('.node');
	allNodes.each(function () {
		if (!$(this).attr('id').includes(formName)) {
			$(this).attr('id', formName + '_' + $(this).attr('id'));
		}
	});
}

function growNodesToFitText($graph) {
	// grow the bounding box of the text of a node to not crop the text.
	var max_delta = 0;
	$graph.find('.node').each(function () {
		var $foreignObject = $(this).find('foreignObject:first');
		var $div = $foreignObject.children('div:first');
		var width = parseFloat($foreignObject.attr('width'))
		var delta = $div.width() - width;
		if (delta > max_delta)
			max_delta = delta;
		var $shape = $(this).children().first()
		$foreignObject.attr('width', width + delta);
		$shape.attr('width', parseFloat($shape.attr('width')) + delta);
	});
	var width = parseFloat($graph.css('max-width'));
	var viewBox = $graph.attr('viewBox').split(' ');
	viewBox[2] = parseFloat(viewBox[2]) + max_delta;
	$graph.css('max-width', width + max_delta);
	$graph.attr('viewBox', viewBox.join(' '));
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
