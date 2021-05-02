<?php
include 'common_vars.inc';
include 'res/libraries/getid3/getid3.php';
if(!isset($_COOKIE["language"])) { 
  setcookie("language", "en", time() + (86400 * 365), "/");
  $_COOKIE["language"] = "en";
}
require('res/translations/' . $_COOKIE["language"] . '.php');
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>HomeVault</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
    <link rel="stylesheet" href="res/stylesheets/bootstrap.min.css"> 
    <link rel="stylesheet" href="res/stylesheets/main.css?v=3">
    <style type="text/css">
        .album_cover {
          height: 100%;
          width: 70px;
          margin-right: 20px;
          object-fit: contain;
        }
        .listing {
          margin-top: 16px;
          margin-bottom: 16px;
          height: 80px;
          width: 100%;
          display: flex;
        }
        .listing-text {
          margin-left: 40px;
          margin-top: 15px;
          height: calc(30px + 2em);
          overflow-y: hidden;
        }
        .note-text:active {
          height: auto;
        }
        .wrapper {
          width: 100vw;
          height: 100vh;
        }
        body {
          overflow-x: hidden;
          background: none transparent;
        }
        .float {
          position: fixed;
          width: 60px;
          height: 60px;
          bottom: 15px;
          right: 15px;
          background-color: #17A67E;
          color: #FFF;
          border-radius: 50px;
          text-align: center;
          box-shadow: 2px 2px 3px #999;
        }
        .floating-button {
          margin-top: 22px;
        }
        a {
          color: #555;
        }
        a:hover {
          color: #000;
        }
        .main_actions_container {
          margin: 0;
          position: absolute;
          top: 50%;
          left: 50%;
          -ms-transform: translate(-50%, -50%);
          transform: translate(-50%, -50%);
        }
    </style>
    <base target="_self">
</head>
<body>
<?php
if (isset($_FILES['upload'])){
    $file_name = $_FILES['upload']['name'];
    $file_tmp = $_FILES['upload']['tmp_name'];
    $file_ext = strtolower(end(explode('.', $_FILES['upload']['name'])));
    
    $extensions = array("mp3");
    
    if (in_array($file_ext, $extensions) === false){
        echo '<script>alert("' . $messages['invalid_music_file'] . '");</script>';
    } else {
        move_uploaded_file($file_tmp, $_SESSION['folder_loc'] . "/" . "music/" . $file_name);
    }
}
?>
<div class="body-overlay" style="overflow: hidden;">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
<div class="row" style="width: 100vw;">
  <div class="col-sm-3" style="height:100vh;">
    <div class="main_actions_container">
        <img src="res/drawables/music_now_playing.png" style="width: 100%; object-fit: contain;" />
        <p style="text-align: center; font-size: 1.3em; padding-top: 10px;"><?php echo $messages['now_playing']; ?></p>
        <br/>
        <img src="res/drawables/music_alternative_shuffle.png" style="width: 100%; object-fit: contain;" />
        <p style="text-align: center; font-size: 1.3em; padding-top: 10px;"><?php echo $messages['shuffle']; ?></p>
        <br/>
        <form action="" target="_self" method="POST" enctype="multipart/form-data">
        <input type="file" name="upload" id="upload" onchange="form.submit()" hidden/>
        <label for="upload" style="width: 100%;"><p class="btn btn-primary" style="background: rgb(232,160,52); background: linear-gradient(137deg, rgba(232,160,52,1) 0%, rgba(204,122,88,1) 100%); border: 0; width: 100%; border-radius: 5px;">
        <?php echo $messages['upload_song']; ?></p></label>
        </form>
    </div>
  </div>
  <div class="col-sm-9" style="height: 100vh; overflow-y: auto;">
        <div class="row">
        <div class="col-sm-6">
        <h3 style="padding-left: 1px; padding-bottom: 6px;"><strong><?php echo $messages['artists']; ?></strong></h3>
        <?php
        $GLOBALS['music_dir'] = $music_dir = $_SESSION['folder_loc'] . '/music' . '/';
        $exts = array('mp3');

        $files = array();
        $times = array();
        if($handle = opendir($music_dir)) {
            while(false !== ($file = readdir($handle))) {
                $extension = strtolower(substr(strrchr($file,'.'),1));
                if($extension && in_array($extension,$exts)) {
                    $files[] = $file;
                    $times[] = strval(filemtime($music_dir . '/' . $file));
                }
            }
            closedir($handle);
        }
        usort($files, function($x, $y) {
            return filemtime($GLOBALS['music_dir'] . '/' . $x) < filemtime($GLOBALS['music_dir'] . '/' . $y);
        });

        $artists = array();
        foreach ($files as $file) {
          $path = $music_dir . '/' . $file;
          $getID3 = new getID3;
          $ThisFileInfo = $getID3->analyze($path);
          getid3_lib::CopyTagsToComments($ThisFileInfo);
          if(isset($ThisFileInfo['comments']['picture'][0])){
              $Image='data:'.$ThisFileInfo['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.base64_encode($ThisFileInfo['comments']['picture'][0]['data']);
          } else {
              $Image='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAIAAAC0Ujn1AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAySURBVEhL7cyhAQAgAMOwwf9H7TQMqn6uMXU9bbNxfwdcg2twDa7BNbgG1+AaXMNsnTxGDQJ8/IeWsgAAAABJRU5ErkJggg==';
          }
          if(!isset($ThisFileInfo['comments_html']['artist'][0])) continue;
          $artists[$ThisFileInfo['comments_html']['artist'][0]]['cover'] = $Image;
          $artists[$ThisFileInfo['comments_html']['artist'][0]]['genre'] = $ThisFileInfo['comments_html']['genre'][0];
          $artists[$ThisFileInfo['comments_html']['artist'][0]]['name'] = $ThisFileInfo['comments_html']['artist'][0];
        }

        foreach ($artists as $artist) {
          echo '<a href="#" class="listing">';
          echo '<img src="' . $artist['cover'] . '" class="album-cover" />';
          echo '<div class="listing-text">';
          echo '<strong>' . $artist['name'] . '</strong></br>' . $artist['genre'] . '</div></a>';
        }
        ?>
        </div>
        <div class="col-sm-6">
        <h3 style="padding-left: 1px; padding-bottom: 6px;"><strong><?php echo $messages['songs']; ?></strong></h3>
        <?php
        foreach ($files as $file) {
            $path = $music_dir . '/' . $file;
            $getID3 = new getID3;
            $ThisFileInfo = $getID3->analyze($path);
            getid3_lib::CopyTagsToComments($ThisFileInfo);
            if(isset($ThisFileInfo['comments']['picture'][0])){
              $Image='data:'.$ThisFileInfo['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.base64_encode($ThisFileInfo['comments']['picture'][0]['data']);
            } else {
              $Image='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAIAAAC0Ujn1AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAySURBVEhL7cyhAQAgAMOwwf9H7TQMqn6uMXU9bbNxfwdcg2twDa7BNbgG1+AaXMNsnTxGDQJ8/IeWsgAAAABJRU5ErkJggg==';
            }
            echo '<a href="#" class="listing">';
            echo '<img src="' . $Image . '" class="album-cover" />';
            echo '<div class="listing-text">';
            echo '<strong>' . (isset($ThisFileInfo['comments_html']['title'][0]) ? $ThisFileInfo['comments_html']['title'][0] : $file) . '</strong></br>' . $ThisFileInfo['comments_html']['artist'][0] . '</div></a>';
        }
        ?>
        </div>
        </div>
  </div>
</div>
</div>
<script>
var color_tag_bg = "";
var selected_item = "";
var must_delete = "0";
$(document).ready(function() {
    parent.showIframe();
});
</script>
</body>
</html>