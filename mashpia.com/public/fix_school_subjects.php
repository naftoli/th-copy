<?php
$admin_auth = ['school'];
require 'header.php';
?>
<!DOCTYPE html>
<html>
<script
  src="https://code.jquery.com/jquery-1.12.4.js"
  integrity="sha256-Qw82+bXyGq6MydymqBxNPYTaUXXq7c8v3CwiYwLLNXU="
  crossorigin="anonymous"></script>
    <script>
        var id = 613;
        var type = 'school';
        $.post('ajax/enrollIntoCampaigns.php', { id: id, type : type });
    </script>
</html>