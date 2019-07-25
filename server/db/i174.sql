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
INSERT INTO `styles` (`name`, `id_type`, `id_group`, `description`) VALUES ('interestedUserForm', '2', (SELECT `id` FROM `styleGroup` WHERE `name` = 'Form'), 'A form to accept an email address and automatically send two emails: An email to the address entered in the form and another email to recepients, specified in the style.');
SET @id_style_interstedUserForm = LAST_INSERT_ID();
INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES ('recepients', (SELECT `id` FROM `fieldType` WHERE `name` = 'json'), '0');
SET @id_field_recepients = LAST_INSERT_ID();
INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES ('email_recepients', (SELECT `id` FROM `fieldType` WHERE `name` = 'email'), '1');
SET @id_field_email_recepients = LAST_INSERT_ID();
INSERT INTO `fields` (`name`, `id_type`, `display`) VALUES ('email_intersted_user', (SELECT `id` FROM `fieldType` WHERE `name` = 'email'), '1');
SET @id_field_email_intersted_user = LAST_INSERT_ID();

INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_interstedUserForm, @id_field_recepients, 'A list of email addresses to be notified on submit with an email as defined in field `email_recepients`.');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_interstedUserForm, @id_field_email_recepients, 'The email to be sent to the the list of recepients defined in the field `recepients`. Use the following keywords to create dynamic content:\n- `@project` will be replaced by the project name\n- `@link` will be replaced by a link to the project home');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_interstedUserForm, @id_field_email_intersted_user, 'The email to be sent to the the email address that was entered into the form. Use the following keywords to create dynamic content:\n- `@project` will be replaced by the project name\n- `@link` will be replaced by a link to the project login\n- `@attachment#__asset__` will attach the `__asset__` to the email');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_interstedUserForm, (SELECT `id` FROM `fields` WHERE `name` = 'label'), 'The label on the submit button');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_interstedUserForm, (SELECT `id` FROM `fields` WHERE `name` = 'type'), 'The bootstrap color of the submit button');
INSERT INTO `styles_fields` (`id_styles`, `id_fields`, `help`) VALUES (@id_style_interstedUserForm, (SELECT `id` FROM `fields` WHERE `name` = 'placeholder'), 'The placeholder in the email input field');
