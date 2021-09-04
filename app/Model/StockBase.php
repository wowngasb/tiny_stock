<?php


namespace app\Model;


use app\Model;
use app\Util;
use Exception;
use Tiny\OrmQuery\Q;
use Tiny\Plugin\DbHelper;

class StockBase extends Model
{
    protected static $cache_time = 300;

    const TABLE_NAME = 'stock_base';    // 股票行情信息

    const PRIMARY_KEY = 'id';   //  BIGINT 主键 格式为 股票code 加 上日期

    const FILLABLE_FIELDS = [
        'id',    //  BIGINT 主键 格式为 股票code 加 上日期
        'lday',    //  INT  日期 整型

        'pchg',  //   FLOAT  涨跌幅度 百分比
        'yclose',  //   INT  前收盘价
        'open',    //   INT  开盘价
        'high',    //   INT  最高
        'low',     //   INT  最低
        'close',   //   INT  收盘价
        'vol',     //   INT  成交量
        'unused',  //   INT  unused
        'amount',  //   FLOAT  成交金额

        'stock_code',    //  VARCHAR(16)  股票代码

        'minline_data',    //  TEXT  每分钟数据 base64 gzcompress 压缩
        'has_minline',    //  TINYINT  每分钟数据 是否存在
    ];

    const HIDDEN_FIELDS = [
    ];

    const SORTABLE_FIELDS = [
        'id',    //  BIGINT 主键 格式为 股票code 加 上日期
        'lday',    //  INT  日期 整型

        'pchg',  //   FLOAT  涨跌幅度 百分比
        'yclose',  //   INT  前收盘价
        'open',    //   INT  开盘价
        'high',    //   INT  最高
        'low',     //   INT  最低
        'close',   //   INT  收盘价
        'vol',     //   INT  成交量
        'unused',  //   INT  unused
        'amount',  //   FLOAT  成交金额

        'stock_code',    //  VARCHAR(16)  股票代码

        'updated_at',    //  TIMESTAMP  记录更新时间
    ];

    const ALL_FIELDS = [
        'id',    //  BIGINT 主键 格式为 股票code 加 上日期
        'lday',    //  INT  日期 整型

        'pchg',  //   FLOAT  涨跌幅度 百分比
        'yclose',  //   INT  前收盘价
        'open',    //   INT  开盘价
        'high',    //   INT  最高
        'low',     //   INT  最低
        'close',   //   INT  收盘价
        'vol',     //   INT  成交量
        'unused',  //   INT  unused
        'amount',  //   FLOAT  成交金额

        'stock_code',    //  VARCHAR(16)  股票代码

        'minline_data',    //  TEXT  每分钟数据 base64 gzcompress 压缩
        'has_minline',    //  TINYINT  每分钟数据 是否存在

        'updated_at',    //  TIMESTAMP  记录更新时间
    ];

    ####################################
    ############# 改写代码 ##############
    ####################################

    protected $primaryKey = self::PRIMARY_KEY;
    protected $table = self::TABLE_NAME;
    protected $fillable = self::FILLABLE_FIELDS;
    protected $hidden = self::HIDDEN_FIELDS;
    protected $sortable = self::SORTABLE_FIELDS;
    protected $allfields = self::ALL_FIELDS;

    public function __sleep()
    {
        return ['original'];
    }

    public function __wakeup()
    {
        $this->attributes = $this->original;

        $this->primaryKey = self::PRIMARY_KEY;
        $this->table = self::TABLE_NAME;
        $this->fillable = self::FILLABLE_FIELDS;
        $this->hidden = self::HIDDEN_FIELDS;
        $this->sortable = self::SORTABLE_FIELDS;
        $this->allfields = self::ALL_FIELDS;

        parent::__wakeup();
    }

    public static function newItem(array $data, $log_op = true)
    {
        if (!isset($data['minline_data'])) {
            $data['minline_data'] = '';
        }

        return parent::newItem($data, $log_op);
    }

    public static function getIdMapGt($minline_start)
    {
        // update `stock_base` set has_minline = 1 WHERE `has_minline` = 0 and minline_data <> '' and  `lday` >= 20210531
        // update `stock_base` set has_minline = 0 WHERE `has_minline` = 1

        /*
        SELECT
            stock_code, GROUP_CONCAT(id)
        FROM
            `stock_base`
        WHERE
            `lday` >= 20210531
        AND `has_minline` = 0
        AND `minline_data` = "" GROUP BY stock_code;
         * */
        $last = self::tableBuilderEx([
            'lday' => Q::where($minline_start, '>='),
            'has_minline' => 0,
            'minline_data' => '',
        ], ['stock_code', self::raw('GROUP_CONCAT(id) as ids')], [], ['stock_code'])->get();
        $ret = [];
        foreach ($last as $item) {
            $item = Util::try2array($item);
            $stock_code = $item['stock_code'];
            $ret[$stock_code] = !empty($item['ids']) ? array_map(function ($i) {
                return intval($i);
            }, explode(',', $item['ids'])) : [];
        }
        return $ret;
    }

    public static function getLdayMapGt($mini_m)
    {
        /*
        SELECT
            GROUP_CONCAT(lday)
        FROM
            stock_base where lday >= 20210900
        GROUP BY
            stock_code;
         * */
        $last = self::tableBuilderEx([
            'lday' => Q::where($mini_m * 100, '>='),
        ], ['stock_code', self::raw('GROUP_CONCAT(lday) as ldays')], [], ['stock_code'])->get();
        $ret = [];
        foreach ($last as $item) {
            $item = Util::try2array($item);
            $stock_code = $item['stock_code'];
            $ret[$stock_code] = !empty($item['ldays']) ? array_map(function ($i) {
                return intval($i);
            }, explode(',', $item['ldays'])) : [];
        }
        return $ret;
    }

    public static function updateBatch($multipleData = [])
    {
        if (empty($multipleData)) {
            return false;
        }
        $tableName = self::TABLE_NAME; // 表名
        $firstRow = current($multipleData);
        if (empty($firstRow['id'])) {
            return false;
        }

        unset($firstRow['id']);
        $updateColumn = array_keys($firstRow);
        $referenceColumn = 'id';

        $sets = [];
        $bindings = [];
        foreach ($updateColumn as $uColumn) {
            $setSql = "`" . $uColumn . "` = CASE ";
            foreach ($multipleData as $data) {
                $setSql .= "WHEN `" . $referenceColumn . "` = ? THEN ? ";
                $bindings[] = $data[$referenceColumn];
                $bindings[] = $data[$uColumn];
            }
            $setSql .= "ELSE `" . $uColumn . "` END ";
            $sets[] = $setSql;
        }

        $updateSql = "UPDATE " . $tableName . " SET ";
        $updateSql .= implode(', ', $sets);
        $whereIn = array_map(function ($i) use ($referenceColumn) {
            return $i[$referenceColumn];
        }, $multipleData);
        $bindings = array_merge($bindings, $whereIn);
        $whereIn = rtrim(str_repeat('?,', count($whereIn)), ',');
        $updateSql = rtrim($updateSql, ", ") . " WHERE `" . $referenceColumn . "` IN (" . $whereIn . ")";

        try {
            return DbHelper::initDb()->getConnection()->update($updateSql, $bindings);
        } catch (Exception $e) {
            return false;
        }
    }

}