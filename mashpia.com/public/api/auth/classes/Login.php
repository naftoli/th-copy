<?php
namespace mashpia\api\auth;

require_once( __DIR__ . '/Auth.php' );

class Login implements \JsonSerializable {

    public $id = false; // the ID for the login to be sent on the wire.
    public $code = 'BLANK'; // code to use in interface
    public $type = false; // the type of login that this is

    public $model; // model that the login refers to
    public $modules;

    private $name; // The name displayed to the user
    private $img; // the icon to accompany it
    
    private $active; // Is this login active?
    private $legacy; // Does this login have access to legacy systems?

    public $inst_id = false; // cache the inst id
    public $school_id = false; // cache the school id
    public $class_id = false; // cache the class id

    public $role = false;

    const LEGACY_ID = 2; // School institution

    /**
     * new Login( $type, $id[, $model ] )
     * @param $type string of login type
     * @param $id string or number to find the model
     * @param $model ( optional ) provide prefetched model.
     * ! Throws \ActiveRecord\RecordNotFound if model cannot be found
     */
    public function __construct( $type = 'BLANK', $id = false, $model = false ) {
        global $current_user;
        $this->type = $type;
        // set the id or fallback to the current user
        $this->id = $id ? $id : ( $current_user ? $current_user->admin_id : false );
        // set the model
        if ( $model ) {
            $this->model = $model;
        } else {
            $this->setModel();
        }
        // setup the details
        $this->setup();
        $this->setModules();
    }

    public function getFilter( $school_key = 's.', $class_key = 'c.' ){
        if ( $this->type == 'HQ' )
            return $school_key.'test_school = 0';
        if ( $this->type == 'institution' )
            return $school_key.'inst_id = '. $this->inst_id;
        if ( $this->type == 'school' )
            return $school_key.'school_id = '. $this->school_id;
        if ( $this->type == 'class' )
            return $class_key.'class_id = '. $this->class_id;
        return false;
    }

    /**
     * setModel
     * * Sets the model for the login based on the id.
     */
    private function setModel() {
        if ( $this->type == 'institution' ) {
            $this->model = \Institution::find([ $this->id ]);
        } else if ( $this->type == 'school' ) {
            $this->model = \School::find([ $this->id ]);
        } else if ( $this->type == 'class' ) {
            $this->model = \Platoon::find([ $this->id ], ['include' => ['school']] );
        } else if ( $this->id ) {
            $this->model = \Admin::find([ $this->id ]);
        }
    }

    /**
     * setup
     * 
     * sets the internal variables based on the internal model
     */
    private function setup() {
        if ( !$this->model )
            return false;
        // Tzivos Hashem Offices
        if ( $this->type == 'HQ' ) {
            $this->name = 'Tzivos Hashem Headquarters';
            $this->img = '/mobile/img_new/TH Logo-colorful-svg.svg';
            $this->code = 'HQ';
            $this->role = 'Headquarters';

            $this->active = true;
            $this->legacy = true;
        // Affiliated institutions
        } else if ( $this->type == 'institution' ) {
            $this->name = $this->model->name;
            $this->img = $this->model->logo();
            $this->code = 'INST';
            $this->role = 'Institution Manager';

            $this->active = true;
            $this->legacy = $this->model->inst_id === self::LEGACY_ID;
            
            $this->inst_id = $this->model->inst_id;
        // Tzivos Hashem Bases
        } else if ( $this->type == 'school' ) {
            $this->name = $this->model->name;
            $this->img = $this->model->logoPath();
            $this->code = 'BC';
            $this->role = 'Base Commander';

            $this->active = is_null( $this->model->school_era );
            $this->legacy = $this->model->inst_id === self::LEGACY_ID;

            $this->inst_id = $this->model->inst_id;
            $this->school_id = $this->model->school_id;
        // Tzivos Hashem Platoons
        } else if ( $this->type == 'class' ) {
            $this->name = $this->model->name();
            $this->img = $this->model->school->logoPath();
            $this->code = 'TEACHER';
            $this->role = 'Teacher';

            $this->active = is_null( $this->model->school->school_era );
            $this->legacy = $this->model->school->inst_id === self::LEGACY_ID;

            $this->inst_id = $this->model->school->inst_id;
            $this->school_id = $this->model->school_id;
            $this->class_id = $this->model->class_id;
        // Tzivos Hashem Parents
        } else if ( $this->type == 'PARENT' ) {
            $this->name = 'My Parent Portal';
            $this->img = '/mobile/img_new/TH Logo-colorful-svg.svg';
            $this->code = 'PARENT';
            $this->role = 'Parent';

            $this->active = true;
            $this->legacy = true;

            $this->key = Auth::mobileKey( $this->model->admin_id );
        } else {
            $this->name = 'Select Account Type';
            $this->img = '/mobile/reg/images/boy.png';
            $this->code = 'BLANK';

            $this->active = false;
            $this->legacy = false;

            $this->key = Auth::mobileKey( $this->model->admin_id );
        }
    }

    /**
     * setModules
     * 
     * sets the internal variables based on the internal model
     */
    private function setModules() {
        if ( !$this->model )
            return $this->modules = [
                'chayolei' => false, 'chidon' => false,
                'tehillim' => false, 'tanya' => false,
                'rewards'  => false
            ];
        // Tzivos Hashem Offices and Institutions have access to everything
        if ( in_array( $this->type, [ 'HQ', 'institution' ] ) )
            $this->modules = [
                'chayolei' => true, 'chidon' => true,
                'tehillim' => true, 'tanya' => true,
                'rewards'  => true
            ];
        // Tzivos Hashem Bases - get what they pay for
        else if ( $this->type == 'school' )
            $this->modules = [
                'chayolei' => !!$this->model->chayolei, 'chidon' => !!$this->model->chidon,
                'tehillim' => !!$this->model->tehillim, 'tanya' => !!$this->model->tanya,
                'rewards'  => !!$this->model->rewards
            ];
        // Tzivos Hashem Platoons - get what their base paid for
        else if ( $this->type == 'class' ) {
            $school = $this->model->school;
            $this->modules = [
                'chayolei' => !!$school->chayolei, 'chidon' => !!$school->chidon,
                'tehillim' => !!$school->tehillim, 'tanya' => !!$school->tanya,
                'rewards'  => !!$school->rewards
            ];
        }
    }
    
    /**
     * jsonSerialize
     * 
     * required by implements JsonSerializable.
     * run when calling json_encode
    */
    public function jsonSerialize() {
        $res = [
            'type' => $this->type,          'id' => $this->id,
            'code' => $this->code,          'name' => $this->name,
            'img' => $this->img,            'legacy' => $this->legacy,
            'active' => $this->active,      'school_id' => $this->school_id,
            'inst_id' => $this->inst_id,    'class_id' => $this->class_id,
            'modules' => $this->modules,    'role'  => $this->role
        ];
        // add the login key for parent accounts to the parent portal
        if ( $this->type == 'PARENT' )
            $res['key'] = Auth::mobileKey( $this->model->admin_id );

        return $res;
    }
}
