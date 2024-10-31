<?php

namespace App\Commands;

use App\Handlers\HelpHandle;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Nutgram;

class HelpCommand extends Command
{
    protected string $command = 'help';

    public function handle(Nutgram $bot)
    {
        $helpHandle = new HelpHandle();
        $helpHandle($bot);
    }
}
