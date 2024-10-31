<?php

use App\CallbackQueryData\BanCallback;
use App\CallbackQueryData\MuteCallback;
use App\CallbackQueryData\WarningCallback;
use App\Commands\HelpCommand;
use App\Commands\StartCommand;
use App\Handlers\HelpHandle;
use SergiX44\Nutgram\Nutgram;

// Commands
$bot->registerCommand(StartCommand::class);
$bot->registerCommand(HelpCommand::class);

// Callbacks
$bot->onCallbackQueryData("help:ban", BanCallback::class);
$bot->onCallbackQueryData("help:mute", MuteCallback::class);
$bot->onCallbackQueryData("help:warning", WarningCallback::class);
$bot->onCallbackQueryData("back:start", function (Nutgram $bot) {
    $helpHandle = new HelpHandle();
    $helpHandle($bot, true);
});
