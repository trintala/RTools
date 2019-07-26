<?php
	
	


	$rtoolsGeoLocates[] = $id;

	
	#$rtoolsScripts[] = "var map_".$id." = new google.maps.Map(document.getElementById('".$id."_map_canvas'),{".join(',',$map_options)."});";
	#$rtoolsScripts[] = "if(navigator.geolocation) {navigator.geolocation.getCurrentPosition(function(position){var pos = new google.maps.LatLng(position.coords.latitude,position.coords.longitude); mw.RTools.locate('".$id."',map_".$id.", pos);}, function(err){ mw.RTools.cancel_locating('".$id."', map_".$id.");}, {maximumAge:0, timeout: 10000, enableHighAccuracy:true});} else mw.RTools.cancel_locating('".$id."', map_".$id.")";
	
	
	echo "<img id='".$id."_map_loader' class='rtools_location_loader' src='".$extension_url."images/ajax-loader3.gif'><div id='".$id."_map_canvas' class='rtools_location_map'></div>";
	echo '<input type="hidden"  id="'.$id.'_input" value="" name="variable['.$name.']" />';
?>
