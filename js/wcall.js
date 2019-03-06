// @FIX This must load after jQuery surely.
// @FIX Whitespace.
var wcallcls = function(options) {
  var opts = {
    pathname: null,
    initargs: null,
    cacheage: null,
    widgetid: null
  };

  this.getData = function(callback) {
    jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
          pathname: opts.pathname,
          initargs: opts.initargs,
          cacheage: opts.cacheage,
          action: "wcallgetdata",
          widgetid: opts.widgetid
        }
      })
      .done(function( msg ) {
        var obj = jQuery.parseJSON(msg);
        obj.widgetid = opts.widgetid;
        callback(obj);
      });
  }

  /*
   * Update the data and then update the contents.
   */
  this.updateView = function() {
    var self = this;

    var container = jQuery("#" + opts.widgetid);
    self.getData(function(data) {
      jQuery("textarea", container).val(JSON.stringify(data, null, 2));
    });
  }

  /*
   * TESTING
   * Create an update button.
   */
  this.test = function() {
    var self = this;

    var container = jQuery("#" + opts.widgetid);
    jQuery(container).append("<button>Update</button>");
    jQuery("button", container).on("click", function(e) {
      jQuery("textarea", container).val("");
      self.updateView();
    });
  }

  /*
   * Constructor
   */
  this.construct = function(options){
    jQuery.extend(opts, options);

    // Do any preflight items while in testing.
    this.test();
  };

  /*
   * Pass options when class instantiated
   */
  this.construct(options);
}
