<?php
// Previne accesul direct
if (!defined('ABSPATH')) {
	exit;
}

/**
 * @var string $pageSlug
 * @var string $settingsGroup
 */
?>

<div class="wrap abnet-poststats-wrap abnet-poststats-stylemetrics-settings-wrap">
	<h1><?php echo esc_html__('Condei Simple Post Stats - Style Metrics', 'abnet-post-stats'); ?></h1>

	<div class="card abnet-poststats-stylemetrics-settings-form-container">
		<form id="abnet-poststats-stylemetrics-settings-form" class="abnet-poststats-form abnet-poststats-settings-form" method="post" action="options.php">
			<?php 
				settings_fields($settingsGroup);
				do_settings_sections($pageSlug);
				submit_button();
			?>
		</form>
	</div>
</div>