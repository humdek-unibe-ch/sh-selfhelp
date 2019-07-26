-- add page type open
INSERT INTO `pageType` (`id`, `name`) VALUES (NULL, 'open');

--
-- Table structure for table `userStatus`
--

CREATE TABLE `userStatus` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `userStatus`
--

INSERT INTO `userStatus` (`id`, `name`, `description`) VALUES
(0000000001, 'interested', 'This user has shown interest in the platform but has not yet met the preconditions to be invited.'),
(0000000002, 'invited', 'This user was invited to join the platform but has not yet validated the email address.'),
(0000000003, 'active', 'This user can log in and visit all accessible pages.');

--
-- Indexes for table `userStatus`
--
ALTER TABLE `userStatus`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `userStatus`
--
ALTER TABLE `userStatus`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

-- Add id_status field to users table
ALTER TABLE `users` ADD `id_status` INT UNSIGNED ZEROFILL NULL DEFAULT '1' AFTER `blocked`, ADD INDEX (`id_status`);
ALTER TABLE `users` ADD CONSTRAINT `fk_users_id_status` FOREIGN KEY (`id_status`) REFERENCES `userStatus`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Update status of users
UPDATE `users` SET `id_status` = '2' WHERE token IS NOT NULL;
UPDATE `users` SET `id_status` = '3' WHERE password IS NOT NULL;
-- Update status of user guest
UPDATE `users` SET `id_status` = NULL WHERE id = '1';

-- add style interstedUserForm
INSERT INTO `styles` (`name`, `id_type`, `id_group`, `description`) VALUES ('emailForm', '2', (SELECT `id` FROM `styleGroup` WHERE `name` = 'Form'), 'A form to accept an email address and automatically send two emails: An email to the address entered in the form and another email to admins, specified in the style.');
SET @id_style_emailForm = LAST_INSERT_ID();
INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES ('admins', (SELECT `id` FROM `fieldType` WHERE `name` = 'json'), '0');
SET @id_field_admins = LAST_INSERT_ID();
INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES ('email_admins', (SELECT `id` FROM `fieldType` WHERE `name` = 'email'), '1');
SET @id_field_email_admins = LAST_INSERT_ID();
INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES ('email_user', (SELECT `id` FROM `fieldType` WHERE `name` = 'email'), '1');
SET @id_field_email_user = LAST_INSERT_ID();
INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES ('subject_user', (SELECT `id` FROM `fieldType` WHERE `name` = 'text'), '1');
SET @id_field_subject_user = LAST_INSERT_ID();
INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES ('attachments_user', (SELECT `id` FROM `fieldType` WHERE `name` = 'json'), '1');
SET @id_field_attachments_user = LAST_INSERT_ID();
INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES ('do_store', (SELECT `id` FROM `fieldType` WHERE `name` = 'checkbox'), '0');
SET @id_field_do_store = LAST_INSERT_ID();
INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES ('is_html', (SELECT `id` FROM `fieldType` WHERE `name` = 'checkbox'), '0');
SET @id_field_is_html = LAST_INSERT_ID();

INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_emailForm, (SELECT `id` FROM `fields` WHERE `name` = 'label'), 'The label on the submit button.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_emailForm, (SELECT `id` FROM `fields` WHERE `name` = 'type'), 'The bootstrap color of the submit button.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_emailForm, (SELECT `id` FROM `fields` WHERE `name` = 'placeholder'), 'The placeholder in the email input field.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_emailForm, @id_field_admins, 'A list of email addresses to be notified on submit with an email as defined in field `email_admins`. Use `json` syntax to specify the list of admins (e.g. `["__admin_1__", ..., "__admin_n__"]`) where `__admin_*__` is the email address of an admin.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_emailForm, @id_field_email_admins, 'The email to be sent to the the list of admins defined in the field `admins`. Use markdown syntax in conjunction with the field `is_html` if you want to send an email with html content. In addition to markdown, the following keyword is supported:\n- `@email` will be replaced by the email address entered in the form.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_emailForm, @id_field_email_user, 'The email to be sent to the the email address that was entered into the form. Use markdown syntax in conjunction with the field `is_html` if you want to send an email with html content.\n');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_emailForm, @id_field_subject_user, 'The subject of the email to be sent to the the email address that was entered into the form. Use the following keywords to create dynamic content:\n- `@project` will be replaced by the project name.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_emailForm, @id_field_attachments_user, 'The list of attachments to the email to be sent to the the address that was entered into the form. Use `json` syntax to specify a list of assets (e.g. `["__asset_1__", ..., "__asset_n__"]`) where `__asset_*__` is the name of an uploaded asset.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style_emailForm, @id_field_do_store, '0', 'If checked, the entered email address will be stored in the database.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `default_value`, `help`) VALUES (@id_style_emailForm, @id_field_is_html, '0', 'If *checked*, the email will parsed as markdown and sent as html. The unparsed email content will be sent as plaintext alternative. If left *unchecked* the emails will only be sent as plaintext');
