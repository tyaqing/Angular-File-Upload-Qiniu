<?php
/*
 * jQuery File Upload Plugin Server PHP for Qiniu
 *
 * Copyright 2016,ArH
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
require_once 'Qiniu/vendor/autoload.php';
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;
$bucket = 'nodeupload';
function C($name){ 
    $Conf = array(
        'AK' =>'AK',
        'SK' =>'SK',
        'bucket' => 'nodeupload',
        'bucketUrl' => '空间域名',
        'server'=>'http://localhost/ngupload/index.php?file=',//服务端 index.php位置
        );
    return $Conf[$name];
}

function getFileInfo($bucket, $key) {
    $auth = new Auth(C('AK'),C('SK'));
    $bucketMgr = new BucketManager($auth);
    list($ret,$err) = $bucketMgr->stat($bucket, $key);
    $file = new \stdClass();
    $size = $ret['fsize'];
    $furl = C('bucketUrl').'/'. $key;

    $file->size = $size;
    $file->name = $key;
    $file->url = $furl;
    $file->thumbnailUrl = $furl.'?imageMogr2/thumbnail/80x60!';
    $file->deleteUrl = C('server').$key;
    $file->deleteType =  "DELETE";
    $files = array();
    $files[] = $file;
    $response = array('files'=>$files);
    return $response;
}

function getListOfContents($bucket, $prefix="") {
    $auth = new Auth(C('AK'), C('SK'));
    $bucketMgr = new BucketManager($auth);
    list($iterms, $marker, $err) = $bucketMgr->listFiles(C('bucket'), $prefix, $marker,10);
    //组装数据
    $files = array();
    foreach ($iterms as &$value) {
        $file = new \stdClass();
        $file->size = $value['fsize'];
        $file->name = $value['key'];
        $key = $value['key'];
        $furl = C('bucketUrl').'/'.$key;
        $file->url = C('bucketUrl').'/'.$key;
        $file->thumbnailUrl = $furl.'?imageMogr2/thumbnail/80x60!';
        $file->deleteUrl =  C('server').$key;
        $file->deleteType =  "DELETE";
        $files[] = $file;
    }
    $resultArray =array('files'=>$files);
    return $resultArray;
}

function uploadFiles($bucket, $prefix="") {
    global $s3;
    if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
        return "";
    }
    $upload = isset($_FILES['files']) ? $_FILES['files'] : null;
    $info = array();
    if ($upload && is_array($upload['tmp_name'])) {
        foreach($upload['tmp_name'] as $index => $value) {
            $fileTempName = $upload['tmp_name'][$index];
            $fileName = (isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index]);
            $fileName = $prefix.str_replace(" ", "_", $fileName);            
            $auth = new Auth(C('AK'),C('SK'));
            $token = $auth->uploadToken($bucket);

            $uploadMgr = new UploadManager();
            $key = md5(uniqid()).'.'.strtolower(substr(strrchr($fileName, '.'), 1));
            list($ret, $err) = $uploadMgr->putFile($token,$key,$fileTempName);

            if($ret){
                $info= getFileInfo($bucket, $key);
            }else{
                echo 'error';
            }
        }
    } else {
        if ($upload || isset($_SERVER['HTTP_X_FILE_NAME'])) {
            $fileTempName = $upload['tmp_name'];
            $fileName = (isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : $upload['name']);
            $fileName =  $prefix.str_replace(" ", "_", $fileName);
            $auth = new Auth(C('AK'),C('SK'));
            $token = $auth->uploadToken($bucket);

            $uploadMgr = new UploadManager();
            $key = md5(uniqid()).'.'.strtolower(substr(strrchr($fileName, '.'), 1));
            list($ret, $err) = $uploadMgr->putFile($token,$key,$fileTempName);

            if($ret){
                $info= getFileInfo($bucket, $key);
            }else{
                echo 'error';
            }
        }
    }
    header('Vary: Accept');
    $json = json_encode($info);
    $redirect = isset($_REQUEST['redirect']) ? stripslashes($_REQUEST['redirect']) : null;
    if ($redirect) {
        header('Location: ' . sprintf($redirect, rawurlencode($json)));
        return;
    }
    if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
        header('Content-type: application/json');
    } else {
        header('Content-type: text/plain');
    }
    return $info;
}

function deleteFiles($bucket) {
    $auth = new Auth(C('AK'), C('SK'));
    $bucketMgr = new BucketManager($auth);

    $err = $bucketMgr->delete(C('bucket'),$_REQUEST['file']);

    $success = !$err;
    
    header('Content-type: application/json');
    return $success;
}
?>