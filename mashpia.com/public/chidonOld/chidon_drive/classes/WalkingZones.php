<?php
require_once __DIR__ . '/../../../api/header/db.php';

class WalkingZones {
  private $db;

  public function __construct() {
    global $MASHPIA_DB;
    $this->db = $MASHPIA_DB;
  }

  public function getStreets() {
    $res = $this->db->query("
      SELECT 
          street
      FROM
          chidon_walking_zones
      GROUP BY street
    ");
    return $res->fetchAll();
  }

  public function getCrossStreets( $street, $number ) {
    // figure out if number is even or odd
    if ( $number % 2 == 0 ) {
      $qry = "
        SELECT 
            *
        FROM
            chidon_walking_zones
        WHERE
            street = :street
                AND even_start <= :number
                AND even_end >= :number
        GROUP BY cross1 , cross2
      ";
    } else {
      $qry = "
        SELECT 
            *
        FROM
            chidon_walking_zones
        WHERE
            street = :street
                AND odd_start <= :number
                AND odd_end >= :number
        GROUP BY cross1 , cross2
      ";
    }
    $stmt = $this->db->prepare( $qry );
    $res = $stmt->execute([
      ':street' =>  $street, 
      ':number' =>  $number
    ]);
    if ( $res ) {
      $rows = $stmt->fetch();
      if ( empty( $rows ) ) {
        return "There's no such address in our system. Either the Street Number or Street Name are incorrect. You won't be able to submit the form until it's fixed.";
      } else {
        return $rows;
      }
    } else {
      return "Error retreiving cross streets.";
    }
  }
}