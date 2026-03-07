<?php

namespace Toolborg\ChatField\Commands;

use Illuminate\Console\Command;

class ChatFieldCommand extends Command
{
    public $signature = 'chat-field';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
