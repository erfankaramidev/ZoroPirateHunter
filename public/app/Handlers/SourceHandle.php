<?php

namespace App\Handlers;

use SergiX44\Nutgram\Nutgram;

class SourceHandle
{
    public function __invoke(Nutgram $bot)
    {
        $bot->sendMessage(
            "Oi, this bot’s open source. If you’re curious, go take a look: https://github.com/erfankaramidev/ZoroPirateHunter. Just don’t expect me to explain it 🗡️😏",
            reply_to_message_id: $bot->messageId()
        );
    }
}
