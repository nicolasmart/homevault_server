<?php
include 'common_vars.inc';
require('res/translations/bg.php'); // TODO: Change when switching languages
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>HomeVault</title>
    <!-- TODO: Switch to local instead of CDN cause Seray would be mad otherwise; 
         TODO 2: Add a common header -->
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
    <base target="_parent">
</head>
<body>
<div class="body-overlay">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
<div class="row" style="width: 100vw;">
  <div class="col-sm-3" style="height:100vh;">
    <div class="main_actions_container">
        <img src="res/drawables/music_liked.png" style="width: 100%; object-fit: contain;" />
        <p style="text-align: center; font-size: 1.3em; padding-top: 10px;">Liked Songs</p>
        <br/>
        <img src="res/drawables/music_alternative_shuffle.png" style="width: 100%; object-fit: contain; margin-top: 25px;" />
        <p style="text-align: center; font-size: 1.3em; padding-top: 10px;">Play Shuffle</p>
    </div>
  </div>
  <div class="col-sm-9" style="height: 100vh; overflow-y: scroll;">
        <div class="row">
        <div class="col-sm-6">
        <h3 style="padding-left: 1px; padding-bottom: 6px;"><strong>Albums</strong></h3>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        </div>
        <div class="col-sm-6">
        <h3 style="padding-left: 1px; padding-bottom: 6px;"><strong>Songs</strong></h3>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
        <a href="#" class="listing">
          <img src="https://d1csarkz8obe9u.cloudfront.net/posterpreviews/artistic-album-cover-design-template-d12ef0296af80b58363dc0deef077ecc_screen.jpg?ts=1561488440" class="album-cover" />
          <div class="listing-text">
            <strong>Test</strong>
            </br>
            TestName
          </div>
        </a>
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