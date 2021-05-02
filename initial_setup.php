<?php
if (file_exists('common_vars.inc')) {
    header("location: index.php");
    exit;
}
if(!isset($_COOKIE["language"])) { 
    setcookie("language", "en", time() + (86400 * 365), "/");
    $_COOKIE["language"] = "en";
}
require('res/translations/' . $_COOKIE["language"] . '.php');

$user_role = 1;
$username = $password = $confirm_password = "";
$username_error = $password_error = $confirm_password_error = $user_role_error = "";
$sql_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $link = mysqli_connect($_POST['serverip'], $_POST['sql_username'], $_POST['sql_password'], $_POST['sql_name']);

    // Check connection
    if (mysqli_connect_errno()) {
        $sql_error = $mysqli -> connect_error;
    } else {
 
    // Validating the name
    if (empty(trim($_POST["username"]))) {
        $username_error = $messages['empty_username'];
    } else {
        $username = trim($_POST["username"]);
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_error = $messages['empty_password'];     
    } else if (strlen(trim($_POST["password"])) < 6) {
        $password_error = $messages['password_6_char'];
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_error = $messages['empty_password'];     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_error) && ($password != $confirm_password)) {
            $confirm_password_error = $messages['wrong_password'];
        }
    }

    if (empty($username_error) && empty($password_error) && empty($confirm_password_error) && empty($user_role_error)) {
        mysqli_query($link, "CREATE TABLE `users` (`name` varchar(255) DEFAULT NULL, `password` varchar(255) DEFAULT NULL, `folder_location` text NOT NULL, `user_role` int(11) NOT NULL DEFAULT '1');");

        $sql = "INSERT INTO users (name, password, user_role, folder_location) VALUES (?, ?, ?, ?)";
        
        if (mkdir("users/" . $username) && mkdir("users/" . $username . '/files') && mkdir("users/" . $username . '/photos') && mkdir('users/' . $username . '/notes') && mkdir("users/" . $username . '/music')) {
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssis", $param_username, $param_password, $param_user_role, $param_folder_location);
                $param_username = $username;
                $param_password = password_hash($password, PASSWORD_DEFAULT);
                $param_user_role = 0;
                $param_folder_location = "users/" . $username; //TODO: Change that before beta
                
                if (mysqli_stmt_execute($stmt)) {
                    $myfile = fopen("common_vars.inc", "w") or die("Unable to open file!");
                    $txt = "<?php
//Common variables that would be used accross the app.

//Modify your database info here:
define('DB_SERVER', '" . $_POST['serverip'] . "');
define('DB_USERNAME', '" . $_POST['sql_username'] . "');
define('DB_PASSWORD', '" . $_POST['sql_password'] . "');
define('DB_NAME', '" . $_POST['sql_name'] . "');

error_reporting(E_ERROR | E_PARSE);
?>";
                    fwrite($myfile, $txt);
                    fclose($myfile);

                    $myfile = fopen("users/" . $username . '/photos' . "/.htaccess", "w") or die("Unable to open file!");
                    $txt = "<Files ~ \"\.$\">
Order Allow,Deny
Deny from all
</Files>";
                    fwrite($myfile, $txt);
                    fclose($myfile);

                    $myfile = fopen("users/" . $username . '/music' . "/.htaccess", "w") or die("Unable to open file!");
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
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="shortcut icon" type="image/x-icon" href="res/drawables/favicon.ico"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <link rel="stylesheet" href="res/stylesheets/main.css?v=5">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2><?php echo $messages['setup_title']; ?></h2>
        <p><?php echo $messages['setup_subtitle']; ?></p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            </br>
            <p><?php echo $messages['setup_title1']; ?></p>
            <div class="form-group">
                <label>SQL Server IP/Domain:</label>
                <input type="text" name="serverip" class="form-control" value="localhost">
            </div>
            <div class="form-group">
                <label>SQL <?php echo $messages['username']; ?></label>
                <input type="text" name="sql_username" class="form-control" value="root">
            </div>
            <div class="form-group">
                <label>SQL <?php echo $messages['password']; ?></label>
                <input type="password" name="sql_password" class="form-control" value="">
            </div>
            <div class="form-group <?php echo (!empty($confirm_password_error)) ? 'has-error' : ''; ?>">
                <label>SQL <?php echo $messages['database_name']; ?></label>
                <input type="text" name="sql_name" class="form-control" value="homevault_db">
                <span class="help-block"><?php echo $sql_error; ?></span>
            </div>
            </br>
            <p><?php echo $messages['setup_title2']; ?></p>
            <div class="form-group <?php echo (!empty($username_error)) ? 'has-error' : ''; ?>">
                <label><?php echo $messages['username']; ?></label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_error; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_error)) ? 'has-error' : ''; ?>">
                <label><?php echo $messages['password']; ?></label>
                <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
                <span class="help-block"><?php echo $password_error; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($confirm_password_error)) ? 'has-error' : ''; ?>">
                <label><?php echo $messages['confirm_password']; ?></label>
                <input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
                <span class="help-block"><?php echo $confirm_password_error; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="<?php echo $messages['register']; ?>" style="width: 100%; margin-top: 20px;">
            </div>
        </form>
    </div>    
</body>
</html>