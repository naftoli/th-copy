<?php
class Columnize {
    private $totalDaily;
    private $totalTasks;
    private $pages = array[];
    
    public function __construct($totalTasks, $totalDaily) {
        $this->totalDaily = $totalDaily;
        $this->totalTasks = $totalTasks;
    }
    
    public function determinePages() {
        $tasks = $this->totalTasks;
        for ($i = 0; $i < $this->totalTasks; $i++) {
            if ($i > 10) {
                if ($tasks > $i) {
                    
                }
            }
        }
    }
}