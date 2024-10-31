<?php

namespace App\Commands;

use App\Database\Database;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Nutgram;

class StartCommand extends Command
{
    protected string $command = 'start';

    public function handle(Nutgram $bot)
    {
        if (!$this->isUserExists($bot)) {
            $this->registerUser($bot->chatId());
        }

        $db = new Database();

        $messageToSend = $db->query("SELECT * FROM settings WHERE `key` = 'start_message'")->find()['value'];

        $bot->sendMessage($messageToSend);
    }

    private function isUserExists(Nutgram $bot): bool
    {
        $db = new Database();

        $user = $db->query("SELECT * FROM users WHERE chat_id = ?", [
            $bot->chatId()
        ])->find();

        return !empty($user);
    }

    private function registerUser(int $chatId)
    {
        $db = new Database();

        $db->query("INSERT INTO users (chat_id) VALUES (?)", [
            $chatId
        ]);
    }
}
