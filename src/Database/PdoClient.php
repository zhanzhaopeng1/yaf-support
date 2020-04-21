<?php

namespace Yaf\Support\Database;

use PDO;
use Exception;

/**
 * Class PdoClients
 */
class PdoClient
{
    const PDO_TIME_OUT = 10; //the timeout value in seconds for communications with the database
    /**
     * @var PDO
     */
    public $dbh;
    public $error;
    public $dbname;
    public $last_query;
    protected $transLevel = 0;

    /**
     * PdoClient constructor.
     * @param $config
     * @throws Exception
     */
    public function __construct($config)
    {
        $port = isset($config['port']) ? $config['port'] : 3306;
        if (isset($config['name'])) {
            $dsn          = sprintf('mysql:host=%s;dbname=%s;port=%d', $config['host'], $config['name'], $port);
            $this->dbname = $config['name'];
        } else {
            $dsn = sprintf('mysql:host=%s;port=%d', $config['host'], $port);
        }
        try {
            $this->dbh = new PDO($dsn, $config['user'], $config['pass'],
                array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\'',
                    PDO::ATTR_PERSISTENT         => true,
                    PDO::ATTR_EMULATE_PREPARES   => true,
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_WARNING,
                    PDO::ATTR_TIMEOUT            => self::PDO_TIME_OUT,
                ));
        } catch (Exception $e) {
            if ($this) $this->error = $e->getMessage();
            throw $e;
        }
    }

    /**
     * @param      $sql
     * @param null $_                           mixed ... or  array eg: PdoClient::condition('?, ?, ?', $a, $b, $c);
     *                                          or eg: PdoClient::condition('?, ?, ?', array($a, $b, $c);
     * @return array
     */
    public static function condition($sql, $_ = null)
    {
        $args = func_get_args();

        if (count($args) > 1) {
            array_shift($args);
            if (isset($args[0]) && is_array($args[0])) {
                $args = $args[0];
            }
        } else {
            $args = null;
        }

        return array($sql, $args);
    }

    function transaction($call)
    {
        $this->beginTransaction();
        $ret = false;
        try {
            $ret = call_user_func($call);
        } catch (Exception $e) {
            $this->rollBack();
        }

        if ($ret) {
            $this->commit();
        } else {
            $this->rollBack();
        }

        return $ret;
    }

    public function beginTransaction()
    {
        if (!$this->transactionNestable() || $this->transLevel == 0) {
            $this->dbh->beginTransaction();
        } else {
            $this->dbh->exec(sprintf('SAVEPOINT LEVEL%d', $this->transLevel));
        }

        $this->transLevel++;
    }

    protected function transactionNestable()
    {
        return true;
    }

    public function rollBack()
    {
        $this->transLevel--;

        if (!$this->transactionNestable() || $this->transLevel == 0) {
            $this->dbh->rollBack();
        } else {
            $this->dbh->exec(sprintf("ROLLBACK TO SAVEPOINT LEVEL%d", $this->transLevel));
        }
    }

    public function commit()
    {
        $this->transLevel--;
        if (!$this->transactionNestable() || $this->transLevel == 0) {
            $this->dbh->commit();
        } else {
            $this->dbh->exec(sprintf("RELEASE SAVEPOINT LEVEL%d", $this->transLevel));
        }
    }

    public function getVarByCondition($table, $condition, $varName)
    {
        list($condition, $values) = $this->getConditionPair($condition);
        $sql = sprintf('SELECT %s FROM %s', $varName, $table);
        if (!empty($condition))
            $sql .= ' WHERE ' . $condition;

        $res = $this->get_var($sql, $values);

        return $res === false ? null : $res;
    }

    public function getConditionPair($condition)
    {
        if (is_array($condition)) {
            return $condition;
        }

        if (empty($condition) || is_string($condition)) {
            return array($condition, null);
        }

        return '';
    }

    public function get_var($sql, $values = null)
    {
        return $this->query($sql, $values)->fetchColumn(0);
    }

    /**
     * @param      $sql
     * @param null $values
     * @return \PDOStatement
     * @throws Exception
     */
    public function query($sql, $values = null)
    {
        if (is_array($values)) {
            $values = array_values($values);
        }
        $stmt             = $this->dbh->prepare($sql);
        $this->last_query = $sql;

        $count = count($values);
        for ($i = 0; $i < $count; ++$i) {
            $data_type = PDO::PARAM_STR;
            switch (gettype($values[$i])) {
                case 'boolean':
                    {
                        $data_type = PDO::PARAM_BOOL;
                        break;
                    }
                case 'integer':
                    {
                        $data_type = PDO::PARAM_INT;
                        break;
                    }
                case 'double':
                    {
                        break;
                    }
                case 'float':
                    {
                        break;
                    }
                case 'string':
                    {
                        $data_type = PDO::PARAM_STR;
                        break;
                    }
                case 'array':
                    {
                        break;
                    }
                case 'object':
                    {
                        break;
                    }
                case 'resource':
                    {
                        break;
                    }
                case 'NULL':
                    {
                        $data_type = PDO::PARAM_NULL;
                        break;
                    }
                case 'unknown type':
                    {
                        break;
                    }
            }
            $stmt->bindParam($i + 1, $values[$i], $data_type);
        }

        $stmt->execute();

        if ($stmt->errorCode() != PDO::ERR_NONE) {
            if (!empty($values))
                $msg = sprintf('%s | (%s)', $sql, implode(',', $values));
            else {
                $msg = $sql;
            }
            throw new Exception(sprintf('500 | database exception, err_msg: %s,  sql:%s', json_encode($stmt->errorInfo(), JSON_UNESCAPED_UNICODE), $msg));
        }

        //print_r($stmt);echo "\n\r";
        return $stmt;
    }

    public function getDbTime()
    {
        return $this->get_var('SELECT unix_timestamp()');
    }

    /**
     * @param        $table
     * @param string $condition
     * @param string $countPara
     * @return int
     */
    public function getDistinctCountByCondition($table, $condition = '', $countPara = '')
    {
        list($condition, $values) = $this->getConditionPair($condition);
        if (empty($countPara))
            return $this->getCountByCondition($table, $condition);
        else {
            if (empty($condition))
                $sql = sprintf('SELECT COUNT(DISTINCT %s) FROM %s', $countPara, $table);
            else
                $sql = sprintf('SELECT COUNT(DISTINCT %s) FROM %s WHERE %s', $countPara, $table, $condition);
        }

        return intval($this->get_var($sql, $values));
    }

    /**
     * @param        $table
     * @param string $condition
     * @return int
     */
    public function getCountByCondition($table, $condition = '')
    {
        list($condition, $values) = $this->getConditionPair($condition);
        if (empty($condition))
            $sql = sprintf('SELECT COUNT(*) FROM %s', $table);
        else
            $sql = sprintf('SELECT COUNT(*) FROM %s WHERE %s', $table, $condition);

        return intval($this->get_var($sql, $values));
    }

    public function getExplainCountByCondition($table, $condition)
    {
        if (empty($condition)) {
            return 0;
        }
        list($condition, $values) = $this->getConditionPair($condition);
        $sql     = sprintf('EXPLAIN SELECT COUNT(*) FROM %s WHERE %s', $table, $condition);
        $explain = $this->get_row($sql, $values);

        return $explain['rows'];
    }

    /**
     * @param      $sql
     * @param null $values
     * @return mixed
     * @throws Exception
     */
    public function get_row($sql, $values = null)
    {
        return $this->query($sql, $values)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $table
     * @param $condition
     * @param $distinct
     * @return array|null
     * @throws Exception
     */
    public function getDistinctByCondition($table, $condition, $distinct)
    {
        list($condition, $values) = $this->getConditionPair($condition);
        $sql = sprintf('SELECT DISTINCT %s FROM %s', $distinct, $table);
        if (!empty($condition))
            $sql .= ' WHERE ' . $condition;

        return $this->get_col($sql, $values);
    }

    /**
     * @param      $sql
     * @param null $values
     * @param int  $offset
     * @return array|null
     * @throws Exception
     */
    public function get_col($sql, $values = null, $offset = 0)
    {
        $res = $this->query($sql, $values)->fetchAll(PDO::FETCH_COLUMN, $offset);

        return $res === false ? null : $res;
    }

    /**
     * @param $table
     * @param $condition
     * @param $colName
     * @return array|null
     * @throws Exception
     */
    public function getColByCondition($table, $condition, $colName)
    {
        list($condition, $values) = $this->getConditionPair($condition);
        if (empty($condition))
            $sql = sprintf('SELECT %s FROM %s', $colName, $table);
        else
            $sql = sprintf('SELECT %s FROM %s WHERE %s', $colName, $table, $condition);

        return $this->get_col($sql, $values);
    }

    /**
     * @param        $table
     * @param string $condition
     * @param string $fields
     * @return array|null
     * @throws Exception
     */
    public function getResultsByCondition($table, $condition = '', $fields = '')
    {
        list($condition, $values) = $this->getConditionPair($condition);
        if (empty($fields)) {
            if (empty($condition))
                $sql = sprintf('SELECT * FROM %s', $table);
            else
                $sql = sprintf('SELECT * FROM %s WHERE %s', $table, $condition);
        } else {
            if (empty($condition))
                $sql = sprintf('SELECT %s FROM %s', $fields, $table);
            else
                $sql = sprintf('SELECT %s FROM %s WHERE %s', $fields, $table, $condition);
        }

        return $this->get_results($sql, $values);
    }

    /**
     * @param      $sql
     * @param null $values
     * @return array|null
     * @throws Exception
     */
    public function get_results($sql, $values = null)
    {
        $res = $this->query($sql, $values)->fetchAll(PDO::FETCH_ASSOC);

        return $res === false ? null : $res;
    }

    /**
     * @param     $table
     * @param     $field
     * @param     $condition
     * @param int $diff
     * @return int
     * @throws Exception
     */
    public function updateFieldByIncrease($table, $field, $condition, $diff = 1)
    {
        list($where, $values) = $this->getConditionPair($condition);

        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sql = sprintf('UPDATE %s SET %s=%s+%d %s', $table, $field, $field, $diff, $where);


        return $this->query($sql, $values)->rowCount();
    }

    /**
     * @param $table
     * @param $field
     * @param $data
     * @param $condition
     * @return bool|\PDOStatement
     * @throws Exception
     */
    public function updateField($table, $field, $data, $condition)
    {
        list($condition, $conditionValues) = $this->getConditionPair($condition);
        if ($condition) {
            $condition = 'WHERE ' . $condition;
        }
        if (is_array($data) && is_array($field)) {
            list ($fields, $values) = $this->getConditionArray($data);
            list ($fields2, $values2) = $this->getConditionArray2($field);
            $fields .= ',' . $fields2;
            $values = array_merge($values, $values2);
            if (count($values) > 0) {
                $sql = sprintf('UPDATE %s SET %s  %s', $table, $fields, $condition);
                if (count($conditionValues))
                    $values = array_merge($values, $conditionValues);

                return $this->query($sql, $values);
            }
        }

        return false;

    }

    public function getConditionArray($data)
    {
        if (count($data) == 0)
            return array(null, null);

        $fields = array();
        $values = array();
        foreach ($data as $k => $v) {
            $fields[] = sprintf('%s=?', $k);
            $values[] = $v;
        }

        return array(implode(',', $fields), $values);
    }

    public function getConditionArray2($data)
    {
        if (count($data) == 0)
            return array(null, null);

        $fields = array();
        $values = array();
        foreach ($data as $k => $v) {
            $fields[] = sprintf('%s=%s+?', $k, $k);
            $values[] = $v;
        }

        return array(implode(',', $fields), $values);
    }

    /**
     * @param $table
     * @param $fields
     * @param $valueData
     * @return int|null
     * @throws Exception
     */
    public function batchInsertData($table, $fields, $valueData)
    {
        if (empty($fields) || empty($valueData)) {
            return null;
        }
        $rows   = array();
        $values = array();
        $count  = count($valueData);
        for ($index = 0; $index < $count; $index++) {
            $padArray = array_pad(array(), count($valueData[$index]), '?');
            $rows[]   = '(' . implode(',', $padArray) . ')';
            foreach ($valueData[$index] as $v)
                $values[] = $v;
        }

        $sql   = "INSERT IGNORE INTO %s (%s) VALUES %s";
        $query = sprintf($sql, $table, implode(',', $fields), implode(',', $rows));

        return $this->query($query, $values)->rowCount();
    }

    /**
     * @param $table
     * @param $data
     * @param $condition
     * @return bool|int
     * @throws Exception
     */
    public function updateFieldsByIncrease($table, $data, $condition)
    {
        list($condition, $conditionValues) = $this->getConditionPair($condition);
        if (is_array($data)) {
            list ($fields, $values) = $this->getConditionArray2($data);
            if (!empty($values)) {
                $sql = sprintf('UPDATE %s SET %s WHERE %s', $table, $fields, $condition);
                if (count($conditionValues))
                    $values = array_merge($values, $conditionValues);

                return $this->query($sql, $values)->rowCount();
            }
        }

        return false;
    }

    /**
     * @param        $table
     * @param        $data
     * @param        $condition
     * @param string $idField
     * @return bool|int
     * @throws Exception
     */
    public function insertOrUpdateTable($table, $data, $condition, $idField = 'id')
    {
        $row = $this->getRowByCondition($table, $condition, $idField);
        if ($row) {
            $this->updateTable($table, $data, $condition);

            return $row[$idField];
        } else {
            return $this->insertTable($table, $data);
        }
    }

    /**
     * @param        $table
     * @param        $condition
     * @param string $fields
     * @return mixed|null
     * @throws Exception
     */
    public function getRowByCondition($table, $condition, $fields = '')
    {
//        $startTime = microtime(true);
        list($condition, $values) = $this->getConditionPair($condition);
        if (empty($fields)) {
            $sql = sprintf('SELECT * FROM %s WHERE %s LIMIT 1', $table, $condition);
        } else {
            $sql = sprintf('SELECT %s FROM %s WHERE %s LIMIT 1', $fields, $table, $condition);
        }
        $res = $this->get_row($sql, $values);

//        Logger::logMemcachedTime('db-row', $table, $startTime, microtime(true));
        return $res === false ? null : $res;
    }

    /**
     * @param $table
     * @param $data
     * @param $condition
     * @return bool|int
     * @throws Exception
     */
    public function updateTable($table, $data, $condition)
    {
        $res = false;
        list($condition, $conditionValues) = $this->getConditionPair($condition);
        if (is_array($data)) {
            list ($fields, $values) = $this->getConditionArray($data);
            if (!empty($values)) {
                $sql = sprintf('UPDATE %s SET %s WHERE %s', $table, $fields, $condition);
                if (count($conditionValues)) {
                    $values = array_merge($values, $conditionValues);
                }
                $res = $this->query($sql, $values)->rowCount();
            }
        }

        return $res;
    }

    /**
     * @param $table
     * @param $data
     * @return bool|int
     * @throws Exception
     */
    public function insertTable($table, $data)
    {
        if (!is_array($data))
            return false;

        list($fields, $values) = $this->getConditionArray($data);

        if (!count($values))
            return false;

        $sql = sprintf('INSERT INTO %s SET %s', $table, $fields);
        $this->query($sql, $values);

        $insertId = $this->dbh->lastInsertId();
        if ($insertId === '0') {
            $desc     = $this->get_results(sprintf('DESC %s', $table));
            $priField = '';
            foreach ($desc as $val) {
                if ($val['Key'] == 'PRI') {
                    $priField = $val['Field'];
                    break;
                }
            }
            if (!empty($priField)) {
                $insertId = isset($data[$priField]) ? $data[$priField] : false;
            }
        }

        return ($insertId === false) ? false : intval($insertId);
    }

    /**
     * @param        $table
     * @param        $data
     * @param        $condition
     * @param string $keyField
     * @return bool|int
     * @throws Exception
     */
    public function insertIfNotExist($table, $data, $condition, $keyField = 'id')
    {
        $rowId = 0;
        $row   = $this->getRowByCondition($table, $condition, $keyField);
        if (!$row) {
            $rowId = $this->insertTable($table, $data);
        } else if (!empty($keyField)) {
            $rowId = $row[$keyField];
        }

        return $rowId;
    }

    /**
     * @param $table
     * @param $data
     * @return bool|string
     * @throws Exception
     */
    public function replaceTable($table, $data)
    {
        if (is_array($data)) {
            list($fields, $values) = $this->getConditionArray($data);
            if (count($values) > 0) {
                $sql = sprintf('REPLACE INTO %s SET %s', $table, $fields);
                $this->query($sql, $values);

                return $this->dbh->lastInsertId();
            }
        }

        return false;
    }

    protected function getConditionPairFromMap($map)
    {
        $placeHolders = array();
        $values       = array();
        foreach ($map as $k => $v) {
            array_push($placeHolders, sprintf('%s=?', $k));
            array_push($values, $v);
        }

        $sql = implode(' AND ', $placeHolders);

        return array($sql, $values);
    }

    /**
     * @param $table
     * @param $condition
     * @return int
     * @throws Exception
     */
    public function delRowByCondition2($table, $condition)
    {
        list($condition, $values) = $this->getConditionPair($condition);
        $sql = sprintf('DELETE FROM %s WHERE %s', $table, $condition);

        return $this->query($sql, $values)->rowCount();
    }

    public function getPlaceHolders($cnt)
    {
        return implode(',', array_pad(array(), $cnt, '?'));
    }

    /**
     * @param $table
     * @throws Exception
     */
    public function truncateTable($table)
    {
        $sql = sprintf('TRUNCATE TABLE %s', $table);
        $this->query($sql);
    }

    function escape($str)
    {
        return $this->dbh->quote($str);
    }

    /**
     * 获取 prepare statement 格式的连续参数占位, 例如传入 count = 3, 则返回 "?,?,?"
     * @param        $count
     * @param string $placeholder 占位符
     * @return string
     */
    public static function placeholders($count, $placeholder = '?')
    {
        if ($count <= 0) {
            return '';
        }

        return implode(',', array_fill(0, $count, $placeholder));
    }
}
