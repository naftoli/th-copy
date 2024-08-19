<?php
class TehillimTasks {
    private $quotas;
    private $year;
    private $dates;
    private $tasks;
    private $langs;
    private $missions;
    private $schoolTypes;
    private $ages;
    private $tracks;
    private $heMonths;
    private $db;

    public function __construct($year, $dbHandler) {
        $this->year = $year;
        $this->db = $dbHandler;
        $this->quotas = $this->setQuotas();
        $this->dates = $this->setDates();
        $this->tasks = $this->setTasks();
        $this->missions = [
            'chabad' 	=>	'שבת מברכים תהילים',
            'frum'		=>	'Shabbos Mevarchim Tehillim'
        ];
        $this->schoolTypes = [
            'chabad' => [2, 3],
            'frum'	=>	[12, 13]
        ];
        $this->ages = [6, 7, 8, 9, 10, 11, 12, 13, 14];
        $this->tracks = [3, 4, 5, 6, 7];
        $this->heMonths = [
            'שבת מברכים תשרי',
            'שבת מברכים  חשון',
            'שבת מברכים כסלו',
            'שבת מברכים טבת',
            'שבת מברכים שבט',
            'שבת מברכים אדר',
            'שבת מברכים אדר ב',
            'שבת מברכים ניסן',
            'שבת מברכים אייר',
            'שבת מברכים סיון',
            'שבת מברכים תמוז',
            'שבת מברכים מנחם-אב',
            'שבת מברכים אלול'
        ];
        $this->langs = ['en', 'yi', 'he'];
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
    }

    private function setTasks() {
        $this->tasks = [
            'en' => [
                'chabad' => [
                    [
                        'name' => 'I said my quota of תהלים. How many קאפיטלעך did you say? ______',
                        'mand' => 1,
                        'ord' => 1,
                        'short' => 'Tehillim Quota',
                        'cat' => 'Quota - שבת מברכים תהילים',
                        'cat_ord' => 8001,
                        'grid_id' => 8001,
                        'mission_marking' => 1,
                        'grid_marking' => 1,
                        'picture' => '19WWTC-Tehillim'
                    ],
                    [
                        'name' => 'How many Minutes did you spend saying תהלים? ______',
                        'mand' => 0,
                        'ord' => 2,
                        'short' => 'Tehillim Minutes',
                        'cat' => 'Minutes - שבת מברכים תהילים',
                        'cat_ord' => 8002,
                        'grid_id' => 8002,
                        'mission_marking' => 1,
                        'grid_marking' => 1,
                        'picture' => '19WWTC-Tehillim'
                    ],
                    [
                        'name' => 'I said תהלים in שול or in a group.',
                        'mand' => 0,
                        'ord' => 3,
                        'short' => 'Tehillim Location',
                        'cat' => 'Location - שבת מברכים תהילים',
                        'cat_ord' => 8003,
                        'grid_id' => 8003,
                        'mission_marking' => 1,
                        'grid_marking' => 0,
                        'picture' => '19WWTC-Tehillim'
                    ]
                ],
                'frum' => [
                    [
                        'name' => 'I said my quota of Tehillim. How many Kapitlach did you say? ______',
                        'mand' => 1,
                        'ord' => 1,
                        'short' => 'Tehillim Quota',
                        'cat' => 'Shabbos Mevarchim Tehillim - Quota',
                        'cat_ord' => 8001,
                        'grid_id' => 8001,
                        'mission_marking' => 1,
                        'grid_marking' => 1,
                        'picture' => '9Tehilim-_boy'
                    ],
                    [
                        'name' => 'How many Minutes did you spend saying Tehillim? ______',
                        'mand' => 0,
                        'ord' => 2,
                        'short' => 'Tehillim Minutes',
                        'cat' => 'Shabbos Mevarchim Tehillim - Minutes',
                        'cat_ord' => 8002,
                        'grid_id' => 8002,
                        'mission_marking' => 1,
                        'grid_marking' => 1,
                        'picture' => '9Tehilim-_boy'
                    ],
                    [
                        'name' => 'I said Tehillim in Shul or in a group.',
                        'mand' => 0,
                        'ord' => 3,
                        'short' => 'Tehillim Location',
                        'cat' => 'Shabbos Mevarchim Tehillim - Location',
                        'cat_ord' => 8003,
                        'grid_id' => 8003,
                        'mission_marking' => 1,
                        'grid_marking' => 0,
                        'picture' => '9Tehilim-_boy'
                    ]
                ]
            ],
            'yi' => [
                'chabad' => [
                    [
                        'name' => "געזאגט מיין תהילים קוואטע. וויפל קאפיטלעך האסטו געזאגט? ______",
                        'mand' => 1,
                        'ord' => 1,
                        'short' => "תהילים קוואטע",
                        'cat' => 'שבת מברכים תהילים - קוואטא',
                        'cat_ord' => 8001,
                        'grid_id' => 8001,
                        'mission_marking' => 1,
                        'grid_marking' => 1,
                        'picture' => '19WWTC-Tehillim'
                    ],
                    [
                        'name' => "געזאגט מיין תהילים קוואטע. וויפל קאפיטלעך האסטו געזאגטוויפל מינוטן האסטו געזאגט תהילים? ______",
                        'mand' => 0,
                        'ord' => 2,
                        'short' => "תהילים מינוטן",
                        'cat' => 'שבת מברכים תהילים - מינוטן',
                        'cat_ord' => 8002,
                        'grid_id' => 8002,
                        'mission_marking' => 1,
                        'grid_marking' => 1,
                        'picture' => '19WWTC-Tehillim'
                    ],
                    [
                        'name' => "געזאגט תהילים אין שול אדער מיט א גרופע קינדער צוזאמען",
                        'mand' => 0,
                        'ord' => 3,
                        'short' => "תהילים ארט",
                        'cat' => 'שבת מברכים תהילים - ארט',
                        'cat_ord' => 8003,
                        'grid_id' => 8003,
                        'mission_marking' => 1,
                        'grid_marking' => 0,
                        'picture' => '19WWTC-Tehillim'
                    ]
                ]
            ],
            'he' => [
                'chabad' => [
                    [
                        'name' => "אמרתי את שיעור התהילים שלי. כמה פרקים אמרת? ______",
                        'mand' => 1,
                        'ord' => 1,
                        'short' => "שיעור התהילים שלי",
                        'cat' => 'שיעור התהילים שלי',
                        'cat_ord' => 8001,
                        'grid_id' => 8001,
                        'mission_marking' => 1,
                        'grid_marking' => 1,
                        'picture' => '19WWTC-Tehillim'
                    ],
                    [
                        'name' => "במשך כמה דקות אמרת תהילים? _______",
                        'mand' => 0,
                        'ord' => 2,
                        'short' => "מספר דקות אמירת התהילים",
                        'cat' => 'מספר דקות אמירת התהילים',
                        'cat_ord' => 8002,
                        'grid_id' => 8002,
                        'mission_marking' => 1,
                        'grid_marking' => 1,
                        'picture' => '19WWTC-Tehillim'
                    ],
                    [
                        'name' => "אמרתי את שיעור תהילים בבית הכנסת או עם קבוצה.",
                        'mand' => 0,
                        'ord' => 3,
                        'short' => "מקום אמירת התהילים",
                        'cat' => 'מקום אמירת התהילים',
                        'cat_ord' => 8003,
                        'grid_id' => 8003,
                        'mission_marking' => 1,
                        'grid_marking' => 0,
                        'picture' => '19WWTC-Tehillim'
                    ]
                ]
            ]
        ];
    }

    private function getStartingMissionNumber() {
        $sql = "SELECT 
                    mission_number
                FROM
                    date_tasks_missions
                WHERE
                    subject_id = 1
                ORDER BY mission_number DESC
                LIMIT 1";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['mission_number'] + 1;
    }

    public function cloneTasks() {
        $missionsCreated = 0;
        $tasksCreated = 0;

        $success = true;
        $this->db->beginTransaction();

        $missionNum = $this->getStartingMissionNumber();

        foreach ($this->tasks as $lang => $more) {
            $lang_id = 1;
            if ($lang == 'yi') $lang_id = 2;
            else if ($lang == 'he') $lang_id = 4;
            foreach ($more as $missionType => $details) {
                foreach ($this->dates as $month => $date) {
                    foreach ($this->schoolTypes[$missionType] as $schoolType) {
                        foreach ($this->ages as $level) {
                            foreach ($this->tracks as $track) {
                                $speed = $this->quotas[$track][$level][$month]['s'];
                                if ($missionType == 'chabad') {
                                    $missionDescription = $this->heMonths[$month];
                                } else {
                                    $missionDescription = 'Shabbos Mevarchim Tehillim';
                                }
                                $sql = "insert into date_tasks_missions 
                                        set school_type_id = $schoolType, 
                                        subject_id = 1, 
                                        level = $level, 
                                        track_id = $track, 
                                        mission_name = '" . $this->missions[$missionType] . "',   
                                        mission_value = 1.0, 
                                        mission_number = " . $missionNum++ . ", 
                                        mission_description = '" . $missionDescription . "', 
                                        start_date = $date, 
                                        end_date = $date, 
                                        default_on = 1, 
                                        lang_id = $lang_id, 
                                        speed = $speed";
                                echo $sql . "<br />";
                                if (!$this->db->query($sql)) {
                                    $success = false;
                                    break 6;
                                }
                                $id = $this->db->lastInsertId();
                                $missionsCreated++;

                                foreach ($details as $taskInfo) {
                                    if ($taskInfo['mand']) {
                                        $mand = 1;
                                        $opt = 0;
                                    } else {
                                        $mand = 0;
                                        $opt = 1;
                                    }
                                    if ($taskInfo['ord'] == 1) {
                                        $desc = $this->quotas[$track][$level][$month]['k'];
                                        $qty = $this->quotas[$track][$level][$month]['q'];
                                    } else if ($taskInfo['ord'] == 2) {
                                        $desc = $this->quotas[$track][$level][$month]['m'];
                                        $qty = $desc;
                                    } else if ($taskInfo['ord'] == 3) {
                                        $desc = '';
                                        $qty = null;
                                    }
                                    $sql = "insert into date_tasks 
                                            set date_tasks_mission_id = $id, 
                                            name = '" . $taskInfo['name'] . "', 
                                            cat = '" . $taskInfo['cat'] . "',
                                            cat_ord_new = " . $taskInfo['cat_ord'] . ", 
                                            points = 0.5, 
                                            short_name = '" . $taskInfo['short'] . "', 
                                            mandatory_qty = $mand, 
                                            optional_qty = $opt, 
                                            daily_task = 0, 
                                            label_id = 37, 
                                            ord = " . $taskInfo['ord'] . ", 
                                            needed = 1,
                                            focus_task = 0,
                                            default_on = 1, 
                                            label_ord = 2,  
                                            description = '" . $desc . "',
                                            grid_id = " . $taskInfo['grid_id'] . ",
                                            mission_marking = " . $taskInfo['mission_marking'] . ",
                                            grid_marking = " . $taskInfo['grid_marking'];
                                    if ($qty) {
                                        $sql .= ", quantity = $qty";
                                    }
                                    echo $sql . "<br /><br />";
                                    if (!$this->db->query($sql)) {
                                        $success = false;
                                        break 7;
                                    }
                                    $tasksCreated++;
                                }
                            }
                        }
                    }
                }
            }
        }

        $success = false;
        if ($success) {
            $this->db->commit();
            echo "Missions created: $missionsCreated<br />";
            echo "Tasks created: $tasksCreated<br />";
        } else {
            $this->db->rollBack();
            echo "Error creating missions and tasks.";
        }
    }
}