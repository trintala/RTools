<div style='clear:both;'>&nbsp;</div>
<div class='rt_footer'>
<?php if ($status == 'completed' || $status == 'timeout') { ?>
<form name='delete_run' action='' method='post'>
	<input type="button" value="<?php echo wfMessage('text_delete_run')->text(); ?>" onclick="mw.RTools.delete_run('<?php echo wfMessage('text_delete_run_confirmation')->text();?>')" />
	<input type="hidden" name="delete_run" value="<?php echo $_GET['id']; ?>"/>
</form>
<?php } ?>
<?php

	if ($status == 'completed')
		echo wfMessage('text_run_completed_in')->text();
	elseif ($status == 'canceled')
		echo wfMessage('text_run_job_canceled')->text();
	elseif ($status == 'timeout')
		echo wfMessage('text_run_job_timeout')->text();
 
 	if (isset($complete_time))
 		echo "&nbsp;".$complete_time;
 
 
?>
</div>