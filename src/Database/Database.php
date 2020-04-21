<?php

namespace Yaf\Support\Database;

use Exception;

/**
 * Class Database
 * @package Yaf\Support\Database
 */
class Database
{
    private static $_instance = [];

    /**
     * @param string $name
     * @return PdoClient
     * @throws Exception
     */
    public function connect($name = 'mysql')
    {
        $pdoConfig = $this->getPdoConfig($name);

        $className = get_called_class();
        if (!isset(self::$_instance[$className])) {
            self::$_instance[$className] = new PdoClient($pdoConfig);
        }

        return self::$_instance[$className];
    }

    /**
     * @param string $name
     * @return array
     */
    private function getPdoConfig($name = 'mysql')
    {
        $dbConfig = $this->getConfigConnect();

        return [
            'host' => empty($dbConfig->db->$name->host) ? '127.0.0.1' : $dbConfig->db->$name->host,
            'port' => empty($dbConfig->db->$name->port) ? 3306 : $dbConfig->db->$name->port,
            'user' => $dbConfig->db->$name->user,
            'name' => $dbConfig->db->$name->name,
            'pass' => $dbConfig->db->$name->passwd
        ];
    }

    private function getConfigConnect()
    {
        return config('database');
    }

    /**
     * @param $object
     * @return array
     * @throws Exception
     */
    public static function toDbArray($object)
    {
        if (!is_object($object)) {
            return array();
        }
        $arr    = array();
        $params = get_object_vars($object);
        foreach ($params as $key => $param) {
            if (is_array($param) || is_object($param)) {
                throw new Exception('object to array error', -1);
            };
            if ($param !== null) {
                $arr[$key] = $param;
            }
        }

        return $arr;
    }

    /**
     * @param $row
     * @param $object
     * @return mixed
     */
    protected static function toObject($row, &$object)
    {
        if (is_array($row)) {
            foreach ($row as $key => $val) {
                if (property_exists($object, $key)) {
                    $object->$key = $val;
                }
            }
        }

        return $object;
    }

    /**
     * @param $data
     * @return array
     */
    public function toShowArray($data)
    {
        $res = array();
        if (count($data)) {
            foreach ($data as $key => &$val) {
                if (is_array($val)) {
                    $val = $this->toShowArray($val);
                }
                $res[$key] = $val;
            }
        }

        return $res;
    }

    public function getMillisecond()
    {
        list($usec, $sec) = explode(' ', microtime());

        return (intval($usec * 1000) + intval($sec * 1000));
    }

    /**
     * * 将查询的结果数组转化成响应Object类的对象
     *
     * @param string $class     需要转换的Object类名
     * @param array  $condition 查询条件
     * @param string $table     查询表名
     * @param string $field     查询的字段
     * @return mixed|null
     * @throws Exception
     */
    public function formatObject($class = null, $condition, $table = null, $field = '*')
    {
        $row = $this->connect()->getRowByCondition($table, $condition, $field);
        if ($row) {
            $object = new $class();
            self::toObject($row, $object);
            $row = $object;
        }

        return $row;
    }

    /**
     * 将查询的结果数组转化成响应Object类的对象数组
     *
     * @param string $class     需要转换的Object类名
     * @param array  $condition 查询条件
     * @param string $table     查询表名
     * @param string $field     查询的字段
     * @return mixed|null
     * @throws Exception
     */
    public function formatObjects($class = null, $condition, $table = null, $field = '*')
    {
        $results = $this->connect()->getResultsByCondition($table, $condition, $field);
        if ($results) {
            foreach ($results as $key => $val) {
                $object = new $class();
                self::toObject($val, $object);
                $results[$key] = $object;
            }
        }

        return $results;
    }

    // 新增操作的相关时间
    protected function addTime(&$data)
    {
        $data['created_at'] = $data['updated_at'] = time();
    }

    // 编辑操作的相关时间
    protected function modifyTime(&$data)
    {
        $data['updated_at'] = time();
    }

    // 删除操作的相关时间
    protected function deleteTime(&$data)
    {
        $data['deleted_at'] = time();
    }

}