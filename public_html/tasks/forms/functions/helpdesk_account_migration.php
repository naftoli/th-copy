<?
// load_admins => loads up the admins from our database and the tickets DBS
function load_admins($admin_id = false){
    $result = ['admins' => [], 'portal' => []];
    // basic select SQL
    $admin_sql = "SELECT admin_id, first, last, username, password, admin_email FROM admins "; // get the id, username, password, and email address...
    if($admin_id){ // if we have an admin id just get that one
        $admin_sql .= "WHERE admin_id = '$admin_id'";
    } else { // otherwise load up all the admins
        $admin_sql .= "JOIN admin_auths aa USING (admin_id) WHERE admin_email != '' AND first != '' " // get superusers and BC's With email addresses
            ."GROUP BY admin_email ORDER BY first, last"; // make sure the emails are unique
    }
    // run the generated query
    $admin_query = mysql_query($admin_sql);
    while($row = mysql_fetch_assoc($admin_query)){
        if(mswIsValidEmail($row['admin_email'])){
            $result['admins'][$row['admin_email']] = $row;
        }
    }
    
    $protal_emails_query = mysql_query("SELECT id, email FROM tickets.msp_portal");
    while($row = mysql_fetch_assoc($protal_emails_query)){
        $result['portal'][$row['email']] = $row;
    }
    
    if(count($result['admins']) == 0){
        return_json_error("No Admins Found");
    }
    
    return $result;
}

// create_admin => creates the admin in the DBS
function create_admin($admin) {
    $name = $admin['first']. " " . $admin['last'];
    $ts = time();
    $email = $admin['admin_email'];
    $userPass = mswEncrypt(SECRET_KEY . $admin['password']);
    $timezone = "US/Eastern";
    $language = "english";
    // create the new user...
    $sql = "INSERT INTO tickets.msp_portal (name, ts, email, userPass, verified, timezone, language) "
                ."VALUES ('$name', '$ts', '$email', '$userPass', 'yes', '$timezone', '$language')";
    
    $query = mysql_query($sql); // run the query
    
    return !!$query;
}

// create_admin => updates the password for the given portal ID
function update_password($password, $portal_id){
    $userPass = mswEncrypt(SECRET_KEY . $password);
    
    $sql = "UPDATE tickets.msp_portal SET userPass='$userPass' WHERE id='$portal_id'";
    
    $query = mysql_query($sql); // run the query
    
    return !!$query;
}

// refresh_admin => refreshes the admin password with a given ID
function refresh_admin($admin_id){
    $admin_info = load_admins($admin_id);
    
    $admin = current($admin_info['admins']);
    $portal = isset($admin_info['portal'][$admin['admin_email']]);
    
    if(!$portal){
        $status = create_admin($admin);
    } else {
        $status = update_password($admin['password'], $admin_info['portal'][$admin['admin_email']]['id']);
    }
    
    if(!$status){
        return_json_error("Could not ".($portal ? "update" : "create")." this account at this time.");
    }
    echo json_encode(["success" => $status]);
}

function refresh_all_admins(){
    $admin_info = load_admins();
    
    $success = [];
    $failure = [];
    
    foreach($admin_info['admins'] as $email => $admin){
        // make sure that we have a portal account
        if(!isset($admin_info['portal'][$email])){
            continue;
        }
        $portal = $admin_info['portal'][$email];
        
        if(update_password($admin['password'], $portal['id'])){
            $success[] = $admin['admin_id'];
        } else {
            $failure[] = $admin['admin_id'];
        };
    }
    // if there was a failure
    if(count($failure) > 0) {
        return_json_error(count($failure)."/".(count($failure) + count($success))." Accounts failed to update."); // let the user know
    };
    // otherwise all is good here
    echo json_encode(["success" => true, "updated_accounts" => $success]);
}

function create_admins(){
    $admin_info = load_admins();
    
    $success = [];
    $failure = [];
    
    foreach($admin_info['admins'] as $email => $admin){
        // make sure that we do not have a portal account
        if(isset($admin_info['portal'][$email])){
            continue;
        }
        
        if(create_admin($admin)){
            $success[] = $admin['admin_id'];
        } else {
            $failure[] = $admin['admin_id'];
        };
    }
    // if there was a failure
    if(count($failure) > 0) {
        return_json_error(count($failure)."/".(count($failure) + count($success))." Accounts failed to update."); // let the user know
    };
    // otherwise all is good here
    echo json_encode(["success" => true, "updated_accounts" => $success]);
}
