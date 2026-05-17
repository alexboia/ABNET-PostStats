<?php
declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * @package ABNet_PostStats
 * @since 1.0.0
 */

class ABNet_PostStats_StyleMetric_Manager {
	private const SETTINGS_GROUP = 'abnet_post_stats_style_metrics';

	private const PAGE_SLUG = 'abnet-post-stats-style-metrics';

	/**
	 * @var ABNet_PostStats_StyleMetricOptions
	 */
	private $_options = null;

	public function __construct() {

	}

	public function isOnOptionsPage(): bool {
		/**
		 * @var \WP_Screen $screen
		 */
		$screen = get_current_screen();
		return $screen && $screen->id === 'settings_page_abnet-post-stats-style-metrics';
	}

	public function init(): void {
		add_action('admin_init', array($this, 'registerSettings'));
	}

	public function setupMenu(): void {
		add_options_page(
			__('Condei Simple Post Stats - Style Metrics', 'abnet-post-stats'),
			__('Condei Simple Post Stats - Style Metrics', 'abnet-post-stats'),
			'manage_options',
			self::PAGE_SLUG,
			array($this, 'renderSettingsPage')
		);
	}

	public function registerSettings(): void {
		register_setting(
			self::SETTINGS_GROUP,
			ABNet_PostStats_StyleMetricOptions::OPTION_NAME,
			array(
				'type' => 'array',
				'sanitize_callback' => array('ABNet_PostStats_StyleMetricOptions', 'sanitizeRawOptionsArray'),
				'default' => ABNet_PostStats_StyleMetricOptions::defaults()->toArray()
			)
		);

		add_settings_section(
			'abnet_post_stats_style_metrics_main',
			__('Metric Providers', 'abnet-post-stats'),
			array($this, 'renderMainSectionDescription'),
			self::PAGE_SLUG
		);

		$this->_registerToggleField(
			ABNet_PostStats_StyleMetricOptions::KEY_USE_AVERAGE_SENTENCE_LENGTH,
			__('Average sentence length', 'abnet-post-stats')
		);

		$this->_registerToggleField(
			ABNet_PostStats_StyleMetricOptions::KEY_USE_ENTROPY,
			__('Entropy', 'abnet-post-stats')
		);

		$this->_registerToggleField(
			ABNet_PostStats_StyleMetricOptions::KEY_USE_NEGATIVITY,
			__('Negativity', 'abnet-post-stats')
		);

		$this->_registerToggleField(
			ABNet_PostStats_StyleMetricOptions::KEY_USE_PUNCTUATION,
			__('Punctuation', 'abnet-post-stats')
		);

		$this->_registerToggleField(
			ABNet_PostStats_StyleMetricOptions::KEY_USE_LIX,
			__('LIX', 'abnet-post-stats')
		);

		$this->_registerToggleField(
			ABNet_PostStats_StyleMetricOptions::KEY_USE_YULES_K,
			__("Yule's K", 'abnet-post-stats')
		);

		$this->_registerToggleField(
			ABNet_PostStats_StyleMetricOptions::KEY_USE_HAPAX_TO_TYPES,
			__('Hapax-to-types ratio', 'abnet-post-stats')
		);

		add_settings_field(
			ABNet_PostStats_StyleMetricOptions::KEY_YULES_K_MULTIPLIER,
			__("Yule's K multiplier", 'abnet-post-stats'),
			array($this, 'renderYulesKMultiplierField'),
			self::PAGE_SLUG,
			'abnet_post_stats_style_metrics_main'
		);

		add_settings_field(
			ABNet_PostStats_StyleMetricOptions::KEY_NEGATIVE_WORD_LIST,
			__('Negative word list', 'abnet-post-stats'),
			array($this, 'renderNegativeWordListField'),
			self::PAGE_SLUG,
			'abnet_post_stats_style_metrics_main'
		);
	}

	private function _registerToggleField(string $key, string $label): void {
		add_settings_field(
			$key,
			$label,
			array($this, 'renderBooleanToggleField'),
			self::PAGE_SLUG,
			'abnet_post_stats_style_metrics_main',
			array(
				'key' => $key,
				'label' => $label
			)
		);
	}

	public function renderMainSectionDescription(): void {
		echo '<p>';
		echo esc_html__('Choose which style metrics are computed and tune provider-specific values.', 'abnet-post-stats');
		echo '</p>';
	}

	public function renderBooleanToggleField(array $args): void {
		$key = $args['key'] ?? '';
		$label = $args['label'] ?? '';

		if ($key === '') {
			return;
		}

		$options = $options = $this->getOptions()->toArray();
		$enabled = !empty($options[$key]);

		echo '<label>';
		echo 	'<input type="checkbox" name="' . esc_attr(ABNet_PostStats_StyleMetricOptions::OPTION_NAME . '[' . $key . ']') . '" value="1" ' . checked(true, $enabled, false) . ' /> ';
		echo 	esc_html__('Enabled', 'abnet-post-stats');
		echo '</label>';
	}

	public function renderYulesKMultiplierField(): void {
		$options = $this->getOptions();
		$value = (int) ($options->getYulesKMultiplier() ?? 10000);

		echo '<input type="number" min="1" step="1" class="regular-text" name="' . esc_attr(ABNet_PostStats_StyleMetricOptions::OPTION_NAME . '[' . ABNet_PostStats_StyleMetricOptions::KEY_YULES_K_MULTIPLIER . ']') . '" value="' . esc_attr((string) $value) . '" />';
		echo '<p class="description">' . esc_html__('Used by the Yule\'s K provider. Must be a positive integer.', 'abnet-post-stats') . '</p>';
	}

	public function renderNegativeWordListField(): void {
		$options = $this->getOptions();
		$words = $options->getNegativeWordList() ?? array();

		if (!is_array($words)) {
			$words = array();
		}

		$value = implode(PHP_EOL, $words);

		echo '<textarea rows="8" cols="60" class="large-text code abnet-poststats-options-textarea" name="' . esc_attr(ABNet_PostStats_StyleMetricOptions::OPTION_NAME . '[' . ABNet_PostStats_StyleMetricOptions::KEY_NEGATIVE_WORD_LIST . ']') . '">' . 
				esc_textarea($value) . 
			'</textarea>';

		echo '<p class="description">' . 
				esc_html__('One word per line. Used by the negativity provider.', 'abnet-post-stats') . 
			'</p>';
	}

	public function renderSettingsPage(): void {
		if (!current_user_can('manage_options')) {
			return;
		}

		$pageSlug = self::PAGE_SLUG;
		$settingsGroup = self::SETTINGS_GROUP;
		
		require_once ABNET_POST_STATS_VIEWS_DIR . '/admin-style-metrics-settings-page.php';
	}

	public function getOptions(): ABNet_PostStats_StyleMetricOptions {
		if ($this->_options === null) {
			$this->_options = ABNet_PostStats_StyleMetricOptions::configured();
		}

		return $this->_options;
	}
}