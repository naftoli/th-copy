<?php
require 'db.php';

$info = array(
    269 => 'Anash Kinder',
    176 => 'Beis Chaya Mushka IA',
    162 => 'Beis Chaya Mushka CA',
    45  => 'Beis Chaya Mushka ON',
    30  => 'Beis Chaya Mushka CH',
    2   => 'Beis Rivka QC',
    54  => 'Beis Rivkah CH',
    7   => 'Bnos Menachem CH',
    112 => 'Chabad Youth AU',
    66  => 'Chabad Youth AU',
    105 => 'Chassidus Club CT',
    63  => 'Cheder at the Ohel',
    63  => 'Cheder at the Ohel',
    81  => 'Cheder Chabad MD',
    49  => 'Cheder Chabad Monsey',
    192 => 'Cheder Chabad Monsey',
    89  => 'Cheder Chabad PA',
    55  => 'Cheder Chabad AU',
    106 => 'Cheder Chabad ON',
    5   => 'Cheder Lubavitch IL',
    50  => 'Cheder Lubavitch IL',
    21  => 'Cheder Lubavitch NJ',
    37  => 'Cheder Lubavitch NJ',
    4   => 'Cheder Menachem CA',
    263 => 'Cheder Menachem Mendel WA',
    60  => 'Cheder Menachem NJ',
    86  => 'Cheder Menachem PA',
    185 => 'Hebrew Academy FL',
    80  => 'Hillel Academy WI',
    110 => 'Keter Torah College AU',
    194 => 'Lamplighters CH',
    3   => 'London Lubavitch',
    265 => 'London Lubavitch',
    39  => 'Lubavitch Cheder MN',
    19  => 'Lubavitch Educational Center FL',
    42  => 'Lubavitch Educational Center FL',
    9   => 'Lubavitcher Yeshiva CH',
    61  => 'MyShliach',
    255 => 'Oholei Torah CH',
    471 => 'Lubavitcher Yeshiva OP',
    48  => 'Ohr Temimim NY',
    84  => 'Torah Day School TX',
    427 => 'Tzivos Hashem Long Beach CA',
    87  => 'Tzivos Hashem Vancouver BC',
    33  => 'Darchai Menachem CH',
    58  => 'Tomchei Temimim QC',
    11  => 'Yeshiva Schools PA',
    40  => 'Yeshiva Schools PA',
    470 => 'Cheder Lubavitch AZ',
    412 => 'Lamplighters Torah Academy',
    448 => 'Jewish Academy NY'
);

foreach ($info as $id => $name) {
    $sql = "update schools set hachayol_name = '" . $name . "' where school_id = " . $id;
    mysql_query( $sql ) or die( mysql_error() );
}
echo "done.";
