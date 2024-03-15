<?php
$data = array(
    "29263" => array(
        "4" => array(
            "maven" => 17,
            "pro" => 15,
            "expert" => 0
        )
    ),
    "29011" => array(
        "4" => array(
            "maven" => 20,
            "pro" => 20,
            "expert" => 20
        )
    ),
    "29300" => array(
        "4" => array(
            "maven" => 20,
            "pro" => 19,
            "expert" => 20
        )
    ),
    "29326" => array(
        "4" => array(
            "maven" => 17,
            "pro" => 16,
            "expert" => 16
        )
    )
);
?>
<!DOCTYPE html>
<html>
<head>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        const scores = <?= json_encode($data); ?>;
        const test_num = 4
        $.post('index.php', { test_num, scores }, function (data) {
            console.log(data)
        })
    </script>
</head>
</html>
