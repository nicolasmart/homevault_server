<?php 
include_once('connection.php');
include '../common_vars.inc';
require('../res/translations/en.php');

$username = "";
$password = "";
$username_error = "";
$password_error = "";

/// $_POST['action'] code:
/// 1 = Move
/// 2 = Copy
/// 3 = Delete
/// 4 = Rename
/// 5 = Encrypt [NEW]
/// 6 = Decrypt [NEW]

if (isset($_POST['logged_in']) && $_POST['logged_in']=='1') {
    session_start();
    if (strpos($_POST['directory'], '../') !== false) return;
    $folder_location = $_SESSION['folder_loc'];
    $dir1 = '../' . $folder_location . '/' . $_POST['directory'];
    //echo $dir1;
    if ($_POST['action'] == '1') {
        if (strpos($_POST['directory2'], '../') !== false) return;
        $dir2 = '../' . $folder_location . '/files' . '/' . $_POST['directory2'];
        rename($dir1, $dir2 . '/' . basename($dir1));
    }
    else if ($_POST['action'] == '2') {
        if (strpos($_POST['directory2'], '../') !== false) return;
        $dir2 = '../' . $folder_location . '/files' . '/' . $_POST['directory2'];
        if (is_dir($dir1)) recurseCopy($dir1, $dir2);
        else copy($dir1, $dir2 . '/' . basename($dir1));
    }
    else if ($_POST['action'] == '3') {
        if (is_dir($dir1)) deleteDirectory($dir1);
        else unlink($dir1);
    }
    else if ($_POST['action'] == '4') {
        if (strpos($_POST['directory2'], '/') !== false) return;
        $dir2 = dirname($dir1) . '/' . $_POST['directory2'];
        rename($dir1, $dir2);
    }
    else if ($_POST['action'] == '5') {
        if (!isset($_POST['password'])) return;
        $username = $_SESSION['username'];
        if (empty(trim($_POST["password"]))) {
            echo $messages['empty_password'];
            return;
        } else{
            $password = trim($_POST["password"]);
        }
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
                                $file1 = file_get_contents($dir1);
                                $encrypted_file = encryptFile($file1, $_POST['password'], $dir1);
                                file_put_contents($dir1 . '.crypt', $encrypted_file);
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
    else if ($_POST['action'] == '6') {
        if (!isset($_POST['password'])) return;
        $file2 = file_get_contents($dir1);
        $decrypted_file = decryptFile($file2, $_POST['password']);
        if (empty($decrypted_file)) echo $messages['wrong_password'];
        else {
            file_put_contents(substr($dir1, 0, -6), $decrypted_file);
            unlink($dir1);
        }
    }
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
                if (mysqli_stmt_num_rows($stmt) == 1) { // Check for existing username
                    mysqli_stmt_bind_result($stmt, $username, $hashed_password, $user_role, $folder_location);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            /// $_POST['action'] code:
                            /// 1 = Move
                            /// 2 = Copy
                            /// 3 = Delete
                            /// 4 = Rename
                            /// 5 = Encrypt [NEW]
                            /// 6 = Decrypt [NEW]

                            if (strpos($_POST['directory'], '../') !== false) return;
                            $dir1 = '../' . $folder_location . '/files' . '/' . $_POST['directory'];
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
                            if ($_POST['action'] == '5') {
                                $file1 = file_get_contents($dir1);
                                $encrypted_file = encryptFile($file1, $_POST['password'], $dir1);
                                file_put_contents($dir1 . '.crypt', $encrypted_file);
                            }
                            if ($_POST['action'] == '6') {
                                $file2 = file_get_contents($dir1);
                                $decrypted_file = decryptFile($file2, $_POST['password']);
                                if (empty($decrypted_file)) echo $messages['wrong_password'];
                                else {
                                    file_put_contents(substr($dir1, 0, -6), $decrypted_file);
                                    unlink($dir1);
                                }
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

function encryptFile($data, $key, $dir1) {
    ob_start();
    $encryption_key = base64_decode($key);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
    $enc_obj = base64_encode($encrypted . '::' . $iv);
    if (ob_get_length() == 0) unlink($dir1);
    return $enc_obj;
}

function decryptFile($data, $key) {
    $encryption_key = base64_decode($key);
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
}

?>