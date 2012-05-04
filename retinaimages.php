<?php

	/* Version: 1.2 */
	
	define('DEBUG',              false);    // Write debugging information to a log file
	define('SEND_EXPIRES',       true);     // 
	define('SEND_CACHE_CONTROL', true);     // 
	define('CACHE_TIME',         24*60*60); // default: 1 day

	$document_root  = $_SERVER['DOCUMENT_ROOT'];
	$requested_uri  = parse_url(urldecode($_SERVER['REQUEST_URI']), PHP_URL_PATH);
	$requested_file = basename($requested_uri);
	$source_file    = $document_root.$requested_uri;
	$source_ext     = strtolower(pathinfo($source_file, PATHINFO_EXTENSION));

	if (DEBUG) {
		$_debug_fh = fopen('retinaimages.log', 'a');
		fwrite($_debug_fh, "* * * * * * * * * * * * * * * * * * * * * * * * * * * *\n\n");
		fwrite($_debug_fh, print_r($_COOKIE, true)."\n\n");
		fwrite($_debug_fh, "document_root:     {$document_root}\n");
		fwrite($_debug_fh, "requested_uri:     {$requested_uri}\n");
		fwrite($_debug_fh, "requested_file:    {$requested_file}\n");
		fwrite($_debug_fh, "source_file:       {$source_file}\n");
		fwrite($_debug_fh, "source_ext:        {$source_ext}\n");
	}

	// Image was requested
	if (in_array($source_ext, array('png', 'gif', 'jpg', 'jpeg', 'bmp'))) {

		// Check if DPR is high enough to warrant retina image
		if (DEBUG) { fwrite($_debug_fh, "devicePixelRatio:  {$_COOKIE['devicePixelRatio']}\n"); }
		if (isset($_COOKIE['devicePixelRatio']) && intval($_COOKIE['devicePixelRatio']) > 1) {
			// Check if retina image exists
			$retina_file = pathinfo($source_file, PATHINFO_DIRNAME).'/'.pathinfo($source_file, PATHINFO_FILENAME).'@2x.'.pathinfo($source_file, PATHINFO_EXTENSION);
			if (DEBUG) { fwrite($_debug_fh, "retina_file:       {$retina_file}\n"); }
			if (file_exists($retina_file)) {
				$source_file = $retina_file;
			}
		}

		// Close debug session if open
		if (DEBUG) {
			fwrite($_debug_fh, "sending file:      {$source_file}\n\n");
			fclose($_debug_fh);
		}

		// Send headers
		if (in_array($source_ext, array('png', 'gif', 'jpeg', 'bmp'))) {
			header("Content-Type: image/".$source_ext);
		}
		else {
			header("Content-Type: image/jpeg");
		}
		header('Content-Length: '.filesize($source_file));
		if (SEND_CACHE_CONTROL) {
			header('Cache-Control: max-age='.CACHE_TIME);  
		}
		if (SEND_EXPIRES) {
			date_default_timezone_set('GMT');
			header('Expires: '.gmdate('D, m M Y G:i:s', time()+CACHE_TIME).'   GMT');
		}

		// Send file
		readfile($source_file);
		exit();
	}

	// DPR value was sent
	elseif(isset($_GET['devicePixelRatio'])) {
		$dpr = $_GET['devicePixelRatio'];

		// Respond with a success content
		header('HTTP/1.1 200 OK');

		// Validate value before setting cookie
		if (''.intval($dpr) !== $dpr) {
			$dpr = '1';
		}

		setcookie('devicePixelRatio', $dpr);
		exit();
	}

	// Respond with an empty content
	header('HTTP/1.1 204 No Content');
