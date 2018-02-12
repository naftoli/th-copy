<?php
header('Content-disposition: attachment; filename=registration_brochure.pdf');
header('Content-type: application/pdf');
readfile('downloads/Registration Brochure 5774.pdf');
?>