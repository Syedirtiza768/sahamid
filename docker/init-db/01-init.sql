-- Ensure the database exists and grant privileges
CREATE DATABASE IF NOT EXISTS `sahamid` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE DATABASE IF NOT EXISTS `sah_saherp` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Grant all privileges to the application user
GRANT ALL PRIVILEGES ON `sahamid`.* TO 'irtiza'@'%';
GRANT ALL PRIVILEGES ON `sah_saherp`.* TO 'irtiza'@'%';
FLUSH PRIVILEGES;

-- Use the sahamid database for subsequent SQL files
USE `sahamid`;
