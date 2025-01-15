<?php

namespace App\Middlewares;

use SergiX44\Nutgram\Nutgram;

class IsBotAdmin
{
    public function __invoke(Nutgram $bot, $next)
    {
        if ($bot->getChat($bot->chatId())->isPrivate()) {
            return;
        }
        $admins = $bot->getChatAdministrators($bot->chatId());

        foreach ($admins as $admin) {
            if ($admin->user->id === $bot->getMe()->id) {
                $next($bot);
                return;
            }
        }
    }
}
