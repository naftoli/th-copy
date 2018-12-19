<?php
define( 'MASHPIA_AUTH_REQUIRED', true );
include_once( __DIR__ . '/../header/header.php' );

// * setup the barcode generator
$generator = new Picqer\Barcode\BarcodeGeneratorPNG();

if ( !isset( $_POST['card_count'] ) || !isset( $_POST['subject_id'] ) || !isset( $_POST['task_id'] ) ) {
  header( 'Location: /new/rewards/cards' );
  die();
}

// * parse post paramaters
$card_count = intval( $_POST['card_count'] );
$subject = \Subject::find([ $_POST['subject_id'] ]);
$task = \AchievementTask::find([ $_POST['task_id'] ]);

// ********************************************************************************
// **************************** Validate Miles Balance ****************************
// ********************************************************************************
$miles_spent = $task->points * $card_count;

// if we are a teacher
if ( $current_user->login->code === 'TEACHER' ) {
  // and our balance is less then the amount that we are trying to print
  if ( $current_user->login->model->miles_balance < $miles_spent ) {
    die('You do not have enough miles to compleate this print. Please consolt your base commander to be given additional miles');
  } else {
    $current_user->login->model->miles_balance -= $miles_spent;
    $current_user->login->model->save();
  }
}
// ********************************************************************************
// ******************************* Generate Barcodes ******************************
// ********************************************************************************
$card_serials = $POINTS_DB->prepare(
  "SELECT card_serial FROM ("
      ." SELECT CAST(CONCAT(4, ROUND(RAND() * 999999999), ROUND(RAND() * 9999999999)) AS CHAR ) AS card_serial "
      ." FROM pointsDB.achievement_cards WHERE 'card_serial' NOT IN ("
          ." SELECT card_serial FROM pointsDB.achievement_cards "
      .") "
  .") AS numbers HAVING LENGTH( card_serial ) = 20 LIMIT ?;"
);

$card_serials->execute([ $card_count ]);
$card_type = $current_user->login->code == 'TEACHER' ? 'Teacher' : 'Institution Administrator';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
  <link rel='stylesheet' type='text/css' href='https://fonts.googleapis.com/css?family=Alef:400,700|Poppins:300,400,500,600,700|Source+Code+Pro|Roboto|Black+Ops+One' />
  <link rel="stylesheet" type="text/css" href='styles/achivement_cards.css' />
</head>
<body>
  <?php
    // ********************************************************************************
    // **************************** Print Achivement Cards ****************************
    // ********************************************************************************
    // for each barcode
    while ( $code = $card_serials->fetch() ) {
      // create and save the card
      $card = AchievementCard::create([
          'institution_id' => $current_user->login->school_id ? $current_user->login->school_id : 0,
          'campaign_id' => $subject->subject_id,
          'task_id' => $task->achievement_task_id,
          'class_id' => $current_user->login->class_id,
          'card_serial' => $code['card_serial'],
          'card_type' => $card_type,
          'card_points' => $task->points,
          'created_by' => $current_user->admin_id
      ]);
      // generate the barcode png image
      $barcode = $generator->getBarcode( $card->card_serial, $generator::TYPE_CODE_128_C );
      ?>
        <div class='AchievementCard'>
          <div class='card'>
            <div class='icon'>
              <img src='img/achieivement_card.png' alt='achieivement_card' />
            </div>
    
            <div class='logos'>
              <img src='<?= $current_user->login->img ?>' alt='base' />
              <div class='card-details'>
                <p class='campaign'><?= $subject->subject_name ?></p>
                <p class='task'><?= $task->task ?></p>
                <p class='miles'>
                  <span><?= number_format( $task->points ) ?> Mile<?= $task->points > 1 ? 's' : '' ?></span>
                </p>
              </div>
              <img src='<?= $subject->logoPath() ?>' alt='campaign' id='campaign' />
            </div>
    
            <div class='barcode'>
              <img src="data:image/png;base64, <?= base64_encode( $barcode ); ?>">
            </div>
            <div class='barcode-text'>
              <?= $card->card_serial ?>
            </div>
          </div>
        </div>
      <?php
    } // end while loop for each card
    ?>
</body>
</html>

