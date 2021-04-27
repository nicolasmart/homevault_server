<?php 
include_once('connection.php');
include '../common_vars.inc';
require('../res/translations/bg.php');

$username = "";
$password = "";
$username_error = "";
$password_error = "";

$color_tag = '1';
if (!empty($_POST['color_tag'])) $color_tag = $_POST['color_tag'];

if (empty($_POST['note_content'])) $_POST['note_content'] = $messages['empty_title'];
$note_content = $_POST['note_content'];

if (isset($_POST['logged_in']) && $_POST['logged_in']=='1') {
    session_start();
    setNote('../' . $_SESSION['folder_loc'] . '/notes' . '/', $note_content);
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
                            $file = '../' . $folder_location . '/notes' . '/';
                            setNote($file, $note_content);
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

function setNote($filepath, $notetxt) {
    $tag_colors = array("245, 205, 60, 0.71", "245, 60, 149, 0.71", "86, 60, 245, 0.71", "65, 194, 117, 0.71", "240, 117, 78, 0.71", "61, 172, 220, 0.71");

    $myfile = fopen($filepath . generateRandomString() . ".mn", "w") or die("Unable to open file!");

    $color_tag_bg = "background: rgba(" . $tag_colors[rand(0, 5)] . ");";
    if (isset($_POST['color_tag_bg'])) $color_tag_bg = $_POST['color_tag_bg'];

    $txt = "###\n" . $color_tag_bg . "\n" . substr($notetxt, 0, strpos($notetxt, "\n")) . "\n###\n" . preg_replace('/^.+\n/', '', $notetxt);
    fwrite($myfile, $txt);
    fclose($myfile);
}

function generateRandomString($length = 11) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

?>