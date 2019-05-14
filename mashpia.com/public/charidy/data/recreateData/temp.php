<?php
ini_set('display_errors',1);
$json = '
{
  "children": [],
  "donor_id": "8312",
  "parent_id": null,
  "phone_number": "9174885613",
  "address": "572 Montgomery St",
  "donation_last_yr": "100.00",
  "rank_last_yr": "Private",
  "donation_this_yr": "126",
  "rank_this_yr": "Sergeant",
  "total_donation_amount": 180,
  "dedication_text": "In honor of ",
  "dedication_name": "Anonymous",
  "date_time": "2018-05-01T18:28:07.717Z",
  "amount": 180
},
{
  "children": [
    {
      "user_id": "17126",
      "name": "Rochel Mochkin",
      "school": "MyShliach",
      "school_id": 61,
      "picture": "https://mashpia.com/mobile/reg/",
      "amount": 0
    },
    {
      "user_id": "19326",
      "name": "Yossi Mochkin",
      "school": "MyShliach",
      "school_id": 61,
      "picture": "https://mashpia.com/mobile/reg/img/20180418010044.png",
      "amount": 0
    },
    {
      "user_id": "52693",
      "name": "Shmuel Mochkin",
      "school": "MyShliach",
      "school_id": 61,
      "picture": "https://mashpia.com/mobile/reg/img/20170918001401.png",
      "amount": 0
    }
  ],
  "donor_id": "35",
  "parent_id": "222",
  "phone_number": "5854614679",
  "address": "34 greenwich ln",
  "donation_last_yr": "18.00",
  "rank_last_yr": "Private",
  "donation_this_yr": "126",
  "rank_this_yr": "Sergeant",
  "total_donation_amount": 18,
  "dedication_text": "Dedicated to our Soldiers! Mochkins,( Roch, Montreal, FL, Little Rock) Labkowski\'s, Singers, & Beguns ",
  "date_time": "2018-05-01T18:32:04.630Z",
  "amount": 18
},
{
  "children": [
    {
      "user_id": "17863",
      "name": "Chaiky Bryski",
      "school": "Bnos Menachem Crown Heights",
      "school_id": 7,
      "picture": "https://mashpia.com/mobile/reg/img/DSC01353.JPG",
      "amount": 5
    },
    {
      "user_id": "22628",
      "name": "Mimi Bryski",
      "school": "Bnos Menachem Crown Heights",
      "school_id": 7,
      "picture": "https://mashpia.com/mobile/reg/img/Bryski.JPG",
      "amount": 5
    },
    {
      "user_id": "54107",
      "name": "Mendel Bryski",
      "school": "Oholei Torah Crown Heights",
      "school_id": 255,
      "picture": "https://mashpia.com/mobile/reg/img/20180218213944.png",
      "amount": 5
    }
  ],
  "donor_id": "797",
  "parent_id": "71328",
  "phone_number": "9175193491",
  "address": "1006 east new york ave",
  "donation_last_yr": "75.00",
  "rank_last_yr": "Private",
  "donation_this_yr": "126",
  "rank_this_yr": "Sergeant",
  "total_donation_amount": 90,
  "dedication_text": "Keep up the amazing work, preparing all the Chayolim and bringing Moshiach NOW!",
  "dedication_name": "Yossi Bryski",
  "date_time": "2018-05-01T18:16:03.125Z",
  "amount": 75
},
{
  "children": [],
  "donor_id": "8049",
  "parent_id": null,
  "phone_number": "17187746398",
  "address": "",
  "name": "Jacob Goldstein",
  "donation_last_yr": 321,
  "rank_last_yr": "Second Lieutenant",
  "donation_this_yr": "504",
  "rank_this_yr": "First Lieutenant",
  "amount": 18,
  "total_donation_amount": 18,
  "dedication_text": "",
  "dedication_name": "David Meyer",
  "date_time": "2018-05-03T14:30:37.066Z"
},
{
  "children": [],
  "donor_id": "0",
  "parent_id": "0",
  "phone_number": "",
  "address": "",
  "donation_last_yr": 0,
  "donation_this_yr": 0,
  "rank_last_yr": "",
  "rank_this_yr": "",
  "total_donation_amount": 400,
  "dedication_text": "",
  "dedication_name": "Abe Diamond",
  "date_time": "2018-05-01T18:17:41.773Z",
  "amount": 400
},
{
  "children": [
    {
      "user_id": "19955",
      "name": "Chana Klein",
      "school": "Lubavitch Educational Center Florida Girls",
      "school_id": 42,
      "picture": "https://mashpia.com/mobile/reg/img/IMG_7275.JPG",
      "amount": 5
    },
    {
      "user_id": "23302",
      "name": "Menachem Klein",
      "school": "Lubavitch Educational Center Florida Boys",
      "school_id": 19,
      "picture": "https://mashpia.com/mobile/reg/",
      "amount": 5
    }
  ],
  "donor_id": "1799",
  "parent_id": "150332",
  "phone_number": "3474044115",
  "address": "4347 nw 69th terrace ",
  "donation_last_yr": 0,
  "rank_last_yr": 0,
  "donation_this_yr": "126",
  "rank_this_yr": "Sergeant",
  "total_donation_amount": 10,
  "dedication_text": "",
  "date_time": "2018-05-01T18:12:31.926Z",
  "amount": 0
},
{
  "children": [],
  "donor_id": "0",
  "parent_id": "0",
  "phone_number": "",
  "address": "",
  "donation_last_yr": 0,
  "donation_this_yr": 0,
  "rank_last_yr": "",
  "rank_this_yr": "",
  "total_donation_amount": 400,
  "dedication_text": "LI\"N\nNochum Ben Freida\nSara Relka Bas Miriam\nChaim Shneur Zalman Ben Tzeita\nChana Chaya Bas Golda Ita\n"
}';
//header('Content-type: application/json');
//echo $json;

$info = [];
require_once '../../../db.php';
$sql = "select * from charidy_temp_data";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $info[$row['id']] = $row['data'];
}

foreach ( $info as $id => $json ) {
  $donation = json_decode( $json );
  if ( $donation ) {
    $name = isset( $donation->name ) ? $donation->name : '';
    $address = isset( $donation->address ) ? $donation->address : '';
    $phone = isset( $donation->phone ) ? $donation->phone : isset( $donation->phone_number ) ? $donation->phone_number : '';
    echo "ID: " . $id . "<br />";
    echo "Name: " . $name . "<br />";
    echo "Phone: " . $phone . "<br />";
    echo "Address: " . $address . "<br />";
    echo "Amount: " . $donation->amount . "<br /><br />";
    if ( !empty( $donation->children ) ) {
      echo "Children Amounts:<br /><br />";
      foreach ( $donation->children as $child ) {
        echo "User ID: " . $child->user_id . "<br />";
        echo "Name: " . $child->name . "<br />";
        echo "Amount: " . $child->amount . "<br /><br />";
      }
    } 
  }
}