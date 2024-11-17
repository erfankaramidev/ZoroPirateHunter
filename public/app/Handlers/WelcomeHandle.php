<?php

namespace App\Handlers;

use App\Database\Database;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class WelcomeHandle
{
    public function __invoke(Nutgram $bot, $welcomeMessage)
    {
        $db = new Database();

        $db->query("UPDATE settings SET `value` = ?, updated_at = CURRENT_TIMESTAMP() WHERE `key` = 'welcome_message'", [$welcomeMessage]);

        $bot->sendMessage("The welcome message has been <i>successfully</i> updated, Captain. ⚔️", parse_mode: ParseMode::HTML, reply_to_message_id: $bot->messageId());
    }
}
