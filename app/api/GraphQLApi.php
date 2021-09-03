<?php
/**
 * Created by PhpStorm.
 * User: kl
 * Date: 2017/3/12 0012
 * Time: 17:08
 */

namespace app\api;

use app\api\Abstracts\AbstractApi;
use app\api\GraphQL\Types;
use app\App;
use app\Request;
use app\Response;
use app\Util;
use ErrorException;
use Exception;
use GraphQL\Error\Debug;
use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use Throwable;
use Tiny\Dispatch\ApiDispatch;

/**
 * GraphQL入口
 * @package app\api
 */
class GraphQLApi extends AbstractApi
{

    public static function _createGraphQLApi()
    {
        $api = new GraphQLApi(new Request(), new Response());
        $api->auth()->onceUsingId(self::_tryFindFirstSuperId());
        return $api;
    }

    const FRAGMENTS_MAP = [
        'pageInfoPage' => <<<EOT
PageInfo {
  total
  hasNextPage
  hasPreviousPage
  page
  num
}
EOT
        ,
        'pageInfoAll' => <<<EOT
PageInfo {
  ...pageInfoPage
  sortOption {
    field
    direction
  }
  allowSortField
}
EOT
        ,

    ];

    public static function _getFragmentsMap()
    {
        $map = self::FRAGMENTS_MAP;
        $ret = [];
        foreach ($map as $name => $str) {
            $str = "fragment {$name} on " . trim($str);
            preg_match_all('(\.\.\.\w+)', $str, $_deps);
            $deps = [];
            if (!empty($_deps[0])) {
                foreach ($_deps[0] as $item) {
                    if (Util::str_startwith($item, '...')) {
                        $dep = substr($item, 3);
                        $deps[$dep] = 1;
                    }
                }
            }
            $ret[$name] = [
                $str, array_keys($deps)
            ];
        }
        return $ret;
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function test()
    {
        $this->auth()->onceUsingId(100);

        $query = <<< 'EOT'
{
  hello
}
EOT;
        $variables = <<<EOT
{}
EOT;

        return $this->exec($query, json_decode($variables, true));
    }

    /**
     * 执行 GraphQL 查询
     * @param string $query
     * @param array|null $variables
     * @return array
     * @throws Throwable
     */
    public function exec($query = '{hello}', array $variables = null)
    {
        // GraphQL schema to be passed to query executor:
        $schema = new Schema([
            'query' => Types::Query([], Types::class)
        ]);

        $debug = false;
        $phpErrors = [];  // Catch custom errors (to report them in query results if debugging is enabled)
        if (App::dev()) {
            $schema->assertValid(); // Enable additional validation of type configs (disabled by default because it is costly)
            set_error_handler(function ($severity, $message, $file, $line) use (&$phpErrors) {
                // Determine if this error is one of the enabled ones in php config (php.ini, .htaccess, etc)
                $error_is_enabled = (bool)($severity & ini_get('error_reporting'));

                // -- FATAL ERROR
                // throw an Error Exception, to be handled by whatever Exception handling logic is available in this context
                if (in_array($severity, array(E_USER_ERROR, E_RECOVERABLE_ERROR)) && $error_is_enabled) {
                    $phpErrors[] = new ErrorException($message, 0, $severity, $file, $line);
                } elseif ($error_is_enabled) {
                    // -- NON-FATAL ERROR/WARNING/NOTICE
                    // Log the error if it's enabled, otherwise just ignore it
                    error_log($message, 0);
                }
            });
            $debug = Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE;
        }

        $myErrorHandler = function ($exs) {
            /** @var Exception $ex */
            $ex = $exs[0];
            $log_msg = "exec error:" . $ex->getMessage();
            self::error($log_msg, __METHOD__, __CLASS__, __LINE__);

            if ($ex instanceof Error) {
                $pre_ex = $ex->getPrevious();
                ApiDispatch::traceException($this->getRequest(), $this->getResponse(), $pre_ex instanceof Exception ? $pre_ex : $ex, true);
            } else {
                $ex instanceof Exception && ApiDispatch::traceException($this->getRequest(), $this->getResponse(), $ex, true);
            }
            $this->getResponse()->end();
        };

        $result = [];

        try {
            $result = GraphQL::executeQuery(
                $schema,
                $query,
                null,
                $this,
                $variables
            )->setErrorsHandler($myErrorHandler)->toArray($debug);

            // Add reported PHP errors to result (if any)
            if (App::dev() && !empty($phpErrors)) {
                $result['extensions']['phpErrors'] = array_map(['GraphQL\Error\FormattedError', 'createFromPHPError'], $phpErrors);
            }
        } catch (Exception $error) {
            $result['extensions']['exception'] = FormattedError::createFromException($error);
        }
        return $result;
    }

}