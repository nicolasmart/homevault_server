<?php 
include_once('connection.php');
include '../common_vars.inc';
require('../res/translations/en.php');

$username = "";
$password = "";
$username_error = "";
$password_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["username"]))) {
        echo $messages['empty_username'];
        return;
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        echo $messages['empty_password'];
        return;
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate login credentials
    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT name, password, user_role, folder_location FROM users WHERE name = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) { // Check for existing username
                    mysqli_stmt_bind_result($stmt, $username, $hashed_password, $user_role, $folder_location);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            echo '
                            <!DOCTYPE html>
                            <html>
                            <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>HomeVault</title>
                            <link rel="stylesheet" href="res/stylesheets/bootstrap.min.css?v=2"> 
                            <link rel="stylesheet" href="../res/stylesheets/main.css?v=3">
                            <style>
                            body,
                            html {
                                position: fixed;
                                width: 100%;
                                height: 100%;
                            }
                            </style>
                            </head>
                            <body>
                            <form action="file_upload.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="username" value="' . $username . '">
                            <input type="hidden" name="password" value="' . $password . '">
                            <input type="hidden" name="directory" value="' . $_POST['directory'] . '">
                            <input type="file" name="file_upload" id="file_upload" onchange="form.submit()" hidden/>
                            <label for="file_upload"><div style="margin: auto; width: 98%; margin-top: -20px; margin-left: -5px;"><p class="btn btn-primary" style="text-align: center; background: rgb(232,160,52); background: linear-gradient(137deg, rgba(232,160,52,1) 0%, rgba(204,122,88,1) 100%); border: 0; width: 100%; padding-top: 10px; padding-bottom: 10px; border-radius: 5px;">'
                            . $messages['upload_song'] . '</p></div></label>
                            </form></body></html>';
                        } else {
                            echo $messages['wrong_password'];
                        }
                    }
                } else {
                    echo $messages['account_not_exist'];
                }
            } else {
                echo $messages['generic_error'];
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    mysqli_close($link);
}

?>