SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'container');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'children');
UPDATE `styles_fields` SET `help` = 'The child sections to be added to the `container` body. This can hold any style.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'div');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'children');
UPDATE `styles_fields` SET `help` = 'The child sections to be added to the `div` body. This can hold any style.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'jumbotron');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'children');
UPDATE `styles_fields` SET `help` = 'The child sections to be added to the `jumbotron` body. This can hold any style.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'quiz');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'type');
UPDATE `styles_fields` SET `help` = 'The visual appearance of the buttons as predefined by [Bootstrap](!https://getbootstrap.com/docs/4.0/utilities/colors/).' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'caption');
UPDATE `styles_fields` SET `help` = 'A question with a binary answer (e.g. Right, Wrong).' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label_right');
UPDATE `styles_fields` SET `help` = 'The label on the first answer button (e.g. right). Clicking this button will reveal the content as defined in the field `right_content`.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label_wrong');
UPDATE `styles_fields` SET `help` = 'The label on the second answer button (e.g. wrong). Clicking this button will reveal the content as defined in the field `wrong_content`.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'right_content');
UPDATE `styles_fields` SET `help` = 'The body to the first answer button as defined in field `label_right`. The content of this field usually states whether this answer was correct or false and provides an explanation as to why.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'wrong_content');
UPDATE `styles_fields` SET `help` = 'The body to the second answer button as defined in field `label_wrong`. The content of this field usually states whether this answer was correct or false and provides an explanation as to why.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'tabs');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'children');
UPDATE `styles_fields` SET `help` = 'The child sections to be added to the `tabs` body. Add only sections of style `tab` here.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'tab');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'children');
UPDATE `styles_fields` SET `help` = 'The child sections to be added to the `tab` body. This can hold any style.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'heading');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'title');
UPDATE `styles_fields` SET `help` = 'The text to be rendered as heading.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'plaintext');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'text');
UPDATE `styles_fields` SET `help` = 'The text to be rendered.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_paragraph');
UPDATE `styles_fields` SET `help` = 'If enabled the text will be rendered within HTML `<p></p>` tags. If disabled the text will be rendered without any wrapping tags.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'rawText');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'text');
UPDATE `styles_fields` SET `help` = 'The text to be rendered with mono-space font.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'link');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label');
UPDATE `styles_fields` SET `help` = 'Specifies the clickable text. If left empty the URL as specified in the field `url` will be used.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'url');
UPDATE `styles_fields` SET `help` = 'Use a full URL or any special characters as defined <a href="https://selfhelp.psy.unibe.ch/demo/style/440" target="_blank">here</a>.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'open_in_new_tab');
UPDATE `styles_fields` SET `help` = 'If checked the link will be opened in a new tab. If unchecked the link will open in the current tab.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'audio');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'alt');
UPDATE `styles_fields` SET `help` = 'The alternative text to be displayed if the audio cannot be loaded.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'sources');
UPDATE `styles_fields` SET `help` = 'This field expects a [JSON](!https://www.json.org/json-en.html) list of source objects where each object has the following keys:\n - `source`: The source of the audio file. If it is an asset, simply use the full name of the asset.\n - `type`: The [type](!https://developer.mozilla.org/en-US/docs/Web/Media/Formats/Containers) of the audio file.\n\nFor example:\n```\n[{\n  "source": "audio_name.mp3",\n  "type": "audio/mpeg"\n}, {\n  "source":"audio_name.ogg",\n  "type": "audio/ogg"\n}]\n```\n' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'carousel');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'sources');
UPDATE `styles_fields` SET `help` = 'This field expects a [JSON](!https://www.json.org/json-en.html) list of source objects where each object has the following keys:\n - `source`: The source of the image file. If it is an asset, simply use the full name of the asset.\n - `alt`: The alternative text to be displayed if the image connot be loaded.\n - `caption`: The image caption to be displayed at the bottom of the image.\n\nFor example:\n```[{\n  "source": "slide1.svg",\n  "alt": "Image Description of Slide 1",\n  "caption": "Image Caption of Slide 1"\n}, {\n  "source":"slide2.svg",\n  "alt": "Image Description of Slide 2",\n  "caption": "Image Caption of Slide 2"\n}, {\n  "source":"slide3.svg",\n  "alt": "Image Description of Slide 3",\n  "caption": "Image Caption of Slide 3"\n}]\n```\n' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'id_prefix');
UPDATE `styles_fields` SET `help` = 'Define any unique name here if multiple carousel styles are used on the same page.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'has_controls');
UPDATE `styles_fields` SET `help` = 'If enabled the carusel is rendered with control arrows on either side of the image.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'has_indicators');
UPDATE `styles_fields` SET `help` = 'If enabled the carousel is rendered with carousel position indicaters at the bottom of the image.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'has_crossfade');
UPDATE `styles_fields` SET `help` = 'If enabled images will fade from one to another instead of using the default sliding animation.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'figure');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'children');
UPDATE `styles_fields` SET `help` = 'The child sections to be added to the `figure` body. Add only sections of style `image` or `audio` here.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'caption_title');
UPDATE `styles_fields` SET `help` = 'The title to be prepended to the text defined in filed `caption`.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'caption');
UPDATE `styles_fields` SET `help` = 'The caption of the figure.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'image');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'title');
UPDATE `styles_fields` SET `help` = 'The text to be shown when hovering over the image.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_fluid');
UPDATE `styles_fields` SET `help` = 'If enabled the image scales responsively.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'alt');
UPDATE `styles_fields` SET `help` = 'The alternative text to be shown if the image cannot be loaded.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'source');
UPDATE `styles_fields` SET `help` = 'The image source. If the image is an asset simply use the full name of the asset here.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'progressBar');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'type');
UPDATE `styles_fields` SET `help` = 'The visual appearance of the progrres bar as predefined by [Bootstrap](!https://getbootstrap.com/docs/4.0/utilities/colors/).' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'count');
UPDATE `styles_fields` SET `help` = 'The current value of the progress bar.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'count_max');
UPDATE `styles_fields` SET `help` = 'The maximal value of the prpgress bar. The minimal value is 0.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_striped');
UPDATE `styles_fields` SET `help` = 'If enabled diagonal stripes are visualized on the progress bar.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'has_label');
UPDATE `styles_fields` SET `help` = 'If enabled a label of the form `<count>/<count_max>` is displayed on the proggress bar where `<count>` is the value defined in field `count` and `<count_max>` the value defined in field `count_max`.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'video');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'alt');
UPDATE `styles_fields` SET `help` = 'The alternative text to be displayed if the video cannot be loaded.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'sources');
UPDATE `styles_fields` SET `help` = 'This field expects a [JSON](!https://www.json.org/json-en.html) list of source objects where each object has the following keys:\n - `source`: The source of the video file. If it is an asset, simply use the full name of the asset.\n - `type`: The [type](!https://developer.mozilla.org/en-US/docs/Web/Media/Formats/Containers) of the video file.\n\nFor example:\n```\n[{\n  "source": "video_name.mp4",\n  "type": "video/mp4"\n}, {\n  "source":"video_name.ogg",\n  "type": "video/ogg"\n}, {\n  "source":"video_name.webm",\n  "type": "video/webm"\n}]\n```\n' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'accordionList');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'title_prefix');
UPDATE `styles_fields` SET `help` = 'This text will be added as a perfix to each root item.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'items');
UPDATE `styles_fields` SET `help` = 'This field expects a [JSON](!https://www.json.org/json-en.html) list of objects where each object has the following keys:\n - `id`: A unique identifier of the item\n - `title`: The name of the item.\n - `url`: An URL to where this item will link. This field is optional.\n - `children`: A list of objects, again with the keys `id` and `title` and the optional keys `url` and `children`\n\nFor example:\n```\n[{\n  "id": 1,\n  "title": "Item1",\n  "children": [{\n    "id": 2,\n    "title": "Item1.1"\n  }, {\n    "id": 3,\n    "title": "Item1.2",\n    "children": [{\n      "id": 4,\n      "title": "Item1.2.1"\n    }]\n  }]\n},\n{\n  "id": 5,\n  "title": "Item2",\n  "children": [{\n     "id": 5,\n     "title": "Item2.1"\n  }] \n},\n{\n  "id": 6,\n  "title": "Item3",\n  "children": [{\n     "id": 7,\n     "title": "Item3.1"\n  }]\n}]\n```\n.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'id_prefix');
UPDATE `styles_fields` SET `help` = 'Define any unique name here if multiple accordionList styles are used on the same page.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label_root');
UPDATE `styles_fields` SET `help` = 'This field only has an effect if root items have an URL defined (see field `items`). If not defined, links on root items will be displayed as symbols. To expand the root item one would click on the root item and to follow to the link one would click on the link symbol. If this field is set instead of the link symbol, a new chlid element will be generated which serves as a link to the URL of the root item. The here defined text will be used as label for this link.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'id_active');
UPDATE `styles_fields` SET `help` = 'Defines which id is marked as active. This will also cause the corresponding root item to be expanded.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'nestedList');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'title_prefix');
UPDATE `styles_fields` SET `help` = 'If this is set the list will be collapse on small screens and the here defined text will be displayed as title of the collpsed list.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_expanded');
UPDATE `styles_fields` SET `help` = 'If enabled all items in the list will be expanded by default. If disabled all items will be collapsed by default. This field only has an effect if `is_collapsible` is enabled.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_collapsible');
UPDATE `styles_fields` SET `help` = 'If enabled all items with child items are collapsible.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'items');
UPDATE `styles_fields` SET `help` = 'This field expects a [JSON](!https://www.json.org/json-en.html) list of objects where each object has the following keys:\n - `id`: A unique identifier of the item\n - `title`: The name of the item.\n - `url`: An URL to where this item will link. This field is optional.\n - `children`: A list of objects, again with the keys `id` and `title` and the optional keys `url` and `children`\n\nFor example:\n```\n[{\n  "id": 1,\n  "title": "Item1",\n  "children": [{\n    "id": 2,\n    "title": "Item1.1"\n  }, {\n    "id": 3,\n    "title": "Item1.2",\n    "children": [{\n      "id": 4,\n      "title": "Item1.2.1"\n    }]\n  }]\n},\n{\n  "id": 5,\n  "title": "Item2",\n  "children": [{\n     "id": 5,\n     "title": "Item2.1"\n  }] \n},\n{\n  "id": 6,\n  "title": "Item3",\n  "children": [{\n     "id": 7,\n     "title": "Item3.1"\n  }]\n}]\n```\n.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'search_text');
UPDATE `styles_fields` SET `help` = 'If defined a small text input field is rendered on top of the list. This input field allows to search for any item within the list.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'id_prefix');
UPDATE `styles_fields` SET `help` = 'Define any unique name here if multiple nestedList styles are used on the same page.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
UPDATE `styles_fields` SET `help` = 'Defines which id is marked as active. This will also cause the corresponding root item to be expanded.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'sortableList');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'items');
UPDATE `styles_fields` SET `help` = 'This field expects a [JSON](!https://www.json.org/json-en.html) list of objects where each object has the following keys:\n - `id`: A unique identifier of the item\n - `title`: The name of the item.\n - `url`: An URL to where this item will link. This field is optional.\n - `css`: A custom css class to be added to this item. This is useful for ignoring or blocking items when dragging.\n\nFor example:\n```\n[{\n  "id": 1,\n  "title": "Item 1"\n},{\n  "id": 2,\n  "title": "Item 2",\n  "url": "#",\n  "css": "custom"\n},{\n  "id": 3,\n  "title": "Item 3"\n}]\n```\n.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_sortable');
UPDATE `styles_fields` SET `help` = 'If enabled the list is sortable. Note that this feature requires additional javascript code. This only has an effect if the field `is_editable` is enabled.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_editable');
UPDATE `styles_fields` SET `help` = 'If enabled the list can be changed (see the fields `is_sortable`, `url_delete`, `url_add`).' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'url_delete');
UPDATE `styles_fields` SET `help` = 'If set next to each item in the list a cross symbol will be rendered. Each symbol is a link with an URL as defined here. The string `:did` will be replaced with the id of the clicked item. For this field to have an effect, the field `is_editable` must be enabled.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label_add');
UPDATE `styles_fields` SET `help` = 'This text will be used on the button to add new elements to the list. This field only has an effect if the field `is_editable` is enabled and the field `url_add` is defined.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'url_add');
UPDATE `styles_fields` SET `help` = 'If set, at the top of the list a button with the text as defined with the field `label_add` is rendered. This field defines the link URL of the button. For this field to have an effect, the field `is_editable` must be enabled.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'form');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'children');
UPDATE `styles_fields` SET `help` = 'The child sections to be added to the `form` body. This can hold any style.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label');
UPDATE `styles_fields` SET `help` = 'The label on the submit button.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'url');
UPDATE `styles_fields` SET `help` = 'The submit URL.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'type');
UPDATE `styles_fields` SET `help` = 'The visual appearance of the submit button as predefined by [Bootstrap](!https://getbootstrap.com/docs/4.0/utilities/colors/).' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label_cancel');
UPDATE `styles_fields` SET `help` = 'If set, a cancel button will be rendered. The here defined text will be used as label for this button.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'url_cancel');
UPDATE `styles_fields` SET `help` = 'The target URL of the cancel button.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'formUserInput');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'children');
UPDATE `styles_fields` SET `help` = 'The child sections to be added to the `formUserInput` body. This can hold any style.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label');
UPDATE `styles_fields` SET `help` = 'The label on the submit button.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'type');
UPDATE `styles_fields` SET `help` = 'The visual appearance of the submit button as predefined by [Bootstrap](!https://getbootstrap.com/docs/4.0/utilities/colors/).' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'alert_success');
UPDATE `styles_fields` SET `help` = 'The here defined text will be rendered upon successful submission of data. If the submission fails, an error message will indicate the reason.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name');
UPDATE `styles_fields` SET `help` = 'A unique name to identify the form when exporting the collected data.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_log');
UPDATE `styles_fields` SET `help` = 'This fiels allows to control how the data is saved in the database:\n - `disabled`: The submission of data will always overwrite prior submissions of the same user. This means that the user will be able to continously update the data that was submitted here. Any input field that is used within this form will always show the current value stored in the database (if nothing has been submitted as of yet, the input field will be empty or set to a default).\n - `enabled`: Each submission will create a new entry in the database. Once entered, an entry cannot be removed or modified. Any input field within this form will always be empty or set to a default value (nothing will be read from the database).' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'graphSankey');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'form_field_names');
UPDATE `styles_fields` SET `help` = 'In order to create a Sankey diagram from a set of user input data two types of information are required:\r\n 1. the form field names defined here (think of it as the column headers of a table where each row holds the data of one subject)\r\n 2. the value types defined in `value_types` (the value entered by the subject).\r\n\r\nThe Sankey diagram consist of *nodes* and *links*. All possible combinations of form field names (1) and value types (2) define the nodes in a Sankey diagram. The links are computed by accumulating all values of the same type (2) when transitioning from one field name (1) to another.\r\n\r\nThis field expects an ordered list (`json` syntax) which specifies the form field names (1) to be used to generate the Sankey diagram. The order is important because two consecutive form field names (1) form a transition. Each list item is an object with the following fields:\r\n - `key`: the name of the field. When using static data this refers to a column name from the table specified in the field `data-source`. When using dynamic data this refers to a user input field name of the form specified in the field `data-source`.\r\n - `label`: A human-readable label which can be displayed on the diagram.\r\n\r\nAn Example\r\n```\r\n[\r\n  { \"key\": \"field1\", \"label\": \"Field 1\" },\r\n  { \"key\": \"field2\", \"label\": \"Field 2\" }\r\n]\r\n```' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'graphBar');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'single_user');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, 1, 'This option only takes effect when using **dynamic** data. If checked, only data from the current logged-in user is used. If unchecked, data form all users is used.');

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'graphPie');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'single_user');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style, @id_field, 1, 'This option only takes effect when using **dynamic** data. If checked, only data from the current logged-in user is used. If unchecked, data form all users is used.');

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'mermaidForm');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'children');
UPDATE `styles_fields` SET `help` = 'The child sections to be added to the `fromMermaid` body. Add only sections of style `input` here. If the field `name` of a child section matches the name of a node in the mermaid diagram, this node becomes editable. To edit, simply click on the node and a modal form is opened.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label');
UPDATE `styles_fields` SET `help` = 'The label on the submit button in the edit window. This field only has an effect if a mermaid node is editable.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'type');
UPDATE `styles_fields` SET `help` = 'The visual appearance of the submit button in the edit window as predefined by [Bootstrap](!https://getbootstrap.com/docs/4.0/utilities/colors/).' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'alert_success');
UPDATE `styles_fields` SET `help` = 'The here defined text will be rendered upon successful submission of data. If the submission fails, an error message will indicate the reason.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name');
UPDATE `styles_fields` SET `help` = 'A unique name to identify the form when exporting the collected data.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'input');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label');
UPDATE `styles_fields` SET `help` = 'If this field is set, a this text will be rendered above the input field.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'type_input');
UPDATE `styles_fields` SET `help` = 'A selection of HTML input types. Note that support for these types depends on the browser. Uf a type is not supported by a browser, usually the type `text` is used.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'placeholder');
UPDATE `styles_fields` SET `help` = 'If this field is set, the text will be rendered as background inside the input field and will disappear when a value is enterd.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_required');
UPDATE `styles_fields` SET `help` = 'If enabled the form can only be submitted if a value is enterd in this input field.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name');
UPDATE `styles_fields` SET `help` = 'The name of the input form field. This name must be unique within a form.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'value');
UPDATE `styles_fields` SET `help` = 'The default value of the input field.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'radio');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label');
UPDATE `styles_fields` SET `help` = 'If this field is set, a this text will be rendered above the radio elements.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_required');
UPDATE `styles_fields` SET `help` = 'If enabled the form can only be submitted if a value is selected.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name');
UPDATE `styles_fields` SET `help` = 'The name of the radio form field. This name must be unique within a form.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'value');
UPDATE `styles_fields` SET `help` = 'The preselected item of the radio elements.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_inline');
UPDATE `styles_fields` SET `help` = 'If enabled the radio items will be rendered in one line as opposed to one below the other.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'items');
UPDATE `styles_fields` SET `help` = 'This field expects a [JSON](!https://www.json.org/json-en.html) list of radio objects where each object has the following keys:\n- `value`: the value to be submitted if this item is selected\n-`text`: the text rendered next to the radio button.\n\nAn Example\n```\n[{\n  "value":"1",\n  "text": "Item1"\n},\n{\n  "value":"2",\n  "text":"Item2"\n},\n{\n  "value":"3",\n  "text": "Item3"\n}]\n```' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'select');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label');
UPDATE `styles_fields` SET `help` = 'If this field is set, a this text will be rendered above the selection.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_required');
UPDATE `styles_fields` SET `help` = 'If enabled the form can only be submitted if a value is selected.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name');
UPDATE `styles_fields` SET `help` = 'The name of the selection form field. This name must be unique within a form.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'value');
UPDATE `styles_fields` SET `help` = 'The preselected item of the selection elements.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_multiple');
UPDATE `styles_fields` SET `help` = 'If enabled the selction items will be rendered as a list where multiple items can be selected as opposed to a dropdown menu.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'items');
UPDATE `styles_fields` SET `help` = 'This field expects a [JSON](!https://www.json.org/json-en.html) list of select objects where each object has the following keys:\n- `value`: the value to be submitted if this item is selected\n-`text`: the text rendered as selection option.\n\nAn Example\n```\n[{\n  "value":"1",\n  "text": "Item1"\n},\n{\n  "value":"2",\n  "text":"Item2"\n},\n{\n  "value":"3",\n  "text": "Item3"\n}]\n```' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'alt');
UPDATE `styles_fields` SET `help` = 'This field specifies the text that is displayed on the disabled option when no default value is defined' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'slider');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label');
UPDATE `styles_fields` SET `help` = 'If this field is set, a this text will be rendered above the slider.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name');
UPDATE `styles_fields` SET `help` = 'The name of the slider form field. This name must be unique within a form.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'value');
UPDATE `styles_fields` SET `help` = 'The preselected position of the slider.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'labels');
UPDATE `styles_fields` SET `help` = 'This field expects a [JSON](!https://www.json.org/json-en.html) list of labels. Each label will be assigned to a slider position so make sure that the number of labels matches the range defined with the fields `min` and `max`.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'min');
UPDATE `styles_fields` SET `help` = 'The minimal value of the range.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'max');
UPDATE `styles_fields` SET `help` = 'The maximal value of the range' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'textarea');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label');
UPDATE `styles_fields` SET `help` = 'If this field is set, a this text will be rendered above the textarea.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'placeholder');
UPDATE `styles_fields` SET `help` = 'If this field is set, the text will be rendered as background inside the textarea and will disappear when a value is enterd.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'is_required');
UPDATE `styles_fields` SET `help` = 'If enabled the form can only be submitted if a value is enterd in this textarea.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'name');
UPDATE `styles_fields` SET `help` = 'The name of the textarea form field. This name must be unique within a form.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'value');
UPDATE `styles_fields` SET `help` = 'The default value of the textarea form field.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'chat');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'alert_fail');
UPDATE `styles_fields` SET `help` = 'The here defined text will be displayed in an danger-alert-box if it was not possible to send the message. If this alert is shown there is probably an issue with the server.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'alt');
UPDATE `styles_fields` SET `help` = 'This message is displayed to a user in the role `Therapist` if no `Subject` is selected.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label_lobby');
UPDATE `styles_fields` SET `help` = 'The name of the root chat room (the room every user is part of).' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label_new');
UPDATE `styles_fields` SET `help` = 'This text is used as a divider between messages that are already read and new messages.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label_submit');
UPDATE `styles_fields` SET `help` = 'The text on the submit button.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'title_prefix');
UPDATE `styles_fields` SET `help` = 'This is the first part of the text that is displayed in the message card header. The second part of this text depends on the role of the user.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'experimenter');
UPDATE `styles_fields` SET `help` = 'This is the second part of the message card header if the role of the user is `Therapist`.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'subjects');
UPDATE `styles_fields` SET `help` = 'This is the second part of the message card header if the role of the user is `Subject`.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;

SET @id_style = (SELECT `id` FROM `styles` WHERE `name` = 'login');
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'type');
UPDATE `styles_fields` SET `help` = 'This allows to choose the colour scheme for the login form.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'alert_fail');
UPDATE `styles_fields` SET `help` = 'This text is displayed in a danger-alert-box whenever the login fails.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label_login');
UPDATE `styles_fields` SET `help` = 'The text on the login button.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label_pw');
UPDATE `styles_fields` SET `help` = 'The placeholder in the password input field.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label_pw_reset');
UPDATE `styles_fields` SET `help` = 'The name of the password reset link.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'label_user');
UPDATE `styles_fields` SET `help` = 'The placeholder in the email input field.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
SET @id_field = (SELECT `id` FROM `fields` WHERE `name` = 'login_title');
UPDATE `styles_fields` SET `help` = 'The text displayed in the login card header.' WHERE `styles_fields`.`id_styles` = @id_style AND `styles_fields`.`id_fields` = @id_field;
