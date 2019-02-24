var wcallcls=function(options){
	var vars={
		pathname:null,
		initargs:null,
		cacheage:null
	}

	var root=this;

	this.getData=function(callback){
		jQuery.ajax({
		  method:"POST",
		  url: ajaxurl,
		  data: { pathname:vars.pathname, initargs:vars.initargs,cacheage:vars.cacheage,action:"wcallgetdata"}
		})
		  .done(function( msg ) {
		  	var obj=jQuery.parseJSON(msg);
		    callback(obj);
		  });
	}

	/*
     * Constructor
     */
    this.construct = function(options){

        jQuery.extend(vars , options);
    };

    /*
     * Pass options when class instantiated
     */
     // console.log(options);
    this.construct(options);
}