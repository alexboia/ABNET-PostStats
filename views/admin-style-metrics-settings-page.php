<?php
/**
 * @package ABNet_PostStats
 * @since 1.1.0
 */

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
			<?php 
				/**
				 * Fires before the style metrics settings form fields are rendered, right after the form tag.
				 *
				 * @param ABNet_PostStats_StyleMetricOptions $options Current style metric options.
				 */
				do_action('abnet_posts_stats_before_style_metrics_settings_form', $options); 
			?>
			
			<?php 
				settings_fields($settingsGroup);
				do_settings_sections($pageSlug); 
			?>

			<?php 
				/**
				 * Fires immediately before the style metrics settings submit button is rendered.
				 *
				 * @param ABNet_PostStats_StyleMetricOptions $options Current style metric options.
				 */
				do_action('abnet_posts_stats_before_style_metrics_settings_savebtn', $options); 
			?>
			
			<?php submit_button();  ?>
			
			<?php 
				/**
				 * Fires after the full style metrics settings form content has been rendered, before closing the form tag.
				 *
				 * @param ABNet_PostStats_StyleMetricOptions $options Current style metric options.
				 */
				do_action('abnet_posts_stats_after_style_metrics_settings_form', $options); 
			?>
		</form>
	</div>
</div>