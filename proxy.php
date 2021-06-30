<?php
# -*- coding: utf-8 -*-
# @Time    : 2021-06-30 20:44
# @Author  : Mas0n
# @FileName: proxy.php
# @Software: PhpStorm
# @Blog    ：https://blog.shi1011.cn

function setLogs($file, $other="") {
    // write logs (Part of the similar waf)
    $ct = "[ " . date("Y-m-d H:i:s", time()) . " ]" . " " . $_SERVER["REMOTE_ADDR"] . " -> [" . $_SERVER["REQUEST_METHOD"] . "] - " . $_SERVER["REQUEST_URI"] . $other. " \n";
    if (!file_exists($file)){
        file_put_contents($file, $ct);
    }
    file_put_contents($file, $ct, FILE_APPEND);
}

function toEmulator($data) {
    // Pseudo-deleted library
    if ($data == null || $data == "") {
        header("HTTP/1.1 502 Bad Gateway");
        return "";
    }
    return $data;

}

function proxyByPOST($remote_server, $remote_path, $post_string) {
    // proxy POST data
    $post_string = preg_replace("/\"/", "\\\"", $post_string);
//    echo "curl --A \"" . $remote_headers['User-Agent'] . "\" -b 'cookie=by mas0n;' -d \"$post_string\" " . $remote_server . $remote_path . "\n";
    $httpResponse = shell_exec("curl -d \"$post_string\" $remote_server$remote_path");
    return $httpResponse;
}

function proxyByUpload($remote_server, $remote_path, $FileARRs) {
    // Proxy Upload file
    $httpResponse = null;
    foreach ($FileARRs as $files) {
        $httpResponse = shell_exec("curl -F 'file=@$files[tmp_name];filename=$files[name];type=$files[type];' $remote_server$remote_path");
    }
    return $httpResponse;
}

//var_dump($inHeader);
//echo  "\n" . "userIP: " . $UserIp ." -> " . $Method. " " . $URI. "\n\n" . $PostData;

// =====================Setting Start======================
$toUser = "http://172.21.16.86";
// ======================Setting End======================

$UserIp = $_SERVER["REMOTE_ADDR"]; // guest ip
$URI = $_SERVER['REQUEST_URI']; // GET uri
setLogs(__DIR__ . "/all.log");

if ($_SERVER['REQUEST_METHOD'] == "POST"){

    if (empty($_FILES)) {
        $PostData = file_get_contents('php://input'); // post body
        setLogs(__DIR__ . "/post.log", " - [BODY] " . $PostData);
        echo toEmulator(proxyByPOST($toUser, $URI, $PostData));
    } else {
        foreach ($_FILES as $f) {
            setLogs(__DIR__ . "/upload.log", " - [Base64] " . base64_encode(file_get_contents($f["tmp_name"])));
        }
        echo toEmulator(proxyByUpload($toUser, $URI, $_FILES));
    }

    die();
} else if ($_SERVER['REQUEST_METHOD'] == "GET") {
    setLogs(__DIR__ . "/get.log");
    echo toEmulator(file_get_contents($toUser . $URI));

    die();
} else {
    // Default
}
