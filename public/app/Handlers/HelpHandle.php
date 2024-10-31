<?php

namespace App\Handlers;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class HelpHandle
{
    public function __invoke(Nutgram $bot, bool $edit = false)
    {
        $messageToSend = "Oi, you need <i>help</i> huh? Fine, listen up.\n\nFrom the <b>buttons</b> below, you can get to all the commands you need. Just use / before them to make 'em work. ⚔️\n\n<b>Useful commands:</b>\n\n- /start: Starts the bot. You’ve probably done this already.\n- /help: I’ll show you all the commands I’ve got.\n- /donate: Want to support the one who built this? Here’s how.\n\nIf you run into any bugs, or got questions, message @erfankaramidev. Don't waste my time with nonsense! 🗡";

        $keyboard = InlineKeyboardMarkup::make()->addRow(
            InlineKeyboardButton::make('Ban', callback_data: 'help:ban'),
            InlineKeyboardButton::make('Mute', callback_data: 'help:mute'),
            InlineKeyboardButton::make('Warning', callback_data: 'help:warning'),
        );

        if (!$edit) {
            $bot->sendMessage(
                text: $messageToSend,
                reply_markup: $keyboard,
                reply_to_message_id: $bot->messageId(),
                parse_mode: ParseMode::HTML,
            );    
        } else {
            $bot->editMessageText(
                text: $messageToSend,
                reply_markup: $keyboard,
                parse_mode: ParseMode::HTML
            );
        }
    }
}
