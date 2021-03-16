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
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
    <link rel="stylesheet" href="res/stylesheets/bootstrap.min.css"> 
    <link rel="stylesheet" href="res/stylesheets/main.css?v=3">
    <style type="text/css">
        .color_tag {
          height: 100%;
          width: 18px;
          margin-right: 20px;
        }
        .note {
          height: 80px;
          width: 100%;
          border-bottom: 1px solid rgba(0, 0, 0, 0.33);
          display: flex;
        }
        .note-text {
          padding-top: 15px;
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

?>
<div class="wrapper">
<?php
$GLOBALS['notes_dir'] = $notes_dir = $_SESSION['folder_loc'] . '/notes' . '/';
/**$files = glob($notes_dir + '/*.{jpg,png,gif,jpeg}', GLOB_BRACE);
usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});*/
$exts = array('mn');

$files = array();
$times = array();
if($handle = opendir($notes_dir)) {
    while(false !== ($file = readdir($handle))) {
        $extension = strtolower(substr(strrchr($file,'.'),1));
        if($extension && in_array($extension,$exts)) {
            $files[] = $file;
            $times[] = strval(filemtime($notes_dir . '/' . $file));
        }
    }
    closedir($handle);
}
//echo json_encode($files);
usort($files, function($x, $y) {
    return filemtime($GLOBALS['notes_dir'] . '/' . $x) < filemtime($GLOBALS['notes_dir'] . '/' . $y);
});

$filecount=0;
foreach ($files as $file) {
    $file_txt = file_get_contents($notes_dir . '/' . $file);
    $rows = preg_split('~[\r\n]+~', $file_txt);
    array_shift($rows);
    $rownum=0;
    foreach($rows as $data) {
        if ($rownum==0) {
          echo '   <a href="#" id="' . $file . '" class="note" onclick="return false;">
          <div class="color_tag" style="' . $data . '">
          </div>';
        }
        else if ($rownum==1) {
          echo '    <div class="note-text">
    <strong>' . $data;
        }
        else if ($rownum==2) {
          echo'</strong>
    </br>
    ';
        }
        else echo $data . ' ';
        $rownum++;
    }
    echo '    </div>
  </a>';
    $filecount++;
}
?>
<a href="#" class="float" data-toggle="modal" data-target="#note_creation_dialog" onclick="$('#note_creation_dialog_title').text('<?php echo $messages['create_note']; ?>'); $('#note-name').val(''); $('#note-text').val('');">
<i class="fa fa-plus floating-button"></i>
</a>
<div class="modal fade" id="note_dialog" tabindex="-1" role="dialog" aria-labelledby="note dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="note_dialog_title">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="note_content" style="display:none;">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="delete_note_button"><?php echo $messages['delete_u']; ?></button>
        <button type="button" class="btn btn-primary" id="edit_note_button"><?php echo $messages['edit']; ?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="note_creation_dialog" tabindex="-1" role="dialog" aria-labelledby="note creation dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="note_creation_dialog_title"><?php echo $messages['create_note']; ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group">
            <label for="note-name" class="col-form-label"><?php echo $messages['title']; ?>:</label>
            <input type="text" class="form-control" id="note-name">
          </div>
          <div class="form-group">
            <label for="note-text" class="col-form-label"><?php echo $messages['content']; ?>:</label>
            <textarea class="form-control" id="note-text"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $messages['cancel']; ?></button>
        <button type="button" class="btn btn-primary" id="save_note_button"><?php echo $messages['save']; ?></button>
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
$(function(){
  $('.note').click(function(){
      var note_id = $(this).attr('id');
      selected_item = note_id;
      color_tag_bg = "background: " + $(this).find('.color_tag').css('background-color') + ";";

      $.ajax({
            type : 'post',
            url : 'mobile_methods/note_preview.php',
            data : {'filename': note_id, 'logged_in': '1'},
            success : function(r)
            {
              $('#note_dialog_title').text(r.split('\n')[0]);
              $('.note_content').show().html(r.replace(/^.*?\r?\n/, ''));
              $('#note_dialog').modal('show');
            }
      });
  });
  $('#save_note_button').click(function(){
      var note_text = $("#note-name").val() + "\n" + $("#note-text").val();
      var save_args;
      if (color_tag_bg != "") save_args = {'note_content': note_text, 'color_tag': '1', 'logged_in': '1', 'color_tag_bg': color_tag_bg};
      else save_args = {'note_content': note_text, 'color_tag': '1', 'logged_in': '1'};

      $.ajax({
            type : 'post',
            url : 'mobile_methods/note_create.php',
            data :  save_args,
            success : function(r)
            {
              console.log("res: " + r);
              if (must_delete == "1") {
                $.ajax({
                      type : 'post',
                      url : 'mobile_methods/file_actions.php',
                      data : {'directory': 'notes/' + selected_item, 'logged_in': '1', 'action': '3'},
                      success : function(r)
                      {
                          must_delete = "0";
                          color_tag_bg = "";
                          $('#note_creation_dialog').modal('hide');
                          parent.hideIframe();
                          location.reload();
                      }
                });
              }
              else
              {
                $('#note_creation_dialog').modal('hide');
                parent.hideIframe();
                location.reload();
              }
            }
      });
  });
  $('#edit_note_button').click(function() {
      $('#note_dialog').modal('hide');
      $('#note_creation_dialog_title').text('<?php echo $messages['edit_note']; ?>');
      $('#note-name').val($('#note_dialog_title').text());
      $('#note-text').val($('.note_content').show().html());
      must_delete="1";
      $('#note_creation_dialog').modal('show');
  });
  $('#delete_note_button').click(function() {
      $.ajax({
            type : 'post',
            url : 'mobile_methods/file_actions.php',
            data : {'directory': 'notes/' + selected_item, 'logged_in': '1', 'action': '3'},
            success : function(r)
            {
                must_delete = "0";
                color_tag_bg = "";
                $('#note_dialog').modal('hide');
                console.log("rr: " + r);
                parent.hideIframe();
                location.reload();
            }
      });
  });
  $('#note_creation_dialog').on('hidden.bs.modal', function () {
      must_delete = "0";
      color_tag_bg = "";
  });
});
</script>
</body>
</html>