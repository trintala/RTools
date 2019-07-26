mw.RTools = {

	markers : new Array(),
	pollers : new Array(),
	pollings : new Array(),
	url : '',
	geo_locating : false,

	show_code: function(id)
	{
		$('#rtools_code_block_'+id).show(500);
		$('#rtools_code_hide_button_'+id).show();
		$('#rtools_code_show_button_'+id).hide();
	},
	
	hide_code: function(id)
	{
		$('#rtools_code_block_'+id).hide(500);
		$('#rtools_code_hide_button_'+id).hide();
		$('#rtools_code_show_button_'+id).show();
	},
	
	// Only for embed and live modes
	post_form: function(form)
	{
		id = $(form).attr('name');
		mw.RTools.url = $(form).attr('action');
		if (! $('#'+id+'_results').is(":visible"))
		{
	  		$('#'+id+'_results').html('<img src="' + form.extension_url.value + '/images/ajax-loader3.gif" alt="Running..." />');
	  		$('#'+id+'_results').show();
		}
		else
		{
	  		$('#'+id+'_results').append('<img class="live_loader" src="' + form.extension_url.value + '/images/ajax-loader3.gif" alt="Running..." />');			
		}
		
		$.post(
			mw.RTools.url,
            $(form).serialize(),
            function(data){
   				var tmp = data.split(';');
   				if (tmp.length != 2)
   					alert('Error in execution!');
				var token = tmp[0];
				var id = tmp[1];
            	cmd =  "mw.RTools.check_status('"+$.trim(id)+"','"+$.trim(token)+"')";
                mw.RTools.pollers[id] = setInterval(cmd, 500);
            }
        );
	},
	
	
	check_status: function(id, token)
	{	
		if (mw.RTools.pollings[id])
			return;
		else
			mw.RTools.pollings[id] = true;
	
		$.ajax({
	  		url: mw.RTools.url + "?id="+token+"&actn=get_session_json&embed=1&form_name="+id,
	  		cache: false,
	  		success: function(response){
	  		
			  res = jQuery.parseJSON(response);

			  if (res == null)
			  {			  
			  	alert('Unable to get json response from the server!');
			    return;
			  }

		   	  id = res.form_name;

		   	  mw.RTools.pollings[id] = false;		  
			  
			  if(res.status != 'running' && res.status != 'pending')
			  {		  	
			  	clearInterval(mw.RTools.pollers[id]);
				$('#'+id+'_results').html(res.result);
			  	$('#'+id+'_results').show();
				/* Lazy load jquery.tablesorter - Copied from resources/mediawiki.page/mediawiki.page.ready.js */
				if ( $( 'table.sortable' ).length ) {
					mw.loader.using( 'jquery.tablesorter', function() {
						$( 'table.sortable' ).tablesorter();
					});
				}

			  }

	 	  	  return true;
	  		},
	  		error: function(){
				alert('AJAX-error: Cannot connect to server');
				return true;
	  		}
		});
	},

	cancel_table: function(id)
	{
		$("#"+id+"_input").attr('value','');
		$("#"+id+"_input").show();
		$("#"+id+"_table_preview").hide();
		$("#"+id+"_cancel_table").hide();
	},

	build_table: function(textarea, id, name)
	{
		var target = id+'_table_preview';
		var source = textarea.id;
		var form = $(textarea).get(0).form;
		window.setTimeout("mw.RTools.timed_build_table('"+id+"','"+source+"', '"+target+"', '"+name+"')",1000);
		$("#"+target).show();
		$("#"+target).html('<img src="' + form.extension_url.value + '/images/ajax-loader3.gif" alt="Running..." />');
		$("#"+id+"_cancel_table").show();
	},	
	
	timed_build_table: function(id, source, target, name)
	{
		var textarea = document.getElementById(source);
		var clipText = document.getElementById(source).value;
		var newRow = null;
		var newCell = null;

		// Hide textarea
		$('#'+source).hide();

		// split into rows		
		var clipRows = clipText.split("\n");
		
		// split rows into columns
		for (var i=0; i<clipRows.length; i++)
			clipRows[i] = clipRows[i].split("\t");		
		
		// write out in a table
		var newTable = document.createElement("table");
		var input = null;
		
		for (var i=0; i < clipRows.length-1; i++){
			newRow = newTable.insertRow(i);
			for (var j=0; j < clipRows[i].length; j++) {
				newCell = newRow.insertCell(j);
				input 	= document.createElement('input');
				input.type = 'text';
				input.value = clipRows[i][j];
				input.className = 'tablecell'+id;
				input.name = 'variable['+name+']['+i+']['+j+']';
				newCell.appendChild(input);
			}
		}
				
		//alert(newTable);
		var t = document.getElementById(target);
		t.innerHTML='';
		if (t == null)
			alert("Target not found!!!");
		else
			t.appendChild(newTable);
	},
	
	set_date: function(date_str, id)
	{
		var tmp = date_str.split('/');
		$('#'+id+'_month').val(tmp[0].replace(/^0+/, ''));
		$('#'+id+'_day').val(tmp[1].replace(/^0+/, ''));
		$('#'+id+'_year').val(tmp[2]);
	},
	
	locate: function(id, map, pos)
	{
		map.setCenter(pos);
		map.setZoom(14);
		mw.RTools.markers[id] = new google.maps.Marker({
			position: pos,
			draggable: true,
		    map: map,
		    title: 'You are here!'
		});
		
		google.maps.event.addListener(mw.RTools.markers[id], 'dragend', function(){var p = mw.RTools.markers[id].getPosition();	$('#'+id+'_input').val(p.lat()+','+p.lng());});
		
		$('#'+id+'_map_loader').hide();
		$('#'+id+'_input').val(pos.lat()+','+pos.lng());
	},
	
	cancel_locating: function(id, map)
	{
		pos = new google.maps.LatLng(62.8925, 27.678333);
		mw.RTools.locate(id, map, pos);
		alert("Unable to automatically geolocate! Please do it manually by dragging the marker.");
	},
	
	hide_varbox: function(id)
	{
		$("#"+id+"_varbox").hide(500);
		$("#"+id+"_varbox input").prop("disabled", true);
		$("#"+id+"_varbox select").prop("disabled", true);
	},
	
	show_varbox: function(id)
	{
		$("#"+id+"_varbox").show(500);
		$("#"+id+"_varbox input").prop("disabled", false);
		$("#"+id+"_varbox select").prop("disabled", false);
	}
	
	
	
}