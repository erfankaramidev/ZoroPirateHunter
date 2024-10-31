<?php

namespace App\CallbackQueryData;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class MuteCallback
{
    public function __invoke(Nutgram $bot)
    {
        $message = "<b>Mute</b> 🚫\n\n" .
            "You can mute a user to prevent them from sending messages in the group if they break the rules. ⚔️\n\n" .
            "<b>Commands:</b>\n" .
            "- /mute: Mute a user.\n" .
            "- /dmute: Mute a user by replying to their message, and I'll erase their message as well.\n\n" .
            "<b>Examples:</b>\n" .
            "- To mute user:\n" .
            "  -> <code>/mute @username</code>\n\n" .
            "Don't let the pests mess with the crew! 🗡️";

        $keyboard = InlineKeyboardMarkup::make()->addRow(
            InlineKeyboardButton::make('Back', callback_data: 'back:start'),
        );

        $bot->editMessageText($message, reply_markup: $keyboard, parse_mode: ParseMode::HTML);
    }
}
