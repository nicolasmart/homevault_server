<?php
/********************************
Simple PHP File Manager
Copyright John Campbell (jcampbell1)

License: MIT
*********************************
The HomeVault File Manager
Nicola Nicolov (nicolasmart)

(includes changes to UI behavior,
more file management options, AES-256
file encryption support and more)

License: GPLv3 
********************************/
if(!isset($_COOKIE["language"])) { 
    setcookie("language", "en", time() + (86400 * 365), "/");
    $_COOKIE["language"] = "en";
}
require('res/translations/' . $_COOKIE["language"] . '.php');
session_start();

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] != true) {
    header('location: login.php');
}

error_reporting( error_reporting() & ~E_NOTICE );

$allow_delete = true;
$allow_upload = true;
$allow_create_folder = true;
$disallowed_patterns = ['*.php'];
$hidden_patterns = ['*.php','.*'];

setlocale(LC_ALL,'en_US.UTF-8');

$tmp_dir = dirname($_SERVER['SCRIPT_FILENAME']);
if(DIRECTORY_SEPARATOR==='\\') $tmp_dir = str_replace('/',DIRECTORY_SEPARATOR,$tmp_dir);
$tmp = get_absolute_path($tmp_dir . '/' .$_REQUEST['file']);

if($tmp === false)
	err(404,'File or Directory Not Found');
if(substr($tmp, 0,strlen($tmp_dir)) !== $tmp_dir)
	err(403,"Forbidden");
if(strpos($_REQUEST['file'], DIRECTORY_SEPARATOR) === 0)
	err(403,"Forbidden");
if(preg_match('@^.+://@',$_REQUEST['file'])) {
	err(403,"Forbidden");
}


if(!$_COOKIE['_sfm_xsrf'])
	setcookie('_sfm_xsrf',bin2hex(openssl_random_pseudo_bytes(16)));
if($_POST) {
	if($_COOKIE['_sfm_xsrf'] !== $_POST['xsrf'] || !$_POST['xsrf'])
		err(403,"XSRF Failure");
}

$file = strpos($_REQUEST['file'], $_SESSION['folder_loc'] . '/') !== false ? $_REQUEST['file'] : $_SESSION['folder_loc'] . '/files' . '/' . $_REQUEST['file'];
if (substr($file, 0, strlen($_SESSION['folder_loc'] . '/')) !== $_SESSION['folder_loc'] . '/') 
	err(403,"Forbidden");

if($_GET['do'] == 'list') {
	if (is_dir($file)) {
		$directory = $file;
		$result = [];
		$files = array_diff(scandir($directory), ['.','..']);
		foreach ($files as $entry) if (!is_entry_ignored($entry, $hidden_patterns)) {
			$i = $directory . '/' . $entry;
			$stat = stat($i);
			$result[] = [
				'mtime' => $stat['mtime'],
				'size' => $stat['size'],
				'name' => basename($i),
				'path' => preg_replace('@^\./@', '', $i),
				'is_dir' => is_dir($i),
				'is_deleteable' => $allow_delete && ((!is_dir($i) && is_writable($directory)) ||
														(is_dir($i) && is_writable($directory) && is_recursively_deleteable($i))),
				'is_readable' => is_readable($i),
				'is_writable' => is_writable($i),
				'is_executable' => is_executable($i),
			];
		}
		usort($result,function($f1,$f2){
			$f1_key = ($f1['is_dir']?:2) . $f1['name'];
			$f2_key = ($f2['is_dir']?:2) . $f2['name'];
			return $f1_key > $f2_key;
		});
	} else {
		err(412,"Not a Directory");
	}
	echo json_encode(['success' => true, 'is_writable' => is_writable($file), 'results' =>$result]);
	exit;
} elseif ($_POST['do'] == 'delete') {
	if($allow_delete) {
		rmrf($file);
	}
	exit;
} elseif ($_GET['do'] == 'delete') {
	if($allow_delete) {
		rmrf($file);
	}
	exit;
} elseif ($_POST['do'] == 'mkdir' && $allow_create_folder) {
	$dir = $_POST['name'];
	$dir = str_replace('/', '', $dir);
	if(substr($dir, 0, 2) === '..')
	    exit;
	chdir($file);
	@mkdir($_POST['name']);
	exit;
} elseif ($_POST['do'] == 'upload' && $allow_upload) {
	foreach($disallowed_patterns as $pattern)
		if(fnmatch($pattern, $_FILES['file_data']['name']))
			err(403,"Files of this type are not allowed.");

	$res = move_uploaded_file($_FILES['file_data']['tmp_name'], $file.'/'.$_FILES['file_data']['name']);
	exit;
} elseif ($_GET['do'] == 'download') {
	foreach($disallowed_patterns as $pattern)
		if(fnmatch($pattern, $file))
			err(403,"Files of this type are not allowed.");

	$filename = basename($file);
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	header('Content-Type: ' . finfo_file($finfo, $file));
	header('Content-Length: '. filesize($file));
	header(sprintf('Content-Disposition: attachment; filename=%s',
		strpos('MSIE',$_SERVER['HTTP_REFERER']) ? rawurlencode($filename) : "\"$filename\"" ));
	ob_flush();
	readfile($file);
	exit;
}

function is_entry_ignored($entry, $hidden_patterns) {
	if ($entry === basename(__FILE__)) {
		return true;
	}

	foreach($hidden_patterns as $pattern) {
		if(fnmatch($pattern,$entry)) {
			return true;
		}
	}
	return false;
}

function rmrf($dir) {
	if(is_dir($dir)) {
		$files = array_diff(scandir($dir), ['.','..']);
		foreach ($files as $file)
			rmrf("$dir/$file");
		rmdir($dir);
	} else {
		unlink($dir);
	}
}
function is_recursively_deleteable($d) {
	$stack = [$d];
	while($dir = array_pop($stack)) {
		if(!is_readable($dir) || !is_writable($dir))
			return false;
		$files = array_diff(scandir($dir), ['.','..']);
		foreach($files as $file) if(is_dir($file)) {
			$stack[] = "$dir/$file";
		}
	}
	return true;
}

function get_absolute_path($path) {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }

function err($code,$msg) {
	http_response_code($code);
	header("Content-Type: application/json");
	echo json_encode(['error' => ['code'=>intval($code), 'msg' => $msg]]);
	exit;
}

function asBytes($ini_v) {
	$ini_v = trim($ini_v);
	$s = ['g'=> 1<<30, 'm' => 1<<20, 'k' => 1<<10];
	return intval($ini_v) * ($s[strtolower(substr($ini_v,-1))] ?: 1);
}
$MAX_UPLOAD_SIZE = min(asBytes(ini_get('post_max_size')), asBytes(ini_get('upload_max_filesize')));
?>
<!DOCTYPE html>
<html><head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">

<style>
body {font-family: "lucida grande","Segoe UI",Arial, sans-serif; font-size: 16px;width:1024;padding:1em;margin:0;}
th {font-weight: normal; color: #34AD8B; background-color: #E9F6F2; padding:.5em 1em .5em .2em;
	text-align: left;cursor:pointer;user-select: none;}
th .indicator {margin-left: 6px }
thead {border-top: 1px solid #65CEAE; border-bottom: 1px solid #65CEAE;border-left: 1px solid #E7F2FB;
	border-right: 1px solid #E7F2FB; }
#top {height:52px;}
#mkdir {display:inline-block;float:right;padding-top:9px;}
label { display:block; font-size:11px; color:#555;}
#file_drop_target {width:calc(100% - 270px); padding:12px 0; border: 4px dashed #ccc;font-size:16px;color:#ccc;
	text-align: center;margin-right:20px;}
#file_drop_target.drag_over {border: 4px dashed #96C4EA; color: #96C4EA;}
#upload_progress {padding: 4px 0;}
#upload_progress .error {color:#a00;}
#upload_progress > div { padding:3px 0;}
.no_write #mkdir, .no_write #file_drop_target {display: none}
.progress_track {display:inline-block;width:200px;height:10px;border:1px solid #333;margin: 0 4px 0 10px;}
.progress {background-color: #82CFFA;height:10px; }
footer {font-size:11px; color:#bbbbc5; padding:4em 0 0;text-align: left;}
footer a, footer a:visited {color:#bbbbc5;}
#breadcrumb { padding-top:34px; font-size:15px; color:#aaa;display:inline-block;float:left;}
#folder_actions {width: 50%;float:right;}
a:hover {text-decoration: underline}
.sort_hide{ display:none;}
table {border-collapse: collapse;width:100%;}
thead {max-width: 1024px}
td { padding:.2em 1em .2em .2em; border-bottom:1px solid #65CEAE66;height:30px; font-size:16px;white-space: nowrap;}
td.first {font-size:16px;white-space: normal;}
td.empty { color:#777; font-style: italic; text-align: center;padding:3em 0;}
.is_dir .size {color:transparent;font-size:0;}
.is_dir .size:before {content: "--"; font-size:16px;color:#333;}
.is_dir .download{visibility: hidden}
a.delete {display:inline-block;
	background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAADtSURBVHjajFC7DkFREJy9iXg0t+EHRKJDJSqRuIVaJT7AF+jR+xuNRiJyS8WlRaHWeOU+kBy7eyKhs8lkJrOzZ3OWzMAD15gxYhB+yzAm0ndez+eYMYLngdkIf2vpSYbCfsNkOx07n8kgWa1UpptNII5VR/M56Nyt6Qq33bbhQsHy6aR0WSyEyEmiCG6vR2ffB65X4HCwYC2e9CTjJGGok4/7Hcjl+ImLBWv1uCRDu3peV5eGQ2C5/P1zq4X9dGpXP+LYhmYz4HbDMQgUosWTnmQoKKf0htVKBZvtFsx6S9bm48ktaV3EXwd/CzAAVjt+gHT5me0AAAAASUVORK5CYII=) no-repeat scroll 0 2px;
	color:#d00;	margin-left: 15px;font-size:16px;padding:0 0 0 13px;
}
.name {
	background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAABAklEQVRIie2UMW6DMBSG/4cYkJClIhauwMgx8CnSC9EjJKcwd2HGYmAwEoMREtClEJxYakmcoWq/yX623veebZmWZcFKWZbXyTHeOeeXfWDN69/uzPP8x1mVUmiaBlLKsxACAC6cc2OPd7zYK1EUYRgGZFkG3/fPAE5fIjcCAJimCXEcGxKnAiICERkSIcQmeVoQhiHatoWUEkopJEkCAB/r+t0lHyVN023c9z201qiq6s2ZYA9jDIwx1HW9xZ4+Ihta69cK9vwLvsX6ivYf4FGIyJj/rg5uqwccd2Ar7OUdOL/kPyKY5/mhZJ53/2asgiAIHhLYMARd16EoCozj6EzwCYrrX5dC9FQIAAAAAElFTkSuQmCC) no-repeat scroll 0px 12px;
	padding:15px 0 10px 40px;
}
.is_dir .name {
	background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAADdgAAA3YBfdWCzAAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAI0SURBVFiF7Vctb1RRED1nZu5977VQVBEQBKZ1GCDBEwy+ISgCBsMPwOH4CUXgsKQOAxq5CaKChEBqShNK222327f79n0MgpRQ2qC2twKOGjE352TO3Jl76e44S8iZsgOww+Dhi/V3nePOsQRFv679/qsnV96ehgAeWvBged3vXi+OJewMW/Q+T8YCLr18fPnNqQq4fS0/MWlQdviwVqNpp9Mvs7l8Wn50aRH4zQIAqOruxANZAG4thKmQA8D7j5OFw/iIgLXvo6mR/B36K+LNp71vVd1cTMR8BFmwTesc88/uLQ5FKO4+k4aarbuPnq98mbdo2q70hmU0VREkEeCOtqrbMprmFqM1psoYAsg0U9EBtB0YozUWzWpVZQgBxMm3YPoCiLpxRrPaYrBKRSUL5qn2AgFU0koMVlkMOo6G2SIymQCAGE/AGHRsWbCRKc8VmaBN4wBIwkZkFmxkWZDSFCwyommZSABgCmZBSsuiHahA8kA2iZYzSapAsmgHlgfdVyGLTFg3iZqQhAqZB923GGUgQhYRVElmAUXIGGVgedQ9AJJnAkqyClCEkkfdM1Pt13VHdxDpnof0jgxB+mYqO5PaCSDRIAbgDgdpKjtmwm13irsnq4ATdKeYcNvUZAt0dg5NVwEQFKrJlpn45lwh/LpbWdela4K5QsXEN61tytWr81l5YSY/n4wdQH84qjd2J6vEz+W0BOAGgLlE/AMAPQCv6e4gmWYC/QF3d/7zf8P/An4AWL/T1+B2nyIAAAAASUVORK5CYII=) no-repeat scroll 0px 10px;
	padding:15px 0 10px 40px;
}
.encrypted-file {
	background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAHgSURBVHgBrZTLjgFBFIZ/zEIigtjYkVjZuLwBSxuJF5DhAfAEwsrOeAIzTyCxIbEQDyAuCxsSIWElQWJB3Gr6VNIdM7pp3f0lJ1R31flP/ae6TLij2WwyaMBkMqXj8fjPy4kk8A6bzYaNx2PWbrdZq9X6lMtphg5cLheOxyMikQgsFsu3nIguAeJ6vcLtdiuK6BYQ/OehJKJbwGazYb1eY7VaYbvdwuPx4Ha7fYnvP6CTQCAg/d/v9zgcDphMJk7DBO6x2+08lsul9EyXRbvdDsVi8eE57UK3ACWPxWJoNBpP52kSEJMPh0NpbJiAmJx+B4MBF6lWq4rzXza52+3ycDqdiEajSKfTPPl8PkcymUQ4HEY+n8dbOxCuGR60kJIsFgvuNX1IRL/fR61W48k7nQ4cDoe05iniZSd8KKxerzOfz8dmsxkfU+RyOSbsQhrLBVEul9lTi6gSsiUUCsHr9UqVZbNZ+P1+xUrpylBlESFU/3A6yHfqhRJywoo9SCQSGI1GKJVKPDHtKJPJIJVKSX7/j5eIPTifzzx6vR4TLKKVTKicFQoF6Z1cXC4X9T0ggsEgptMpt0q0RlWldzwVEBGPoRZkBYTjBi2YzWZ1AlarFUbxR4C8rlQqOJ1OMIpfp9OLlrTZAlIAAAAASUVORK5CYII=) no-repeat scroll 0px 12px;
	padding:15px 0 10px 40px;
}
.download {
	background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAB2klEQVR4nJ2ST2sTQRiHn5mdmj92t9XmUJIWJGq9NHrRgxQiCtqbl97FqxgaL34CP0FD8Qv07EHEU0Ew6EXEk6ci8Q9JtcXEkHR3k+zujIdUqMkmiANzmJdnHn7vzCuIWbe291tSkvhz1pr+q1L2bBwrRgvFrcZKKinfP9zI2EoKmm7Azstf3V7fXK2Wc3ujvIqzAhglwRJoS2ImQZMEBjgyoDS4hv8QGHA1WICvp9yelsA7ITBTIkwWhGBZ0Iv+MUF+c/cB8PTHt08snb+AGAACZDj8qIN6bSe/uWsBb2qV24/GBLn8yl0plY9AJ9NKeL5ICyEIQkkiZenF5XwBDAZzWItLIIR6LGfk26VVxzltJ2gFw2a0FmQLZ+bcbo/DPbcd+PrDyRb+GqRipbGlZtX92UvzjmUpEGC0JgpC3M9dL+qGz16XsvcmCgCK2/vPtTNzJ1x2kkZIRBSivh8Z2Q4+VkvZy6O8HHvWyGyITvA1qndNpxfguQNkc2CIzM0xNk5QLedCEZm1VKsf2XrAXMNrA2vVcq4ZJ4DhvCSAeSALXASuLBTW129U6oPrT969AK4Bq0AeWARs4BRgieMUEkgDmeO9ANipzDnH//nFB0KgAxwATaAFeID5DQNatLGdaXOWAAAAAElFTkSuQmCC) no-repeat scroll 0px 5px;
	padding:4px 0 4px 20px;
}
.delete-dropdown:focus, .delete-dropdown:hover {
    color: #16181b !important;
    text-decoration: none;
    background-color: #f8f9fa !important;
}
.delete-dropdown.active, .delete-dropdown:active {
    color: #fff !important;
}
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
var global_list = null;
var id_num = 1;
(function($){
	$.fn.tablesorter = function() {
		var $table = this;
		this.find('th').click(function() {
			var idx = $(this).index();
			var direction = $(this).hasClass('sort_asc');
			$table.tablesortby(idx,direction);
		});
		return this;
	};
	$.fn.tablesortby = function(idx,direction) {
		var $rows = this.find('tbody tr');
		function elementToVal(a) {
			var $a_elem = $(a).find('td:nth-child('+(idx+1)+')');
			var a_val = $a_elem.attr('data-sort') || $a_elem.text();
			return (a_val == parseInt(a_val) ? parseInt(a_val) : a_val);
		}
		$rows.sort(function(a,b){
			var a_val = elementToVal(a), b_val = elementToVal(b);
			return (a_val > b_val ? 1 : (a_val == b_val ? 0 : -1)) * (direction ? 1 : -1);
		})
		this.find('th').removeClass('sort_asc sort_desc');
		$(this).find('thead th:nth-child('+(idx+1)+')').addClass(direction ? 'sort_desc' : 'sort_asc');
		for(var i =0;i<$rows.length;i++)
			this.append($rows[i]);
		this.settablesortmarkers();
		return this;
	}
	$.fn.retablesort = function() {
		var $e = this.find('thead th.sort_asc, thead th.sort_desc');
		if($e.length)
			this.tablesortby($e.index(), $e.hasClass('sort_desc') );

		return this;
	}
	$.fn.settablesortmarkers = function() {
		this.find('thead th span.indicator').remove();
		this.find('thead th.sort_asc').append('<span class="indicator">&darr;<span>');
		this.find('thead th.sort_desc').append('<span class="indicator">&uarr;<span>');
		return this;
	}
})(jQuery);
$(function(){
	var XSRF = (document.cookie.match('(^|; )_sfm_xsrf=([^;]*)')||0)[2];
	var MAX_UPLOAD_SIZE = <?php echo $MAX_UPLOAD_SIZE ?>;
	var $tbody = $('#list');
	$(window).on('hashchange',list).trigger('hashchange');
	$('#table').tablesorter();

	$('#table').on('click','.delete',function(data) {
		var deleteConfirmation = confirm(<?php echo "'" . $messages['delete_confirm'] . "'"; ?>);
		if (deleteConfirmation == true) {
			$.post("",{'do':'delete',file:$(this).attr('data-file'),xsrf:XSRF},function(response){
				list();
			},'json');
			list();
		}
		return false;
	});

	$('#mkdir').submit(function(e) {
		var hashval = decodeURIComponent(window.location.hash.substr(1)),
			$dir = $(this).find('[name=name]');
		e.preventDefault();
		$dir.val().length && $.post('?',{'do':'mkdir',name:$dir.val(),xsrf:XSRF,file:hashval},function(data){
			list();
		},'json');
		$dir.val('');
		return false;
	});
<?php if($allow_upload): ?>
	// file upload stuff
	$('#file_drop_target').on('dragover',function(){
		$(this).addClass('drag_over');
		return false;
	}).on('dragend',function(){
		$(this).removeClass('drag_over');
		return false;
	}).on('drop',function(e){
		e.preventDefault();
		var files = e.originalEvent.dataTransfer.files;
		$.each(files,function(k,file) {
			uploadFile(file);
		});
		$(this).removeClass('drag_over');
	});
	$('input[type=file]').change(function(e) {
		e.preventDefault();
		$.each(this.files,function(k,file) {
			uploadFile(file);
		});
	});


	function uploadFile(file) {
		var folder = decodeURIComponent(window.location.hash.substr(1));

		if(file.size > MAX_UPLOAD_SIZE) {
			var $error_row = renderFileSizeErrorRow(file,folder);
			$('#upload_progress').append($error_row);
			window.setTimeout(function(){$error_row.fadeOut();},5000);
			return false;
		}

		var $row = renderFileUploadRow(file,folder);
		$('#upload_progress').append($row);
		var fd = new FormData();
		fd.append('file_data',file);
		fd.append('file',folder);
		fd.append('xsrf',XSRF);
		fd.append('do','upload');
		var xhr = new XMLHttpRequest();
		xhr.open('POST', '?');
		xhr.onload = function() {
			$row.remove();
    		list();
  		};
		xhr.upload.onprogress = function(e){
			if(e.lengthComputable) {
				$row.find('.progress').css('width',(e.loaded/e.total*100 | 0)+'%' );
			}
		};
	    xhr.send(fd);
	}
	function renderFileUploadRow(file,folder) {
		return $row = $('<div/>')
			.append( $('<span class="fileuploadname" />').text( (folder ? folder+'/':'')+file.name))
			.append( $('<div class="progress_track"><div class="progress"></div></div>')  )
			.append( $('<span class="size" />').text(formatFileSize(file.size)) )
	};
	function renderFileSizeErrorRow(file,folder) {
		return $row = $('<div class="error" />')
			.append( $('<span class="fileuploadname" />').text( 'Error: ' + (folder ? folder+'/':'')+file.name))
			.append( $('<span/>').html(' file size - <b>' + formatFileSize(file.size) + '</b>'
				+' exceeds max upload size of <b>' + formatFileSize(MAX_UPLOAD_SIZE) + '</b>')  );
	}
<?php endif; ?>
	function list() {
		id_num = 1;
		var hashval = window.location.hash.substr(1);
		$.get('?do=list&file='+ hashval,function(data) {
			$tbody.empty();
			$('#breadcrumb').empty().html(renderBreadcrumbs(hashval));
			if(data.success) {
				if (hashval == "")	$tbody.append('<tr class="is_dir"><td class="first"><div class="btn-group dropright" id="0" style="display: initial;"><a href="#" class="name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="if (document.getElementById(\'0\').classList.contains(\'show\')) { location.href = \'#%2F..\'; }"><?php echo $messages['user_root']; ?></a><div class="dropdown-menu" style="color: #000;"><a class="dropdown-item" href="#%2F.."><?php echo $messages['open_folder']; ?></a></div></div></td><td data-sort="0"></td><td data-sort="0"></td><td></td><td></td></tr>');
				//console.log(hashval);
				$.each(data.results,function(k,v){
					$tbody.append(renderFileRow(v));
				});
				!data.results.length && $tbody.append('<tr><td class="empty" colspan=5>This folder is empty</td></tr>')
				data.is_writable ? $('body').removeClass('no_write') : $('body').addClass('no_write');
			} else {
				console.warn(data.error.msg);
			}
			$('#table').retablesort();
		},'json');
	}
	global_list = list;
	function renderFileRow(data) {
		var $link;
		var shortFilePath = (data.path).substr((data.path).includes('../') ? (data.path).indexOf('../') + 2 : (data.path).split('/', 2).join('/').length + 1);
		if (!data.is_dir) $link = '<div class="btn-group dropright" id="' + id_num.toString() + '" style="display: initial;"><a href="#" class="name' + (data.name.endsWith('.crypt') ? ' encrypted-file' : '') + '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="if (document.getElementById(\'' + id_num.toString() + '\').classList.contains(\'show\')) { ' + (data.name.endsWith('.crypt') ? 'window.parent.fileCrypt(\'7\', \'' + shortFilePath + '\', global_list);' : 'location.href = \'?do=download&file=' + encodeURIComponent(data.path) + '\';') + ' }">' + data.name + '</a><div class="dropdown-menu" style="color: #000;"><a class="dropdown-item" href="?do=download&file=' + encodeURIComponent(data.path) + '"><b><?php echo $messages['download_u']; ?></b></a><a class="dropdown-item" href="javascript:void(0);" onclick="window.parent.fileCrypt(\'' + (data.name.endsWith('.crypt') ? '6' : '5') + '\', \'' + shortFilePath + '\', global_list)">' + (data.name.endsWith('.crypt') ? '<?php echo $messages['decrypt']; ?>' : '<?php echo $messages['encrypt']; ?>') + '</a><a class="dropdown-item" href="javascript:void(0);" onclick="fileAction(\'1\', \'' + shortFilePath + '\', prompt(\'<?php echo $messages['move_prompt']; ?>\', \'' + shortFilePath.substr(6, shortFilePath.lastIndexOf('/') - 5) + '\'))"><?php echo $messages['move']; ?></a><a class="dropdown-item" href="javascript:void(0);" onclick="fileAction(\'2\', \'' + shortFilePath + '\', prompt(\'<?php echo $messages['copy_prompt']; ?>\', \'' + shortFilePath.substr(6, shortFilePath.lastIndexOf('/') - 5) + '\'))"><?php echo $messages['copy']; ?></a><a class="dropdown-item" href="javascript:void(0);" onclick="fileAction(\'4\', \'' + shortFilePath + '\', prompt(\'<?php echo $messages['rename_prompt']; ?>\', \'' + data.name + '\'))"><?php echo $messages['rename']; ?></a><a class="dropdown-item delete delete-dropdown" style="background: none; color: #000; margin-left: 0px; padding: .25rem 1.5rem;" href="#" data-file="' + data.path + '"><?php echo $messages['delete_u']; ?></a></div></div>';
		else $link = '<div class="btn-group dropright" id="' + id_num.toString() + '" style="display: initial;"><a href="#' + encodeURIComponent(data.path.replace('<?php echo $_SESSION['folder_loc'] . '/files' . '/'; ?>', '')) + '" class="name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="if (document.getElementById(\'' + id_num.toString() + '\').classList.contains(\'show\')) { location.href = \'#' + encodeURIComponent(data.path.replace('<?php echo $_SESSION['folder_loc'] . '/files' . '/'; ?>', '')) + '\'; }">' + data.name + '</a><div class="dropdown-menu" style="color: #000;"><a class="dropdown-item" href="#' + encodeURIComponent(data.path.replace('<?php echo $_SESSION['folder_loc'] . '/files' . '/'; ?>', '')) + '"><?php echo $messages['open_folder']; ?></a><a class="dropdown-item" href="javascript:void(0);" onclick="fileAction(\'1\', \'' + shortFilePath + '\', prompt(\'<?php echo $messages['move_prompt']; ?>\', \'' + shortFilePath.substr(6, shortFilePath.lastIndexOf('/') - 5) + '\'))"><?php echo $messages['move']; ?></a><a class="dropdown-item" href="javascript:void(0);" onclick="fileAction(\'2\', \'' + shortFilePath + '\', prompt(\'<?php echo $messages['copy_prompt']; ?>\', \'' + shortFilePath.substr(6, shortFilePath.lastIndexOf('/') - 5) + '\'))"><?php echo $messages['copy']; ?></a><a class="dropdown-item" href="javascript:void(0);" onclick="fileAction(\'4\', \'' + shortFilePath + '\', prompt(\'<?php echo $messages['rename_prompt']; ?>\', \'' + data.name + '\'))"><?php echo $messages['rename']; ?></a><a class="dropdown-item delete delete-dropdown" style="background: none; color: #000; margin-left: 0px; padding: .25rem 1.5rem;" href="#" data-file="' + data.path + '"><?php echo $messages['delete_u']; ?></a></div></div>';
		var $dl_link = $('<a/>').attr('href','?do=download&file='+ encodeURIComponent(data.path))
			.addClass('download').text(<?php echo "'" . $messages['download'] . "'"; ?>);
		var $delete_link = $('<a href="#" />').attr('data-file',data.path).addClass('delete').text(<?php echo "'" . $messages['delete'] . "'"; ?>);
		var perms = [];
		if(data.is_readable) perms.push('<?php echo $messages['read_perm']; ?>');
		if(data.is_writable) perms.push('<?php echo $messages['write_perm']; ?>');
		if(data.is_executable) perms.push('<?php echo $messages['exec_perm']; ?>');
		id_num = id_num + 1;
		var $html = $('<tr />')
			.addClass(data.is_dir ? 'is_dir' : '')
			.append( $('<td class="first" />').append($link) )
			.append( $('<td/>').append(data.is_dir ? '<?php echo $messages['folder']; ?>' : getExtensionHV(data.name)) )
			.append( $('<td/>').attr('data-sort',data.is_dir ? -1 : data.size)
				.html($('<span class="size" />').text(formatFileSize(data.size))) )
			.append( $('<td/>').attr('data-sort',data.mtime).text(formatTimestamp(data.mtime)) )
			//.append( $('<td/>').text(perms.join('+')) )
			.append( $('<td/>')/**.append($dl_link)*/.append( data.is_deleteable ? $delete_link : '') )
		return $html;
	}
	function renderBreadcrumbs(path) {
		var base = "",
			$html = $('<div/>').append( $('<a href=#><?php echo $messages['home']; ?></a></div>') );
		$.each(path.split('%2F'),function(k,v){
			if(v) {
				var v_as_text = decodeURIComponent(v);
				$html.append( $('<span/>').text(' ▸ ') )
					.append( $('<a/>').attr('href','#%2F'+base+v).text(v_as_text) );
				base += v + '%2F';
			}
		});
		return $html;
	}
	function formatTimestamp(unix_timestamp) {
		var m = <?php echo $messages['month_array']; ?>;
		var d = new Date(unix_timestamp*1000);
		return [m[d.getMonth()],' ',d.getDate(),', ',d.getFullYear()," ",
			(d.getHours()),":",(d.getMinutes() < 10 ? '0' : '')+d.getMinutes()].join('');
	}
	function formatFileSize(bytes) {
		var s = ['bytes', 'KB','MB','GB','TB','PB','EB'];
		for(var pos = 0;bytes >= 1000; pos++,bytes /= 1024);
		var d = Math.round(bytes*10);
		return pos ? [parseInt(d/10),".",d%10," ",s[pos]].join('') : bytes + ' bytes';
	}
	function getExtensionHV(filename) {
		if (filename.lastIndexOf('.crypt') != -1) {
			var lastDot = filename.substr(0, filename.lastIndexOf('.crypt')).lastIndexOf('.')+1;
			return filename.substr(lastDot, filename.lastIndexOf('.crypt')-lastDot).toUpperCase();
		}
		return filename.substr(filename.lastIndexOf('.')+1).toUpperCase();
	}

	
});
function fileAction(action, d1, d2)
{
	if (d2 === null) {
        return;
    }
	$.ajax({
			type : 'post',
			url : 'mobile_methods/file_actions.php',
			data : {'directory': d1, 'directory2': d2, 'logged_in': '1', 'action': action},
			success : function(r)
			{
				global_list();
			}
	});
	global_list();
}
</script>
<link rel="stylesheet" href="res/stylesheets/bootstrap.min.css"> 
<link rel="stylesheet" href="res/stylesheets/main.css?v=3">
<style type="text/css">
body {
	background: transparent;
	font-size: 16px !important;
}
</style>
</head><body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
<div id="top">
   <?php if($allow_create_folder): ?>
	<form action="?" method="post" id="mkdir" />
		<input id=dirname class="form-control" type=text name=name placeholder="<?php echo $messages['new_folder']; ?>" value="" style="display: inline-block; width: 190px;" />
		<input type="submit" class="btn btn-outline-primary" value="+" style="height: 35px;margin-bottom: 3px;font-size: 14px; padding-left:24px;padding-right:24px;" />
	</form>

   <?php endif; ?>

   <?php if($allow_upload): ?>

	<div id="file_drop_target">
		<?php echo $messages['file_drag_or']; ?>
		<input type="file" multiple />
	</div>
   <?php endif; ?>
	<div id="breadcrumb">&nbsp;</div>
</div>

<div id="upload_progress"></div>
<table id="table"><thead><tr>
	<th><?php echo $messages['file_name']; ?></th>
	<th><?php echo $messages['file_type']; ?></th>
	<th><?php echo $messages['file_size']; ?></th>
	<th><?php echo $messages['file_modified']; ?></th>
	<!--<th><?php echo $messages['file_permissions']; ?></th>-->
	<th><?php echo $messages['file_actions']; ?></th>
</tr></thead><tbody id="list">

</tbody></table>
</body></html>
