<UL>
<LI><A HREF="index.php"><?= T_('Home') ?></A>
<? $points_total = mysql_result(mq("SELECT IFNULL(SUM(mark_points), 0) points_total FROM marks WHERE marks.user_id = {$user['user_id']}"), 0); ?>
<LI><?=sprintf(T_('Total Points: %s'), $points_total)?></LI>

<?
$result = mq("SELECT SUM(mark_points) points_total, institutions.inst_name FROM marks JOIN tasks USING (task_id) JOIN subjects USING (subject_id) JOIN institutions USING (inst_id) WHERE marks.user_id = {$user['user_id']} GROUP BY institutions.inst_id, institutions.inst_name");
while($row = mysql_fetch_assoc($result)):
?>
<LI><?=sprintf(T_('Total %s Points: %s'), $row['inst_name'], $row['points_total'])?></LI>
<? endwhile; ?>
<?if($user['team_id'] >= 0):?>
<? list($team_total, $team_count) = mysql_fetch_row(mq("
SELECT   IFNULL(SUM(marks.mark_points), 0) team_points,
         (SELECT COUNT(users.user_id)
          FROM   users
          WHERE  users.team_id = teams.team_id AND users.school_id = teams.school_id) team_count
FROM     marks JOIN users USING(user_id)
          RIGHT JOIN teams USING (team_id, school_id)
WHERE    teams.team_id = {$user['team_id']} AND teams.school_id = {$user['school_id']}
GROUP BY teams.school_id, teams.team_id
")); ?>
<LI><?=sprintf(T_('Squad Average Points: %s'), ($team_total/$team_count))?></LI>
<?endif;?>
<?if(in_array($user['settings'], array('managed_personal', 'self_managed', 'personal_only'))):?>
<LI><A HREF="tasks_personal.php"><?= T_('Manage tasks') ?></A>
<?endif;?>
<LI><A HREF="auction.php"><?= T_('Chinese Auction prizes') ?></A>
<LI><A HREF="store.php"><?= T_('Store prizes') ?></A>
<?if(empty($ruser)):?><LI><A HREF="preferences.php"><?= T_('Preferences') ?></A><?endif;?>
</UL>
