<?php

use SergiX44\Nutgram\Nutgram;
use App\Handlers\{BanHandle, HelpHandle, MuteHandle, UserHandle, StartHandle};
use App\Middlewares\{IsAdminMiddleware};
use App\CallbackQueryData\{BanCallback, MuteCallback, WarningCallback};

// Commands
$bot->onCommand('start', StartHandle::class);
$bot->onCommand("start@{$_ENV['BOT_USERNAME']}", StartHandle::class);

$bot->onCommand('help', HelpHandle::class);
$bot->onCommand("help@{$_ENV['BOT_USERNAME']}", HelpHandle::class);

// Bans
$bot->group(function (Nutgram $bot) {
    $bot->onCommand('ban', function (Nutgram $bot) {
        $banHandle = new BanHandle($bot);
        $banHandle->banByReply();
    });
    $bot->onCommand('dban', function (Nutgram $bot) {
        $banHandle = new BanHandle($bot);
        $banHandle->banByReply(true);
    });
    $bot->onCommand('unban', function (Nutgram $bot) {
        $banHandle = new BanHandle($bot);
        $banHandle->unbanByReply();
    });
    $bot->onCommand('ban @{username}', function (Nutgram $bot, string $username) {
        $banHandle = new BanHandle($bot);
        $banHandle->banByUserName($username);
    });
    $bot->onCommand("unban @{username}", function (Nutgram $bot, string $username) {
        $banHandle = new BanHandle($bot);
        $banHandle->unbanByUserName($username);
    });
})->middleware(IsAdminMiddleware::class);

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
})->middleware(IsAdminMiddleware::class);

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
