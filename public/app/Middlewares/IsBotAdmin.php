<?php

namespace App\Middlewares;

use SergiX44\Nutgram\Nutgram;

class IsBotAdmin
{
    public function __invoke(Nutgram $bot, $next)
    {
        $admins = $bot->getChatAdministrators($bot->chatId());

        foreach($admins as $admin) {
            if ($admin->user->id === $bot->getMe()->id) {
                $next($bot);
                return;
            }
        }
    }
}
