<?php

namespace App\Handlers;

use App\Database\Database;
use SergiX44\Nutgram\Nutgram;

class UserHandle
{
    public function __construct(private Nutgram $bot)
    {
    }

    public function checkUser()
    {
        if (! $this->isUserExists()) {
            $this->registerUser();
            return;
        }

        $currentUsername = $this->findUser()['username'];

        if ($currentUsername !== $this->bot->message()->from->username)
            $this->updateUser();
    }

    private function isUserExists()
    {
        $db = new Database();

        $user = $db->query("SELECT * FROM users WHERE user_id = ?", [$this->bot->userId()])->find();

        return ! empty($user);
    }

    private function registerUser()
    {
        $db = new Database();

        $db->query("INSERT INTO users (user_id, username) VALUES (?, ?)", [
            $this->bot->message()->from->id,
            $this->bot->message()->from->username
        ]);
    }

    private function updateUser()
    {
        $db = new Database();

        $db->query("UPDATE users SET username = ?, updated_at = NOW() WHERE user_id = ?", [
            $this->bot->message()->from->username,
            $this->bot->message()->from->id,
        ]);
    }

    private function deleteUser()
    {
        $db = new Database();

        $db->query("DELETE FROM users WHERE user_id = ?", [
            $this->bot->message()->from->id
        ]);
    }

    private function findUser()
    {
        $db = new Database();

        return $db->query("SELECT * FROM users WHERE user_id = ?", [
            $this->bot->message()->from->id
        ])->find();
    }
}
