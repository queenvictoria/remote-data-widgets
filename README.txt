=== Remote Data Widgets ===
Contributors: Queenvictoria
Donate link: https://hol.ly
Tags: widgets, REST API, remote data, visualisation
Requires at least: 4.4
Tested up to: 4.5.3
Stable tag: 3.9.9
License: GPLv3

This is a base plugin that can be forked to create Wordpress sidebar widgets that require data from remote REST API.

== Description ==

First fork the project. Without modifications the widget will only display the raw output of the REST API.

Next include your own custom content. We built the plugin to call a custom javascript class. See below in (Examples)[#examples] for our customisations.

This plugin [requires pretty permalinks](https://stackoverflow.com/questions/44204307/rest-api-init-event-not-fired#44626898) of some variety in order that the plugin can work with the Wordpress REST API.

== Installation ==

1. Customise the plugin
2. Upload the remote-data-widgets directory to `wp-content/plugins`
3. Enable permalinks of some kind in Admin > Settings > Permalinks
4. Enable the plugin at Admin > Plugins

== Usage ==

4. Add a base REST URI at Admin > Settings > Remote data widgets
5. Add a Remote data widget at Admin > Appearance > Widgets
6. Add a remote path to the widget.

== Examples ==

= Additional scripts and css in `ext` =
```PHP
  function wp_enqueue_scripts() {
    wp_register_script('remote_data_widgets', plugin_dir_url(__FILE__) . '/js/remote_data_widgets.js', array('jquery'));

    // Customisations for these particular widgets.
    wp_register_script('d3', 'https://cdnjs.cloudflare.com/ajax/libs/d3/5.9.1/d3.min.js', null, null, true);
    wp_register_style('Font_Awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css');

    wp_register_script('custom', plugin_dir_url( __FILE__ ) . 'ext/custom/charts.js', array('d3'), '0.1.1');
    wp_register_style('custom', plugin_dir_url( __FILE__ ) . 'ext/custom/charts.css', array('Font_Awesome'));
  }
```

```PHP
  public function widget($args, $instance) {
    // ...

    // Customisations for these particular widgets.
    wp_enqueue_style('Font_Awesome');

    wp_enqueue_script('evs-charts');
    wp_enqueue_style('evs-charts');

    // ...
  }
```

= Call the custom scripts =

From js/remote-data-widgets.js

```javascript
  this.createView = function() {
    // Parse our configuration items
    if ( opts.initargs.indexOf('?') !== 0 )
      opts.initargs = "?" + opts.initargs;
    var params = new URLSearchParams(opts.initargs);
    opts.chartType = params.get("chart") || "doughnut";

    var selector = "#" + opts.widgetid + " [data-target]";

    if ( opts.chartType === 'doughnut' )
      opts.chartClass = custom.donut;
    else
      opts.chartClass = custom.bubble;

    this.getData(function(result) {
      opts.chart = opts.chartClass({data: result.data, containerSelector: selector});
    });
  }
```

```javascript
  this.updateView = function() {
    var wrapper = jQuery("#" + opts.widgetid);
    this.getData(function(result) {
      opts.chart.update(result.data)
    });
  }
```

```javascript
  this.construct = function(options) {
    jQuery.extend(opts, options);

    this.createView();
  }
```
