<?php
	//$str = rtPlotsPath."/".$sid."_plot*.png";


//		foreach (glob($str) as $f)
		//	echo '<a class="rt_plot"  target="_blank" href="'.rtPlotsUrl.'/'.basename($f).'"><img class="rt_plot" src="'.rtPlotsUrl.'/'.basename($f).'" alt="plot" /></a>';
		if (isset($plots) && is_array($plots))
			foreach($plots as $plot)
			if (! empty($plot))
			{
				echo '<a class="rt_plot"  target="_blank" href="'.rtPlotsURI.'/'.$plot.'"><img class="rt_plot" src="'.rtPlotsURI.'/'.$plot.'" alt="plot" /></a>';
				
			}
		
		
?>
