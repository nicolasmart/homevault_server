<?php
if (!file_exists('common_vars.inc')) {
    header("location: initial_setup.php");
    exit;
}
include 'common_vars.inc';
if(!isset($_COOKIE["language"])) {
    header('location: login.php');
    exit;
}
require('res/translations/' . $_COOKIE["language"] . '.php');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $messages['home_files']; ?> - HomeVault</title>
    <!-- TODO: Switch to local instead of CDN cause Seray would be mad otherwise; 
         TODO 2: Add a common header -->
    <link rel="stylesheet" href="res/stylesheets/bootstrap.min.css"> 
    <link rel="stylesheet" href="res/stylesheets/main.css?v=5">
    <style type="text/css">
        .body-overlay {
            background: url('res/drawables/homevault_default_backdrop.jpg') no-repeat center center fixed; 
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
            padding-top: 20px;
            width: 100vw;
            height: 100vh;
        }
        .toolbar { 
            width: calc(100vw - 60px);
            margin-left: 30px;
            margin-right: 30px;
        }
        .main-wrapper { 
            width: calc(100vw - 60px);
            margin-left: 30px;
            margin-right: 30px;
            margin-top: 20px;
            height: calc(100vh - 150px);
            display: flex;
            flex-flow: column;
        }
        .form-group {
            margin-top: 30px;
        }
        input[type="button"] {
            padding-left: 18px;
            padding-right: 18px;
            border-radius: 100px;
        }
        .dropdown-item {
            padding: 14px;
            padding-left: 20px;
            font-size: 1.15em;
        }
        .dropdown-item.active {
            background: #17A67E;
        }
    </style>
</head>
<body>
<div class="body-overlay">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
<?php
session_start();

if (!isset($_SESSION["logged_in"]) || empty($_SESSION["logged_in"]) || $_SESSION['logged_in'] != true) {
    header('location: login.php');
    exit;
}

if (!empty($_FILES['file']['name'])) {
    $filecount = count($_FILES['file']['name']);
   
    // Looping through multiple uploaded files
    for ($i=0; $i<$filecount; $i++) {
        $filename = $_FILES['file']['name'][$i];
        if (file_exists($_SESSION["folder_loc"] . '/files' . '/' . $filename)) {
            $path_parts = pathinfo($filename);
            $filename = $path_parts['filename'] . '_' . date('Y-m-d_H-i-s') . '.' . $path_parts['extension'];
        }
        move_uploaded_file($_FILES['file']['tmp_name'][$i], $_SESSION["folder_loc"] . '/files' . '/' . $filename);
        //echo '<p>' . $_SESSION["folder_loc"] . '/files' . '/' . $filename . '</p>';
    }
} 

if (isset($_POST['new_folder']) && !empty($_POST['new_folder'])) {
    mkdir($_SESSION["folder_loc"] . '/files' . '/' . $_POST['new_folder']);
}

function getSymbolByQuantity($bytes) {
    $symbols = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
    $exp = $bytes ? floor(log($bytes) / log(1024)) : 0;

    return sprintf('%.2f '.$symbols[$exp], ($bytes/pow(1024, floor($exp))));
}
?>
    <div class="toolbar popout-card" style="z-index: 900; position: relative;">
        <div class="left-action">
        <div class="btn-group">
            <input type="image" src="res/drawables/md_long_hamburger_button.svg" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"/>
            <div class="dropdown-menu" id="main_nav">
                <a class="dropdown-item active" href="#" onclick="$('#main_nav > a.active').removeClass('active'); $(this).addClass('active'); document.getElementById('page-content').src='file_manager.php'; document.title = '<?php echo $messages['home_files']; ?> - HomeVault';"><?php echo $messages['home_files']; ?></a>
                <a class="dropdown-item" href="#" onclick="$('#main_nav > a.active').removeClass('active'); $(this).addClass('active'); document.getElementById('page-content').src='photos.php'; document.title = '<?php echo $messages['photos']; ?> - HomeVault';"><?php echo $messages['photos']; ?></a>
                <a class="dropdown-item" href="#" onclick="$('#main_nav > a.active').removeClass('active'); $(this).addClass('active'); document.getElementById('page-content').src='music.php'; document.title = '<?php echo $messages['music']; ?> - HomeVault';"><?php echo $messages['music']; ?></a>
                <a class="dropdown-item" href="#" onclick="$('#main_nav > a.active').removeClass('active'); $(this).addClass('active'); document.getElementById('page-content').src='notes.php'; document.title = '<?php echo $messages['notes']; ?> - HomeVault';"><?php echo $messages['notes']; ?></a>
                <!--<a class="dropdown-item" href="#">Пароли</a>-->
                <!--<div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Настройки</a>-->
            </div>
        </div>
        </div>
        <div class="right-action">
        <div class="btn-group">
            <input type="image" src="res/drawables/md_user_circle.svg" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"/>
            <div class="dropdown-menu dropdown-menu-right" id="user_nav">
                <a class="dropdown-item" href="#" onclick="event.preventDefault();"><i><?php echo $_SESSION['username']; ?></i></a>
                <a class="dropdown-item" href="#" onclick="event.preventDefault();"><i><?php echo getSymbolByQuantity(disk_free_space('.')) . ' out of ' . getSymbolByQuantity(disk_total_space('.')); ?></i></a>
                <div class="dropdown-divider"></div>
                <?php if ($_SESSION['user_role'] == '0') echo '<a class="dropdown-item" href="register.php">' . $messages['register_user'] . '</a>'; ?>
                <a class="dropdown-item" href="change_password.php"><?php echo $messages['change_password']; ?></a>
                <a class="dropdown-item" href="logout.php"><?php echo $messages['logout']; ?></a>
                <!--<div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">Настройки</a>-->
            </div>
        </div>
        </div>
        <div class="center-logo"><img src="res/drawables/homevault_logo_big.svg"></div>
    </div>  
    <div class="main-wrapper popout-card" style="z-index: -1; ">
        <div class="center-elements" style="z-index: -1;">
            <form id="create_folder" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="new_folder" id="new_folder">
                <!-- The button is in the next form in order to be on the same line -->
            </form>
            <form id="file_upload" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <input type="file" name="file[]" id="file" multiple hidden>
                <!--<input type="button" id="add_folder" class="btn btn-primary" value="<?php echo $messages['create_folder']; ?>" style="margin-right: 10px;">
                <input type="button" id="upload_overlay" class="btn btn-primary" value="<?php echo $messages['upload_file']; ?>">-->
            </form>
        </div>
        <iframe src="file_manager.php" id="page-content" allowtransparency="true" frameBorder="0" style="flex: 1; width: 100%; z-index: 1;"></iframe>
    </div>  
    <script>

    function hideIframe() {
        $("#page-content").hide();
    }
    function showIframe() {
        $("#page-content").show();
    }

    var fcAction, fcD1, global_list, notRunning = true;
    function fileCrypt(action, d1, list_function)
    {
        fcAction = action;
        fcD1 = d1;
        global_list = list_function;
        $('#passwordModal').on('shown.bs.modal', function () {
            $('#password-box').trigger('focus');
        });
        $('#passwordModal').modal('show'); 
        document.getElementById('password-box').addEventListener("keyup", function(event) {
            // Number 13 is the "Enter" key on the keyboard
            if (event.keyCode === 13) {
                event.preventDefault();
                if (notRunning) fileCryptFinish();
            }
        });
        document.getElementById('password-box').addEventListener("keydown", function(event) {
            // Number 13 is the "Enter" key on the keyboard
            if (event.keyCode === 13) {
                event.preventDefault();
            }
        });
    }
    function fileCryptFinish()
    {
        notRunning = false;
        var passkey = document.getElementById('password-box').value;
        if (passkey === null) {
            notRunning = true;
            return;
        }
        document.getElementById("password-box").disabled = true;
        if (fcAction == "7") { /// Special case for decryption while downloading
            $.ajax({
                type : 'post',
                url : 'mobile_methods/file_download.php',
                cache : false,
                xhr : function () {
                    var xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState == 2) {
                            if (xhr.status == 200) {
                                xhr.responseType = "blob";
                            } else {
                                xhr.responseType = "text";
                            }
                        }
                    };
                    return xhr;
                },
                data : {'directory': fcD1.substr(fcD1.indexOf('/')+1), 'username': '<?php echo $_SESSION['username']; ?>', 'password': passkey},
                success : function(r)
                {
                    if (r === '') 
                    {
                        alert('<?php echo $messages['wrong_password']; ?>');
                        return;
                    }
                    var blob = new Blob([r], { type: "application/octetstream" });
                    
                    //Check the Browser type and download the File.
                    var isIE = false || !!document.documentMode;
                    if (isIE) {
                        window.navigator.msSaveBlob(blob, fileName);
                    } else {
                        var url = window.URL || window.webkitURL;
                        link = url.createObjectURL(blob);
                        var a = $("<a />");
                        a.attr("download", fcD1.substr(fcD1.lastIndexOf('/')+1, fcD1.lastIndexOf('.crypt')-fcD1.lastIndexOf('/')-1));
                        a.attr("href", link);
                        $("body").append(a);
                        a[0].click();
                        $("body").remove(a);
                    }

                    $('#passwordModal').modal('hide'); 
                    document.getElementById('password-box').value = '';
                    document.getElementById("password-box").disabled = false;
                    global_list();
                    notRunning = true;
                }
            });
            global_list();
            return;
        }
        $.ajax({
            type : 'post',
            url : 'mobile_methods/file_actions.php',
            data : {'directory': fcD1, 'password': passkey, 'logged_in': '1', 'action': fcAction},
            success : function(r)
            {
                if (r && !(r === "")) alert(r);
                else $('#passwordModal').modal('hide'); 
                document.getElementById('password-box').value = '';
                document.getElementById("password-box").disabled = false;
                global_list();
                notRunning = true;
            }
        });
        global_list();

    }

    </script>    
</div>

<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="passwordModalLabel"><?php echo $messages['password']; ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group" style="margin-top: 0px;">
            <label for="password-box" class="col-form-label"><?php echo $messages['password_prompt']; ?></label>
            <input type="password" class="form-control" id="password-box">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="fileCryptFinish();"><?php echo $messages['confirm']; ?></button>
      </div>
    </div>
  </div>
</div>
</body>
</html>