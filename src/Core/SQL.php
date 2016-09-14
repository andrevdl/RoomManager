<?php
/**
 * Created by PhpStorm.
 * User: André
 * Date: 11-9-2016
 * Time: 14:44
 */

namespace RoomManager\Core;

use \PDO;
use \PDOException;

class SQL
{

    /**
     * @var PDO Database Handler
     */
    private $DBH;

    /**
     * @var int Count the last effected rows
     */
    private $rowCount;

    /**
     * @var string Table prefix
     */
    public $prefix = "";

    private static $errorCallback = null;

    /**
     * sql constructor.
     * @param string $host Database host (most of the time "localhost")
     * @param string $dbname Database name
     * @param string $user Database user
     * @param string $pass Database password
     */
    public function __construct($host, $dbname, $user, $pass) {
        try {
            $this->DBH = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
            $this->DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        } catch (PDOException $ex) {
            $this->error($ex->getMessage(), false, true);
            $this->error('Database connection problems!', true);
        }
    }

    /**
     * @param $name string Get table name with the prefix
     */
    public function __get($name) {
        $this->prefix . $name;
    }

    public static function setErrorCallback(callable $func) {
        self::$errorCallback = $func;
    }
    
    private function error($message, $critical = false, $hide = false) {
        if (!is_null(self::$errorCallback)) {
            call_user_func(self::$errorCallback, $message, $critical, $hide);
        }
    }

    /**
     * Disconnect the database connection
     */
    public function disconnect(){
        $this->DBH = NULL;
    }

    /**
     * @param string $format Format type.
     * @return int Returns a PHP filter type
     */
    private function filterType($format){
        switch ($format){
            case '%d':
                return FILTER_SANITIZE_NUMBER_INT;
            case '%f':
                return FILTER_SANITIZE_NUMBER_FLOAT;
            case '%s':
            default :
                return FILTER_SANITIZE_STRING;
        }
    }

    /**
     * @param array $data
     * @param array|string $format Filter data with the filter format. To do that it use filter_var.
     * @return array The filtered data
     */
    public function prepare(array $data, $format = null){
        $filter_data = [];
        if (!is_array($format)) {
            $filter = $this->filterType($format);
            foreach ($data as $key => $item){
                $filter_data[$this->sanitizeString($key)] = filter_var($item, $filter);
            }
        }
        else {
            $i = 0;
            foreach ($data as $key => $item){
                $filter = isset($format[$i]) ? $this->filterType($format[$i]) : $this->filterType(NULL);
                $filter_data[$this->sanitizeString($key)] = filter_var($item, $filter);
                $i++;
            }
        }
        return $filter_data;
    }

    /**
     * @param string $string Filtered Data table names from SQL escapes
     * @return mixed
     */
    public function sanitizeString($string){
        return preg_replace("/[^a-zA-Z0-9_\-]+/", "", $string);
    }

    /**
     * @param array $fields Database fields to transform to placeholders
     * @return array Returns placeholders
     */
    public function createPlaceholder(array $fields) {
        $placeholders = [];
        foreach ($fields as $field){
            $placeholders[$field] = ":" . $field;
        }
        return $placeholders;
    }

    private function prepareCondition(array $data, $type) {
        $data_array = [];
        $condition = [];
        foreach ($data as $key => $value) {
            //relocate array
            $data_array["_{$type}_" . $key] = $value;
            $condition[$key] = ":_{$type}_" . $key;
        }
        return array("data" => $data_array, "condition" => $condition);
    }

    public function createCondition($condition, $conjunction = NULL, $compare = NULL, $whitespace = TRUE) {
        $conjunction = $this->createConditionHelper($conjunction, count($condition) - 1, "AND");
        $compare = $this->createConditionHelper($compare, count($condition), "=");

        $output = "";
        $i = 0;
        foreach ($condition as $key => $value) {
            $output .= sprintf(" %s%s%s", $key, $compare[$i], $value);

            if ($i < count($conjunction)) {
                $ws = $whitespace ? " " : "";
                $output .= $ws . $conjunction[$i];
            }
            $i++;
        }

        return $output;
    }

    private function createConditionHelper($values, $times, $default = null) {
        $temp_values = [];
        if (!is_array($values)) {
            for ($i = 0; $i < $times; $i++) {
                $temp_values[] = is_null($values) ? $default : $values;
            }
            $values = $temp_values;
        }
        else {
            foreach ($values as $value) {
                $temp_values[] = is_null($value) ? $default : $value;
            }

            $values = $temp_values;

            // if $values < $times than set all other to default
            for ($i = count($values); $i < $times; $i++) {
                $values[] = $default;
            }
        }
        return $values;
    }

    private function processStatement($table, array $data, $format) {
        $statement = [];

        $statement['table'] = $this->sanitizeString($table);
        $statement['data'] = $this->prepare($data, $format);
        $statement['keys'] = array_keys($statement['data']);
        $statement['placeholders'] = $this->createPlaceholder($statement['keys']);
        return $statement;
    }

    public function query($query, $fetch_type = PDO::FETCH_ASSOC){
        $STH = $this->DBH->query($query);
        return $STH->fetch($fetch_type);
    }

    public function insert($table, array $data, $format = NULL){
        return $this->insertReplaceHelper($table, $data, $format, "INSERT");
    }

    public function replace($table, array $data, $format = NULL){
        return $this->insertReplaceHelper($table, $data, $format, "REPLACE");
    }

    private function insertReplaceHelper($table, array $data, $format, $type){
        if (!in_array(strtoupper($type), array("INSERT", "REPLACE"))) {
            return FALSE;
        }
        try {
            $statement = $this->processStatement($table, $data, $format);
            $fields = implode(",", $statement['keys']);
            $placeholders = implode(",", $statement['placeholders']);

            //Statement Handle
            $STH = $this->DBH->prepare("$type INTO {$statement['table']} ($fields) VALUES ($placeholders)");
            $STH->execute($statement['data']);
            $this->rowCount = $STH->rowCount();
            return $this->rowCount;
        } catch (PDOException $ex) {
            $this->error($ex->getMessage());
            return FALSE;
        }
    }

    public function update($table, array $data, array $where, $format = NULL, $where_format = NULL){
        try {
            $statement = $this->processStatement($table, $data, $format);
            $prepare_where = $this->prepare($where, $where_format);
            $where_statement = $this->prepareCondition($prepare_where, "where");

            $fields = $this->createCondition($statement['placeholders'], ",", "=", FALSE);
            $where_string = $this->createCondition($where_statement['condition']);

            $STH = $this->DBH->prepare("UPDATE {$statement['table']} SET $fields WHERE $where_string");
            $STH->execute(array_merge($statement['data'], $where_statement['data']));
            $this->rowCount = $STH->rowCount();
            return $this->rowCount;
        } catch (PDOException $ex) {
            $this->error($ex->getMessage());
            return FALSE;
        }
    }

    public function delete($table, $where, $where_format = NULL) {
        try {
            $statement = $this->processStatement($table, $where, $where_format);
            $where_string = $this->createCondition($statement['placeholders']);

            $STH = $this->DBH->prepare("DELETE FROM {$statement['table']} WHERE $where_string");
            $STH->execute($statement['data']);
            $this->rowCount = $STH->rowCount();
            return $this->rowCount;
        } catch (PDOException $ex) {
            $this->error($ex->getMessage());
            return FALSE;
        }
    }

    public function select($statement, array $values = [], $fetch_style = PDO::FETCH_ASSOC){
        try {
            $STH = $this->DBH->prepare($statement);
            $STH->execute(array_values($values));
            $this->rowCount = $STH->rowCount();
            return $STH->fetchAll($fetch_style);
        } catch (PDOException $ex) {
            $this->error($ex->getMessage());
        }

        return null;
    }

    private function bindParams(\PDOStatement $statement, $format, array $values = []) {
        if (!is_array($format)) {
            foreach ($values as $key => $item){
                $this->bindParam($statement, $key, $item, $format);
            }
        }
        else {
            $i = 0;
            foreach ($values as $key => $item){
                isset($format[$i]) ? $this->bindParam($statement, $key, $item, $format[$i]) : $this->bindParam($statement, $key, $item, NULL);
                $i++;
            }
        }
    }

    private function bindParam(\PDOStatement $statement, $key, $value, $format) {
        switch ($format){
            case '%b':
                $type = PDO::PARAM_BOOL;
                break;
            case '%n':
                $type = PDO::PARAM_NULL;
                break;
            case '%d':
                $value = intval($value);
                $type = PDO::PARAM_INT;
                break;
            case '%l':
                $type = PDO::PARAM_LOB;
                break;
            case '%s':
            default :
                $type = PDO::PARAM_STR;
                break;
        }

        $statement->bindParam(":" . $key, $value, $type);
    }

    public function select2($statement, $format, array $values = [], $fetch_style = PDO::FETCH_ASSOC){
        try {
            $STH = $this->DBH->prepare($statement);
            $this->bindParams($STH, $format, $values);
            $STH->execute();

            $this->rowCount = $STH->rowCount();
            return $STH->fetchAll($fetch_style);
        } catch (PDOException $ex) {
            $this->error($ex->getMessage());
        }

        return null;
    }

    public function column($statement, array $values = [], $offset = 0){
        try {
            $STH = $this->DBH->prepare($statement);
            $STH->execute(array_values($values));
            $result = [];
            while ($row = $STH->fetchColumn($offset)) {
                $result[] = $row;
            }
            $this->rowCount = $STH->rowCount();
            return $result;
        } catch (PDOException $ex) {
            $this->error($ex->getMessage());
        }

        return null;
    }

    public function row($statement, array $values = [], $fetch_style = PDO::FETCH_ASSOC) {
        try {
            $STH = $this->DBH->prepare($statement);
            $STH->execute(array_values($values));
            $this->rowCount = $STH->rowCount();
            return $STH->fetch($fetch_style);
        } catch (PDOException $ex) {
            $this->error($ex->getMessage());
        }

        return null;
    }

    public function variable($statement, array $values = [], $offset = 0) {
        try {
            $STH = $this->DBH->prepare($statement);
            $STH->execute(array_values($values));
            $this->rowCount = $STH->rowCount();
            return $STH->fetchColumn($offset);
        } catch (PDOException $ex) {
            $this->error($ex->getMessage());
        }

        return null;
    }

    public function lastInsertId() {
        return $this->DBH->lastInsertId();
    }

    public function rowCount() {
        return $this->rowCount;
    }

    public function connection() {
        return $this->DBH;
    }
}