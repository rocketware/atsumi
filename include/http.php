<?php

function http_post($host, $path, $headers_in, $in, &$headers_out, &$out) {
	$matches = null;

	// open socket
	$fp = fsockopen($host, 80);
	if(! $fp) return false;

	// post
	fputs($fp, "POST $path HTTP/1.0\r\n");
	$headers_in ["Host"] = $host;
	$headers_in ["Connection"] = "close";
	$headers_in ["Content-Length"] = strlen($in);
	foreach($headers_in as $header_name => $header_value)
		fputs($fp, "$header_name: $header_value\r\n");
	fputs($fp, "\r\n");
	fputs($fp, $in);

	// read response
	$response = "";
	while(! feof($fp)) {
		$response .= fgets($fp, 1024);
	}

	// close socket
	fclose($fp);

	// extract status
	$i = strpos($response, "\r\n");
	if($i === false) return false;
	if(! preg_match("/^(\\S+)(\\d+)(.+)$/", substr($response, 0, $i), $matches)) return false;
	$response_status =(int) $matches [2];

	// extract body
	$i = strpos($response, "\r\n\r\n");
	if($i === false) return false;
	$response_body = substr($response, $i + 4);

	// unserialize and return
	$out = $response_body;
	return $response_status;
}

function http_post_serialized($host, $path, $in, &$out) {

	// serialize
	$in = serialize($in);

	// setup headers
	$headers = array("Content-Type" => "application/vnc.php.serialized");

	// call http_post	
	$status = http_post($host, $path, $headers, $in, $headers, $out);

	// unserialize
	$out = @unserialize($out);

	// return
	return $status;
}

?>