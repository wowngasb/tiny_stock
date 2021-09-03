<?php

namespace app\Console\Commands;

use app\Console\SiteCommand;

class Inspire extends SiteCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Inspire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';


    /**
     * @param int $timestamp
     * @param mixed $siteConfig
     */
    public function handleSite($timestamp, $siteConfig)
    {
        $_line = "Inspire timestamp:{$timestamp}, siteConfig:" . json_encode($siteConfig);
        $this->handelLog($timestamp, $siteConfig, $_line, __FILE__, __LINE__);
    }
}
