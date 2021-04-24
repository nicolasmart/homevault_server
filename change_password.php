<?php
if (!file_exists('common_vars.inc')) {
    header("location: initial_setup.php");
    exit;
}
include 'common_vars.inc';
require('res/translations/bg.php'); // TODO: Change when switching languages
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
// No need to be on the login page if user is already logged in
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    header("location: index.php");
    exit;
}

$username = "";
$password = "";
$username_error = "";
$password_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    // Checking for empty username or password
    if (empty(trim($_POST["username"]))) {
        $username_error = $messages['empty_username'];
        return;
    } else {
        $username = trim($_POST["username"]);
    }
    
    if (empty(trim($_POST["password"]))) {
        $password_error = $messages['empty_password'];
        return;
    } else{
        $password = trim($_POST["password"]);
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
                            
                            $_SESSION["logged_in"] = true;
                            $_SESSION["username"] = $username;
                            $_SESSION["user_role"] = $user_role;
                            $_SESSION["folder_loc"] = $folder_location;               
                            
                            echo "<script>window.location.href = 'index.php';
                            </script>";
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
            <div class="form-group <?php echo (!empty($username_error)) ? 'has-error' : ''; ?>">
                <input type="text" name="username" class="form-control" placeholder="<?php echo $messages['username']; ?>" value="<?php echo $username; ?>" style="background: url('res/drawables/username_textbox.svg');">
                <span class="help-block"><?php echo $username_error; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($password_error)) ? 'has-error' : ''; ?>">
                <input type="password" name="old_password" class="form-control" placeholder="<?php echo $messages['old_password']; ?>" style="background: url('res/drawables/password_textbox.svg');">
                <span class="help-block"><?php echo $password_error; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($password_error)) ? 'has-error' : ''; ?>">
                <input type="password" name="new_password" class="form-control" placeholder="<?php echo $messages['new_password']; ?>" style="background: url('res/drawables/password_textbox.svg');">
                <span class="help-block"><?php echo $password_error; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($password_error)) ? 'has-error' : ''; ?>">
                <input type="password" name="confirm_password" class="form-control" placeholder="<?php echo $messages['confirm_password']; ?>" style="background: url('res/drawables/password_textbox.svg');">
                <span class="help-block"><?php echo $password_error; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="<?php echo $messages['login']; ?>">
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