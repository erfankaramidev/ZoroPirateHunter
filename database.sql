CREATE DATABASE IF NOT EXISTS zoropiratehunter
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_520_ci;

CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `chat_id` BIGINT UNIQUE
);

CREATE TABLE IF NOT EXISTS `settings` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(255) UNIQUE,
    `value` text
);

CREATE TABLE IF NOT EXISTS `bans` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `chat_id` BIGINT UNIQUE
);

CREATE TABLE IF NOT EXISTS `mutes` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `chat_id` BIGINT UNIQUE
);

CREATE TABLE IF NOT EXISTS `warnings` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `chat_id` BIGINT UNIQUE,
    `numbers` TINYINT UNSIGNED,
    `reason` VARCHAR(100)
);

INSERT INTO `settings` (`key`, `value`)
VALUES
    (
        'start_message',
        'Oi, I\'m Roronoa Zoro, the Pirate Hunter. 🗡\n' 
        'I ain\'t got time for nonsense, but if you need help defending your crew from rule breakers, ' 
        'I’m your guy! ⚔️ Use /help to see how I can slice down your enemies! 💥\n\n' 
        'And if you feel like showin\' some appreciation for the captain who set this up, ' 
        'use /donate to toss some coins their way. 🏴‍☠️💰\n\n' 
        'Now, let\'s get to it!'
    ) 
ON DUPLICATE KEY UPDATE
    `value` = VALUES(`value`);

INSERT INTO `settings` (`key`, `value`)
VALUES
    ('warnlimit', '3') 
ON DUPLICATE KEY UPDATE
    `value` = VALUES(`value`);