<?
class Tehillim {
    private $tehillim;
    private $kapitels;
    
    public function __construct() {
        $this->tehillim = array();
        $this->kapitels = array();
    }
    
    public function setTehillim() {
        $sql = "select * from tehillim order by kapitel";
        $result = mysql_query( $sql );
        if ( mysql_num_rows( $result ) == 0 ) {
            $this->initTehillimDB();
            $this->setTehillim();
        }
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $this->tehillim[] = $row;
        }
    }
    
    public function getTehillim() {
        return $this->tehillim;
    }
    
    public function setKapitels() {
        $sql = "select * from kapitels order by kapitel, posuk";
        $result = mysql_query( $sql );
        if ( mysql_num_row( $result ) == 0 ) {
            $this->initKapitelsDB();
            $this->setKapitels();
        }
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $this->kapitels[] = $row;
        }
    }
    
    public function getKapitels() {
        return $this->kapitels;
    }
    
    public function initTehillimDB() {
        for ( $i = 1; $i <= 150; $i++ ) {
            $sql = "insert into tehillim values( $i, 0 )";
            mysql_query( $sql );
        }
    }
    
    public function initKapitelsDB() {
        $sql = "select * from tehillim where pesukim > 0 order by kapitel";
        $result = mysql_query( $sql );
        if ( mysql_num_rows( $result ) == 0 ) {
            return false;
        } else {
            while ( $row = mysql_fetch_assoc( $result ) ) {
                $kapitel = $row['kapitel'];    
                $num = $row['pesukim'];
                $i = 1; 
                do {
                    $sql = "insert into kapitels values( $kapitel, $i, 0 )";
                    mysql_query( $sql );
                } while ( $i++ <= $num );               
            }
        }
    }
}
?>