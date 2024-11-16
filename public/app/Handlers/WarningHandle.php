<?php

namespace App\Handlers;

use App\Database\Database;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class WarningHandle
{
    public function __construct(private Nutgram $bot)
    {
    }

    public function warnByReply(string $reason, bool $deleteMessage = false)
    {
        $repliedMessageUserId = $this->bot->message()->reply_to_message->from->id ?? null;

        if (! $repliedMessageUserId) {
            $this->bot->sendMessage(
                "User not found 🤨. Who are you looking for, a ghost?",
                reply_to_message_id: $this->bot->messageId()
            );
            return;
        }

        $admins = $this->bot->getChatAdministrators($this->bot->chatId());

        foreach ($admins as $admin) {
            if ($admin->user->id == $repliedMessageUserId) {
                $this->bot->sendMessage(
                    "Oh? You’re gonna warn an <i>admin</i>? Bold move. Let’s see how that goes. 😒⚔️",
                    reply_to_message_id: $this->bot->messageId(),
                    parse_mode: ParseMode::HTML
                );
                return;
            }
        }

        $userPreviousWarns = $this->userWarns($repliedMessageUserId);

        if (count($userPreviousWarns) == $this->getWarnLimit() - 1) {
            $this->ban($repliedMessageUserId);
            $this->resetWarns($repliedMessageUserId);

            $name = $this->bot->message()->reply_to_message->from->first_name;

            $this->bot->sendMessage(
                text: "That’s it, <a href=\"tg://user?id=$repliedMessageUserId\">$name</a>. {$this->getWarnLimit()}/{$this->getWarnLimit()}. Guess you’re outta here. Don’t let the door hit you on the way out 😏✌️",
                parse_mode: ParseMode::HTML,
                reply_to_message_id: $this->bot->messageId()
            );
        } else {
            $name = $this->bot->message()->reply_to_message->from->first_name;

            $this->warnUser($repliedMessageUserId, $reason);
            $removeWarnBtn = InlineKeyboardMarkup::make()->addRow(
                InlineKeyboardButton::make("Remove warn (admin only)", callback_data: "warn:rmwarn{$repliedMessageUserId}")
            );

            $userWarns = count($userPreviousWarns) + 1;
            $reason = ! empty($reason) ? $reason : "Tch, no reason given.";

            $this->bot->sendMessage(
                text: "Watch your mouth, <a href=\"tg://user?id=$repliedMessageUserId\">$name</a>! That’s $userWarns/{$this->getWarnLimit()}. Don’t push your luck ⚔️\nReason:\n$reason",
                reply_to_message_id: $this->bot->messageId(),
                parse_mode: ParseMode::HTML,
                reply_markup: $removeWarnBtn
            );
        }

        if ($deleteMessage === true)
            $this->bot->deleteMessage($this->bot->chatId(), $this->bot->message()->reply_to_message->message_id);
    }

    public function warnByUsername(string $username, string $reason)
    {
        $db = new Database();

        $user = $db->query("SELECT user_id FROM users WHERE username = ?", [
            $username
        ])->find();

        if (! $user)
            $this->bot->sendMessage("Cannot the user's warns with this method.");

        $admins = $this->bot->getChatAdministrators($this->bot->chatId());

        foreach ($admins as $admin) {
            if ($admin->user->id == $user['user_id']) {
                $this->bot->sendMessage(
                    "Oh? You’re gonna warn an <i>admin</i>? Bold move. Let’s see how that goes. 😒⚔️",
                    reply_to_message_id: $this->bot->messageId(),
                    parse_mode: ParseMode::HTML
                );
                return;
            }
        }

        $userPreviousWarns = $this->userWarns($user['user_id']);

        if (count($userPreviousWarns) == $this->getWarnLimit() - 1) {
            $this->ban($user['user_id']);
            $this->resetWarns($user['user_id']);

            $this->bot->sendMessage(
                text: "That’s it, @$username. {$this->getWarnLimit()}/{$this->getWarnLimit()}. Guess you’re outta here. Don’t let the door hit you on the way out 😏✌️",
                reply_to_message_id: $this->bot->messageId()
            );
        } else {
            $this->warnUser($user['user_id'], $reason);

            $userWarns = count($userPreviousWarns) + 1;
            $reason = ! empty($reason) ? $reason : "Tch, no reason given.";

            $this->bot->sendMessage(
                text: "Watch your mouth, @$username! That’s $userWarns/{$this->getWarnLimit()}. Don’t push your luck ⚔️\nReason:\n$reason",
                reply_to_message_id: $this->bot->messageId(),
            );
        }
    }

    public function removeWarnByReply()
    {
        $repliedMessageUserId = $this->bot->message()->reply_to_message->from->id ?? null;

        if (! $repliedMessageUserId) {
            $this->bot->sendMessage(
                "User not found 🤨. Who are you looking for, a ghost?",
                reply_to_message_id: $this->bot->messageId()
            );
            return;
        }

        $this->rmWarnUser($repliedMessageUserId);

        $name = $this->bot->message()->reply_to_message->from->first_name;
        $this->bot->sendMessage(
            "Tch, looks like <a href=\"tg://user?id=$repliedMessageUserId\">$name</a> got a warning erased. Someone's feeling generous... don't count on it happening again 😒.",
            parse_mode: ParseMode::HTML,
            reply_to_message_id: $this->bot->messageId()
        );
    }

    public function removeWarnByUsername(string $username)
    {
        $db = new Database();

        $user = $db->query("SELECT user_id FROM users WHERE username = ?", [
            $username
        ])->find();

        if (! $user)
            $this->bot->sendMessage("Cannot remove the user's warns with this method.");

        $this->rmWarnUser($user['user_id']);

        $this->bot->sendMessage(
            "Tch, looks like @$username got a warning erased. Someone's feeling generous... don't count on it happening again 😒.",
            reply_to_message_id: $this->bot->messageId()
        );
    }

    public function removeWarnByCallback(string|int $userId)
    {
        $this->rmWarnUser($userId);

        $this->bot->answerCallbackQuery(text: "The user’s warning got erased ✅");
        $this->bot->editMessageText("Looks like the user’s warning got erased by an admin. Lucky break... but don’t go testing fate 😒.", message_id: $this->bot->messageId());
    }

    public function warns()
    {
        $userId = $this->bot->message()->reply_to_message->from->id ?? $this->bot->userId();
        $name = $this->bot->message()->reply_to_message->from->first_name ?? $this->bot->user()->first_name;

        $userWarns = $this->userWarns($userId);

        if (count($userWarns) === 0) {
            $this->bot->sendMessage(
                "Huh, no warnings for <a href=\"tg://user?id=$userId\">$name</a>? Guess they've been behaving… for now. Don’t get cocky 😏.",
                reply_to_message_id: $this->bot->messageId(),
                parse_mode: ParseMode::HTML
            );

            return;
        }

        $text = "The user <a href=\"tg://user?id=$userId\">$name</a> has " . count($userWarns) . "/{$this->getWarnLimit()} warnings. They better watch themselves, or they'll be out before they know it! 🗡️\n";
        foreach ($userWarns as $warn) {
            $warn['reason'] = ! empty($warn['reason']) ? $warn['reason'] : '<i>Tch, no reason given</i>';
            $text .= "\n- {$warn['reason']}";
        }

        $this->bot->sendMessage(
            $text,
            reply_to_message_id: $this->bot->messageId(),
            parse_mode: ParseMode::HTML
        );
    }

    public function resetWarnByReply()
    {
        $repliedMessageUserId = $this->bot->message()->reply_to_message->from->id ?? null;

        if (! $repliedMessageUserId) {
            $this->bot->sendMessage(
                "User not found 🤨. Who are you looking for, a ghost?",
                reply_to_message_id: $this->bot->messageId()
            );
            return;
        }

        $this->rmWarnUser($repliedMessageUserId, true);

        $name = $this->bot->message()->reply_to_message->from->first_name;
        $this->bot->sendMessage(
            "Heh, all of <a href=\"tg://user?id=$repliedMessageUserId\">$name</a>'s warnings are gone. Someone's feeling merciful... but don’t get too comfortable 🙄.",
            parse_mode: ParseMode::HTML,
            reply_to_message_id: $this->bot->messageId()
        );
    }

    public function resetWarnByUsername(string $username)
    {
        $db = new Database();

        $user = $db->query("SELECT user_id FROM users WHERE username = ?", [
            $username
        ])->find();

        if (! $user)
            $this->bot->sendMessage("Cannot remove the user's warns with this method.");

        $this->rmWarnUser($user['user_id'], true);

        $this->bot->sendMessage(
            "Heh, all of @$username warnings are gone. Someone's feeling merciful... but don’t get too comfortable 🙄.",
            reply_to_message_id: $this->bot->messageId()
        );
    }

    public function setWarnLimit(string|int $warnLimit)
    {
        $db = new Database();

        $db->query("UPDATE settings SET `value` = ?, updated_at = CURRENT_TIMESTAMP() WHERE `key` = 'warnlimit'", [
            $warnLimit
        ]);

        $this->bot->sendMessage(
            "The warn limit’s been bumped to $warnLimit. Looks like someone’s making room for more trouble 🙄.",
            reply_to_message_id: $this->bot->messageId()
        );
    }

    private function warnUser(string|int $userId, string $reason)
    {
        $db = new Database();

        $db->query("INSERT INTO warnings (user_id, reason) VALUES (?, ?)", [
            $userId,
            $reason
        ]);
    }

    private function rmWarnUser(string|int $userId, bool $removeAllWarns = false)
    {
        $db = new Database();

        if (! $removeAllWarns) {
            $db->query("DELETE FROM warnings WHERE user_id = ? ORDER BY id DESC LIMIT 1", [
                $userId
            ]);
        } else {
            $db->query("DELETE FROM warnings WHERE user_id = ?", [
                $userId
            ]);
        }
    }

    private function userWarns(int|string $userId)
    {
        $db = new Database();

        return $db->query("SELECT * FROM warnings WHERE user_id = ?", [
            $userId
        ])->findAll();
    }

    private function getWarnLimit()
    {
        $db = new Database();

        return $db->query("SELECT * FROM `settings` WHERE `key` = 'warnlimit'")->find()['value'];
    }

    private function resetWarns(string|int $userId)
    {
        $db = new Database();

        $db->query("DELETE FROM warnings WHERE user_id = ?", [
            $userId
        ]);
    }

    private function ban(string|int $userId)
    {
        $banUser = $this->bot->banChatMember($this->bot->chatId(), $userId);
        if (! $banUser) {
            $this->bot->sendMessage("There was an error.\nCheck log file.");
            return false;
        }

        $db = new Database();
        $db->query("INSERT INTO bans (user_id) VALUES (?) ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP()", [
            $userId
        ]);

        return true;
    }
}
