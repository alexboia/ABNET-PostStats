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
 * @var boolean $enabled
 * @var string $description
 */

$fieldName = sprintf('%s[%s]', 
	$optionName, 
	$optionKey);
?>

<label>
	<input type="checkbox" 
		name="<?php echo esc_attr($fieldName) ?>" 
		value="1" 
		<?php echo checked(true, $enabled, false) ?> 
	/>
	<?php echo esc_html__('Enabled', 'abnet-post-stats'); ?>
</label>

<?php if (!empty($description)): ?>
	<p class="description"><?php echo esc_html($description); ?></p>
<?php endif; ?>