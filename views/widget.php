<?php
/**
 * Post Mega Widget: Default widget template
 * 
 * @since 1.0.0
 */

// Block direct requests
if (!defined('ABSPATH')) die('-1');
?>

<?php echo $args['before_widget']; ?>

<?php if ($this->has_title($instance)) : ?>
<?php echo $args['before_title']; ?>
<?php $this->the_title($instance); ?>
<?php echo $args['after_title']; ?>
<?php endif; ?>

<?php echo $args['after_widget']; ?>
