<?php

namespace App\CallbackQueryData;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class BanCallback
{
    public function __invoke(Nutgram $bot)
    {
        $message = "<b>Ban</b> 🚫\n\n" .
            "Some people just need to be thrown overboard—spammers, annoyances, ads, trolls, you name it. I’ll handle ’em. ⚔️\n\n" .
            "<b>Admin Commands:</b>\n" .
            "- /ban: Ban a user from the group.\n" .
            "- /dban: Ban a user by replying to their message, and I'll erase their message as well.\n\n" .
            "<b>Examples:</b>\n" .
            "- To ban a user:\n" .
            "  -> <code>/ban @username</code>\n\n" .
            "Don't let the pests mess with the crew! 🗡️";

        $keyboard = InlineKeyboardMarkup::make()->addRow(
            InlineKeyboardButton::make('Back', callback_data: 'back:start'),
        );

        $bot->editMessageText($message, reply_markup: $keyboard, parse_mode: ParseMode::HTML);
    }
}
