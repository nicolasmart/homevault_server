<?php
if (!file_exists('common_vars.inc')) {
    header("location: initial_setup.php");
    exit;
}
include 'common_vars.inc';
if(!isset($_COOKIE["language"])) setcookie("language", "en", time() + (86400 * 365), "/");
require('res/translations/' . $_COOKIE["language"] . '.php');
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $messages['login']; ?> - HomeVault</title>
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
            transform: translateX(-50%) translateY(calc(-50% - .5px));
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
<?php

$username = $_SESSION["username"];
$password = "";
$username_error = "";
$password_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    // Checking for empty password
    if (empty(trim($_POST["old_password"]))) {
        $old_password_error = $messages['empty_password'];
        return;
    } else{
        $password = trim($_POST["old_password"]);
    }

    // Validate password
    if (empty(trim($_POST["new_password"]))) {
        $new_password_error = "Please enter a password.";     
    } else if (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_error = "Password must have atleast 6 characters.";
    } else {
        $new_password = trim($_POST["new_password"]);
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

    $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
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
                            session_start();
                            
                            $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

                            $sql2 = "UPDATE users SET password = ". "'" . password_hash($new_password, PASSWORD_DEFAULT) . "'" . " WHERE name = " . "'" . $username . "'";
                            if ($conn->query($sql2) === TRUE) {
                                echo "<script>alert('" . $messages["password_changed"] . "');window.location.href = 'index.php';
                                </script>";
                            } else {
                                echo "Error updating record: " . $conn->error;
                            }
                            
                            $conn->close();
                        } else {
                            $password_error = $messages['wrong_password'];
                        }
                    }
                } else {
                    $username_error = $messages['account_not_exist'];
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
    <div class="wrapper popout-card">
        <img src="res/drawables/homevault_logo_big.svg"></img>
        <p style="margin-top: 10px;"><?php echo $messages['change_password_title']; ?></p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($old_password_error)) ? 'has-error' : ''; ?>">
                <input type="password" name="old_password" class="form-control" placeholder="<?php echo $messages['old_password']; ?>" style="background: url('res/drawables/password_textbox.svg');">
                <span class="help-block"><?php echo $old_password_error; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($new_password_error)) ? 'has-error' : ''; ?>">
                <input type="password" name="new_password" class="form-control" placeholder="<?php echo $messages['new_password']; ?>" style="background: url('res/drawables/password_textbox.svg');">
                <span class="help-block"><?php echo $new_password_error; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($confirm_password_error)) ? 'has-error' : ''; ?>">
                <input type="password" name="confirm_password" class="form-control" placeholder="<?php echo $messages['confirm_password']; ?>" style="background: url('res/drawables/password_textbox.svg');">
                <span class="help-block"><?php echo $confirm_password_error; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="<?php echo $messages['update_password']; ?>">
            </div>
            <!--<p>Don't have an account? <a href="register.php">Sign up now</a>.</p>-->
        </form>
    </div>
    <script>
    $(document).ready(function() {
        $('body').css('display', 'none');
        $('body').fadeIn(600);
    });
    </script>  
</div>
</body>
</html>