<?php
if (!defined('ABSPATH')) {
	exit;
}

// Get the current directory
$currentDir = dirname(__FILE__);

//The combination of RecursiveDirectoryIterator and RecursiveIteratorIterator 
//	is necessary because they serve different purposes:

//Why Both Are Needed
//RecursiveDirectoryIterator alone only provides the structure for recursion - 
//	it knows how to navigate directories and identify subdirectories, 
//	but it doesn't actually traverse them automatically.

//RecursiveIteratorIterator is the engine that performs the actual recursive traversal, 
//	flattening the tree structure into a linear sequence you can iterate over.

$iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($currentDir),
	RecursiveIteratorIterator::SELF_FIRST
);

$phpFiles = array();
foreach ($iterator as $file) {
	if ($file->isFile() && $file->getExtension() === 'php' && $file->getFilename() !== 'feature.php') {
		$phpFiles[] = $file->getPathname();
	}
}

// Include all files
foreach ($phpFiles as $file) {
	require_once $file;
}