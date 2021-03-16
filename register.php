<?php
session_start();
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] != true || $_SESSION["user_role"] != '0') {
    header('location: login.php');
}
include 'common_vars.inc';
require('res/translations/bg.php'); // TODO: Change when switching languages

// TODO: Disallow registering when there's an admin that's not logged in.

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
        
        if (mkdir("users/" . $username) && mkdir("users/" . $username . '/files') && mkdir("users/" . $username . '/photos') && mkdir("users/" . $username . '/notes')) {
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssis", $param_username, $param_password, $param_user_role, $param_folder_location);
                $param_username = $username;
                $param_password = password_hash($password, PASSWORD_DEFAULT);
                $param_user_role = $user_role;
                $param_folder_location = "users/" . $username; //TODO: Change that before beta
                
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
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <link rel="stylesheet" href="res/stylesheets/main.css?v=5">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Sign Up</h2>
        <p>Please fill this form to create an account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_error)) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_error; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_error)) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
                <span class="help-block"><?php echo $password_error; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($confirm_password_error)) ? 'has-error' : ''; ?>">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
                <span class="help-block"><?php echo $confirm_password_error; ?></span>
            </div>
            <div class="form-group">
                <label>Account Type</label>
                <input type="radio" name="user_role" class="form-control" value="admin" checked> <p style="text-align:center">Administrator</p>
                <input type="radio" name="user_role" class="form-control" value="standard"> <p style="text-align:center">Standard User</p>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Register" style="width: 100%; margin-top: 20px;">
            </div>
        </form>
    </div>    
</body>
</html>