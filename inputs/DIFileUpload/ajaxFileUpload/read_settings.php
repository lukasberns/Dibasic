<?php

$settingsfile = dirname(__FILE__).'/upload_settings.php';

if (file_exists($settingsfile)) {
  eval(file_get_contents($settingsfile));
} else {
  eval(file_get_contents(dirname(__FILE__).'/upload_settings-default.php'));
  // die("ajaxFileUpload/read_settings.php<br><br>Error:<br>Could not find settings file, which should be in the directory Dibasic/inputs/DIFileUpload/ajaxFileUpload/. Probably, you haven't renamed the default settings file (upload_settings_default.php) to upload-settings.php.");
}

?>