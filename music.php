<?php
include 'common_vars.inc';
include 'res/libraries/getid3/getid3.php';
if(!isset($_COOKIE["language"])) { 
  setcookie("language", "en", time() + (86400 * 365), "/");
  $_COOKIE["language"] = "en";
}
require('res/translations/' . $_COOKIE["language"] . '.php');
session_start();

if (!isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] != true && isset($_POST["username"]) && isset($_POST["password"])) {
  $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
  $sql = "SELECT name, password, user_role, folder_location FROM users WHERE name = ?";
        
  if ($stmt = mysqli_prepare($link, $sql)) {
      mysqli_stmt_bind_param($stmt, "s", $param_username);
      $param_username = $_POST["username"];
      
      if (mysqli_stmt_execute($stmt)) {
          mysqli_stmt_store_result($stmt);
          if (mysqli_stmt_num_rows($stmt) == 1) {
              mysqli_stmt_bind_result($stmt, $username, $hashed_password, $user_role, $folder_location);
              if (mysqli_stmt_fetch($stmt)) {
                  if (password_verify($_POST["password"], $hashed_password)) {
                      //session_start();
                      
                      $_SESSION["logged_in"] = true;
                      $_SESSION["username"] = $username;
                      $_SESSION["user_role"] = $user_role;
                      $_SESSION["folder_loc"] = $folder_location;               
                  } else {
                      header('location: login.php');
                  }
              }
          }
      }
      mysqli_stmt_close($stmt);
  }
}
else if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] != true) {
  header('location: login.php');
}

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

$trackIndex = 0;
if (isset($_GET['shuffle']) && $_GET['shuffle'] == '1')
{
  shuffle($files);
  header('Content-Type: application/json');
  $tracklist = array();
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
    array_push($tracklist, array("name" => (isset($ThisFileInfo['comments_html']['title'][0]) ? $ThisFileInfo['comments_html']['title'][0] : $file),
    "artist" => $ThisFileInfo['comments_html']['artist'][0], "image" => $Image, "path" => $path));
  }
  echo(json_encode(array("tracklist" => $tracklist, "trackindex" => $trackIndex)));
  exit;
}
else if (isset($_GET['artist']))
{
  shuffle($files);
  header('Content-Type: application/json');
  $tracklist = array();
  foreach ($files as $file) {
    $path = $music_dir . '/' . $file;
    $getID3 = new getID3;
    $ThisFileInfo = $getID3->analyze($path);
    getid3_lib::CopyTagsToComments($ThisFileInfo);
    if(!isset($ThisFileInfo['comments_html']['artist'][0])) continue;
    if($ThisFileInfo['comments_html']['artist'][0] != $_GET['artist']) continue;
    if(isset($ThisFileInfo['comments']['picture'][0])){
      $Image='data:'.$ThisFileInfo['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.base64_encode($ThisFileInfo['comments']['picture'][0]['data']);
    } else {
      $Image='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAIAAAC0Ujn1AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAySURBVEhL7cyhAQAgAMOwwf9H7TQMqn6uMXU9bbNxfwdcg2twDa7BNbgG1+AaXMNsnTxGDQJ8/IeWsgAAAABJRU5ErkJggg==';
    }
    array_push($tracklist, array("name" => (isset($ThisFileInfo['comments_html']['title'][0]) ? $ThisFileInfo['comments_html']['title'][0] : $file),
    "artist" => $ThisFileInfo['comments_html']['artist'][0], "image" => $Image, "path" => $path));
  }
  echo(json_encode(array("tracklist" => $tracklist, "trackindex" => $trackIndex)));
  exit;
}
else if (isset($_GET['song'])) {
  $pastSelected = false;
  header('Content-Type: application/json');
  $tracklist = array();
  foreach ($files as $file) {
    if ($file != $_GET['song'] && $pastSelected == false) $trackIndex++;
    else if ($file == $_GET['song']) $pastSelected = true;
    $path = $music_dir . '/' . $file;
    $getID3 = new getID3;
    $ThisFileInfo = $getID3->analyze($path);
    getid3_lib::CopyTagsToComments($ThisFileInfo);
    if(isset($ThisFileInfo['comments']['picture'][0])){
      $Image='data:'.$ThisFileInfo['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.base64_encode($ThisFileInfo['comments']['picture'][0]['data']);
    } else {
      $Image='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAIAAAC0Ujn1AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAySURBVEhL7cyhAQAgAMOwwf9H7TQMqn6uMXU9bbNxfwdcg2twDa7BNbgG1+AaXMNsnTxGDQJ8/IeWsgAAAABJRU5ErkJggg==';
    }
    array_push($tracklist, array("name" => (isset($ThisFileInfo['comments_html']['title'][0]) ? $ThisFileInfo['comments_html']['title'][0] : $file),
    "artist" => $ThisFileInfo['comments_html']['artist'][0], "image" => $Image, "path" => $path));
  }
  echo(json_encode(array("tracklist" => $tracklist, "trackindex" => $trackIndex)));
  exit;
}
ob_start();
ob_end_clean();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeVault</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
    <link rel="stylesheet" href="res/stylesheets/bootstrap.min.css?v=2"> 
    <link rel="stylesheet" href="res/stylesheets/main.css?v=3">
    <style type="text/css">
        .album-cover {
          height: 80px;
          width: 80px;
          object-fit: contain;
        }
        .listing {
          margin-top: 16px;
          margin-bottom: 16px;
          height: 80px;
          width: 100%;
          display: flex;
          align-items: flex-start;
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
          overflow: hidden;
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
        @media (min-width: 576px) {
          .main_actions_container {
            margin: 0;
            position: absolute;
            top: 50%;
            left: 50%;
            -ms-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
          }
        }
        @media (max-width: 767px) {
          .hidden-mobile {
            display: none;
          }
          .col-sm-9 {
            max-width: 100vw !important;
            margin-left: 10px;
          }
          .col-sm-6 {
            margin-top: 10px;
          }
        }
        @media (min-width: 768px) {
          .hidden-desktop {
            display: none;
          }
        }
        .col-sm-9 {
            -ms-flex: 0 0 100%;
            flex: 0 0 100%;
            max-width: calc(100vw - 40vh);
        }
        .player {
          height: 440px;
          display: flex;
          align-items: center;
          flex-direction: column;
          justify-content: center;
        }
        .details {
          display: flex;
          align-items: center;
          flex-direction: column;
          justify-content: center;
          margin-top: 25px;
        }
        .track-art {
          margin: 10px;
          height: 100px;
          width: 100px;
          background-size: cover;
          border-radius: 5px;
        }
        .now-playing {
          font-size: 1rem;
        }
        .track-name {
          font-size: 1.9rem;
        }
        .track-artist {
          font-size: 1.1rem;
        }
        .buttons {
          display: flex;
          flex-direction: row;
          align-items: center;
        }
        .playpause-track, .prev-track, .next-track {
          padding: 25px;
          opacity: 0.8;
          transition: opacity .2s;
          color: #187359;
        }
        .playpause-track:hover, .prev-track:hover, .next-track:hover {
          opacity: 1.0;
        }
        .slider_container {
          width: 75%;
          max-width: 400px;
          display: flex;
          justify-content: center;
          align-items: center;
          margin-bottom: 20px;
        }
        .seek_slider, .volume_slider {
          -webkit-appearance: none;
          -moz-appearance: none;
          appearance: none;
          height: 5px;
          background: black;
          opacity: 0.7;
          -webkit-transition: .2s;
          transition: opacity .2s;
        }
        .seek_slider::-webkit-slider-thumb, .volume_slider::-webkit-slider-thumb {
          -webkit-appearance: none;
          -moz-appearance: none;
          appearance: none;
          width: 15px;
          height: 15px;
          background: #999;
          cursor: pointer;
          border-radius: 50%;
        }
        .seek_slider:hover, .volume_slider:hover {
          opacity: 1.0;
        }
        .seek_slider {
          width: 60%;
        }
        .volume_slider {
          width: 30%;
        }
        .current-time, .total-duration {
          padding: 10px;
        }
        i.fa-volume-down, i.fa-volume-up {
          padding: 10px;
        }
        i.fa-play-circle, i.fa-pause-circle, i.fa-step-forward, i.fa-step-backward {
          cursor: pointer;
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

function get_thumb($img, $desired_width = 200) {
  $source_image = imagecreatefromstring($img);
  $width = imagesx($source_image);
  $height = imagesy($source_image);

  $desired_height = floor($height * ($desired_width / $width));
  $virtual_image = imagecreatetruecolor($desired_width, $desired_height);
  imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

  ob_start();
  imagejpeg($virtual_image);
  $buffer = ob_get_clean();
  return base64_encode($buffer);
}
?>
<div class="body-overlay" style="overflow: hidden;">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
<div class="row" style="width: 100vw;">
  <div class="col-sm-3 hidden-mobile" style="height:100vh; max-width:40vh;">
    <div class="main_actions_container">
        <a href="#" data-toggle="modal" data-target="#musicWidget"><img src="res/drawables/music_now_playing.png" style="width: 100%; object-fit: contain;" />
        <p style="text-align: center; font-size: 1.3em; padding-top: 10px;"><?php echo $messages['now_playing']; ?></p></a>
        <br/>
        <a href="#" onclick="ajaxGet('?shuffle=1'); return false;"><img src="res/drawables/music_alternative_shuffle.png" style="width: 100%; object-fit: contain;" />
        <p style="text-align: center; font-size: 1.3em; padding-top: 10px;"><?php echo $messages['shuffle']; ?></p></a>
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
        <div class="col-sm-6 hidden-desktop" style="font-size: 1.2rem;">
          <a href="#" data-toggle="modal" data-target="#musicWidget" class="listing">
          <img src="res/drawables/music_now_playing.png" class="album-cover" />
          <div class="listing-text" style="margin-top: 25px;"><strong><?php echo $messages['now_playing']; ?></strong></div></a>
          <a href="#" onclick="ajaxGet('?shuffle=1'); return false;" class="listing">
          <img src="res/drawables/music_alternative_shuffle.png" class="album-cover" />
          <div class="listing-text" style="margin-top: 25px;"><strong><?php echo $messages['shuffle']; ?></strong></div></a>
        </div>
        <div class="col-sm-6">
        <h3 style="padding-left: 1px; padding-bottom: 6px;"><strong><?php echo $messages['artists']; ?></strong></h3>
        <?php
        $artists = array();
        foreach ($files as $file) {
          $path = $music_dir . '/' . $file;
          $getID3 = new getID3;
          $ThisFileInfo = $getID3->analyze($path);
          getid3_lib::CopyTagsToComments($ThisFileInfo);
          if(isset($ThisFileInfo['comments']['picture'][0])){
              $Image='data:'.$ThisFileInfo['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.get_thumb($ThisFileInfo['comments']['picture'][0]['data']);
          } else {
              $Image='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAIAAAC0Ujn1AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAySURBVEhL7cyhAQAgAMOwwf9H7TQMqn6uMXU9bbNxfwdcg2twDa7BNbgG1+AaXMNsnTxGDQJ8/IeWsgAAAABJRU5ErkJggg==';
          }
          if(!isset($ThisFileInfo['comments_html']['artist'][0])) continue;
          $artists[$ThisFileInfo['comments_html']['artist'][0]]['cover'] = $Image;
          $artists[$ThisFileInfo['comments_html']['artist'][0]]['genre'] = $ThisFileInfo['comments_html']['genre'][0];
          $artists[$ThisFileInfo['comments_html']['artist'][0]]['name'] = $ThisFileInfo['comments_html']['artist'][0];
        }

        foreach ($artists as $artist) {
          echo '<a href="#" onclick="ajaxGet(\'?artist=' . urlencode($artist['name']) . '\'); return false;" class="listing">';
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
              $Image='data:'.$ThisFileInfo['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.get_thumb($ThisFileInfo['comments']['picture'][0]['data']);
            } else {
              $Image='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAIAAAC0Ujn1AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAySURBVEhL7cyhAQAgAMOwwf9H7TQMqn6uMXU9bbNxfwdcg2twDa7BNbgG1+AaXMNsnTxGDQJ8/IeWsgAAAABJRU5ErkJggg==';
            }
            echo '<a href="#" onclick="ajaxGet(\'?song=' . urlencode($file) . '\'); return false;" class="listing">';
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

<!-- Player Widget -->
<div class="modal fade" id="musicWidget" tabindex="-1" role="dialog" aria-labelledby="musicWidgetTitle" aria-hidden="true" style="overflow: hidden;">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="musicWidgetTitle"><?php echo $messages['now_playing']; ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="player">
          <div class="details">
            <div class="now-playing"><?php echo $messages['nothing_playing']; ?></div>
            <div class="track-art"></div>
            <div class="track-name"></div>
            <div class="track-artist"></div>
          </div>
          <div class="buttons">
            <div class="prev-track" onclick="prevTrack()"><i class="fa fa-step-backward fa-2x"></i></div>
            <div class="playpause-track" onclick="playpauseTrack()"><i class="fa fa-play-circle fa-5x"></i></div>
            <div class="next-track" onclick="nextTrack()"><i class="fa fa-step-forward fa-2x"></i></div>
          </div>
          <div class="slider_container">
            <div class="current-time">00:00</div>
            <input type="range" min="1" max="100" value="0" class="seek_slider" onchange="seekTo()">
            <div class="total-duration">00:00</div>
          </div>
          <div class="slider_container">
            <i class="fa fa-volume-down"></i>
            <input type="range" min="1" max="100" value="99" class="volume_slider" onchange="setVolume()">
            <i class="fa fa-volume-up"></i>
          </div>
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

let now_playing = document.querySelector(".now-playing");
let track_art = document.querySelector(".track-art");
let track_name = document.querySelector(".track-name");
let track_artist = document.querySelector(".track-artist");

let playpause_btn = document.querySelector(".playpause-track");
let next_btn = document.querySelector(".next-track");
let prev_btn = document.querySelector(".prev-track");

let seek_slider = document.querySelector(".seek_slider");
let volume_slider = document.querySelector(".volume_slider");
let curr_time = document.querySelector(".current-time");
let total_duration = document.querySelector(".total-duration");

let isPlaying = false;
let updateTimer;

let curr_track = document.createElement('audio');

var track_list = [];
var track_index = 0;

function loadTrack(track_index) {
  clearInterval(updateTimer);
  resetValues();
  curr_track.src = track_list[track_index].path;
  curr_track.load();

  track_art.style.backgroundImage = "url(" + track_list[track_index].image + ")";
  track_name.textContent = track_list[track_index].name;
  track_artist.textContent = track_list[track_index].artist;
  now_playing.textContent = "<?php echo $messages['playing_n1']; ?>" + (track_index + 1) + "<?php echo $messages['playing_n2']; ?>" + track_list.length;

  updateTimer = setInterval(seekUpdate, 1000);
  curr_track.addEventListener("ended", nextTrack);

  playTrack();
}

function resetValues() {
  curr_time.textContent = "00:00";
  total_duration.textContent = "00:00";
  seek_slider.value = 0;
}

function playpauseTrack() {
  if (!isPlaying) playTrack();
  else pauseTrack();
}

function playTrack() {
  curr_track.play();
  isPlaying = true;
  playpause_btn.innerHTML = '<i class="fa fa-pause-circle fa-5x"></i>';
}

function pauseTrack() {
  curr_track.pause();
  isPlaying = false;
  playpause_btn.innerHTML = '<i class="fa fa-play-circle fa-5x"></i>';;
}

function nextTrack() {
  if (track_index < track_list.length - 1)
    track_index += 1;
  else track_index = 0;
  loadTrack(track_index);
  playTrack();
}

function prevTrack() {
  if (track_index > 0)
    track_index -= 1;
  else track_index = track_list.length;
  loadTrack(track_index);
  playTrack();
}

function seekTo() {
  let seekto = curr_track.duration * (seek_slider.value / 100);
  curr_track.currentTime = seekto;
}

function setVolume() {
  curr_track.volume = volume_slider.value / 100;
}

function seekUpdate() {
  let seekPosition = 0;

  if (!isNaN(curr_track.duration)) {
    seekPosition = curr_track.currentTime * (100 / curr_track.duration);

    seek_slider.value = seekPosition;

    let currentMinutes = Math.floor(curr_track.currentTime / 60);
    let currentSeconds = Math.floor(curr_track.currentTime - currentMinutes * 60);
    let durationMinutes = Math.floor(curr_track.duration / 60);
    let durationSeconds = Math.floor(curr_track.duration - durationMinutes * 60);

    if (currentSeconds < 10) { currentSeconds = "0" + currentSeconds; }
    if (durationSeconds < 10) { durationSeconds = "0" + durationSeconds; }
    if (currentMinutes < 10) { currentMinutes = "0" + currentMinutes; }
    if (durationMinutes < 10) { durationMinutes = "0" + durationMinutes; }

    curr_time.textContent = currentMinutes + ":" + currentSeconds;
    total_duration.textContent = durationMinutes + ":" + durationSeconds;
  }
}

function ajaxGet(get_uri) {
  $.ajax({
    type: 'GET', 
    url: 'music.php' + get_uri,
    dataType: 'json',
    success: function (data) {
      track_list = data.tracklist;
      track_index = data.trackindex;
      loadTrack(track_index);
      $('#musicWidget').modal('show');
    },
    error: function (data) {
      console.log(data);
    }
  });
}
</script>
</body>
</html>