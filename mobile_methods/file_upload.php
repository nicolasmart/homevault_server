<?php 
include_once('connection.php');
include '../common_vars.inc';
require('../res/translations/en.php'); // TODO: Change when switching languages

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

    if (empty(trim($_POST["directory"]))) return;
    
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
                            $filename = basename($_FILES["file_upload"]["name"]);
                            if ($filename == 'null') $filename = $_POST['post_filename'];
                            //if (strpos($_POST['directory'], '../') !== false) return;
                            $target_dir = '../' . $folder_location  . '/files' . $_POST["directory"] . '/';
                            $target_file = $target_dir . $filename;
                            while (file_exists($target_file)) {
                                $ii = 1;
                                $target_file = $target_dir . $filename . ' (' . strval($ii) . ')';
                                $ii++;
                            }
                            move_uploaded_file($_FILES["file_upload"]["tmp_name"], $target_file);
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