<?php
/**
 * Admin page for managing content pillars - LIST EXISTING PILLARS
 * 
 * @package ABNet_Post_Stats
 * @since 1.0.0
 * 
 * Variables passed from the main plugin file:
 * @var string $message Success/error message
 * @var string $messageType 'success' or 'error'
 * @var ABNet_Post_Stats_Content_Pillar[] $contentPillars Array of content pillars
 * @var WP_Term[] $categories All categories
 * @var WP_Term[] $mostUsedCategories Top 10 most used categories
 * @var ABNet_Post_Stats_Content_Pillar|null $editingPillar Pillar being edited, if any
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}
?>

<!-- Content Pillars List -->
<div class="card">
	<h2><?php _e('Existing Content Pillars', 'abnet-post-stats'); ?></h2>
	<?php if (empty($contentPillars)): ?>
		<p><?php _e('No content pillars defined yet.', 'abnet-post-stats'); ?></p>
	<?php else: ?>
		<table class="wp-list-table widefat fixed striped abnet-content-pillars-table">
			<thead>
				<tr>
					<th><?php _e('Name', 'abnet-post-stats'); ?></th>
					<th><?php _e('Color', 'abnet-post-stats'); ?></th>
					<th><?php _e('Categories', 'abnet-post-stats'); ?></th>
					<th><?php _e('Show by default', 'abnet-post-stats'); ?></th>
					<th><?php _e('Created', 'abnet-post-stats'); ?></th>
					<th><?php _e('Actions', 'abnet-post-stats'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($contentPillars as $pillar): ?>
					<tr>
						<td><strong><?php echo esc_html($pillar->getName()); ?></strong></td>
						<td>
							<div class="color-preview" style="display: inline-flex; align-items: center;">
								<span class="color-swatch" style="display: inline-block; width: 20px; height: 20px; background-color: <?php echo esc_attr($pillar->getColor()); ?>; border: 1px solid #ccc; border-radius: 3px; margin-right: 8px;"></span>
								<code><?php echo esc_html($pillar->getColor()); ?></code>
							</div>
						</td>
						<td>
							<?php
								$categoryNames = array();
								foreach ($pillar->getCategoryIds() as $categoryId) {
									$category = get_category($categoryId);
									if ($category && !is_wp_error($category)) {
										$categoryNames[] = $category->name;
									}
								}
								echo esc_html(implode(', ', $categoryNames));
							?>
						</td>
						<td>
							<?php echo $pillar->showByDefault() ? __('Yes', 'abnet-post-stats') : __('No', 'abnet-post-stats') ?>
						</td>
						<td>
							<?php echo esc_html(date_i18n(get_option('date_format'), strtotime($pillar->getCreatedAt()))); ?>
						</td>
						<td>
							<a href="<?php echo esc_url(admin_url('options-general.php?page=abnet-post-stats-content-pillars&edit=' . $pillar->getId())); ?>" 
								class="button button-small">
								<?php _e('Edit', 'abnet-post-stats'); ?>
							</a>
							<form id="abnet_content_pillar_action_form_<?php echo esc_attr($pillar->getId()); ?>" method="post" style="display: inline;" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to delete this content pillar?', 'abnet-post-stats'); ?>');">
								<?php wp_nonce_field('abnet_content_pillar_action', 'abnet_content_pillar_nonce'); ?>
								<input type="hidden" name="action" value="delete" />
								<input type="hidden" name="pillar_id" value="<?php echo esc_attr($pillar->getId()); ?>" />
								<button type="submit" class="button button-small button-link-delete">
									<?php _e('Delete', 'abnet-post-stats'); ?>
								</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>