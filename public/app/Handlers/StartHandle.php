<?php

namespace App\Handlers;

use App\Database\Database;
use SergiX44\Nutgram\Nutgram;

class StartHandle
{
    public function __invoke(Nutgram $bot)
    {
        $db = new Database();

        $messageToSend = $db->query("SELECT * FROM settings WHERE `key` = 'start_message'")->find()['value'];

        $bot->sendMessage($messageToSend, reply_to_message_id: $bot->messageId());
    }
}
