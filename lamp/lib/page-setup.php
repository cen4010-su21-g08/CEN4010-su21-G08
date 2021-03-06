<?php
    define("app_page", true);

    $is_logged_in = false;
    $user = null;

    require_once('lib/database.php');
    require_once('lib/functions.php');
    require_once('lib/authentication.php');

    session_start();
    if (isset($_SESSION['user_id'])) {
        $is_logged_in = true;
        $user = new User($_SESSION['user_id']);
    }
    if ($user != null && !$user->is_active()) {
        $is_logged_in = false;
        $user = null;
        session_destroy();
        session_start();
    }

    if (isset($auth_needed) && ($auth_needed == false)) {
        
    } else {
        if (!$is_logged_in) {
            header("Location: signin.php?r");
            die();
        } else {
            if (isset($verify_page) && $verify_page == true) {

            } else {
                // redirect to verify page
                if ($user == null || !$user->has_verified()) {
                    header("Location: verify.php");
                }
            }
        }
    }

    $sidebar_shown = false;
    require_once('common/sidebar.php');