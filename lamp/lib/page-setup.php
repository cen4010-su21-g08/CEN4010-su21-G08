<?php
    define("app_page", true);

    $is_logged_in = false;

    require_once('lib/database.php');
    require_once('lib/functions.php');
    require_once('lib/authentication.php');

    session_start();
    if (isset($_SESSION['user_id'])) {
        $is_logged_in = true;
    }

    if (isset($auth_needed) && ($auth_needed == false)) {
        
    } else {
        if (!$is_logged_in) {
            header("Location: signin.php?r");
        }
    }

    

