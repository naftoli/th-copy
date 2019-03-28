<?php
class Communities {
  private $communities;

  public function __construct() {
    $this->communities = [
      'Baltimore'           => [ 81 ],
      'Monsey'              => [ 49,192 ],
      'Toronto'             => [ 106,45,474 ],
      'Montreal'            => [ 2,58,584 ],
      'Crown Heights'       => [ 30,54,7,255,542,9,194,33,13,471 ],
      'Los Angeles'         => [ 162,4,517 ],
      'Chicago'             => [ 5,50 ],
      'Seattle'             => [ 263 ],
      'New Jersey'          => [ 21,37 ],
      'North Brunswick NJ'  => [ 60 ],
      'Miami'               => [ 19,42,434 ],
      'Pittsburgh'          => [ 11,40 ],
      'Vancouver'           => [ 427 ],
      'Long Beach'          => [ 87 ],
      'Houston'             => [ 84 ],
      'Minnesota'           => [ 39 ],
      'London'              => [ 265,3,432 ],
      'Sydney'              => [ 110,55 ],
      'Melbourne'           => [ 112,66 ],
      'Postville'           => [ 176 ],
      'New Haven'           => [ 105,577 ],
      'Long Island'         => [ 63 ],
      'Buffalo'             => [ 48 ],
      'Arizona'             => [ 470 ],
      'Philadelphia'        => [ 89 ],
      'Wilkes Barre'        => [ 86 ],
      'Milwaukee'           => [ 80 ],
      'Margate'             => [ 185 ],
      'Oxnard'              => [ 412 ],
      'Atlanta'             => [ 430 ],
      'San Diego'           => [ 544 ],
      'Shluchim'            => [ 61 ],
      'Anash'               => [ 269 ],
      'Brooklyn Heights'    => [ 448 ],
      'Coconut Creek'       => [ 554 ],
      'Tampa'               => [ 483 ],
      'South Africa'        => [ 180 ],
      'Albany'              => [ 220 ],
      'Poconos'             => [ 583 ],
      'Boro Park'           => [ 588 ],
      'Boston'              => [ 482 ]
    ];
  }

  public function getCommunities() {
    return $this->communities;
  }
}