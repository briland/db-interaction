<?php
/*
* The MIT License (MIT)
*
* Copyright (c) 2014 Briland Hitaj
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*/

/**
 * @description: DBConn Class, for database interactions
 * @author: Briland Hitaj, <briland.hitaj90@gmail.com>
 * @copyright: read LICENSE.txt
 */

require("variables.php");

class DBConn
{
    private $hostname;
    private $dbname;
    private $dbuser;
    private $dbpass;

    private $connection;
    private $dbselect;

    protected $_query;
    protected $_where = NULL;

    public function __construct()
    {
        $argv = func_get_args();  // B.H.Note: reading the number of parameters passed to the function
        switch(func_get_args())
        {
            case 0:
                self::__defaultConstruct();
                break;
            case 4:
                self::__userDefinedConstruct($argv[0], $argv[1], $argv[2], $argv[3]);
                break;
            default:
                self::__defaultConstruct();
                break;
        }
    }
    /*** END OF FUNCTION ***/

    public function __defaultConstruct()
    {
        $this->hostname = DB_HOST;
        $this->dbname = DB_NAME;
        $this->dbuser = DB_USER;
        $this->dbpass = DB_PASS;
        $this->establishConnection();
    }
    /*** END OF FUNCTION ***/

    /**
     *	@param: $hostname -> the hostname ex. localhost
     *	@param: $dbname -> the database to connect ex. bhdb
     *	@param: $username -> the user ex. root
     *	@param: $password -> the password ex. briland
     */
    public function __userDefinedConstruct($hostname, $dbname, $username, $password)
    {
        $this->hostname = $hostname;
        $this->dbname = $dbname;
        $this->dbuser = $username;
        $this->dbpass = $password;
        $this->establishConnection();
    }
    /*** END OF FUNCTION ***/

    private function establishConnection()
    {
        $this->connection = mysql_connect($this->hostname, $this->dbuser, $this->dbpass);
        if(!($this->connection))
        {
            die("There was an error in establishing connection. ". mysql_error());
        }
        else
        {
            $this->dbselect = mysql_select_db($this->dbname, $this->connection);
            if(!($this->dbselect))
            {
                die("There was an error connecting to database. ". mysql_error());
            }
        }
    }
    /*** END OF FUNCTION ***/

    public function checkConnection()
    {
        return (($this->connection) && ($this->dbselect));
    }
    /*** END OF FUNCTION ***/

    /**
     * @param $query, query written manually from the user
     * @return array
     */
    public function userDefinedSelectQuery($query)
    {
        $this->_query = filter_var($query, FILTER_SANITIZE_STRING);
        $res = $this->executeQuery();
        $this->_checkExecutionSuccess($res);
        $results = $this->_getQueryResults($res);
        return $results;
    }
    /*** END OF FUNCTION ***/

    /**
     * @param $tableName
     * @param null $limit
     * @return array
     */
    public function getInformation($tableName, $limit = NULL)
    {
        $this->_query = "SELECT * FROM {$tableName}";
        $this->_constructQuery($limit);
        $res = $this->executeQuery();
        $this->_checkExecutionSuccess($res);
        $results = $this->_getQueryResults($res);
        return $results;
    }
    /*** END OF FUNCTION ***/

    /**
     * @param $tableName
     * @param $insertValues
     * @return bool
     */
    public function insertInformation($tableName, $insertValues)
    {
        $this->_query = "INSERT INTO {$tableName}";
        $this->_constructQuery(NULL, $insertValues);
        $res = $this->executeQuery();
        $this->_checkExecutionSuccess($res);
        if(mysql_affected_rows() !== 0)
        {
            return true;
        }
    }
    /*** END OF FUNCTION ***/

    /**
     * @param $tableName
     * @param $updateValues
     * @return bool
     */
    public function updateInformation($tableName, $updateValues)
    {
        $this->_query = "UPDATE {$tableName} SET ";
        $this->_constructQuery(NULL, $updateValues);
        $res = $this->executeQuery();
        $this->_checkExecutionSuccess($res);
        if(mysql_affected_rows() !== 0)
        {
            return true;
        }
    }
    /*** END OF FUNCTION ***/

    /**
     * @param $tableName
     * @return bool
     */
    public function deleteInformation($tableName)
    {
        $this->_query = "DELETE FROM {$tableName} ";
        $this->_constructQuery();
        $res = $this->executeQuery();
        $this->_checkExecutionSuccess($res);
        if(mysql_affected_rows() !== 0)
        {
            return true;
        }
    }
    /*** END OF FUNCTION ***/

    /**
     * @param array $whereArray an associative array of field name => field Value
     *			association.
     *			For example: $c = array('fieldName_1' => 'fieldValue_1',
     *									'fieldName_2' => 'fieldValue_2',
     *									'fieldName_3' => 'fieldValue_3');
     * @param null $conBy, condition statement "AND", "OR"
     */
    public function whereCondition($whereArray = array(), $conBy = NULL)
    {
        $this->_where = " WHERE ";
        if($conBy != NULL)
        {
            foreach($whereArray as $fieldName => $fieldValue)
            {
                $this->_where .= "`".$fieldName."`" ." = '". $fieldValue ."' ". $conBy ." ";
            }
            $this->_where = substr($this->_where, 0, strlen($this->_where) - strlen($conBy) - 2);
        }
        else
        {
            if(count($whereArray) === 1)
            {
                foreach($whereArray as $fieldName => $fieldValue)
                {
                    $this->_where .= "`".$fieldName."`" ." = '". $fieldValue ."'";
                }
            }
        }
    }
    /*** END OF FUNCTION ***/

    /**
     * @param null $limit
     * @param bool $tableInfo
     */
    protected function _constructQuery($limit = NULL, $tableInfo = false)
    {
        $hasTableInfo = null;
        if(gettype($tableInfo) === 'array')
        {
            $hasTableInfo = true;
        }

        if($this->_where !== NULL)
        {
            if($hasTableInfo == true)
            {
                if(strpos($this->_query, 'UPDATE') !== false)
                {
                    foreach($tableInfo as $key => $val)
                    {
                        $this->_query .= $key . " = " . "'".$val."', ";
                    }
                    $this->_query = substr($this->_query, 0, strlen($this->_query) - 2);
                    $this->_query .= $this->_where;
                }
            }
            else
            {
                $this->_query .= $this->_where;
            }
        }

        if($hasTableInfo == true)
        {
            if(strpos($this->_query, 'INSERT') !== false)
            {
                $keys = array_keys($tableInfo); // B.H.Note: Get array keys/indexes and store them in $keys array
                $data = array_values($tableInfo); // B.H.Note: Get array values and store them in $data array
                // B.H.Note: Add quotes to array values
                foreach($data as $key => $val)
                {
                    $data[$key] = "'{$val}'";
                }
                $this->_query .= '(' . implode($keys, ', ') . ')';
                $this->_query .= ' VALUES';
                $this->_query .= '(' . implode($data, ', ') . ')';
            }
        }

        if(isset($limit))
        {
            $this->_query .= " LIMIT " . (int)$limit;
        }
    }
    /*** END OF FUNCTION ***/

    /**
     * @return resource|void
     */
    protected function executeQuery()
    {
        return ($this->checkConnection()) ? mysql_query($this->_query, $this->connection) : die(mysql_error());
    }
    /*** END OF FUNCTION ***/

    /**
     * @param $result
     */
    protected function _checkExecutionSuccess($result)
    {
        if(!$result)
        {
            die("There was a problem in executing your query" .mysql_error());
        }
    }
    /*** END OF FUNCTION ***/

    /**
     * @param $result
     * @return array
     */
    protected function _getQueryResults($result)
    {
        $retrieved_results = array();
        $fieldNames = array();

        for($i = 0; $i < mysql_num_fields($result); $i++)
        {
            $fieldNames[$i] = mysql_field_name($result, $i);
        }

        while($row = mysql_fetch_assoc($result))
        {
            $x = array();
            for($i = 0; $i<count($fieldNames); $i++)
            {
                $x[$fieldNames[$i]] = $row[$fieldNames[$i]];
            }
            $retrieved_results[] = $x;
        }
        return $retrieved_results;
    }
    /*** END OF FUNCTION ***/

    /**
     * @param $value
     * @return string
     */
    public function cleanParameters($value)
    {
        return mysql_real_escape_string($value);
    }
    /*** END OF FUNCTION ***/

    /**
     * @return int
     */
    public function getLastInsertedId(){
        return mysql_insert_id();
    }

    /**
     * destructor
     */
    public function __destruct()
    {
        if(is_resource($this->connection)){
            mysql_close($this->connection);
        }
        unset($this->connection);
    }
    /*** END OF FUNCTION ***/
}
/*** END OF CLASS ***/
?>