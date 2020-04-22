<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/globals_untracked.php";

define('CSS_FOLDER', 'css');
define('CSS_PATH', BASE_PATH . '/' . CSS_FOLDER);
define('CSS_SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/' . CSS_FOLDER);
define('JS_FOLDER', 'js');
define('JS_SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/' . JS_FOLDER);
define('ASSET_FOLDER', 'assets');
define('ASSET_PATH', BASE_PATH . '/' . ASSET_FOLDER);
define('ASSET_SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/' . ASSET_FOLDER);
define('STATIC_FOLDER', 'static');
define('STATIC_PATH', BASE_PATH . '/' . STATIC_FOLDER);
define('STATIC_SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/' . STATIC_FOLDER);
define('STYLE_PATH', '/server/component/style');
define('STYLE_SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . STYLE_PATH);
define('SERVICE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/server/service');
define('EMAIL_PATH', $_SERVER['DOCUMENT_ROOT'] . '/server/email');
define('NAME_PATTERN', '[a-zA-Z0-9_-]+'); // pattern used for naming

define('MAX_USER_COUNT', 100000);

/* Static DB Content */
define('GUEST_USER_ID', 1);
define('ADMIN_USER_ID', 2);
define('ADMIN_GROUP_ID', 1);

define('NAVIGATION_STYLE_ID', 33);
define('NAVIGATION_CONTAINER_STYLE_ID', 30);

define('CSS_FIELD_ID', 23);
define('LABEL_FIELD_ID', 8);
define('NAME_FIELD_ID', 57);
define('TYPE_INPUT_FIELD_ID', 54);
define('EMAIL_TYPE_ID', 11);

define('STYLE_GROUP_INTERN_ID', 1);

define('MALE_GENDER_ID', 1);
define('ALL_LANGUAGE_ID', 1);

define('EXPERIMENTER_GROUP_ID', 2);
define('SUBJECT_GROUP_ID', 3);

define('GLOBAL_CHAT_ROOM_ID', 1);

define('INTERNAL_PAGE_ID', 1);
define('CORE_PAGE_ID', 2);
define('EXPERIMENT_PAGE_ID', 3);
define('OPEN_PAGE_ID', 4);

/* User Status code from table userStatus */
define('USER_STATUS_INTERESTED', 1);
define('USER_STATUS_INVITED', 2);
define('USER_STATUS_ACTIVE', 3);

/* Callback status */
define('CALLBACK_NEW', 'callback_new');
define('CALLBACK_ERROR', 'callback_error');
define('CALLBACK_SUCCESS', 'callback_success');

/* Module names */
define('MODULE_QUALTRICS', 'moduleQualtrics');
define('MODULE_MAIL', 'moduleMail');
define('ALL_MODULES', [MODULE_QUALTRICS, MODULE_MAIL]);
?>
