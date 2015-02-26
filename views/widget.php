<?php
/**
 * Post Gallery Widget: Default widget template
 * 
 * @since 1.0.0
 */

// Block direct requests
if (!defined('ABSPATH')) die('-1');
?>

<?php echo $args['before_widget']; ?>

<?php $post_query = $this->query_post($instance); ?>
<?php if ($post_query->have_posts()) : ?>
<?php while ($post_query->have_posts()) : $post_query->the_post(); ?>

<?php if ($this->has_title($instance)) : ?>
<?php echo $args['before_title']; ?>
<a href="<?php $this->the_permalink($instance);?>" title="<?php $this->the_title_attribute($instance);?>"><?php $this->the_title($instance); ?></a>
<?php echo $args['after_title']; ?>
<?php endif; ?>

<?php endwhile; ?>
<?php wp_reset_postdata(); ?>
<?php else : ?>
<div class="pt-widget-text"><?php _e('Sorry, this entry does not exist!', $this->get_widget_text_domain()); ?></div>
<?php endif; ?>

<?php echo $args['after_widget']; ?>
