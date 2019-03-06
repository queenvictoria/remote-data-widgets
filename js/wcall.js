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
   * Constructor
   */
  this.construct = function(options){
    jQuery.extend(opts, options);
  };

  /*
   * Pass options when class instantiated
   */
  this.construct(options);
}
