<?php
ini_set('display_errors',1);

require_once '../db.php';
require_once 'class.tehillimBackup.php';

$tb = new TehillimBackup();
$tb->runBackup();

echo "Running backup...\n";
$errors = $tb->getErrors();
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo $error . "\n";
    }
} else {
    echo "done.";
}