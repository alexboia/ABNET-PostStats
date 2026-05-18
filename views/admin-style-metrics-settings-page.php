<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * @var string $pageSlug
 * @var string $settingsGroup
 * @var \ABNet_PostStats_StyleMetricOptions $options
 */
?>

<div class="wrap abnet-poststats-wrap abnet-poststats-stylemetrics-settings-wrap">
	<h1><?php echo esc_html__('Condei Simple Post Stats - Style Metrics', 'abnet-post-stats'); ?></h1>

	<div class="card abnet-poststats-stylemetrics-settings-form-container">
		<form id="abnet-poststats-stylemetrics-settings-form" class="abnet-poststats-form abnet-poststats-settings-form" method="post" action="options.php">
			<?php do_action('abnet_posts_stats_before_style_metrics_settings_form', $options); ?>
			
			<?php 
				settings_fields($settingsGroup);
				do_settings_sections($pageSlug); 
			?>

			<?php do_action('abnet_posts_stats_before_style_metrics_settings_savebtn', $options); ?>
			<?php submit_button();  ?>
			<?php do_action('abnet_posts_stats_after_style_metrics_settings_form', $options); ?>
		</form>
	</div>
</div>