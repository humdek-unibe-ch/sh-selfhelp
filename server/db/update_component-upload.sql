--
-- Table structure for table `uploadCells`
--

CREATE TABLE `uploadCells` (
  `id_uploadRows` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_uploadCols` int(10) UNSIGNED ZEROFILL NOT NULL,
  `value` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `uploadCols`
--

CREATE TABLE `uploadCols` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL,
  `id_uploadTables` int(10) UNSIGNED ZEROFILL NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `uploadRows`
--

CREATE TABLE `uploadRows` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `id_uploadTables` int(10) UNSIGNED ZEROFILL NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `uploadTables`
--

CREATE TABLE `uploadTables` (
  `id` int(10) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for table `uploadCells`
--
ALTER TABLE `uploadCells`
  ADD PRIMARY KEY (`id_uploadRows`,`id_uploadCols`),
  ADD KEY `id_uploadRows` (`id_uploadRows`),
  ADD KEY `id_uploadCols` (`id_uploadCols`);

--
-- Indexes for table `uploadCols`
--
ALTER TABLE `uploadCols`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_uploadTables` (`id_uploadTables`);

--
-- Indexes for table `uploadRows`
--
ALTER TABLE `uploadRows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_uploadTables` (`id_uploadTables`);

--
-- Indexes for table `uploadTables`
--
ALTER TABLE `uploadTables`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `uploadCols`
--
ALTER TABLE `uploadCols`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `uploadRows`
--
ALTER TABLE `uploadRows`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `uploadTables`
--
ALTER TABLE `uploadTables`
  MODIFY `id` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `uploadCells`
--
ALTER TABLE `uploadCells`
  ADD CONSTRAINT `uploadCells_fk_id_uploadCols` FOREIGN KEY (`id_uploadCols`) REFERENCES `uploadCols` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `uploadCells_fk_id_uploadRows` FOREIGN KEY (`id_uploadRows`) REFERENCES `uploadRows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `uploadCols`
--
ALTER TABLE `uploadCols`
  ADD CONSTRAINT `uploadCols_fk_id_uploadTables` FOREIGN KEY (`id_uploadTables`) REFERENCES `uploadTables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `uploadRows`
--
ALTER TABLE `uploadRows`
  ADD CONSTRAINT `uploadRows_fk_id_uploadTables` FOREIGN KEY (`id_uploadTables`) REFERENCES `uploadTables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
