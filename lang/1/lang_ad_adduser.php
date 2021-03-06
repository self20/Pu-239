<?php

global $site_config;

$lang = [
    //Misc
    'std_adduser'        => 'Add user',
    'std_err'            => 'Error',
    'std_success'        => 'Success',
    'btn_okay'           => 'Okay',
    //err
    'err_username'       => 'Forgot username or not long enough (min 5 chars)',
    'err_password'       => 'Forgot passwords or passwords mismatch or not longh enough (min 6 chars)',
    'err_email'          => 'Forgot email or not a vaild email',
    'err_mysql_err'      => 'There was a mysql error : %s, report to staff',
    'err_already_exists' => 'User already exists...wait for redirect!',
    //Texts
    'text_user_added'    => 'User was added, visit users details <a href="' . $site_config['baseurl'] . '/userdetails.php?id=%d">here</a>',
    'text_username'      => 'Username',
    'text_password'      => 'Password',
    'text_password2'     => 'Re-type password',
    'text_email'         => 'Email',
];
