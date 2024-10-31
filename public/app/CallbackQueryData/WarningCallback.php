<?php

namespace App\CallbackQueryData;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class WarningCallback
{
    public function __invoke(Nutgram $bot)
    {
        $message = "<b>Warning</b> 🚫\n\n" .
            "You can keep your members in check with warning and stop them getting out of control.\n\n" .
            "<b>Admin Commands:</b>\n" .
            "- /warn: Warn a user.\n" .
            "- /dwarn: Warn a user by replying to their message, and I'll erase their message as well.\n" .
            "- /rwarn: Remove warning from a user.\n" .
            "- /resetwarn: Reset all warnings.\n" .
            "- /warnlimit <code>&lt;number&gt;</code>: Set the number of warnings before a user is banned.\n\n" .
            "<b>Examples:</b>\n" .
            "- To Warn a user:\n" .
            "  -> <code>/warn @username</code>\n\n" .
            "Keep the crew in line! 🗡️";

        $keyboard = InlineKeyboardMarkup::make()->addRow(
            InlineKeyboardButton::make('Back', callback_data: 'back:start'),
        );

        $bot->editMessageText($message, reply_markup: $keyboard, parse_mode: ParseMode::HTML);
    }
}
