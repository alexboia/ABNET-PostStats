<?php
/**
 * @package ABNet_PostStats
 * @since 1.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_PostStats_View {
	private string $_viewsDir = ABNET_POST_STATS_VIEWS_DIR;

	private string $_settingsControlsDir = ABNET_POST_STATS_VIEWS_DIR . '/settings-controls';

	private static ?ABNet_PostStats_View $_instance = null;

	private function __construct() {
		return;
	}

	public static function getInstance(): ABNet_PostStats_View {
		if (self::$_instance === null) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function render(string $file, array $data = array(), bool $return = false): string|null {
		extract($data, EXTR_OVERWRITE);
	
		ob_start();
		require $this->_viewsDir . '/' . $file;
		$contents = ob_get_clean();

		if ($return) {
			return $contents;
		} else {
			echo $contents;
			return null;
		}
	}

	public function renderSettingsControl(string $file, array $data = array(), bool $return = false): string|null {
		extract($data, EXTR_OVERWRITE);
		
		ob_start();
		require $this->_settingsControlsDir . '/' . $file;
		$contents = ob_get_clean();

		if ($return) {
			return $contents;
		} else {
			echo $contents;
			return null;
		}
	}
}