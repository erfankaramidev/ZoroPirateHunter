<?php

namespace App\Handlers;

use App\Database\Database;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class BanHandle
{
    public function __construct(private Nutgram $bot)
    {
    }

    public function banByReply(bool $deleteMessage = false)
    {
        $repliedMessageUserId = $this->bot->message()->reply_to_message->from->id ?? null;

        if (! $repliedMessageUserId) {
            $this->bot->sendMessage(
                "User not found. 🤨 Who are you looking for, a ghost?",
                reply_to_message_id: $this->bot->messageId()
            );
            return;
        }

        $admins = $this->bot->getChatAdministrators($this->bot->chatId());

        foreach ($admins as $admin) {
            if ($admin->user->id == $repliedMessageUserId) {
                $this->bot->sendMessage(
                    "Oh? You’re gonna ban an <i>admin</i>? Bold move. Let’s see how that goes. 😒⚔️",
                    reply_to_message_id: $this->bot->messageId(),
                    parse_mode: ParseMode::HTML
                );
                return;
            }
        }

        $banUser = $this->bot->banChatMember($this->bot->chatId(), $repliedMessageUserId);

        if ($banUser) {
            $this->saveToDatabase($repliedMessageUserId);

            $name = $this->bot->message()->reply_to_message->from->first_name;

            $this->bot->sendMessage(
                '<a href="tg://user?id=' . $repliedMessageUserId . '">' . $name . '</a> is banned. Good, that’s less nonsense to deal with. 🚫',
                parse_mode: ParseMode::HTML,
                reply_to_message_id: $this->bot->messageId()
            );

            if ($deleteMessage)
                $this->bot->deleteMessage($this->bot->chatId(), $this->bot->message()->reply_to_message->message_id);
        } else
            $this->bot->sendMessage("Tch. There was an error with the task 😤📝", reply_to_message_id: $this->bot->messageId());
    }

    public function banByUsername(string $username)
    {
        $db = new Database();

        $user = $db->query("SELECT * FROM users WHERE username = ?", [
            $username
        ])->find();

        if (! $user) {
            $this->bot->sendMessage(
                "User not found. 🤨 Who are you looking for, a ghost?",
                reply_to_message_id: $this->bot->messageId()
            );
            return;
        }

        $admins = $this->bot->getChatAdministrators($this->bot->chatId());

        foreach ($admins as $admin) {
            if ($admin->user->id == $user['user_id']) {
                $this->bot->sendMessage(
                    "Oh? You’re gonna ban an <i>admin</i>? Bold move. Let’s see how that goes. 😒⚔️",
                    reply_to_message_id: $this->bot->messageId(),
                    parse_mode: ParseMode::HTML
                );
                return;
            }
        }

        $isBanned = $db->query("SELECT * FROM bans WHERE user_id = ?", [
            $user['user_id']
        ])->find();

        if ($isBanned)
            $this->deleteFromDatabase($user['user_id']);

        $banUser = $this->bot->banChatMember($this->bot->chatId(), $user['user_id']);

        if ($banUser) {
            $this->saveToDatabase($user['user_id']);

            $this->bot->sendMessage(
                '<a href="tg://user?id=' . $user['user_id'] . '">@' . $user['username'] . '</a> is banned. Good, that’s less nonsense to deal with. 🚫',
                parse_mode: ParseMode::HTML,
                reply_to_message_id: $this->bot->messageId()
            );
        } else
            $this->bot->sendMessage("Tch. There was an error with the task 😤📝", reply_to_message_id: $this->bot->messageId());
    }

    public function unbanByReply()
    {
        $repliedMessageUserId = $this->bot->message()->reply_to_message->from->id ?? null;

        if (! $repliedMessageUserId) {
            $this->bot->sendMessage(
                "User not found. 🤨 Who are you looking for, a ghost?",
                reply_to_message_id: $this->bot->messageId()
            );
            return;
        }

        $this->deleteFromDatabase($repliedMessageUserId);

        $isUnbanned = $this->bot->unbanChatMember($this->bot->chatId(), $repliedMessageUserId);

        if ($isUnbanned) {
            $this->bot->sendMessage(
                'Ugh <a href="tg://user?id=' . $repliedMessageUserId . '">' . $this->bot->message()->reply_to_message->from->first_name . '</a> is unbanned. Guess you just can’t stay outta trouble, huh? 🙄',
                parse_mode: ParseMode::HTML,
                reply_to_message_id: $this->bot->messageId()
            );
        } else {
            $this->bot->sendMessage("Tch. There was an error with the task 😤📝", reply_to_message_id: $this->bot->messageId());
        }
    }

    public function unBanByUsername(string $usernmae)
    {
        $db = new Database();

        $user = $db->query("SELECT * FROM users WHERE username = ?", [
            $usernmae
        ])->find();

        if (! $user) {
            $this->bot->sendMessage(
                "User not found. 🤨 Who are you looking for, a ghost?",
                reply_to_message_id: $this->bot->messageId()
            );
            return;
        }

        $this->deleteFromDatabase($user['user_id']);

        $isUnbanned = $this->bot->unbanChatMember($this->bot->chatId(), $user['user_id']);

        if ($isUnbanned) {
            $this->bot->sendMessage(
                'Ugh @' . $user['username'] . ' is unbanned. Guess you just can’t stay outta trouble, huh? 🙄',
                reply_to_message_id: $this->bot->messageId()
            );
        } else
            $this->bot->sendMessage("Tch. There was an error with the task 😤📝", reply_to_message_id: $this->bot->messageId());
    }

    private function saveToDatabase(string|int $userId)
    {
        $db = new Database();

        $db->query("INSERT INTO bans (user_id) VALUES (?)", [
            $userId
        ]);
    }

    private function deleteFromDatabase(string|int $userId)
    {
        $db = new Database();

        $db->query("DELETE FROM bans WHERE user_id = ?", [
            $userId
        ]);
    }
}
