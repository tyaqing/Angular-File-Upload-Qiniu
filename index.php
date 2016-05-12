<?php
/*
 * jQuery File Upload Plugin PHP Example for Qiniu
 * https://github.com/tyaqing/jQuery-File-Upload-Qiniu
 *
 * Copyright 2012, Roberto Colonello
 * http://www.yqapi.com
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(E_ALL | E_STRICT);
require('qiniu.php');
header('Pragma: no-cache');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Disposition: inline; filename="files.json"');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');
switch ($_SERVER['REQUEST_METHOD']) {
    case 'OPTIONS':
        break;
    case 'HEAD':
    case 'GET':
        echo json_encode(getListOfContents($bucket, $subFolder));
        break;
    case 'POST':
        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            echo json_encode(deleteObject($bucket, $subFolder));
        } else {
        	echo json_encode(uploadFiles($bucket, $subFolder));
        }
        break;
    case 'DELETE':
         echo json_encode(deleteFiles($bucket, $subFolder));
        break;
    default:
        header('HTTP/1.1 405 Method Not Allowed');
}
