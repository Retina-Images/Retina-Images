<?php

	$document_root  = $_SERVER['DOCUMENT_ROOT'];
	$requested_uri  = parse_url(urldecode($_SERVER['REQUEST_URI']), PHP_URL_PATH);
	$requested_file = basename($requested_uri);
	$source_file    = $document_root.$requested_uri;
	$retina_file    = pathinfo($source_file, PATHINFO_DIRNAME).'/'.pathinfo($source_file, PATHINFO_FILENAME).'@2x.'.pathinfo($source_file, PATHINFO_EXTENSION);

	if (file_exists($retina_file)) {
		$filename = $retina_file;
	}
	else {
		$filename = $source_file;
	}

	$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	if (in_array($extension, array('png', 'gif', 'jpeg'))) {
		header("Content-Type: image/".$extension);
	} else {
		header("Content-Type: image/jpeg");
	}
	header('Content-Length: '.filesize($filename));
	readfile($filename);

function sendErrorImage($message) {
  $im         = ImageCreateTrueColor(800, 200);
  $text_color = ImageColorAllocate($im, 233, 14, 91);
  ImageString($im, 1, 5, 5, $message, $text_color);
  header("Cache-Control: no-store");
  header('Expires: '.gmdate('D, d M Y H:i:s', time()-1000).' GMT');
  header('Content-Type: image/jpeg');
  ImageJpeg($im);
  ImageDestroy($im);
  exit();
}
