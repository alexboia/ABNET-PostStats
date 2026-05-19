<?php
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
	esc_attr($optionName),
	 esc_attr($optionKey));

$maxFieldName = sprintf('%s[%s][max]', 
	esc_attr($optionName), 
	esc_attr($optionKey));
?>

<label class="abnet-post-stats-interval-input-container abnet-post-stats-interval-input-min-container">
	<span class="abnet-post-stats-interval-input-label abnet-post-stats-interval-min-input-label"><?php echo esc_html__('Min', 'abnet-post-stats'); ?></span><br />
	<input type="number" 
		step="1.0" 
		class="small-text" 
		name="<?php echo $minFieldName ?>" 
		value="<?php echo esc_attr((string) $min); ?>" 
	/>
</label>

<label class="abnet-post-stats-interval-input-container abnet-post-stats-interval-input-max-container">
	<span class="abnet-post-stats-interval-input-label abnet-post-stats-interval-max-input-label"><?php echo esc_html__('Max', 'abnet-post-stats'); ?></span><br />
	<input type="number" 
		step="1.0" 
		class="small-text" 
		name="<?php echo $maxFieldName ?>" 
		value="<?php echo esc_attr((string) $max); ?>" 
	/>
</label>

<?php if (!empty($description)): ?>
	<p class="description"><?php echo esc_html($description); ?></p>
<?php endif; ?>