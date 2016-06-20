<?php

    /* Version: 1.7.2 - now with even more pixels */

    define('DEBUG',              false);    // Write debugging information to a log file
    define('SEND_ETAG',          true);     // You will want to disable this if you load balance multiple servers
    define('SEND_EXPIRES',       true);
    define('SEND_CACHE_CONTROL', true);
    define('USE_X_SENDFILE',     false);    // This will reduce memory usage, but isn't enabled on all systems. If you have issues enabling this setting, contact your host
    define('DOWNSIZE_NOT_FOUND', true);     // If a regular image is requested and not found, send a retina file instead?
    define('CACHE_TIME',         24*60*60); // 1 day
    define('DISABLE_RI_HEADER',  false);

    $document_root   = $_SERVER['DOCUMENT_ROOT'];
    $requested_uri   = parse_url(urldecode(!empty($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : $_SERVER['REQUEST_URI']), PHP_URL_PATH);
    $requested_file  = basename($requested_uri);
    $source_file     = $document_root.$requested_uri;
    $source_dirname  = pathinfo($source_file, PATHINFO_DIRNAME);
    $source_filename = pathinfo($source_file, PATHINFO_FILENAME);
    $source_ext      = pathinfo($source_file, PATHINFO_EXTENSION);
    $at2x_file       = $source_dirname.'/'.$source_filename.'@2x.'.$source_ext;
    $at3x_file       = $source_dirname.'/'.$source_filename.'@3x.'.$source_ext;
    $at4x_file       = $source_dirname.'/'.$source_filename.'@4x.'.$source_ext;
    $cache_directive = 'must-revalidate';
    $status          = 'regular image';

    if (DEBUG) {
        $_debug_fh = fopen('retinaimages.log', 'a');
        fwrite($_debug_fh, "* * * * * * * * * * * * * * * * * * * * * * * * * * * *\n\n");
        fwrite($_debug_fh, print_r($_COOKIE, true)."\n\n");
        fwrite($_debug_fh, "document_root:     {$document_root}\n");
        fwrite($_debug_fh, "requested_uri:     {$requested_uri}\n");
        fwrite($_debug_fh, "requested_file:    {$requested_file}\n");
        fwrite($_debug_fh, "source_file:       {$source_file}\n");
        fwrite($_debug_fh, "source_ext:        {$source_ext}\n");
        fwrite($_debug_fh, "@2x_file:          {$at2x_file}\n");
        fwrite($_debug_fh, "@3x_file:          {$at3x_file}\n");
        fwrite($_debug_fh, "@4x_file:          {$at4x_file}\n");
    }

    // Image was requested
    if (in_array($source_ext, array('png', 'gif', 'jpg', 'jpeg', 'bmp'))) {

        // Check if a cookie is set
        $cookie_value = false;
        if (isset($_COOKIE['devicePixelRatio'])) {
            $cookie_value = intval($_COOKIE['devicePixelRatio']);
        }
        else {
            // Force revalidation of cache on next request
            $cache_directive = 'no-cache';
            $status = 'no cookie';
        }
        if (DEBUG) {
            fwrite($_debug_fh, "devicePixelRatio:  {$cookie_value}\n");
            fwrite($_debug_fh, "cache_directive:   {$cache_directive}\n");
        }

        // No need to check for retina images if screen is low DPR
        if ($cookie_value !== false && $cookie_value > 1) {
            // Check over images and match the largest resolution available
            foreach (array($at4x_file => 3, $at3x_file => 2, $at2x_file => 1) as $retina_file => $min_dpr) {
                if ($cookie_value > $min_dpr && file_exists($retina_file)) {
                    $source_file = $retina_file;
                    $status = 'retina image';
                    break;
                }
            }
        }

        // Check if we can shrink a larger version of the image
        if (!file_exists($source_file) && DOWNSIZE_NOT_FOUND){
            // Check over increasingly larger images and see if one is available
            foreach (array($at2x_file, $at3x_file, $at4x_file) as $retina_file) {
                if (file_exists($retina_file)) {
                    $source_file = $retina_file;
                    $status = 'downsized image';
                    break;
                }
            }
        }

        // Check if the image to send exists
        if (!file_exists($source_file)) {
            if (DEBUG) { fwrite($_debug_fh, "Image not found. Sending 404\n"); }
            if (!DISABLE_RI_HEADER) {
                header('X-Retina-Images: not found');
            }
            header('HTTP/1.1 404 Not Found', true);
            exit();
        }

        // Attach a Retina Images header for debugging
        if (!DISABLE_RI_HEADER) {
            header('X-Retina-Images: '.$status);
        }

        // Send cache headers
        if (SEND_CACHE_CONTROL) {
            header("Cache-Control: private, {$cache_directive}, max-age=".CACHE_TIME, true);
        }
        if (SEND_EXPIRES) {
            date_default_timezone_set('GMT');
            header('Expires: '.gmdate('D, d M Y H:i:s', time()+CACHE_TIME).' GMT', true);
        }
        if (SEND_ETAG) {
            $etag = '"'.filemtime($source_file).fileinode($source_file).'"';
            header("ETag: $etag", true);

            if (DEBUG) {
                fwrite($_debug_fh, "generated etag:    {$etag}\n");
                if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
                    fwrite($_debug_fh, "received etag:     {$_SERVER['HTTP_IF_NONE_MATCH']}\n\n");
                }
            }

            if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && ($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
                // File in cache hasn't change
                header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($source_file)).' GMT', true, 304);
                exit();
            }
        }
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) === filemtime($source_file))) {
            // File in cache hasn't change
            header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($source_file)).' GMT', true, 304);
            exit();
        }

        // Send image headers
        if (in_array($source_ext, array('png', 'gif', 'jpeg', 'bmp'))) {
            header("Content-Type: image/".$source_ext, true);
        }
        else {
            header("Content-Type: image/jpeg", true);
        }
        header('Content-Length: '.filesize($source_file), true);

        // Close debug session if open
        if (DEBUG) {
            fwrite($_debug_fh, "sending file:      {$source_file}\n\n");
            fclose($_debug_fh);
        }

        // Send file
        if (USE_X_SENDFILE) {
            header('X-Sendfile: '.$source_file);
        }
        else {
            readfile($source_file);
        }
        exit();
    }

    // DPR value was sent
    elseif(isset($_GET['devicePixelRatio'])) {
        $dpr = $_GET['devicePixelRatio'];

        // Validate value before setting cookie
        if (''.ceil(intval($dpr)) !== $dpr) {
            $dpr = '1';
        }

        setcookie('devicePixelRatio', $dpr);
        exit();
    }

    // Respond with an empty content
    header('HTTP/1.1 204 No Content', true);
