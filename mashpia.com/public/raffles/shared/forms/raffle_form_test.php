<?php

$run_date = '2019-09-26';
$format_run_date = date('Y-n-j', strtotime($run_date));
$format_run_date = explode('-', $format_run_date);

$runYear  = $format_run_date[0];
$runMonth = $format_run_date[1];
$runDay   = $format_run_date[2];

$run_date_jd = gregoriantojd($runMonth, $runDay, $runYear);

echo $run_date_jd;
