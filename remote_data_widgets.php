<?php
/*
Plugin Name: Remote data widgets
Plugin URI:
Description: Provide data from a remote REST endpoint via the Wordpress Transient API to the Widget sidebar via the Wordpress REST API. This is a base widget from which you can build other widgets.
Version: 0.1.1
Author: Seema Kumari and Snow
Author Email: webmaster@hol.ly
License:
*/


// @FIX Rename everything to Remote Data Widgets.
// @FIX PSR formatting. Note autoformatting this breaks the plugin completely.
// @FIX Code documentation.
// @FIX Remove comments that aren't documentation.
class remote_data_widgets{
  public function __construct() {
    add_action('admin_menu', array($this,'admin_menu'));
    add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
  }

  function wp_enqueue_scripts() {
    wp_register_script('remote_data_widgets', plugin_dir_url(__FILE__) . '/js/remote_data_widgets.js', array('jquery'));
  }

  // @FIX Don't use a custom top level menu.
  // @FIX Do add configuration link to plugin menu.
  public function admin_menu() {
    add_options_page('Remote data widgets', 'Remote data widgets', 'manage_options', 'remote-data-widgets-settings', array($this, 'settings'));
  }

  public function settings() {
    require_once("views/settings.php");
  }
}

new remote_data_widgets();

// Register and load the widget.
function remote_data_widgets_load_widget() {
  register_widget('remote_data_widget');
}
add_action('widgets_init', 'remote_data_widgets_load_widget');

// Creating the widget
class remote_data_widget extends WP_Widget {
  public static $base_path = 'remote-data-widget/v1';
  public static $update_fragment = 'getdata';

  function __construct() {
    parent::__construct(

      // Base ID of your widget
      'remote_data_widget',

      // Widget name will appear in UI
      __('Remote data widget', 'remote_data_widget_domain'),

      // Widget description
      array('description' => __('Provide remote API data to the sidebar', 'remote_data_widget_domain'),)
    );


    add_action('rest_api_init', array($this, 'call_to_custom_widget'));
  }

  // @FIX This gets called once per widget. Register it with the plugin instead?
  // @FIX Create one per widget so we can scope the callback function too and
  // not need to pass around the widget number.
  public function call_to_custom_widget() {
    $route = '/' . $this->_get_local_rest_route();
    register_rest_route($this::$base_path, $route, array(
      'methods' => 'POST',
      'callback' => array($this, 'get_remote_data'),
    ));
  }

  function _get_local_rest_route() {
    // Include the number in the route.
    // NOTE: This still gets destroyed and only one route is maintained.
    // $fragments = array($this::$update_fragment, $this->number);
    $fragments = array($this::$update_fragment);
    return join('/', $fragments);
  }

  function _get_local_rest_uri() {
    $fragments = array('', 'wp-json', $this::$base_path, $this->_get_local_rest_route());
    return join('/', $fragments);
  }

  function _get_remote_rest_uri($widget_id) {
    $host = trim(get_option("remote_data_widgets_remote_host"), "/");
    $path = $this->_get_widget_option($widget_id, 'pathname', '');
    return implode('/', array($host, $path));
  }

  function _get_widget_option($widget_id, $option_name, $default) {
    $widget_instance = end(explode('-', $widget_id));
    $settings = get_option($this->option_name);
    $options = $settings[$widget_instance];
    if ( $options[$option_name] )
      return $options[$option_name];
    return $default;
  }

  function get_remote_data() {
    $result = array('status' => false, 'msg' => "Unknown error!");
    if (isset($_POST['widgetid'])) {
      $widget_id = $_POST['widgetid'];
      $cachedata = get_transient($widget_id);

      if (!$cachedata) {
        $uri = $this->_get_remote_rest_uri($widget_id);
        parse_str(trim($_POST['remote_args']), $args);
        $response = wp_remote_get(add_query_arg($args, $uri));

        if (!is_wp_error($response) && $response['response']['code'] == 200) {
          $body = json_decode($response['body']);
          if ( $body ) {
            $result = array('status' => true, 'msg' => '','data' => $body);
            $cache_expiration = $this->_get_widget_option($widget_id, 'cache', 0);
            if ( intval($cache_expiration) ) {
              set_transient($widget_id, $result, 60 * 60 * $cache_expiration);
            }
            $result['cached'] = false;
          }
        }
      } else {
        $result = $cachedata;
        $result['cached'] = true;
      }
    }

    echo json_encode($result);
    die;
  }

  // Creating widget front-end
  public function widget($args, $instance) {
    wp_enqueue_script('remote_data_widgets');

    $url = $this->_get_local_rest_uri();

    $title = apply_filters('widget_title', $instance['title']);

    // before and after widget arguments are defined by themes
    echo $args['before_widget'];
    if (! empty($title))
    echo $args['before_title'] . $title . $args['after_title'];

    // print_r($instance);die;
    // This is where you run the code and display the output
    // @FIX I don't think this is the right way to do this.
    ?>
    <textarea name="placeholder"></textarea>
    <script type="text/javascript">
      jQuery(document).ready(function() {
        new wcallcls({
          initargs: '<?php echo $instance['initargs']; ?>',
          widgetid: '<?php echo $this->id; ?>',
          url:      '<?php echo $url; ?>'
        });
      });
    </script>
    <?php
    echo $args['after_widget'];
  }

  // Widget Backend
  public function form($instance) {
    if (isset($instance['title'])) {
      $title = $instance['title'];
    }
    else {
      $title = __('New title', 'remote_data_widget_domain');
    }

    if (isset($instance['pathname'])) {
      $pathname = $instance['pathname'];
    }
    else {
      $pathname = __('', 'remote_data_widget_domain');
    }

    if (isset($instance['initargs'])) {
      $initargs = $instance['initargs'];
    }
    else {
      $initargs = __('', 'remote_data_widget_domain');
    }

    if (isset($instance['cache'])) {
      $cache = $instance['cache'];
    }
    else {
      $cache = __('', 'remote_data_widget_domain');
    }
    // Widget admin form
    ?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
    </p>

    <p>
      <label for="<?php echo $this->get_field_id('pathname'); ?>"><?php _e('Pathname:'); ?> (Remote REST path)</label>
      <input class="widefat" id="<?php echo $this->get_field_id('pathname'); ?>" name="<?php echo $this->get_field_name('pathname'); ?>" type="text" value="<?php echo esc_attr($pathname); ?>" />
    </p>

    <p>
      <label for="<?php echo $this->get_field_id('initargs'); ?>"><?php _e('Init argument:'); ?> (Argument to pass to the `init` method on page load)</label>
      <input placeholder="arg1=val1&arg2=val2" class="widefat" id="<?php echo $this->get_field_id('initargs'); ?>" name="<?php echo $this->get_field_name('initargs'); ?>" type="text" value="<?php echo esc_attr($initargs); ?>" />
    </p>

    <p>
      <label for="<?php echo $this->get_field_id('cache'); ?>"><?php _e('Cache max-age:'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('cache'); ?>" name="<?php echo $this->get_field_name('cache'); ?>" type="text" value="<?php echo esc_attr($cache); ?>" />
      <span style="font-size: 12px;">Specify the maximum length to cache the response in hours. Use 0 to never cache the data.</span>
    </p>
    <?php
  }

  // Updating widget replacing old instances with new
  public function update($new_instance, $old_instance) {
    $instance = array();
    $instance['title'] = (! empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
    $instance['pathname'] = (! empty($new_instance['pathname'])) ? strip_tags($new_instance['pathname']) : '';
    $instance['initargs'] = (! empty($new_instance['initargs'])) ? strip_tags($new_instance['initargs']) : '';
    $instance['cache'] = (! empty($new_instance['cache'])) ? strip_tags($new_instance['cache']) : '';

    // Clear the transient cache.
    delete_transient($this->id);
    return $instance;
  }
}
?>
