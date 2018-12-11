<?php
require '../db.php';
require '../class.gradeCreation.php';

$g = new GradeCreation( 54 );
if (!$g->createGrades()) {
    echo $g->getErrors();
}

echo "done";