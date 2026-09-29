<?php

namespace App\Console\Commands;

use App\Services\PostManager;
use Illuminate\Console\Command;

class PublishDuePosts extends Command
{
    protected $signature = 'posts:publish-due';

    protected $description = 'Publica las noticias cuya fecha programada ya llegó.';

    public function handle(PostManager $posts): int
    {
        $count = $posts->publishDueScheduled();
        $this->info("Publicaciones activadas: {$count}.");

        return self::SUCCESS;
    }
}
