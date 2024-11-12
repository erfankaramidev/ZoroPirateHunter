<?php

namespace App\Middlewares;

use SergiX44\Nutgram\Nutgram;

class IsAdminMiddleware
{
    public function __invoke(Nutgram $bot, $next)
    {
        $admins = $bot->getChatAdministrators($bot->chat()->id);

        foreach ($admins as $admin) {
            if ($admin->user->id === $bot->user()->id) {
                $next($bot);
                return;
            }
        }
        $bot->sendMessage('Oi, you’re not an admin. So don’t get any big ideas ⚔️', reply_to_message_id: $bot->messageId());    
    }
}
