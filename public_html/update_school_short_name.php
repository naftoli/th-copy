<?php
require 'db.php';

$info = array(
    269	=> 'Anash Kinder',
    176	=> 'Beis Chaya Mushka IA',
    162	=> 'Beis Chaya Mushka CA',
    45	=> 'Beis Chaya Mushka ON',
    30	=> 'Beis Chaya Mushka CH',
    2	=> 'Beis Rivka QC',
    54	=> 'Beis Rivkah CH',
    7	=> 'Bnos Menachem CH',
    112	=> 'Chabad Youth AU Boys',
    66	=> 'Chabad Youth AU Girls',
    105	=> 'Chassidus Club CT',
    63	=> 'Cheder at the Ohel Girls',
    63	=> 'Cheder at the Ohel Boys',
    81	=> 'Cheder Chabad MD Boys',
    81	=> 'Cheder Chabad MD Girls',
    49	=> 'Cheder Chabad NY Boys',
    192	=> 'Cheder Chabad NY Girls',
    89	=> 'Cheder Chabad PA',
    55	=> 'Cheder Chabad AU',
    106	=> 'Cheder Chabad ON',
    5	=> 'Cheder Lubavitch IL Boys',
    50	=> 'Cheder Lubavitch IL Girls',
    21	=> 'Cheder Lubavitch NJ Boys',
    37	=> 'Cheder Lubavitch NJ Girls',
    4	=> 'Cheder Menachem CA',
    263	=> 'Cheder Menachem Mendel WA',
    60	=> 'Cheder Menachem NJ',
    86	=> 'Cheder Menachem PA',
    185	=> 'Hebrew Academy FL',
    80	=> 'Hillel Academy WI',
    110	=> 'Keter Torah College AU',
    194	=> 'Lamplighters CH',
    3	=> 'London Lubavitch Boys',
    265	=> 'London Lubavitch Girls',
    39	=> 'Lubavitch Cheder MN',
    19	=> 'Lubavitch Educational Center FL Boys',
    42	=> 'Lubavitch Educational Center FL Girls',
    9	=> 'Lubavitcher Yeshiva CH',
    61	=> 'MyShliach',
    255	=> 'Oholei Torah CH',
    471	=> 'Lubavitcher Yeshiva OP',
    48	=> 'Ohr Temimim NY',
    84	=> 'Torah Day School TX',
    427	=> 'Tzivos Hashem Long Beach CA',
    87	=> 'Tzivos Hashem Vancouver BC',
    264	=> 'Chok Leyisroel Lubavitch CH',
    33	=> 'Darchai Menachem CH',
    11	=> 'Yeshiva Schools PA Boys',
    40	=> 'Yeshiva Schools PA Girls',
    470	=> 'Cheder Lubavitch AZ',
    412	=> 'Lamplighters Torah Academy',
    448	=> 'Brooklyn Heights Jewish Academy'
);

foreach ($info as $id => $short_name) {
    $sql = "update schools set nickname = '" . $short_name . "' where school_id = " . $id;
    //echo $sql . "<br />";
    mysql_query($sql);
}
echo "done.";