<?php
/**
 * Admin page for managing content pillars - EDIT FORM
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

<form method="post" id="abnet-content-pillar-form">
	<?php wp_nonce_field('abnet_content_pillar_action', 'abnet_content_pillar_nonce'); ?>
	<input type="hidden" name="action" value="<?php echo $editingPillar ? 'update' : 'create'; ?>">
	<?php if ($editingPillar): ?>
		<input type="hidden" 
			name="pillar_id" 
			value="<?php echo esc_attr($editingPillar->getId()); ?>" 
		/>
	<?php endif; ?>
	
	<table class="form-table">
		<tr>
			<th scope="row">
				<label for="pillar_name"><?php _e('Content Pillar Name', 'abnet-post-stats'); ?></label>
			</th>
			<td>
				<input type="text" 
					id="pillar_name" 
					name="pillar_name" 
					value="<?php echo $editingPillar ? esc_attr($editingPillar->getName()) : ''; ?>" 
					class="regular-text" 
					required="required" 
				/>
				<p class="description"><?php _e('Enter a unique name for this content pillar.', 'abnet-post-stats'); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="pillar_color"><?php _e('Chart Color', 'abnet-post-stats'); ?></label>
			</th>
			<td>
				<input type="color" 
					id="pillar_color" 
					name="pillar_color" 
					value="<?php echo $editingPillar ? esc_attr($editingPillar->getColor()) : ABNET_POST_STATS_DEFAULT_CHART_COLOR; ?>" 
					class="color-picker" 
				/>
				<p class="description"><?php _e('Choose the color for this content pillar\'s charts and statistics.', 'abnet-post-stats'); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="category_search"><?php _e('Categories', 'abnet-post-stats'); ?></label>
			</th>
			<td>
				<div id="abnet-category-selector">
					<div class="category-search-container">
						<input type="text" 
							id="category_search" 
							placeholder="<?php esc_attr_e('Search for categories...', 'abnet-post-stats'); ?>" 
							autocomplete="off" 
						/>
						<div id="category-dropdown" class="category-dropdown"></div>
					</div>
					
					<div id="selected-categories" class="selected-categories">
						<?php if ($editingPillar): ?>
							<?php foreach ($editingPillar->getCategoryIds() as $categoryId): ?>
								<?php $category = get_category($categoryId); ?>
								<?php if ($category && !is_wp_error($category)): ?>
									<span class="selected-category" data-category-id="<?php echo esc_attr($categoryId); ?>">
										<?php echo esc_html($category->name); ?>
										<span class="remove-category">&times;</span>
										<input type="hidden" name="category_ids[]" value="<?php echo esc_attr($categoryId); ?>">
									</span>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
					<div class="category-actions">
						<button type="button" id="clear-all-categories" class="button button-secondary" style="margin-top: 10px;">
							<?php _e('Clear All Categories', 'abnet-post-stats'); ?>
						</button>
					</div>
				</div>
				<p class="description"><?php _e('Search and select categories for this content pillar. At least one category is required.', 'abnet-post-stats'); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="show_by_default"><?php _e('Show by default', 'abnet-post-stats'); ?></label>
			</th>
			<td>
				<?php $showByDefaultChecked = !$editingPillar || $editingPillar->showByDefault(); ?>
				<input 
					type="checkbox" 
					id="show_by_default" 
					name="show_by_default" 
					value="yes" 
					<?php echo $showByDefaultChecked ? 'checked="checked"' : ''; ?>
				/>
			</td>
		</tr>
	</table>
	
	<p class="submit">
		<button type="submit" class="button button-primary">
			<?php echo $editingPillar ? __('Update Content Pillar', 'abnet-post-stats') : __('Create Content Pillar', 'abnet-post-stats'); ?>
		</button>
		<?php if ($editingPillar): ?>
			<a href="<?php echo esc_url(admin_url('options-general.php?page=abnet-post-stats-content-pillars')); ?>" class="button">
				<?php _e('Cancel', 'abnet-post-stats'); ?>
			</a>
		<?php endif; ?>
	</p>
</form>