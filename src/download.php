<?php
/* msqur - MegaSquirt .msq file viewer web application
Copyright 2014-2019 Nicholas Earwood nearwood@gmail.com https://nearwood.dev

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>. */

require "msqur.php";

if (isset($_GET['msq'])) {
  $id = intval($_GET['msq']);

  $xml = $msqur->getMSQForDownload($id);

  if ($xml) {
    header('Content-Type: application/xml');
    header('Content-Disposition: attachment; filename="' . $id . '.msq"');
    header('Pragma: no-cache');
    echo trim($xml); //`trim` is a workaround for #30
  } else {
    http_response_code(404);
    unset($_GET['msq']);
    include "view/header.php";
    echo '<div class="error">404 MSQ file not found.</div>';
    include "view/footer.php";
  }
}
else if (isset($_GET['log'])) {
  $id = intval($_GET['log']);

  $data = $rusefi->getLogForDownload($id);

  if ($data) {
    header('Content-Type: application/octet-stream');
    // todo: better file name and extension detect
    header('Content-Disposition: attachment; filename="' . $id . '.mlg"');
    header('Pragma: no-cache');
    echo $data;
  } else {
    http_response_code(404);
    unset($_GET['log']);
    include "view/header.php";
    echo '<div class="error">404 LOG file not found.</div>';
    include "view/footer.php";
  }
} else {
  include "index.php";
}
?>