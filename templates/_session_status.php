<?php
if ($status == 'running')
{
	echo wfMessage('text_running')->text().'&nbsp;&nbsp;<img src="'.$path.'images/ajax-loader.gif" alt="" />';
	echo '<form style="margin-top: 10px;" name="cancel_run" action="" method="post">';
	echo '<input type="submit" value="'.wfMessage('text_cancel_run')->text().'"/>';
	echo '<input type="hidden" name="cancel_run" value="'.$_GET['id'].'"/>';
	echo '</form>';
} elseif ($status == 'pending')
	echo wfMessage('text_pending')->text().'&nbsp;&nbsp;<img src="'.$path.'images/ajax-loader.gif" alt="" />';
elseif ($status == 'canceled')
	echo '<strong>'.wfMessage('text_run_job_canceled')->text().'</strong>';
elseif ($status == 'timeout')
	echo '<strong>'.wfMessage('text_run_job_timeout')->text().'</strong>';
elseif ($status == 'deleted')
	echo '<strong>'.wfMessage('text_run_job_deleted')->text().'</strong>';
?>

<input id="session_status_code" name="session_status_code" type="hidden" value="_session_<?php echo $sid; ?>_<?php echo $status;?>_" />
