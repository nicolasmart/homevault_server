<?php 
include_once('connection.php');
include '../common_vars.inc';
require('../res/translations/en.php');

$username = "";
$password = "";
$username_error = "";
$password_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["username"]))) {
        echo $messages['empty_username'];
        return;
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        echo $messages['empty_password'];
        return;
    } else{
        $password = trim($_POST["password"]);
    }

    if (empty(trim($_POST["directory"]))) return;
    
    // Validate login credentials
    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT name, password, user_role, folder_location FROM users WHERE name = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) { // Check for existing username
                    mysqli_stmt_bind_result($stmt, $username, $hashed_password, $user_role, $folder_location);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            //if (strpos($_POST['directory'], '../') !== false) return;
                            $file = '../' . $folder_location . '/files' . $_POST["directory"];
                            if (is_dir($file)) {
                                $zip = new ZipArchive();
                                $zip->open('temp/file.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

                                $files = new RecursiveIteratorIterator(
                                    new RecursiveDirectoryIterator($file),
                                    RecursiveIteratorIterator::LEAVES_ONLY
                                );

                                foreach ($files as $name => $file_single)
                                {
                                    if (!$file_single->isDir())
                                    {
                                        $filePath = $file_single->getRealPath();
                                        $relativePath = substr($filePath, strlen(realpath($file)) + 1);
                                        $zip->addFile($filePath, $relativePath);
                                    }
                                }

                                $zip->close();

                                header('Content-Description: File Transfer');
                                header('Content-Type: application/octet-stream');
                                header('Content-Disposition: attachment; filename='.basename($file).'.zip');
                                header('Content-Transfer-Encoding: binary');
                                header('Expires: 0');
                                header('Cache-Control: must-revalidate');
                                header('Pragma: public');
                                header('Content-Length: ' . filesize('temp/file.zip'));
                                readfile('temp/file.zip');
                                unlink('temp/file.zip');
                                exit;
                            }
                            if (substr($file, -6)) {
                                $file2 = file_get_contents($file);
                                $decrypted_file = decryptFile($file2, $password);
                                if (empty($decrypted_file)) {
                                    echo $messages['wrong_password'];
                                    exit;
                                }
                                else {
                                    $filename = substr(basename($file), 0, -6);
                                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                    header('Content-Type: ' . finfo_buffer($finfo, $decrypted_file));
                                    //header('Content-Length: '. filesize($file)/2);
                                    header(sprintf('Content-Disposition: attachment; filename=%s',
                                        strpos('MSIE',$_SERVER['HTTP_REFERER']) ? rawurlencode($filename) : "\"$filename\"" ));
                                    ob_flush();
                                    echo $decrypted_file;
                                    exit;
                                }
                            }

                            $filename = basename($file);
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            header('Content-Type: ' . finfo_file($finfo, $file));
                            header('Content-Length: '. filesize($file));
                            header(sprintf('Content-Disposition: attachment; filename=%s',
                                strpos('MSIE',$_SERVER['HTTP_REFERER']) ? rawurlencode($filename) : "\"$filename\"" ));
                            ob_flush();
                            readfile($file);
                            exit;
                        } else {
                            echo $messages['wrong_password'];
                        }
                    }
                } else {
                    echo $messages['account_not_exist'];
                }
            } else {
                echo $messages['generic_error'];
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    mysqli_close($link);
}

function decryptFile($data, $key) {
    $encryption_key = base64_decode($key);
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
}

?>