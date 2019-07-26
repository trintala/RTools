
<div id='session_status'>
</div>

<div id='session_output'>
<?php
	include(dirname(__FILE__).'/_output.php');
?>
</div>

<script type='text/javascript'>

RLQ.push(function () {
        $( document ).ready(function() {
                window.setTimeout(function(){mw.RTools.update_page("<?php echo $sid;?>");}, 500);
        });
});

//jQuery( document ).ready( function( $ ) {
//	mw.RTools.update_page("<?php echo $sid;?>");
//});

</script>

<noscript>
<p>
<strong>
<?php wfMessage('text_no_jscript')->text(); ?>
</strong>
</p>
</noscript>

