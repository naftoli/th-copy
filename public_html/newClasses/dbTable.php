<?php
require_once 'db.php';

class DbTable {  
    
    private $name;
    private $fields;
    private $data;
    private $db;
    
    public function __construct($name) {
        try {
            $this->db = DB::getInstance();
        } catch (Exception $e) {
            echo $e->getMessage();
            return 0;
        }
        $this->name = $name;
        $this->fields = array();
        $this->data = array();
        $this->setFields();
    }
    
    private function setFields() {
        $sql = "show columns from " . $this->name;
        foreach ($this->db->query($sql) as $row) {
            $this->fields[] = $row['Field'];
        }
    }
    
    public function getFields() {
        return $this->fields;
    }
    
    public function setData($sql) { 
        $result = $this->db->query($sql);
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $this->data[] = $row;
        }
    }
    
    public function getData() {
        return $this->data;
    }
}
?>
