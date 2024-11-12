<?php

namespace App\Handlers;

use App\Database\Database;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Chat\ChatPermissions;

class MuteHandle
{
    public function __construct(private Nutgram $bot)
    {
    }

    public function muteByReply(bool $deleteMessage = false)
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
                    "Oh? You’re gonna mute an <i>admin</i>? Bold move. Let’s see how that goes. 😒⚔️",
                    reply_to_message_id: $this->bot->messageId(),
                    parse_mode: ParseMode::HTML
                );
                return;
            }
        }

        $muteUser = $this->bot->restrictChatMember(
            $this->bot->chatId(),
            $repliedMessageUserId,
            ChatPermissions::make(
                can_send_messages: false,
            ),
        );

        if ($muteUser) {
            $this->saveToDatabase($repliedMessageUserId);

            $name = $this->bot->message()->reply_to_message->from->first_name;

            $this->bot->sendMessage(
                "<a href=\"tg://user?id=$repliedMessageUserId\">$name</a> couldn’t keep quiet, huh? Well, they’re muted now. 🍃 Finally, some peace.",
                reply_to_message_id: $this->bot->messageId(),
                parse_mode: ParseMode::HTML
            );

            if ($deleteMessage)
                $this->bot->deleteMessage($this->bot->chatId(), $this->bot->message()->reply_to_message->message_id);
        } else
            $this->bot->sendMessage(
                "Tch. Tried muting, but something went wrong. Guess they’re tougher than I thought. 🍶", reply_to_message_id: $this->bot->messageId());
    }

    public function muteByUsername(string $username)
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

        $isMuted = $db->query("SELECT * FROM mutes WHERE user_id = ?", [
            $user['user_id']
        ])->find();

        if ($isMuted)
            $this->deleteFromDatabase($user['user_id']);

        $chatPermissions = ChatPermissions::make(
            can_send_messages: false
        );
        $muteUser = $this->bot->restrictChatMember($this->bot->chatId(), $user['user_id'], $chatPermissions);

        if ($muteUser) {
            $this->saveToDatabase($user['user_id']);

            $this->bot->sendMessage(
                'Huh, <a href="tg://user?id=' . $user['user_id'] . '">@' . $user['username'] . '</a> is muted. Finally, some peace and quiet. Don’t mess it up 😒',
                parse_mode: ParseMode::HTML,
                reply_to_message_id: $this->bot->messageId()
            );
        } else
            $this->bot->sendMessage("Tch. There was an error with the task 😤📝", reply_to_message_id: $this->bot->messageId());

    }

    public function unmuteByReply()
    {
        $repliedMessageUserId = $this->bot->message()->reply_to_message->from->id ?? null;

        if (! $repliedMessageUserId) {
            $this->bot->sendMessage(
                "User not found. 🤨 Who are you looking for, a ghost?",
                reply_to_message_id: $this->bot->messageId()
            );
            return;
        }

        $permissions = ChatPermissions::make(true, true, true, true, true, true, true, true, true, true, true, true, true, true);
        $unmuteUser = $this->bot->restrictChatMember(
            $this->bot->chatId(),
            $repliedMessageUserId,
            $permissions
        );

        if ($unmuteUser) {
            $this->deleteFromDatabase($repliedMessageUserId);

            $name = $this->bot->message()->reply_to_message->from->first_name;

            $this->bot->sendMessage(
                "Ugh, <a href=\"tg://user?id=$repliedMessageUserId\">$name</a> is back! 🔥 As if we needed more noise around here. Let’s see if they can shut up this time!.",
                reply_to_message_id: $this->bot->messageId(),
                parse_mode: ParseMode::HTML
            );
        } else
            $this->bot->sendMessage(
                "Tch. Tried unmuting, but something went wrong. Guess they’re tougher than I thought. 🍶", reply_to_message_id: $this->bot->messageId());
    }

    public function unmuteByUsername(string $username)
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

        $this->deleteFromDatabase($user['user_id']);

        $chatPermissions = ChatPermissions::make(
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
        );

        $isMuted = $this->bot->restrictChatMember($this->bot->chatId(), $user['user_id'], $chatPermissions);

        if ($isMuted) {
            $this->bot->sendMessage(
                'Tch, @' . $user['username'] . ' unmuted. Let’s see how long you can keep quiet this time 🙄',
                reply_to_message_id: $this->bot->messageId()
            );
        } else
            $this->bot->sendMessage("Tch. There was an error with the task 😤📝", reply_to_message_id: $this->bot->messageId());

    }

    private function saveToDatabase(string|int $userId)
    {
        $db = new Database();

        $db->query("INSERT INTO mutes (user_id) VALUES (?)", [
            $userId
        ]);
    }

    private function deleteFromDatabase(string|int $userId)
    {
        $db = new Database();

        $db->query("DELETE FROM mutes WHERE user_id = ?", [
            $userId
        ]);
    }
}
