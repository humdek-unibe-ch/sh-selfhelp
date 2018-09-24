<?php
define('ASSET_FOLDER', 'assets');
define('ASSET_PATH', BASE_PATH . '/' . ASSET_FOLDER);
define('ASSET_SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/' . ASSET_FOLDER);
define('SERVICE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/server/service');
define('EMAIL_PATH', $_SERVER['DOCUMENT_ROOT'] . '/server/email');

/* Static DB Content */
define('GUEST_USER_ID', 1);
define('ADMIN_GROUP_ID', 1);

define('NAVIGATION_STYLE_ID', 16);

define('CSS_FIELD_ID', 23);
define('LABEL_FIELD_ID', 8);

define('MALE_GENDER_ID', 1);
define('ALL_LANGUAGE_ID', 1);

define('EXPERIMENTER_GROUP_ID', 2);
define('SUBJECT_GROUP_ID', 3);

define('INTERNAL_PAGE_ID', 1);
define('CORE_PAGE_ID', 2);
define('EXPERIMENT_PAGE_ID', 3);
?>
