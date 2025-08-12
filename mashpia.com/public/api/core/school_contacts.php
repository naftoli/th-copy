    <?php
    define( "MASHPIA_AUTH_REQUIRED", true );
    include_once( __DIR__ . "/../header/header.php" );

    class ContactsRouter {

        public function index() {
            global $current_user; global $MASHPIA_DB;

            $sql = "SELECT * FROM schools WHERE school_id = :school_id";
            $query = $MASHPIA_DB->prepare( $sql );
            $school_id = array_filter($current_user->logins(), function($login) {
                return $login->type === 'school';
            })[0]->id;
            $query->execute( [ 'school_id' => $school_id ] );
            if ($query->errorCode() !== "00000") {
                json_error("SQL Error: ".implode(', ', $query->errorInfo()), false, 500);
            }
            $info = [];
            $row = $query->fetch();
            $info = [
                'principal' => [
                    'id' => $row['school_id'],
                    'name' => $row['principal'], 
                    'email' => $row['principal_email'],
                    'phone' => $row['principal_number'],
                    'grades' => $row['principal_grades']
                ],
                'chidon' => [
                    'id' => $row['school_id'],
                    'name' => $row['chidon_name'],
                    'email' => $row['chidon_email'],
                    'phone' => $row['chidon_number'], 
                    'is_bc' => $row['chidon_also_bc']
                ], 
                'bc' => [
                    'id' => $current_user->admin_id,
                    'name' => $current_user->name(),
                    'email' => $current_user->admin_email,
                    'phone' => $current_user->admin_phone_mobile, 
                ]
            ];

            // get extra principals
            $sql = "SELECT * FROM extra_principals WHERE school_id = :school_id";
            $query = $MASHPIA_DB->prepare( $sql );
            $query->execute( [ 'school_id' => $school_id ] );
            if ($query->errorCode() !== "00000") {
                json_error("SQL Error: ".implode(', ', $query->errorInfo()), false, 500);
            }
            $extra_principals = $query->fetchAll();
            foreach( $extra_principals as $extra_principal ) {
                $info['extra_principals'][] = [
                    'id' => $extra_principal['extra_principal_id'],
                    'name' => $extra_principal['name'],
                    'email' => $extra_principal['email'],
                    'phone' => $extra_principal['phone'], 
                    'grades' => $extra_principal['grades']
                ];
            }

            json_response( $info );
        }

        public function create() {
            global $current_user; global $MASHPIA_DB;

            $school_id = $_POST['school_id'];
            $extra_principals = $_POST['extra_principals'];

            $sql = "INSERT INTO extra_principals 
                (name, email, phone, grades, school_id) 
                VALUES 
                (:name, :email, :phone, :grades, :school_id)";
            $query = $MASHPIA_DB->prepare( $sql );

            foreach ( $extra_principals as $extra_principal ) {
                $query->execute( [ 
                    'name' => $extra_principal['name'], 
                    'email' => $extra_principal['email'], 
                    'phone' => $extra_principal['phone'], 
                    'grades' => $extra_principal['platoons'], 
                    'school_id' => $school_id 
                ] );

                if ($query->errorCode() !== "00000") {
                    json_error("Error creating Extra Principal");
                }
            }

            json_response( false );
        }

        public function update() {
            global $MASHPIA_DB;

            $bc = $_POST['bc'];
            $chidon = $_POST['chidon'];
            $principal = $_POST['principal'];
            $extra_principals = $_POST['extra_principals'];

            $MASHPIA_DB->beginTransaction();

            // update bc
            $bc_name = $bc['name'];
            // split the name into first and last
            $bc_name = explode( ' ', $bc_name );
            foreach ( $bc_name as $idx => $name ) {
                if ($idx != count($bc_name) - 1) {
                    $bc_first .= $name . ' ';
                } else {
                    $bc_last = $name;
                }
            }

            $sql = "UPDATE admins SET 
                        first = :first, 
                        last = :last, 
                        admin_email = :admin_email, 
                        admin_phone_mobile = :admin_phone_mobile 
                    WHERE admin_id = :admin_id";
            $query = $MASHPIA_DB->prepare( $sql );
            $query->execute( [ 
                'first' => $bc_first, 
                'last' => $bc_last, 
                'admin_email' => $bc['email'], 
                'admin_phone_mobile' => $bc['phone'], 
                'admin_id' => $bc['id'] 
            ] );

            if ($query->errorCode() !== "00000") {
                $MASHPIA_DB->rollBack();
                json_error("Error updating Base Commander's info");
            }

            // update schools table
            $sql = "
                UPDATE schools SET 
                    principal = :principal, 
                    principal_email = :principal_email, 
                    principal_number = :principal_number, 
                    principal_grades = :principal_grades, 
                    chidon_name = :chidon_name, 
                    chidon_email = :chidon_email, 
                    chidon_number = :chidon_number, 
                    chidon_also_bc = :chidon_also_bc 
                WHERE school_id = :school_id";
            $query = $MASHPIA_DB->prepare( $sql );
            $query->execute( [ 
                'principal' => $principal['name'], 
                'principal_email' => $principal['email'], 
                'principal_number' => $principal['phone'], 
                'principal_grades' => $principal['grades'], 
                'chidon_name' => $chidon['name'], 
                'chidon_email' => $chidon['email'], 
                'chidon_number' => $chidon['phone'], 
                'chidon_also_bc' => $chidon['is_bc'], 
                'school_id' => $chidon['id'] 
            ] );
            // $query->debugDumpParams();

            if ($query->errorCode() !== "00000") {
                $MASHPIA_DB->rollBack();
                json_error("Error updating Contacts");
            }

            // update extra principals
            foreach( $extra_principals as $extra_principal ) {
                $sql = "UPDATE extra_principals SET 
                    name = :name, 
                    email = :email, 
                    phone = :phone, 
                    grades = :grades 
                    WHERE extra_principal_id = :id";
                $query = $MASHPIA_DB->prepare( $sql );
                $query->execute( [ 
                    'id' => $extra_principal['id'],
                    'name' => $extra_principal['name'], 
                    'email' => $extra_principal['email'], 
                    'phone' => $extra_principal['phone'], 
                    'grades' => $extra_principal['grades'],  
                ] );
                if ($query->errorCode() !== "00000") {
                    $MASHPIA_DB->rollBack();
                    json_error("Error updating Extra Principal");
                }
            }

            $MASHPIA_DB->commit();
            return json_response( $_POST );
        }
    }

    rest_router( new ContactsRouter );
