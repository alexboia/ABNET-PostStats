<?php
/**
 * Dashboard Widget View - Simple Vertical Bar Graph
 * Renders a vertical bar graph for ABNet_Post_Stats_Result data
 * 
 * @package ABNet_Post_Stats
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * @var ABNet_Post_Stats_Result $data The data to render
 */
$maxHeight = apply_filters('abnet_posts_stats_max_bar_height', ABNET_DEFAULT_MAX_BAR_HEIGHT);
if ($maxHeight <= 0) {
	$maxHeight = ABNET_DEFAULT_MAX_BAR_HEIGHT;
}
?>
   
<div class="abnet-stats-widget">
	<h3 class="abnet-widget-title"><?php echo esc_html($data->getTitle()); ?></h3>
	
	<div class="abnet-bar-graph-container">
		<div class="abnet-bar-graph">
			<?php foreach ($data->getItems() as $item): ?>
				<?php
					$height = $data->getMaxValue() > 0 
						? ($item->getValue() / $data->getMaxValue()) * $maxHeight 
						: 0;
					$label = $item->getLabel();
				?>
				<div class="abnet-bar-item" title="<?php echo esc_attr($label . ': ' . $item->getValue() . ' posts'); ?>">
					<div class="abnet-bar" style="height: <?php echo $height; ?>px;">
						<span class="abnet-bar-value"><?php echo $item->getValue(); ?></span>
					</div>
					<div class="abnet-bar-label">
						<?php echo esc_html($label); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		
		<div class="abnet-graph-stats">
			<div class="abnet-stat-item">
				<span class="abnet-stat-label"><?php esc_html_e('Total Posts', 'abnet-post-stats'); ?>:</span>
				<span class="abnet-stat-value"><?php echo $data->getSumOfValues(); ?></span>
			</div>
			<div class="abnet-stat-item">
				<span class="abnet-stat-label"><?php esc_html_e('Average', 'abnet-post-stats'); ?>:</span>
				<span class="abnet-stat-value"><?php echo $data->getAverageValue(); ?></span>
			</div>
			<div class="abnet-stat-item">
				<span class="abnet-stat-label"><?php esc_html_e('Peak', 'abnet-post-stats'); ?>:</span>
				<span class="abnet-stat-value"><?php echo $data->getMaxValue(); ?></span>
			</div>
		</div>
	</div>
</div>