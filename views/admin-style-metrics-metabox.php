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

					/**
					 * Filters the row CSS class used to indicate 
					 * 	whether the metric is within its configured bracket.
					 *
					 * @param string $defaultBracketMarkerCssClass Default CSS class name.
					 * @param ABNet_PostStats_StyleMetric $metric Current metric instance.
					 */
					$bracketMarkerCssClass = apply_filters('abnet_post_stats_style_metrics_bracket_marker_css_class', 
						$defaultBracketMarkerCssClass, 
						$metric);

					if (empty($bracketMarkerCssClass)) {
						$bracketMarkerCssClass = $defaultBracketMarkerCssClass;
					}
						/* translators: Text describing the bracket range (e.g., "Between 4 and 6"), used for style metrics */
						$defaultBracketDescription = sprintf(__('Between %1$s and %2$s', 'abnet-post-stats'), 
						$metric->getBracket()->getMin(), 
						$metric->getBracket()->getMax());

					/**
					 * Filters the tooltip/description text rendered as tooltip (HTML title attribute) 
					 * 	for a metric bracket in the metabox.
					 *
					 * @param string $defaultBracketDescription Default bracket description.
					 * @param ABNet_PostStats_StyleMetric $metric Current metric instance.
					 */
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