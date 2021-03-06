This folder contains PHP functions that are only meant to be accessed from third-party clients, such as the mobile app.
Each of them take POST parameters 'username' and 'password' + others that are specific to the function.

Here's a guide to what each of the classes is doing:
[connection.php] - it's there to provide connection to the SQL database for the other classes, just ignore it.
[auth.php] - doesn't take any additional parameters, used on the client login page to check if the entered credentials are valid.
[file_fetch_dir.php] - outputs the folder structure by a given 'directory' POST parameter with '/' being the root of the users/user/files directory.
[file_download.php] - sends a file to client by given 'directory' filepath POST parameter .
[file_actions.php] - ???
[file_upload.php] - uploads file to a given 'directory' POST parameter, must provide the file to $FILES['file_upload'].
[file_create_dir.php] - creates the directory given by the 'directory' POST parameter.