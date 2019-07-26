mw.RTools = {

	session_id : '',
	poller : null,
	code_block_statuses : new Array(),
	polling : false,
	result_length : 0,

	delete_run: function(msg)
	{
		if (confirm(msg))
			document.delete_run.submit();
	},

	update_output: function()
	{
		var oThis = mw.RTools;
	
		$.ajax({
	  		url: '?title=Special:RTools&id='+oThis.session_id+'&actn=get_session_output&rl='+oThis.result_length+'&ob='+oThis.code_block_statuses.join(','),
	  		cache: false,
		    success: function(response){
	          if (response.length > 1)
	          {
	          	$('#session_output').html(response);
	 	        oThis.result_length = response.length;
				/* Lazy load jquery.tablesorter - Copied from resources/mediawiki.page/mediawiki.page.ready.js */
				if ( $( 'table.sortable' ).length ) {
					mw.loader.using( 'jquery.tablesorter', function() {
						$( 'table.sortable' ).tablesorter();
					});
				}
	 	      	//if(typeof rtools_initialize == 'function') {
	 	      		// This is a hook method after output is refreshed
				//	rtools_initialize();
				//}
	 	      }
		    },
		    error: function(){ alert('AJAX-error: Cannot connect to server') }
		  });
	},

	resolve_current_code_block: function()
	{
		var id = 0;
		$('div.rt_output_block').each(function(i){
			id = this.id.split("_").pop();
		});
		return 'rtools_code_block_status_' + (parseInt(id) + 1);
	},

	update_status: function(status)
	{
	    var cb = mw.RTools.resolve_current_code_block();
	    var target = $('#'+cb);
	    if (target.length > 0)
	    {
			$('#session_status').html('');
	    	target.html(status);
	        target.show();
	    }
	    else
	    	$('#session_status').html(status);
	},

	check_status: function()
	{
		// Prevent multiple simultaneous status polls

		var oThis = mw.RTools;

		if (oThis.polling)
			return;
		else
			oThis.polling = true;

		$.ajax({
	  		url: "?title=Special:RTools&id="+oThis.session_id+"&actn=check_session_status",
	  		cache: false,
	  		success: function(response){
		   	  oThis.polling = false;
			  if ( response.search('_session_'+oThis.session_id+'_running_') < 0 && response.search('_session_'+oThis.session_id+'_pending_') < 0)
			  	clearInterval(oThis.poller);
			  if (response.search('_session_'+oThis.session_id+'_pending_') < 0)
	 	  	  	oThis.update_output();
	 	  	  oThis.update_status(response);
	 	  	  return true;
	  		},
	  		error: function(){
	  			oThis.polling = false;
				alert('AJAX-error: Cannot connect to server');
	  		}
		});
	},

	update_page: function(id)
	{
		mw.RTools.session_id = id;
		mw.RTools.poller = setInterval(mw.RTools.check_status, 1500);
	},

	show_code: function(id)
	{
		$('#rtools_code_block_'+id).slideDown(500);
		$('#rtools_code_hide_button_'+id).show();
		$('#rtools_code_show_button_'+id).hide();
		mw.RTools.code_block_statuses[id] = 1;
	},

	hide_code: function(id)
	{
		$('#rtools_code_block_'+id).slideUp(500);
		$('#rtools_code_hide_button_'+id).hide();
		$('#rtools_code_show_button_'+id).show();
		mw.RTools.code_block_statuses[id] = 0;
	}
}
