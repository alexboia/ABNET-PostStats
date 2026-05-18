<?php
/**
 * @package ABNet_PostStats
 * @since 1.0.0
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_PostStats_Feature {
	/**
	 * @var string
	 */
	private $_targetDirectory;

	/**
	 * @var string[]
	 */
	private $_exclude = array( 'feature.php', 'index.php' );

	/**
	 * @var string
	 */
	private $_featureId = '';

	public function __construct(string $targetDirectory) {
		$this->_targetDirectory = $targetDirectory;
		$this->_featureId = basename($targetDirectory);
	}

	public function setup(): void {
		//The combination of RecursiveDirectoryIterator and RecursiveIteratorIterator 
		//	is necessary because they serve different purposes:

		//Why Both Are Needed
		//RecursiveDirectoryIterator alone only provides the structure for recursion - 
		//	it knows how to navigate directories and identify subdirectories, 
		//	but it doesn't actually traverse them automatically.

		//RecursiveIteratorIterator is the engine that performs the actual recursive traversal, 
		//	flattening the tree structure into a linear sequence you can iterate over.

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($this->_targetDirectory),
			RecursiveIteratorIterator::SELF_FIRST
		);

		$phpFiles = array();
		foreach ($iterator as $file) {
			$fileName = $file->getFilename();
			if (in_array($fileName, $this->_exclude)) {
				continue;
			}

			if ($file->isFile() && $file->getExtension() === 'php') {
				$phpFiles[] = $file->getPathname();
			}
		}

		do_action('abnet_poststats_before_feature_setup_' . $this->_featureId);

		// Include all files
		foreach ($phpFiles as $file) {
			require_once $file;
		}

		do_action('abnet_poststats_after_feature_setup_' . $this->_featureId);
	}

	public function getFeatureId(): string {
		return $this->_featureId;
	}
}