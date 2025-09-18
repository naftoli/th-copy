<?php
// Both dates in New York timezone
$timezone = new DateTimeZone('America/New_York');
return new DateTime('now', $timezone) > new DateTime('2025-09-18T09:00:00', $timezone) ? 1 : 0;