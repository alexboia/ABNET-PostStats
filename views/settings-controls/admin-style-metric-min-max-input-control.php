<?php
/**
 * @package ABNet_PostStats
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * @var string $optionName
 * @var string $optionKey
 * @var float $min
 * @var float $max
 * @var string $description
 */

$minFieldName = sprintf('%s[%s][min]', 
	$optionName,
	 $optionKey);

$maxFieldName = sprintf('%s[%s][max]', 
	$optionName, 
	$optionKey);
?>

<label class="abnet-post-stats-interval-input-container abnet-post-stats-interval-input-min-container">
	<span class="abnet-post-stats-interval-input-label abnet-post-stats-interval-min-input-label"><?php echo esc_html__('Min', 'abnet-post-stats'); ?></span><br />
	<input type="number" 
		step="1.0" 
		class="small-text" 
		name="<?php echo esc_attr($minFieldName) ?>" 
		value="<?php echo esc_attr((string) $min); ?>" 
	/>
</label>

<label class="abnet-post-stats-interval-input-container abnet-post-stats-interval-input-max-container">
	<span class="abnet-post-stats-interval-input-label abnet-post-stats-interval-max-input-label"><?php echo esc_html__('Max', 'abnet-post-stats'); ?></span><br />
	<input type="number" 
		step="1.0" 
		class="small-text" 
		name="<?php echo esc_attr($maxFieldName) ?>" 
		value="<?php echo esc_attr((string) $max); ?>" 
	/>
</label>

<?php if (!empty($description)): ?>
	<p class="description"><?php echo esc_html($description); ?></p>
<?php endif; ?>