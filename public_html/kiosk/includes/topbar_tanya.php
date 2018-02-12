<div class="org_photo"><?=(!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id'],100,100) : '');?></div>
    Base: #<?=$user_row['school_number']?><br>
	<?=$user_row['school_name']?><br>
	<?=$user_row['rank_name'].' '.$user_row['first'].' '.$user_row['last']?>
</div>