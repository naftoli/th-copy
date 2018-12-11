<? 
$admin_auth = array('school','user'); 

require('header.php');

$school_name = gr('school_name', '');
$school_number = gr('school_number', '');
$sql = gr('sql', '');
$query = mq($sql);

// SELECT user_id, first, last, first_he, last_he, username, gender, user_code, user_serial, 
//        user_photo_id, dob, dob_he_offset, user_start_date, class_id, class_grade, class_sub, 
//        class_teacher, rank_ord, rank_name, rank_image_id, rank_color 

// FROM users 

// LEFT JOIN classes USING (school_id, class_id) 
// LEFT JOIN (SELECT MAX(rank_ord) rank_ord, user_id FROM rank_marks GROUP BY user_id) rank USING (user_id) 
// LEFT JOIN ranks USING (rank_ord) 
// WHERE school_id = 82 ORDER BY last, first, username
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML>

	<HEAD>
		<STYLE type="text/css">
			.certificate {
				height:600px;
				text-align:center;
			}
			
			.certificateinfo {
				position:relative;
				top:200px;
			}
			
			.basenumber {
				font-family:serif;
				font-size:10px;
			}
			
			.username {
				font-family:sans-serif;
				font-size:40px;
				font-weight:bolder;
			}
			
			.pgbrk {
				page-break-before:always;
			}
			
			HR {
				page-break-before:always;
			}

			@media print {
				HR {
					display:none;
				}							
			}
		</STYLE>
	</HEAD>

	<BODY>
		<? while($row = mysql_fetch_assoc($query)) : ?>
			<DIV class="certificate">
				<DIV class="certificateinfo">
					<b><? echo $school_name; ?></b><br />
					<DIV class="basenumber">
					BASE <? echo " # " . $school_number; ?>
					</DIV>
					
					<br />
					<br />
					
					THIS IS TO CERTIFY THAT<br /> 
					<DIV class="username">
					<? echo $row['first'] . " " . $row['last']; ?>
					</DIV>
					
					<br />
					
					IS HEREBY OFFICIALLY PROMOTED<br />
					IN THE ARMY OF HASHEM TO THE RANK OF<br />
				</DIV>
								
			</DIV>
			
			<!--<HR>-->
			<DIV class="pgbrk"></DIV>
			
		<? endwhile; ?>
			
	</BODY>
	
</HTML>
