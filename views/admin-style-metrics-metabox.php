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
				<tr>
					<th class="row-title" style="width: 60%;"><?php echo esc_html($metric->getName()); ?></th>
					<td>
						<?php echo esc_html($metric->getFriendlyRepresentation()) ?>
						
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<?php echo esc_html__('No style metrics enabled', 'abnet-post-stats'); ?>
<?php endif; ?>