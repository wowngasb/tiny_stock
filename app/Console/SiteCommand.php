<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/1/12 0012
 * Time: 10:31
 */

namespace app\Console;

use app\App;
use app\Util;
use Exception;
use Tiny\Abstracts\AbstractClass;

abstract class SiteCommand extends AbstractClass
{
    protected $signature = '';
    protected $description = '';

    private $_log_handle = null;

    public static function createFromSiteCommand(SiteCommand $siteCmd)
    {
        $_siteCmd = new static();
        $logHandle = $siteCmd->getLogHandle();
        if (!empty($logHandle)) {
            $_siteCmd->setLogHandle($logHandle);
        }
        return $_siteCmd;
    }

    public function getName()
    {
        return $this->signature;
    }

    public function getDoc()
    {
        return $this->description;
    }

    public function setLogHandle(callable $log_handle)
    {
        $this->_log_handle = $log_handle;
    }

    public function getLogHandle()
    {
        return $this->_log_handle;
    }

    private function _log($line, $tag = 'INFO')
    {
        $tag = strtoupper($tag);
        if (!empty($this->_log_handle) && is_callable($this->_log_handle)) {
            call_user_func_array($this->_log_handle, [$line, $tag]);
        } else {
            echo date('Y-m-d H:i:s') . " [{$tag}] {$line}\n";
        }
    }

    /**
     * @return array
     */
    protected function listAllSite()
    {
        return [
            App::config('ENV_WEB')
        ];
    }

    /**
     * @param int $timestamp
     * @param mixed $siteConfig
     */
    abstract public function handleSite($timestamp, $siteConfig);

    /**
     * Execute the console command.
     * @param int $timestamp
     * @param string $dev_domain
     */
    public function handle($timestamp, $dev_domain = '')
    {

        $all = $this->listAllSite();
        $dev_domain = !empty($dev_domain) ? trim($dev_domain) : trim(App::config('app.dev_schedule_host'));

        $_line = "get all site: " . count($all) . ", dev_domain:{$dev_domain}";
        $this->_log($_line);

        foreach ($all as $site) {
            if (!empty($dev_domain)) {
                if ($dev_domain != $site['devsrv']) {
                    continue;
                }
            }

            $siteConfig = $this->handleSitePre($timestamp, $site);

            try {
                $this->handleSite($timestamp, $siteConfig);
            } catch (Exception $e) {
                $this->handleSiteException($timestamp, $siteConfig, $e, __FILE__, __LINE__);
            }
        }
    }

    public function handleCurrentSite($timestamp)
    {
        $siteConfig = $this->handleSitePre($timestamp);

        try {
            $this->handleSite($timestamp, $siteConfig);
        } catch (Exception $e) {
            $this->handleSiteException($timestamp, $siteConfig, $e, __FILE__, __LINE__);
        }
    }

    protected function handelLog($timestamp, $siteConfig, $msg, $file = "", $line = 0, $tag = 'info')
    {

        $file_str = !empty($file) ? Util::file_name($file) : "";
        $file_str = !empty($file_str) && !empty($line) ? "{$file_str}<{$line}>" : $file_str;

        $_line = "{$timestamp} {$file_str} name:{$siteConfig['name']}, devsrv:{$siteConfig['devsrv']} {$msg}";
        $this->_log($_line, $tag);
    }

    protected function handleSiteException($timestamp, $siteConfig, Exception $e, $file = "", $line = 0)
    {

        $file_str = !empty($file) ? Util::file_name($file) : "";
        $file_str = !empty($file_str) && !empty($line) ? "{$file_str}<{$line}>" : $file_str;

        $_line = "{$timestamp} {$file_str} name:{$siteConfig['name']}, devsrv:{$siteConfig['devsrv']}\n MSG:" . $e->getMessage() . "\n TRACE:" . $e->getTraceAsString();
        $this->_log($_line, 'error');
    }

    /**
     * 只打印日志 和配置的一些预处理
     * @param $timestamp
     * @param mixed $siteConfig
     * @return mixed|null
     */
    protected function handleSitePre($timestamp, $siteConfig = null)
    {
        $scheduleTime = date('Y-m-d H:i:s', $timestamp);
        if (empty($siteConfig)) {
            $siteConfig = App::config('ENV_WEB');
        }
        $_line = get_class($this) . " name:{$siteConfig['name']}, devsrv:{$siteConfig['devsrv']}, ts:{$scheduleTime}";
        $this->_log($_line);
        return $siteConfig;
    }

}