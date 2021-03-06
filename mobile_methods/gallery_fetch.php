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

    if (empty(trim($_POST["directory"]))) $_POST["directory"] = '/';
    
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
                            if (strpos($_POST['directory'], '../') !== false) return;
                            $GLOBALS['images_dir'] = $images_dir = '../' . $folder_location . '/photos' . '/' . $_POST["directory"];
                            /**$files = glob($images_dir + '/*.{jpg,png,gif,jpeg}', GLOB_BRACE);
                            usort($files, function($a, $b) {
                                return filemtime($b) - filemtime($a);
                            });*/

                            $exts = array('jpg', 'png', 'gif', 'jpeg');

                            $files = array();
                            $times = array();
                            if($handle = opendir($images_dir)) {
                                while(false !== ($file = readdir($handle))) {
                                    $extension = strtolower(substr(strrchr($file,'.'),1));
                                    if($extension && in_array($extension,$exts)) {
                                        $files[] = $file;
                                        $times[] = strval(filemtime($images_dir . '/' . $file));
                                    }
                                }
                                closedir($handle);
                            }
                            //echo json_encode($files);
                            usort($files, function($x, $y) {
                                return filemtime($GLOBALS['images_dir'] . '/' . $x) < filemtime($GLOBALS['images_dir'] . '/' . $y);
                            });
                            //array_multisort($files, SORT_DESC, $times);
                            echo "listing_success_key:" . json_encode($files);
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