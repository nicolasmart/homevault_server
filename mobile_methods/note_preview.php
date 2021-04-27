<?php 
include_once('connection.php');
include '../common_vars.inc';
require('../res/translations/bg.php');

$username = "";
$password = "";
$username_error = "";
$password_error = "";

if (isset($_POST['logged_in']) && $_POST['logged_in']=='1') {
    session_start();
    getNote('../' . $_SESSION['folder_loc'] . '/notes' . '/' . $_POST['filename']);
}
else if ($_SERVER["REQUEST_METHOD"] == "POST") {

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

    if (empty(trim($_POST["filename"]))) return;
    
    // Validate login credentials
    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT name, password, user_role, folder_location FROM users WHERE name = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $username, $hashed_password, $user_role, $folder_location);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            //if (strpos($_POST['directory'], '../') !== false) return;
                            $file = '../' . $folder_location . '/notes' . '/' . $_POST['filename'];
                            getNote($file);
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

function getNote($filepath) {
    $file_txt = file_get_contents($filepath);
    $rows = preg_split('~[\r\n]+~', $file_txt);
    array_shift($rows);
    $rownum=0;
    foreach($rows as $data) {
        if ($rownum==1) {
          echo $data . PHP_EOL;
        }
        else if ($rownum>=3) echo $data . '</br>';
        $rownum++;
    }
}

?>