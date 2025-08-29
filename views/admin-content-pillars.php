<?php
/**
 * Admin page for managing content pillars
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

<div class="wrap">
	<h1><?php _e('Simple Post Stats - Content Pillars Definitions', 'abnet-post-stats'); ?></h1>
	
	<?php if ($message): ?>
		<div class="notice notice-<?php echo esc_attr($messageType); ?> is-dismissible">
			<p><?php echo esc_html($message); ?></p>
		</div>
	<?php endif; ?>
	
	<div id="abnet-content-pillars-container">
		<!-- Add/Edit Form -->
		<div class="card">
			<h2><?php echo $editingPillar ? __('Edit Content Pillar', 'abnet-post-stats') : __('Add New Content Pillar', 'abnet-post-stats'); ?></h2>
			<?php require_once ABNET_POST_STATS_VIEWS_DIR . 'admin-content-pillars-form.php' ?>	
		</div>
		
		<?php require_once ABNET_POST_STATS_VIEWS_DIR . 'admin-content-pillars-list.php' ?>
	</div>
</div>