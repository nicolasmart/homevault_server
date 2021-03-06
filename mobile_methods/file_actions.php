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
                            /// $_POST['action'] code:
                            /// 1 = Move
                            /// 2 = Copy
                            /// 3 = Delete
                            if (strpos($_POST['directory'], '../') !== false) return;
                            $dir1 = '../' . $folder_location . '/files' . $_POST['directory'];
                            if ($_POST['action'] == '1') {
                                if (strpos($_POST['directory2'], '../') !== false) return;
                                $dir2 = '../' . $folder_location . '/files' . '/' . $_POST['directory2'];
                                rename($dir1, $dir2 . '/' . basename($dir1));
                            }
                            if ($_POST['action'] == '2') {
                                if (strpos($_POST['directory2'], '../') !== false) return;
                                $dir2 = '../' . $folder_location . '/files' . '/' . $_POST['directory2'];
                                if (is_dir($dir1)) recurseCopy($dir1, $dir2);
                                else copy($dir1, $dir2 . '/' . basename($dir1));
                            }
                            if ($_POST['action'] == '3') {
                                if (is_dir($dir1)) deleteDirectory($dir1);
                                else unlink($dir1);
                            }
                            if ($_POST['action'] == '4') {
                                if (strpos($_POST['directory2'], '/') !== false) return;
                                $dir2 = dirname($dir1) . '/' . $_POST['directory2'];
                                rename($dir1, $dir2);
                            }
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

function recurseCopy($src,$dst, $childFolder='') { 

    $dir = opendir($src); 
    mkdir($dst);
    if ($childFolder!='') {
        mkdir($dst.'/'.$childFolder);

        while(false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . '/' . $file) ) { 
                    $this->recurseCopy($src . '/' . $file,$dst.'/'.$childFolder . '/' . $file); 
                } 
                else { 
                    copy($src . '/' . $file, $dst.'/'.$childFolder . '/' . $file); 
                }  
            } 
        }
    }else{
            // return $cc; 
        while(false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . '/' . $file) ) { 
                    $this->recurseCopy($src . '/' . $file,$dst . '/' . $file); 
                } 
                else { 
                    copy($src . '/' . $file, $dst . '/' . $file); 
                }  
            } 
        } 
    }
    
    closedir($dir); 
}

function deleteDirectory($dirname) {
        if (is_dir($dirname))
        $dir_handle = opendir($dirname);
    if (!$dir_handle)
        return false;
    while($file = readdir($dir_handle)) {
        if ($file != "." && $file != "..") {
            if (!is_dir($dirname."/".$file))
                    unlink($dirname."/".$file);
            else
                    delete_directory($dirname.'/'.$file);
        }
    }
    closedir($dir_handle);
    rmdir($dirname);
    return true;
}

?>