<?php
/*
Plugin Name: Dynamic Widget Call
Plugin URI:
Description: Provide data from a remote REST endpoint via the Wordpress Transient API to the Widget sidebar via the Wordpress REST API.
Version: 0.1.1
Author: Seema Kumari and Snow
Author Email: webmaster@hol.ly
License:
*/


// @FIX Rename everything to Remote Data Widgets.
// @FIX PSR formatting. Note autoformatting this breaks the plugin completely.
// @FIX Code documentation.
// @FIX Remove comments that aren't documentation.
class wcall{
  public function __construct() {
    add_action('admin_menu', array($this,'wcall_menu_page'));
    add_action('wp_enqueue_scripts',array($this,'wcall_scripts_method'));
  }

  function wcall_scripts_method() {
    wp_register_script('wcall', plugin_dir_url(__FILE__) . '/js/wcall.js', array('jquery'));
  }

  // @FIX Don't use a custom top level menu.
  // @FIX Do add configuration link to plugin menu.
  public function wcall_menu_page() {
    add_menu_page('Dynamic Widget', 'Dynamic Widget', 'manage_options', 'wcall-settings', array($this,'wcall_settings'));
  }

  public function wcall_settings() {
    require_once("views/settings.php");
  }
}

new wcall();

// Register and load the widget
function wcall_load_widget() {
  register_widget('wcall_widget');
}
add_action('widgets_init', 'wcall_load_widget');

// Creating the widget
class wcall_widget extends WP_Widget {
  public static $base_path = 'wcall/v1';
  public static $update_fragment = 'getdata';

  function __construct() {
    parent::__construct(

      // Base ID of your widget
      'wcall_widget',

      // Widget name will appear in UI
      __('Wcall Widget', 'wcall_widget_domain'),

      // Widget description
      array('description' => __('Provide remote API data to the sidebar', 'wcall_widget_domain'),)
    );


    add_action('rest_api_init', array($this, 'call_to_custom_widget'));
  }

  // @FIX This gets called once per widget. Register it with the plugin instead?
  function call_to_custom_widget() {
    register_rest_route($this::$base_path, '/' . $this::$update_fragment, array(
      'methods' => 'POST',
      'callback' => array($this, 'wcallgetdata_func'),
    ));
  }

  function wcallgetdata_func() {
    $result = array('status' => false, 'msg' => "Unknown error!");
    if (isset($_POST['pathname'])) {
      // @FIX Unnecessarily scoped option.
      $host = trim(get_option("wcall_host"), "/");
      $path = trim($_POST['pathname'], "/");
      $cacheage = trim($_POST['cacheage']);
      $widgetid = $_POST['widgetid'];
      $resturl = $host . "/" . $path . "/";

      // @FIX This is not a good argument name.
      parse_str(trim($_POST['initargs']),$args);

      $cachedata = get_transient($widgetid);

      if (!$cachedata) {
        error_log("Cache miss for ${widgetid}.");
        $response = wp_remote_get(add_query_arg($args, $resturl));

        if (!is_wp_error($response) && $response['response']['code'] == 200) {
          $remote_posts = json_decode($response['body']);
          $result = array('status' => true, 'msg' => "",'data' => $remote_posts);
          set_transient($widgetid, $result, 60 * 60 * $cacheage);
        }
      } else {
        $result = $cachedata;
      }
    }

    echo json_encode($result);
    die;
  }

  // Creating widget front-end
  public function widget($args, $instance) {
    wp_enqueue_script('wcall');

    $fragments = array('', 'wp-json', $this::$base_path, $this::$update_fragment);
    $url = join('/', $fragments);

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
          pathname: '<?php echo $instance['pathname']; ?>',
          initargs: '<?php echo $instance['initargs']; ?>',
          cacheage: '<?php echo $instance['cache']; ?>',
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
      $title = __('New title', 'wcall_widget_domain');
    }

    if (isset($instance['pathname'])) {
      $pathname = $instance['pathname'];
    }
    else {
      $pathname = __('', 'wcall_widget_domain');
    }

    if (isset($instance['initargs'])) {
      $initargs = $instance['initargs'];
    }
    else {
      $initargs = __('', 'wcall_widget_domain');
    }

    if (isset($instance['cache'])) {
      $cache = $instance['cache'];
    }
    else {
      $cache = __('', 'wcall_widget_domain');
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
      <span style="font-size: 12px;">Specify in hours</span>
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
    return $instance;
  }
}
?>
