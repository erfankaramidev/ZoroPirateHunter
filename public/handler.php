<?php

use SergiX44\Nutgram\Nutgram;
use App\Handlers\{BanHandle, HelpHandle, MuteHandle, UserHandle, StartHandle, WarningHandle, JoinHandle, WelcomeHandle, SourceHandle};
use App\Middlewares\{IsAdminMiddleware, IsBotAdmin};
use App\CallbackQueryData\{BanCallback, MuteCallback, WarningCallback};
use SergiX44\Nutgram\Telegram\Exceptions\TelegramException;

// Commands
$bot->onCommand('start', StartHandle::class);
$bot->onCommand("start@{$_ENV['BOT_USERNAME']}", StartHandle::class);

$bot->onCommand('help', HelpHandle::class);
$bot->onCommand("help@{$_ENV['BOT_USERNAME']}", HelpHandle::class);

// On user join
$bot->onNewChatMembers(JoinHandle::class);

// Set welcome message
$bot->onCommand('setwelcome {welcomeMessage}', WelcomeHandle::class)->middleware(IsAdminMiddleware::class);

// Bans
$bot->group(function (Nutgram $bot) {
    $bot->onText('/ban(?!\s+@)(?:\s+(.*))?', function (Nutgram $bot, ?string $reason) {
        $banHandle = new BanHandle($bot);
        $banHandle->banByReply($reason);
    });
    $bot->onText('/dban(?:\s+(.*))?', function (Nutgram $bot, ?string $reason) {
        $banHandle = new BanHandle($bot);
        $banHandle->banByReply($reason, true);
    });
    $bot->onCommand('unban', function (Nutgram $bot) {
        $banHandle = new BanHandle($bot);
        $banHandle->unbanByReply();
    });
    $bot->onText('/ban @{username}(?:\s+(.*))?', function (Nutgram $bot, string $username, ?string $reason) {
        $banHandle = new BanHandle($bot);
        $banHandle->banByUserName($username, $reason);
    });
    $bot->onCommand("unban @{username}", function (Nutgram $bot, string $username) {
        $banHandle = new BanHandle($bot);
        $banHandle->unbanByUserName($username);
    });
})->middleware(IsBotAdmin::class)->middleware(IsAdminMiddleware::class);

// Mutes
$bot->group(function (Nutgram $bot) {
    $bot->onCommand('mute', function (Nutgram $bot) {
        $muteHandle = new MuteHandle($bot);
        $muteHandle->muteByReply();
    });
    $bot->onCommand('mute @{username}', function (Nutgram $bot, string $username) {
        $muteHandle = new MuteHandle($bot);
        $muteHandle->muteByUsername($username);
    });
    $bot->onCommand('dmute', function (Nutgram $bot) {
        $muteHandle = new MuteHandle($bot);
        $muteHandle->muteByReply(true);
    });
    $bot->onCommand('unmute', function (Nutgram $bot) {
        $muteHandle = new MuteHandle($bot);
        $muteHandle->unmuteByReply();
    });
    $bot->onCommand('unmute @{username}', function (Nutgram $bot, string $username) {
        $muteHandle = new MuteHandle($bot);
        $muteHandle->unmuteByUsername($username);
    });
})->middleware(IsBotAdmin::class)->middleware(IsAdminMiddleware::class);

// Warnings
$bot->group(function (Nutgram $bot) {
    $bot->onText('/warn(?!\s+@)(?:\s+(.*))?', function (Nutgram $bot, $reason) {
        $warningHandler = new WarningHandle($bot);
        $warningHandler->warnByReply($reason ?? '');
    });
    $bot->onText('/warn @{username}(?:\s+(.*))?', function (Nutgram $bot, string $username, $reason) {
        $warningHandler = new WarningHandle($bot);
        $warningHandler->warnByUsername($username, reason: $reason ?? '');
    });
    $bot->onText('/dwarn(?:\s+(.*))?', function (Nutgram $bot, $reason) {
        $warningHandler = new WarningHandle($bot);
        $warningHandler->warnByReply(true, $reason ?? '');
    });
    $bot->onCommand('rmwarn', function (Nutgram $bot) {
        $warningHandler = new WarningHandle($bot);
        $warningHandler->removeWarnByReply();
    });
    $bot->onText('/rmwarn @{username}', function (Nutgram $bot, string $username) {
        $warningHandler = new WarningHandle($bot);
        $warningHandler->removeWarnByUsername($username);
    });
    $bot->onText('/resetwarn', function (Nutgram $bot) {
        $warningHandler = new WarningHandle($bot);
        $warningHandler->resetWarnByReply();
    });
    $bot->onText('/resetwarn @{username}', function (Nutgram $bot, string $username) {
        $warningHandler = new WarningHandle($bot);
        $warningHandler->resetWarnByUsername($username);
    });
    $bot->onText("/warnlimit {warnLimit}", function (Nutgram $bot, $warnLimit) {
        $warningHandler = new WarningHandle($bot);
        $warningHandler->setWarnLimit($warnLimit);
    });
})->middleware(IsBotAdmin::class)->middleware(IsAdminMiddleware::class);
$bot->onCommand('warns', function (Nutgram $bot) {
    $warningHandler = new WarningHandle($bot);
    $warningHandler->warns();
});
$bot->onCallbackQueryData("warn:rmwarn{userId}", function (Nutgram $bot, $userId) {
    $warningHandler = new WarningHandle($bot);
    $warningHandler->removeWarnByCallback($userId);
})->middleware(IsBotAdmin::class)->middleware(function (Nutgram $bot, $next) {
    $adminMiddleware = new IsAdminMiddleware(true);
    $adminMiddleware($bot, $next);
});

$bot->onText("/say {message}", function (Nutgram $bot, $message) {
    $bot->deleteMessage($bot->chatId(), $bot->messageId());

    $bot->sendMessage(
        $message,
        reply_to_message_id: $bot->message()->reply_to_message->message_id,
    );
})->middleware(IsAdminMiddleware::class);

// Bot source code
$bot->onCommand('sourcecode', SourceHandle::class);

// Register new users to the database
$bot->onMessage(function (Nutgram $bot) {
    $userHandle = new UserHandle($bot);
    $userHandle->checkUser();
});

// Callbacks
$bot->onCallbackQueryData("help:ban", BanCallback::class);
$bot->onCallbackQueryData("help:mute", MuteCallback::class);
$bot->onCallbackQueryData("help:warning", WarningCallback::class);
$bot->onCallbackQueryData("back:start", function (Nutgram $bot) {
    $helpHandle = new HelpHandle();
    $helpHandle($bot, true);
});

// On error
if ($_ENV['ENV'] === 'production') {
    $bot->onException(function (Nutgram $bot, \Throwable $exception) {
        $error = date("Y-m-d H:i:s") . " - " . $exception->getMessage() . " - " . $exception->getCode();

        $bot->sendMessage(
            $error,
            $_ENV['OWNER_CHAT_ID']
        );
        error_log($exception);

        $bot->sendMessage(
            "Oops, the bot ran into an issue and crashed. Don’t worry, the details are being sent to the bot owner to fix it. 🚧"
        );
    });

    $bot->onApiError(function (Nutgram $bot, TelegramException $exception) {
        $error = date("Y-m-d H:i:s") . " - " . $exception->getMessage() . " - " . $exception->getCode();

        $bot->sendMessage(
            $error,
            $_ENV['OWNER_CHAT_ID']
        );
        error_log($exception);

        $bot->sendMessage(
            "Oops, the bot ran into an issue and crashed. Don’t worry, the details are being sent to the bot owner to fix it. 🚧",
        );
    });
}
