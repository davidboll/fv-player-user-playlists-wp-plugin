<?php

	spl_autoload_register(function ($class) {
		$prefix = 'FVPUPE\\';
		$len = strlen($prefix);
		if (strncmp($prefix, $class, $len) !== 0)
		    return;

		$plugin_dir = WP_PLUGIN_DIR . '/fvplayer-playlists';
		$className = substr($class, $len);
		$baseDir = __DIR__ . '/classes/';
		$file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';

		if (file_exists($file)) {
		    require $file;
		}
	});

?>