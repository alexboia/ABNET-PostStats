<?php
/**
 * Dashboard Widget View - Simple Vertical Bar Graph
 * Renders a vertical bar graph for ABNet_Post_Stats_Result data
 * 
 * @package ABNet_PostStats
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * @var ABNet_PostStats_Result $data The data to render, passed from the dashboard renderer method that invokes this view
 * @var int $maxHeight The maximum height for the bars, can be filtered
 * @var bool $showTitle Whether to show the widget title, can be filtered
 * @var bool $showSummary Whether to show the summary stats, can be filtered
 */

/**
 * Filters the maximum bar height used when rendering the dashboard widget chart.
 *
 * @param int $maxHeight Maximum bar height in pixels.
 * @param ABNet_PostStats_Result $data Dataset rendered by the widget.
 */
$maxHeight = apply_filters('abnet_posts_stats_max_bar_height', 
	ABNET_POST_STATS_DEFAULT_MAX_BAR_HEIGHT, 
	$data);

if ($maxHeight <= 0) {
	$maxHeight = ABNET_POST_STATS_DEFAULT_MAX_BAR_HEIGHT;
}

/**
 * Filters whether the dashboard widget title should be displayed.
 *
 * @param bool $showTitle Whether to show the title.
 * @param ABNet_PostStats_Result $data Dataset rendered by the widget.
 */
$showTitle = apply_filters('abnet_posts_stats_show_widget_title', 
	ABNET_POST_STATS_DEFAULT_SHOW_TITLE, 
	$data) 
	=== true; 

/**
 * Filters whether the dashboard widget summary section should be displayed.
 *
 * @param bool $showSummary Whether to show summary statistics.
 * @param ABNet_PostStats_Result $data Dataset rendered by the widget.
 */
$showSummary = apply_filters('abnet_posts_stats_show_widget_summary', 
	ABNET_POST_STATS_DEFAULT_SHOW_SUMMARY, 
	$data)
	=== true;
?>
   
<div class="abnet-stats-widget">
	<?php if ($showTitle): ?>
		<h3 class="abnet-widget-title"><?php echo esc_html($data->getTitle()); ?></h3>
	<?php endif; ?>
	
	<div class="abnet-bar-graph-container">
		<div class="abnet-bar-graph">
			<?php foreach ($data->getItems() as $item): ?>
				<?php
					/**
					 * @var ABNet_PostStats_Item $item
					 */
					$defaultItemHeight = $data->getMaxValue() > 0 
						? ($item->getValue() / $data->getMaxValue()) * $maxHeight 
						: 0;

					/**
					 * Filters the computed bar height for a single chart item.
					 *
					 * @param float|int $height Computed item bar height in pixels.
					 * @param ABNet_PostStats_Item $item Current chart item.
					 * @param ABNet_PostStats_Result $data Dataset rendered by the widget.
					 */
					$height = apply_filters('abnet_posts_stats_item_bar_height', 
						$defaultItemHeight, 
						$item, 
						$data);

					/**
					 * Filters the bar color for a single chart item.
					 *
					 * @param string $color Current bar color.
					 * @param ABNet_PostStats_Item $item Current chart item.
					 * @param ABNet_PostStats_Result $data Dataset rendered by the widget.
					 */
					$color = apply_filters('abnet_post_stats_item_bar_color', 
						$item->getBarColor(), 
						$item, 
						$data);

					if (empty($color)) {
						$color = $item->getBarColor();
					}

					$height = max(1, $height);
					$label = $item->getLabel();
				?>
				<div class="abnet-bar-item" title="<?php echo esc_attr($label . ': ' . $item->getValue() . ' posts'); ?>">
					<div class="abnet-bar" style="height: <?php echo $height; ?>px; background: linear-gradient(to top, <?php echo $color ?>cc, <?php echo $color ?>66);">
						<span class="abnet-bar-value"><?php echo $item->hasValue() ? $item->getValue() : ''; ?></span>
					</div>
					<div class="abnet-bar-label">
						<?php echo esc_html($label); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		
		<?php if ($showSummary): ?>
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
		<?php endif; ?>
	</div>
</div>