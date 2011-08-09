# <?php
# This file will be used directly by both php and perl, so it must be valid in both languages.
# the first line is a trick to be able to achive this php+perl parsing whilst hiding the contents when accessed via url

# Max size allowed for uploaded files
$max_upload = 41943040;

# temporary directory, must be writable by both cgi-script and php scripts
$tmp_dir = "/tmp"; 

# Where to permanently store the uploaded files, when the upload is completed
# This directory must be writable by php
$upload_dir = "/home/username/www/uploaded_files"; 
