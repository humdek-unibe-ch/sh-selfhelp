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
