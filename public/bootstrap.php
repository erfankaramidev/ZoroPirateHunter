<?php
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Webhook;

require_once __DIR__ . "/vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use SergiX44\Nutgram\Configuration;

date_default_timezone_set($_ENV['TIMEZONE']);

if ($_ENV['ENV'] = 'development') {
    $config = new Configuration(
        enableHttp2: false,
        isLocal: true,
        botName: 'Zoro "Pirate Hunter"',
        clientTimeout: 15,
    );
} else {
    $config = new Configuration(
        botName: 'Zoro "Pirate Hunter"',
        clientTimeout: 15,
    );
}

$bot = new Nutgram($_ENV['BOT_TOKEN'], $config);

require_once __DIR__ . '/handler.php';

$webhook = new Webhook(secretToken: $_ENV['SECRET_TOKEN']);
$webhook->setSafeMode(true);

$bot->setRunningMode($webhook);

return $bot;
