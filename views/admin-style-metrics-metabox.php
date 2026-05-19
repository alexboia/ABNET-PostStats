<?php
/**
 * @package ABNet_PostStats
 * @since 1.1.0
 * 
 * @var \ABNet_PostStats_StyleInfo $styleInfo
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}
?>

<?php if ($styleInfo->hasMetrics()): ?>
	<table id="abnet-poststats-metabox-metrics-list" class="striped abnet-poststats-metabox-metrics-list">
		<tbody>
			<?php foreach($styleInfo->getMetrics() as $metric): ?>
				<?php 
					$isWithinBracket = $metric->isWithingBracket(); 
					$defaultBracketMarkerCssClass = $isWithinBracket 
						? 'abnet-post-stats-metric-ok' 
						: 'abnet-post-stats-metric-outside';

					$bracketMarkerCssClass = apply_filters('abnet_post_stats_style_metrics_bracket_marker_css_class', 
						$defaultBracketMarkerCssClass, 
						$metric);

					if (empty($bracketMarkerCssClass)) {
						$bracketMarkerCssClass = $defaultBracketMarkerCssClass;
					}

					$defaultBracketDescription = sprintf(__('Between %s and %s'), 
						$metric->getBracket()->getMin(), 
						$metric->getBracket()->getMax());

					$bracketDescription = apply_filters('abnet_post_stats_style_metrics_bracket_description', 
						$defaultBracketDescription, 
						$metric);

					if (empty($bracketDescription)) {
						$bracketDescription = $defaultBracketDescription;
					}
				?>
				<tr class="<?php echo esc_attr($bracketMarkerCssClass) ?>">
					<th class="row-title" style="width: 60%;" title="<?php echo esc_attr($bracketDescription); ?>"><?php echo esc_html($metric->getName()); ?></th>
					<td title="<?php echo esc_attr($bracketDescription); ?>">
						<?php echo esc_html($metric->getFriendlyRepresentation()) ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<?php echo esc_html__('No style metrics enabled', 'abnet-post-stats'); ?>
<?php endif; ?>