<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/db.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/class.shabbosMevorchim.php' );
$sm = new ShabbosMevorchim();

$date = false;
if ( isset( $_POST[ 'date' ] ) ) {
    $sm->setReportDates( $_POST['date'] );
    $reportDates = $sm->getReportDates();
    $date = end( $reportDates ); // the last date of the report dates is the date we want to generate
}

if ( $date && !$sm->getBackupRan( $date ) ) {
    echo "Sorry but it appears that amounts are still being entered for the current month. Please try again after the deadline has passed.<br/><br/>";
    unset($_POST['submit']);
}

if (isset($_POST['submit'])) {
    // get the gender that we want to generate the report for
    $gender = mysql_real_escape_string( $_POST['gender'] );
    // fetch all the classes and sort them by grade
    $gradeInfo = $sm->setWhatsappClasses( $gender );
    // set the totals for each class
    $sm->setClassResults( false );
    $classes = $sm->getClasses();
    $goal = $sm->getClassResults();
    $done = $sm->getClassDoneResults();
    $totalUsers = $sm->getTotalUsers();

    $grades = array_keys($totalUsers);
    foreach ($grades as $class_id) {
        $sm->setWhatsappStudentResults($class_id);
    }
    $doneQuotas = $sm->getDoneQuotas();
    
    $schoolInfo = array();
    $sql = "select c.class_id, s.school_name
            from classes c 
            join schools s using (school_id)
            where c.class_id in (" . implode(',', $grades) . ")
            and c.whatsapp = 1";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $schoolInfo[$row['class_id']] = $row['school_name'];
    }
    
    // create data array sorted by percent of chayolim that completed their quota
    foreach ($gradeInfo as $grade => $info) {
        $completed[$grade] = array();
        foreach ($info as $id) {
            $percent = $totalUsers[$id] ? number_format((($doneQuotas['Kapitelach'][$id] / $totalUsers[$id]) * 100.00), 2) : 0.00;
            $completed[$grade][$id] = $percent;
        }
        arsort($completed[$grade]);
        //echo "<pre>"; print_r($completed[$grade]); echo "</pre>";
    }
} else {
    $sm->setReportDates();
    $reportDates = $sm->getReportDatesAll();
}
?>