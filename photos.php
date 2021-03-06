<?php
include 'common_vars.inc';
require('res/translations/bg.php'); // TODO: Change when switching languages
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $messages['home_files']; ?> - HomeVault</title>
    <!-- TODO: Switch to local instead of CDN cause Seray would be mad otherwise; 
         TODO 2: Add a common header -->
    <link rel="stylesheet" href="res/stylesheets/bootstrap.min.css"> 
    <link rel="stylesheet" href="res/stylesheets/main.css?v=3">
    <style type="text/css">
        .row {
          display: -ms-flexbox; /* IE 10 */
          display: flex;
          -ms-flex-wrap: wrap; /* IE 10 */
          flex-wrap: wrap;
          padding: 0 4px;
        }

        /* Create two equal columns that sits next to each other */
        .column {
          -ms-flex: 10%; /* IE 10 */
          flex: 10%;
          width: 280px;
          height: 280px;
          padding: 0 4px;
        }

        .column img {
          vertical-align: middle;
          margin-top: 4px;
          margin-bottom: 4px;
          width: 100%;
          min-height:12vw;
          max-height:12vw;
          object-fit: cover;
        }

        body {
          overflow-x: hidden;
          background: none transparent;
        }
    </style>
    <base target="_parent">
</head>
<body>
<div class="body-overlay">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
<?php
session_start();

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] != true) {
    header('location: login.php');
}

function correctImageOrientation($filename) {
    if (function_exists('exif_read_data')) {
      $exif = exif_read_data($filename);
      if($exif && isset($exif['Orientation'])) {
        $orientation = $exif['Orientation'];
        if($orientation != 1){
          $deg = 0;
          switch ($orientation) {
            case 3:
              $deg = 180;
              break;
            case 6:
              $deg = 270;
              break;
            case 8:
              $deg = 90;
              break;
          }  
          if ($deg) {
            //return " transform: rotate(-" . $deg . "deg); ";
          }
          //else return " width: 200px; ";
        }
      }
    }
    return "";
  }

$GLOBALS['images_dir'] = $images_dir = $_SESSION['folder_loc'] . '/photos' . '/';
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

$files_len = count($files);
?>
<div class="row">
  <div class="column">
    <?php for ($ii = 0; $ii<$files_len; $ii = $ii + 8) { $img_grid = correctImageOrientation($images_dir . $files[$ii]); echo '<a href="' . $images_dir . $files[$ii] . '"><img onerror="this.src=\'' . $images_dir . $files[$ii] . '\'" style="' . $img_grid . '" src="data:image/jpeg;base64,' . base64_encode(exif_thumbnail($images_dir . $files[$ii], $width, $height, $type)) . '"></a>'; }?>
  </div>
  <div class="column">
    <?php for ($ii = 1; $ii<$files_len; $ii = $ii + 8) { $img_grid = correctImageOrientation($images_dir . $files[$ii]); echo '<a href="' . $images_dir . $files[$ii] . '"><img onerror="this.src=\'' . $images_dir . $files[$ii] . '\'" style="' . $img_grid . '" src="data:image/jpeg;base64,' . base64_encode(exif_thumbnail($images_dir . $files[$ii], $width, $height, $type)) . '"></a>'; }?>
  </div>
  <div class="column">
    <?php for ($ii = 2; $ii<$files_len; $ii = $ii + 8) { $img_grid = correctImageOrientation($images_dir . $files[$ii]); echo '<a href="' . $images_dir . $files[$ii] . '"><img onerror="this.src=\'' . $images_dir . $files[$ii] . '\'" style="' . $img_grid . '" src="data:image/jpeg;base64,' . base64_encode(exif_thumbnail($images_dir . $files[$ii], $width, $height, $type)) . '"></a>'; }?>
  </div>
  <div class="column">
    <?php for ($ii = 3; $ii<$files_len; $ii = $ii + 8) { $img_grid = correctImageOrientation($images_dir . $files[$ii]); echo '<a href="' . $images_dir . $files[$ii] . '"><img onerror="this.src=\'' . $images_dir . $files[$ii] . '\'" style="' . $img_grid . '" src="data:image/jpeg;base64,' . base64_encode(exif_thumbnail($images_dir . $files[$ii], $width, $height, $type)) . '"></a>'; }?>
  </div>
  <div class="column">
    <?php for ($ii = 4; $ii<$files_len; $ii = $ii + 8) { $img_grid = correctImageOrientation($images_dir . $files[$ii]); echo '<a href="' . $images_dir . $files[$ii] . '"><img onerror="this.src=\'' . $images_dir . $files[$ii] . '\'" style="' . $img_grid . '" src="data:image/jpeg;base64,' . base64_encode(exif_thumbnail($images_dir . $files[$ii], $width, $height, $type)) . '"></a>'; }?>
  </div>
  <div class="column">
    <?php for ($ii = 5; $ii<$files_len; $ii = $ii + 8) { $img_grid = correctImageOrientation($images_dir . $files[$ii]); echo '<a href="' . $images_dir . $files[$ii] . '"><img onerror="this.src=\'' . $images_dir . $files[$ii] . '\'" style="' . $img_grid . '" src="data:image/jpeg;base64,' . base64_encode(exif_thumbnail($images_dir . $files[$ii], $width, $height, $type)) . '"></a>'; }?>
  </div>
  <div class="column">
    <?php for ($ii = 6; $ii<$files_len; $ii = $ii + 8) { $img_grid = correctImageOrientation($images_dir . $files[$ii]); echo '<a href="' . $images_dir . $files[$ii] . '"><img onerror="this.src=\'' . $images_dir . $files[$ii] . '\'" style="' . $img_grid . '" src="data:image/jpeg;base64,' . base64_encode(exif_thumbnail($images_dir . $files[$ii], $width, $height, $type)) . '"></a>'; }?>
  </div>
  <div class="column">
    <?php for ($ii = 7; $ii<$files_len; $ii = $ii + 8) { $img_grid = correctImageOrientation($images_dir . $files[$ii]); echo '<a href="' . $images_dir . $files[$ii] . '"><img onerror="this.src=\'' . $images_dir . $files[$ii] . '\'" style="' . $img_grid . '" src="data:image/jpeg;base64,' . base64_encode(exif_thumbnail($images_dir . $files[$ii], $width, $height, $type)) . '"></a>'; }?>
  </div>
</div>
</div>
</body>
</html>