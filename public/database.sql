CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNIQUE,
    `username` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP(),
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP()
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;

CREATE TABLE IF NOT EXISTS `settings` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(255) UNIQUE,
    `value` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP(),
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP()
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;

CREATE TABLE IF NOT EXISTS `bans` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNIQUE,
    `reason` VARCHAR(100),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP(),
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP()
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;

CREATE TABLE IF NOT EXISTS `mutes` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNIQUE,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP(),
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP()
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;

CREATE TABLE IF NOT EXISTS `warnings` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT,
    `reason` VARCHAR(100),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP(),
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP()
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;

INSERT INTO `settings` (`key`, `value`)
VALUES
    (
        "start_message",
        "Oi, I'm Roronoa Zoro, the Pirate Hunter. 🗡\n"
        "I ain't got time for nonsense, but if you need help defending your crew from rule breakers, "
        "I’m your guy! ⚔️ Use /help to see how I can slice down your enemies! 💥\n\n"
        "Now, let's get to it!"
    ) 
ON DUPLICATE KEY UPDATE
    `value` = VALUES(`value`);

INSERT INTO `settings` (`key`, `value`)
VALUES
    ('warnlimit', '3') 
ON DUPLICATE KEY UPDATE
    `value` = VALUES(`value`);

INSERT INTO `settings` (`key`, `value`)
VALUES
    (
        'bot_join_message',
        "Oi, I’m Roronoa Zoro, the Pirate Hunter. 🗡\n"
        "Thanks for adding me to your crew. If you want me to actually do my job, make me an admin already. ⚔️ Let’s cut through the nonsense and get to it!"
    )
ON DUPLICATE KEY UPDATE
    `value` = VALUES(`value`);

INSERT INTO `settings` (`key`, `value`)
VALUES
    (
        'welcome_message',
        "Oi {first_name}, welcome to the crew. Make sure you follow the rules—or else. 🗡😏"
    )
ON DUPLICATE KEY UPDATE
    `value` = VALUES(`value`);
