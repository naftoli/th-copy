<?php
class TehillimTasks {
    private $quotas;
    private $year;
    private $dates;
    private $tasks;
    private $db;

    public function __construct($year, $dbHandler) {
        $this->year = $year;
        $this->db = $dbHandler;
        $this->quotas = $this->setQuotas();
        $this->dates = $this->setDates();
        $this->tasks = $this->setTasks();
    }

    private function setQuotas() {
        $stmt = $this->db->query("SELECT * FROM tehillim_ladders");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $this->quotas[$row['ladder']][$row['age']][$row['month']] = [
                'k' => $row['kapitelach'],
                'm' => $row['minutes'],
                'q' => $row['qty'],
                's' => $row['speed']
            ];
        }
    }

    private function setDates() {
        $this->dates = calculateSM($this->year);
        echo "<pre>"; print_r($this->dates); echo "</pre>";
    }

    private function setTasks() {
        $this->tasks = [];
    }

    public function getQuotas() {
        return $this->quotas;
    }

    public function getDates() {
        return $this->dates;
    }

    public function getTasks() {
        return $this->tasks;
    }
}