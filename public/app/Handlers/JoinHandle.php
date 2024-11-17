<?php

namespace App\Handlers;

use App\Database\Database;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class JoinHandle
{
    public function __invoke(Nutgram $bot)
    {
        $db = new Database();

        if ($bot->message()->new_chat_members[0]->id === $bot->getMe()->id) {
            $joinMessage = $db->query("SELECT * FROM settings WHERE `key` = 'bot_join_message'")->find()['value'];
            $keyboard = InlineKeyboardMarkup::make()->addRow(
                InlineKeyboardButton::make("Get Help", "t.me/{$_ENV['BOT_USERNAME']}")
            );

            $bot->sendMessage($joinMessage, reply_markup: $keyboard);

            return;
        }

        $firstName = "<a href=\"tg://user?id={$bot->userId()}\">{$bot->user()->first_name}</a>";

        $welcomeMessage = $db->query("SELECT * FROM settings WHERE `key` = 'welcome_message'")->find()['value'];
        $welcomeMessage = preg_replace("#\{first_name\}#", $firstName, $welcomeMessage);

        $bot->sendMessage($welcomeMessage, parse_mode: ParseMode::HTML, reply_to_message_id: $bot->messageId());
    }
}
