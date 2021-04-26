<?php
session_start();
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] != true || $_SESSION["user_role"] != '0') {
    header('location: login.php');
}
include 'common_vars.inc';
if(!isset($_COOKIE["language"])) setcookie("language", "en", time() + (86400 * 365), "/");
require('res/translations/' . $_COOKIE["language"] . '.php');

$user_role = 1;
$username = $password = $confirm_password = "";
$username_error = $password_error = $confirm_password_error = $user_role_error = "";
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
    // Validating the name
    if (empty(trim($_POST["username"]))) {
        $username_error = $messages['empty_username'];
    } else {
        $sql = "SELECT name FROM users WHERE name = ?"; // Check for existing username
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = trim($_POST["username"]);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $username_error = $messages['username_taken'];
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo $messages['generic_error'];
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_error = "Please enter a password.";     
    } else if (strlen(trim($_POST["password"])) < 6) {
        $password_error = "Password must have atleast 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_error = "Please confirm password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_error) && ($password != $confirm_password)) {
            $confirm_password_error = "Password did not match.";
        }
    }

    // Validate account type
    if (isset($_POST["user_role"]) && $_POST["user_role"] == "admin") {
        $user_role = 0;
    } else if (isset($_POST["user_role"]) && $_POST["user_role"] == "standard") {
        $user_role = 1;
    } else {
        $user_role_error = "Please pick a user account type.";
    }
    
    if (empty($username_error) && empty($password_error) && empty($confirm_password_error) && empty($user_role_error)) {
        $sql = "INSERT INTO users (name, password, user_role, folder_location) VALUES (?, ?, ?, ?)";
        
        if (mkdir("users/" . $username) && mkdir("users/" . $username . '/files') && mkdir("users/" . $username . '/photos') && mkdir("users/" . $username . '/notes') && mkdir("users/" . $username . '/music')) {
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssis", $param_username, $param_password, $param_user_role, $param_folder_location);
                $param_username = $username;
                $param_password = password_hash($password, PASSWORD_DEFAULT);
                $param_user_role = $user_role;
                $param_folder_location = "users/" . $username;
                
                if (mysqli_stmt_execute($stmt)) {
                    $myfile = fopen("users/" . $username . '/photos' . "/.htaccess", "w") or die("Unable to open file!");
                    $txt = "<Files ~ \"\.$\">
Order Allow,Deny
Deny from all
</Files>";
                    fwrite($myfile, $txt);
                    fclose($myfile);
                    
                    // Account created; Redirect to login page
                    echo '<script>
                    alert("' . $messages['account_created'] . '");
                    window.location.href = "login.php"
                    </script>';
                    //header("location: login.php");
                } else {
                    echo $messages['generic_error'];
                }

                mysqli_stmt_close($stmt);
            }
        } else {
            echo $messages['generic_error'];
        }
    }
    
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $messages['register']; ?></title>
    <link rel="stylesheet" href="res/stylesheets/bootstrap.min.css">
    <link rel="stylesheet" href="res/stylesheets/main.css?v=3">
    <style type="text/css">
        .body-overlay {
            background: url('res/drawables/homevault_default_backdrop.jpg') no-repeat center center fixed; 
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
            width: 100vw;
            height: 100vh;
        }
        .wrapper { 
            width: 400px;
            position: absolute;
            justify-content: center;
            text-align: center;
            top: 50%;
            left: 50%;
            margin-right: -50%;
            transform: translateX(-50%) translateY(-50%);
        }
        .form-group {
            margin-top: 30px;
        }
        input[type="text"], input[type="password"] {
            border: 0px;
            padding-left: 45px;
            padding-right: 45px;
        }
        input[type="submit"] {
            width: 70%;
        }
    </style>
</head>
<body>
<div class="body-overlay">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
    <div class="wrapper popout-card">
        <h2><?php echo $messages['register']; ?></h2>
        <p><?php echo $messages['register_subtitle']; ?></p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_error)) ? 'has-error' : ''; ?>">
                <input type="text" name="username" class="form-control" placeholder="<?php echo $messages['username']; ?>" value="<?php echo $username; ?>" style="background: url('res/drawables/username_textbox.svg');">
                <span class="help-block"><?php echo $username_error; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_error)) ? 'has-error' : ''; ?>">
                <input type="password" name="password" class="form-control" placeholder="<?php echo $messages['password']; ?>" style="background: url('res/drawables/password_textbox.svg');">
                <span class="help-block"><?php echo $password_error; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($confirm_password_error)) ? 'has-error' : ''; ?>">
                <input type="password" name="confirm_password" class="form-control" placeholder="<?php echo $messages['confirm_password']; ?>" style="background: url('res/drawables/password_textbox.svg');">
                <span class="help-block"><?php echo $confirm_password_error; ?></span>
            </div>
            <div style="text-align: left;">
                <div class="form-check" style="padding-top: 10px;">
                    <label><?php echo $messages['account_user']; ?></label>
                    <input type="radio" name="user_role" class="form-check-input" value="admin" id="adminuseropt" checked>
                    <label class="form-check-label" for="adminuseropt">
                        <?php echo $messages['administrator']; ?></p>
                    </label>
                </div>
                <div class="form-check" style="margin-top: -10px; margin-left: 5px; margin-bottom: -24px;">
                    <input type="radio" name="user_role" class="form-check-input" value="standard" id="stduseropt">
                    <label class="form-check-label" for="stduseropt">
                        <?php echo $messages['standard_user']; ?>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="<?php echo $messages['register']; ?>" style="width: 100%; margin-top: 20px;">
            </div>
        </form>
    </div>    
</div>
</body>
</html>