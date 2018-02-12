<?php
class NewAuction
{
  private $auction_id;
  private $tickets;
  private $winners;
  private $missing;
  private $errors;
  private $usersWon;
  
  public function __construct($id) {
    $this->auction_id = $id;
    $this->usersWon = array();
    $this->missing = array();
  }
  
  function setTickets($prize, $school, $user = 0) {
    $this->tickets = array();
    $sql = "select user_id, prize_id, quantity, school_id 
            from auction_user_prizes aup
            join users u using (user_id)
            where aup.auction_id = " . $this->auction_id . "
            and u.school_id = " . $school . "
            and aup.prize_id = " . $prize;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
      //make sure user hasn't already won
      //$sqlWon = "select * from auction_winners where auction_id = " . $this->auction_id . " and user_id = " . $row['user_id'];
      //$resWon = mysql_query($sqlWon);
      //if (mysql_num_rows($resWon) > 0) continue;
      if ($row['user_id'] == $user) continue;
      $num = $row['quantity'];
      for ($i = 0; $i < $num; $i++) {
          $this->tickets[$row['prize_id']][] = $row['user_id'];
      }
    }
  }
  
  public function setWinner($prize, $school = 0) {
    // make sure we have available users left
    if (isset($this->tickets[$prize]) && count($this->tickets[$prize])) {
      // get random index
      $numTickets = count($this->tickets[$prize]);
      $index = rand(0, ($numTickets-1));
      if (key_exists($index, $this->tickets[$prize])) {
        $winner = $this->tickets[$prize][$index];
      } else {
        echo "<pre>"; print_r($this->tickets[$prize]); echo "</pre>"; exit;
      }
      if (in_array($winner, $this->usersWon)) {
        // remove user from tickets
        $keys = array_keys($this->tickets[$prize], $winner);
        foreach ($keys as $key) {
          unset($this->tickets[$prize][$key]);
        }
        $this->tickets[$prize] = array_values($this->tickets[$prize]);
        $this->setWinner($prize, $school);
      } else {
        if ($school > 0) {
          $this->winners[$prize] = $winner;
        } else {
          $this->winners[$prize][] = $winner;
        }
        $this->usersWon[] = $winner;
        
        if (!$this->saveWinner($winner, $prize)) {
          $this->errors[] = "Error saving user: " . $winner . " for prize number: " . $prize;
        }
        
      }
    } else {
      //echo "<pre>"; print_r($this->tickets[$prize]); echo "</pre>"; exit;
      if ($school > 0) {
        if (isset($this->missing[$prize]) && !in_array($school, $this->missing[$prize]))
            $this->missing[$prize][] = $school;
      } else {
        if (isset($this->missing[$prize])) $this->missing[$prize]++;
        else $this->missing[$prize] = 1;
      }
    }
  }
  
  function setTicketsMulti($prize, $schools) {
    $temp = array();
    foreach ($schools as $school => $qty) {
      $temp[] = $school;
    }
    $this->tickets = array();
    $sql = "select user_id, prize_id, quantity, school_id 
            from auction_user_prizes aup
            join users u using (user_id)
            where aup.auction_id = " . $this->auction_id . "
            and u.school_id in (" . implode(',', $temp) . ") 
            and aup.prize_id = " . $prize;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
      //make sure user hasn't already won
      //$sqlWon = "select * from auction_winners where auction_id = " . $this->auction_id . " and user_id = " . $row['user_id'];
      //$resWon = mysql_query($sqlWon);
      //if (mysql_num_rows($resWon) > 0) continue;
      $num = $row['quantity'];
      for ($i = 0; $i < $num; $i++) {
          $this->tickets[$row['prize_id']][$row['school_id']][] = $row['user_id'];
      }
    }
  }
  
  public function setWinners($prize, $schools) {
    foreach ($schools as $school => $qty) {
      do {
        // make sure we have available users left
        if (isset($this->tickets[$prize][$school]) && count($this->tickets[$prize][$school])) {
          // get random index
          $numTickets = count($this->tickets[$prize][$school]);
          $index = rand(0, ($numTickets-1));
          if (key_exists($index, $this->tickets[$prize][$school])) {
            $winner = $this->tickets[$prize][$school][$index];
          } else {
            echo "<pre>"; print_r($this->tickets[$prize][$school]); echo "</pre>"; exit;
          }
          $this->winners[$prize][$school] = $winner;
          //$this->usersWon[] = $winner;
          
          if (!$this->saveWinner($winner, $prize)) {
            $this->errors[] = "Error saving user: " . $winner . " for prize number: " . $prize;
          }
          
        } else {
          //echo "<pre>"; print_r($this->tickets[$prize]); echo "</pre>"; exit;
          if (isset($this->missing[$prize]) && !in_array($school, $this->missing[$prize]))
              $this->missing[$prize][] = $school;
        }
      } while (--$qty > 0);
    }
  }
  
  public function saveWinner($winner, $prize) {
    $sql = "insert into auction_winners
            set quantity = 1, 
            user_id = " . $winner . ",
            prize_id = " . $prize . ", 
            auction_id = " . $this->auction_id;
    if (mysql_query($sql)) {
      return true;
    } else {
      echo $sql . "<br />";
      return false;
    }
  }
  
  public function run($info) {
    foreach ($info as $prize => $school) {
      $this->setTickets($prize, $school);
      $this->setWinner($prize, $school);      
    }
    
    if (count($this->errors) > 0) {
        echo "Showing errors...<br />";
        foreach ($this->errors as $error) echo $error . "<br />";
    }
    
    if (count($this->missing) > 0) {
        echo "Prizes which do not have any elligible winners...<br />";
        foreach ($this->missing as $msg) echo $msg . "<br />";
    }
    
    echo "<pre>"; print_r($this->winners); echo "</pre>";
    
    /*
    $this->missing = array();
    if (count($schools)) {
      $this->schoolUsers = array();
      $this->usersSchool = array();
    }
    echo "Setting prizes...<br />";
    $this->setPrizes($prizes);
    //print_r($this->prizes);
    
    echo "Setting tickets...<br />";
    $this->setTickets($schools);
    //print_r($this->tickets);
    
    echo "Setting winners...<br />";
    $this->setWinners();
    if (count($this->errors) > 0) {
        echo "Showing errors...<br />";
        foreach ($this->errors as $error) echo $error . "<br />";
    }
    
    if (count($this->missing) > 0) {
        echo "Prizes which do not have any elligible winners...<br />";
        foreach ($this->missing as $msg) echo $msg . "<br />";
    }
    //echo "<pre>"; print_r($this->winners); echo "</pre>";
    */
  }
  
  public function runLast($info) {
    foreach ($info as $prize => $schools) {
      $this->setTicketsMulti($prize, $schools);
      $this->setWinners($prize, $schools);
    }
    foreach ($this->missing as $prize => $schools) {
      echo "Couldn't assign " . $prize . " to:<br />";
      foreach ($schools as $school) {
        echo $school . "<br />";
      }
    }
    echo "<pre>"; print_r($this->winners); echo "</pre>";
  }
  
  public function setTicketsAll($prize) {
    $sql = "select user_id, prize_id, quantity, school_id 
            from auction_user_prizes aup
            join users u using (user_id)
            where aup.auction_id = " . $this->auction_id . "
            and aup.prize_id = " . $prize;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
      $num = $row['quantity'];
      for ($i = 0; $i < $num; $i++) {
          $this->tickets[$row['prize_id']][] = $row['user_id'];
      }
    }
  }
  
  public function findWinners($prize, $qty) {
    do {
      $this->setWinner($prize);
    } while (--$qty > 0);
  }
  
  public function runMegaLast($info) {
    foreach ($info as $prize => $qty) {
      $this->setTicketsAll($prize);
      $this->findWinners($prize, $qty);
    }
    echo "<pre>";
    print_r($this->missing);
    print_r($this->winners);
    echo "</pre>";
  }
  
  public function fix($info) {
    foreach ($info as $prize => $user) {
      $sql = "select school_id from users where user_id = " . $user;
      $result = mysql_query($sql);
      $row = mysql_fetch_assoc($result);
      $school = $row['school_id'];
      $this->setTickets($prize, $school, $user);
      $this->setWinner($prize, $school);
    }
    echo "<pre>";
    print_r($this->missing);
    print_r($this->winners);
    echo "</pre>";
  }
}
?>