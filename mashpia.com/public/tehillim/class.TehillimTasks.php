<?php
class TehillimTasks {
    private $quotas;
    private $year;
    private $dates;
    private $tasks;
    private $db;

    public function __construct($year, $db) {
        $this->year = $year;
        $this->db = $db;
        $this->quotas = $this->setQuotas();
        $this->dates = $this->setDates();
        $this->tasks = $this->setTasks();
    }

    public function setQuotas() {
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

    public function getQuotas() {
        return $this->quotas;
    }
}