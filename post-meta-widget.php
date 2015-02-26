<?php
/**
 * Post Meta Widget
 *
 * Display posts as widget items.
 *
 * @package   DPT_Post_Meta_Widget
 * @author    Wilfried Reiter <wilfried.reiter@devpoint.at>
 * @license   GPL-2.0+
 * @link      http://wordpress.org/extend/plugins/post-meta-widget
 * @copyright 2015 Wilfried Reiter
 *
 * @post-meta-widget
 * Plugin Name:       Post Meta Widget
 * Plugin URI:        http://wordpress.org/extend/plugins/post-meta-widget
 * Description:       An advanced posts display widget with many options: get posts by post type and taxonomy & term or by post ID; sorting & ordering; feature images; custom templates and more.
 * Version:           1.0.0
 * Author:            willriderat
 * Author URI:        http://devpoint.at
 * Text Domain:       post-meta-widget
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/devpoint/wordpress-post-meta-widget
 */

/**
 * Copyright 2015  Wilfried Reiter (email: wilfried.reiter@devpoint.at)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


// Block direct requests
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Load the widget on widgets_init
function dpt_load_post_meta_widget() {
	register_widget('DPT_Post_Meta_Widget');
}
add_action('widgets_init', 'dpt_load_post_meta_widget');


/**
 * Post Meta Widget Class
 */
class DPT_Post_Meta_Widget extends WP_Widget {

    /**
     * Plugin version number
     *
     * The variable name is used as a unique identifier for the widget
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $plugin_version = '1.0.0';

    /**
     * Unique identifier for your widget.
     *
     * The variable name is used as a unique identifier for the widget
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $widget_slug = 'dpt_post-meta-widget';
    
    /**
     * Unique identifier for your widget.
     *
     * The variable name is used as the text domain when internationalizing strings
     * of text. Its value should match the Text Domain file header in the main
     * widget file.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $widget_text_domain = 'post-meta-widget';
    
	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() 
	{
		// load translation
		load_plugin_textdomain(
			$this->get_widget_text_domain(), 
			false,
			basename(dirname(__FILE__)) . '/languages/');

		// The widget constructor
		parent::__construct(
			$this->get_widget_slug(),
			__('Post Meta', $this->get_widget_text_domain()),
			array(
				'description' => __('Displays custom fields from the current post.', $this->get_widget_text_domain()),
				'classname' => $this->get_widget_text_domain(),
			)
		);
		
		// Setup the default variables after wp is loaded
		add_action('wp_loaded', array($this, 'setup_defaults'));

		// Register admin styles and scripts
		add_action('admin_enqueue_scripts', array($this, 'register_admin_styles'));
		add_action('admin_enqueue_scripts', array($this, 'register_admin_scripts'));
	}
	
	/**
	 * Return the widget slug.
	 *
	 * @since  1.0.0
	 *
	 * @return string - Plugin slug variable.
	 */
	public function get_widget_slug() 
	{
		return $this->widget_slug;
	}

	/**
	 * Return the widget text domain.
	 *
	 * @since  1.0.0
	 *
	 * @return string - Plugin text domain variable.
	 */
	public function get_widget_text_domain() 
	{
		return $this->widget_text_domain;
	}
	
	/**
	 * Return the plugin version.
	 *
	 * @since  1.0.0
	 *
	 * @return string - Plugin version variable.
	 */
	public function get_plugin_version() 
	{
		return $this->plugin_version;
	}

	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/
	
	/**
	 * Outputs the content of the widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param  array $args - The array of form elements
	 * @param  array $instance - The current instance of the widget
	 * @return void
	 */
	public function widget($args, $instance) 
	{
		$instance['title'] = $this->_apply_text_filters(apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']));
		$instance['max_count'] = apply_filters($this->get_widget_slug() . '_max_count', $instance['max_count'], $args, $instance);
		include ($this->get_template('widget', $instance['template']));
    }

    /**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param  array $new_instance - Values just sent to be saved.
	 * @param  array $old_instance - Previously saved values from database.
	 * @return array - Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance) 
	{
		$instance = $old_instance;
		$new_instance = wp_parse_args((array)$new_instance, self::get_defaults());
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['max_count'] = strip_tags($new_instance['max_count']);
		$instance['template'] = strip_tags($new_instance['template']);
        return $instance;
    }

    /**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance - Previously saved values from database.
	 */
	public function form($instance) 
	{
		include ($this->get_template('widget-admin', 'default'));
	}

	/**
	 * Loads theme files in appropriate hierarchy:
	 * 1. child theme 2. parent theme 3. plugin resources.
	 * Will look in the plugins/contact-box-widget directory in a theme
	 * and the views directory in the plugin
	 *
	 * Based on a function in the amazing image-widget
	 * by Matt Wiebe at Modern Tribe, Inc.
	 * http://wordpress.org/extend/plugins/image-widget/
	 * 
	 * @param  string $template - template file to search for
	 * @param  string $custom_template
	 * @return string - with template path
	 **/
	protected function get_template($template, $custom_template) 
	{
		// whether or not .php was added
		$template_slug = rtrim($template, '.php');
		$template = $template_slug . '.php';
		
		// set to the default
		$file = 'views/' . $template;

		// look for a custom version
		$widgetThemeTemplates = array();
		$widgetThemePath = 'plugins/' . $this->get_widget_text_domain() . '/';
		if (!empty($custom_template) && $custom_template != 'default')
		{
			$custom_template_slug = rtrim($custom_template, '.php');
			$widgetThemeTemplates[] = $widgetThemePath . $custom_template_slug . '.php';
		}
		$widgetThemeTemplates[] = $widgetThemePath . $template;
		if ($theme_file = locate_template($widgetThemeTemplates))
		{
			$file = $theme_file;
		}
		
		return apply_filters($this->get_widget_slug() . '_template_' . $template_slug, $file);
	}

	/**
	 * @return array - with default values
	 */
	private static function get_defaults() 
	{
		$defaults = array(
			'title' => '',
			'max_count' => '',
			'template' => 'default'
		);
		return $defaults;
	}

	/*--------------------------------------------------*/
	/* Template functions
	/*--------------------------------------------------*/

	/**
	 * Check for widget title
	 *
     * @since  1.0.0
     *
     * @param  array $instance 
	 * @return bool
	 */
	public function has_title(&$instance)
	{
		return !empty($instance['title']);
	}

	/**
	 * Print widget title
	 *
     * @since  1.0.0
     *
     * @param  array $instance 
	 * @return void
	 */
	public function the_title(&$instance)
	{
		echo $instance['title'];
	}

	/**
	 * Retrieve widget max count property
	 *
     * @since  1.0.0
     *
     * @param  array $instance 
	 * @return int
	 */
	public function get_max_count(&$instance)
	{
		$max_count = 0;
		if (!empty($instance['max_count']))
		{
			$max_count = $instance['max_count'];
		}
		return $max_count;
	}

	/**
	 * Compare with widget template
	 *
     * @since  1.0.0
     *
     * @param  array  $instance 
     * @param  string $template
	 * @return bool
	 */
	public function is_template(&$instance, $template)
	{
		return ($template == (!empty($instance['template']) ? $instance['template'] : 'default'));
	}

	/**
	 * Retrieve list with custum widget templates
	 *
     * @since  1.0.0
     *
     * @return array - with custom_template[name,label]
	 */
	public function get_custom_template_list()
	{
		$custom_templates = array();
		$custom_templates = apply_filters($this->get_widget_slug() . '_template_list', $custom_templates);
		return $custom_templates;
	}

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/

	/**
	 * Registers and enqueues admin-specific styles.
	 *
     * @since  1.0.0
     *
     * @return void
 	 */
	public function register_admin_styles($hook) 
	{
		if ('widgets.php' == $hook) 
		{
    	}
	}

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 *
     * @since  1.0.0
     *
     * @param  string $hook
     * @return void
 	 */
	public function register_admin_scripts($hook) 
	{
		if ('widgets.php' == $hook) 
		{
		}
	}

	/**
     * Setup a number of default variables used throughout the plugin
     *
     * @since  1.0.0
     *
     * @return void
     */
	public function setup_defaults() 
	{
	}

	/*--------------------------------------------------*/
	/* Helper Functions
	/*--------------------------------------------------*/

	/**
     * Apply internal text filters
     *
     * @since  1.0.0
     *
     * @param string
     * @return string
     */
	protected function _apply_text_filters($input)
	{
		if (!empty($input))
		{
			$input = str_replace('[-]', '&shy;', $input);
		}
		return $input;
	}
}

