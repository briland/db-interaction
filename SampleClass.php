<?php
/**
 * @description: SampleClass, for demonstration on how to use DBConn to
 * perform CRUD operations making use of DBConn Class methods.
 *
 * @author: Briland Hitaj, <briland.hitaj90@gmail.com>
 * @copyright: read LICENSE.txt
 */

class SampleClass extends DBConn {

    // B.H.Note: Sample Class attributes
    private $id;
    private $name;
    private $surname;

    // B.H.Note: name of the table this class will interact with
    protected $_tableName = "sample";
    // B.H.Note: attributes of the table
    protected $_id = "id_field";
    protected $_name = "name_field";
    protected $_surname = "surname_field";

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $surname
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    /**
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /*****************************************
     * B.H.Note CRUD Methods for SampleClass *
     *****************************************/

    /**
     * CREATE OPERATION
     * ----------------
     * Method to insert a new record in the database
     *
     * @return bool, true if successful creation; false otherwise
     */
    public function createNewSampleRecord(){
        // associative array of field name => field value pairs
        $values = array($this->_name => $this->name, $this->_surname => $this->surname);
        return $result = parent::insertInformation($this->_tableName, $values);
    }

    /**
     * READ OPERATION i.e. SELECT ALL OPERATION
     * ----------------------------------------
     * Method to select all of the records found on the specified table
     *
     * @return array
     */
    public function getAllSampleRecords(){
        return $result = parent::getInformation($this->_tableName);
    }

    /**
     * READ OPERATION i.e. SELECT
     * --------------------------
     * Method to select a specific record from the table by using
     * the record's id value.
     *
     * @return array
     */
    public function getSampleRecordByID(){
        // associative array containing the set up value for id of the record
        $where = array($this->_id => $this->id);
        // setting up the where statement of mysql query
        parent::whereCondition($where);
        // return result as an associative array of name - value pairs and setting the query limit to 1
        return $result = parent::getInformation($this->_tableName, 1);
    }

    /**
     * UPDATE OPERATION
     * ----------------
     * Method to update a record found in the table
     *
     * @return bool, true if successful update; false otherwise
     */
    public function updateSampleRecord(){
        // associative array of field name => field value pairs
        $values = array($this->_name => $this->name, $this->_surname => $this->surname);
        // associative array containing the set up value for id of the record
        $where = array($this->_id => $this->id);
        // setting up the where statement of mysql query
        parent::whereCondition($where);
        // update the record information in the table
        return $result = parent::updateInformation($this->_tableName, $values);
    }

    /**
     * DELETE OPERATION
     * ----------------
     * Method to delete a record found in the table
     *
     * @return bool, true if successful deletion; false otherwise
     */
    public function deleteSampleRecord(){
        // associative array containing the set up value for id of the record
        $where = array($this->_id => $this->id);
        // setting up the where statement of mysql query
        parent::whereCondition($where);
        // delete the record from the table
        return $result = parent::deleteInformation($this->_tableName);
    }

} 