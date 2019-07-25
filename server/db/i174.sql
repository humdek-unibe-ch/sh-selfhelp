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
