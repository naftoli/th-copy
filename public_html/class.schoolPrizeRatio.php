<?
class SchoolPrizeRatio {
    private $ratio;
    
    public function __construct() {
        //key = number of times to win during year
        //value = how many children need to be in the school to go to next number
        $this->ratio = array( 
            2   =>  1, 
            4   =>  51, 
            6   =>  101, 
            12  =>  201, 
            18  =>  301, 
            24  =>  401, 
            30  =>  501, 
            36  =>  601, 
            42  =>  701, 
            48  =>  801, 
            54  =>  901
        );
    }

    public function getRatio( $num ) {
        $low = 0; 
        foreach ( $this->ratio as $key => $value ) {
            if ( $num >= $value ) {
                $low = $key;
            } else {
                break;
            }
        }
        return $low;        
    }
}
?>