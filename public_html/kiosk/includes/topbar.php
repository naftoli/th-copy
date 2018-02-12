<div class="org">
    <div class="nav">
        <ul>
            <li class="icon_back"><a href="#" onclick="javascript:history.back(); return false">Back</a></li>
            <li class="icon_home"><a href="../statement.php">Home</a></li>
            <li class="icon_logout"><a href="../logout.php?n=kiosk.php">Logout</a></li>
        </ul>
    </div>
	<div class="org_photo"><?=(!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id'],100,100) : '');?></div>
    Base: #<?=$user_row['school_number']?><br>
	<?=$user_row['school_name']?><br>
	<?=$user_row['rank_name'].' '.$user_row['first'].' '.$user_row['last']?>
</div>
