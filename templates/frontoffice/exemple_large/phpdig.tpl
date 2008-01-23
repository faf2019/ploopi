<style type="text/css">
.phpdigHighlight { font-weight: bolder; }
</style>

<div>
	<phpdig:result_message/><br /><i><phpdig:ignore_message/></i><br /><i><phpdig:ignore_commess/></i>

	<phpdig:results>
	<div style="margin-bottom:8px;">
		<span style="font-weight:bold;"><phpdig:page_link/></span><span>&nbsp;(<phpdig:filesize/> ko) | Pertinence : <phpdig:weight/> %&nbsp;</span>
		<br /><phpdig:text/>
		<br /><a href="<phpdig:complete_path/>" title="go to <phpdig:complete_path/>" class="v"><phpdig:complete_path/></a>
	</div>
	</phpdig:results>

	<div>
		<phpdig:listing/>
		<phpdig:previous_link src='./phpdig/tpl_img/left.gif'/><phpdig:pages_bar/><phpdig:next_link src='./phpdig/tpl_img/right.gif'/>
	</div>

</div>
