<script type="text/javascript">
AC_FL_RunContent( 'codebase','http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0','width','165','height','37','id','niftyPlayer1','align','','src','plugins/niftyplayer/niftyplayer?file=<?php echo $this->audioFile; ?>&as=0','quality','high','bgcolor','#FFFFFF','name','niftyPlayer1','swliveconnect','true','pluginspage','http://www.macromedia.com/go/getflashplayer','movie','plugins/niftyplayer/niftyplayer?file=<?php echo $this->audioFile; ?>&as=0' ); //end AC code
</script>
<noscript>  
    <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="550" height="400" id="flashLoader" align="middle">
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="allowFullScreen" value="false" />
	<param name="movie" value="plugins/niftyplayer/niftyplayer.swf?file=<?php echo $this->audioFile; ?>" /><param name="quality" value="high" /><param name="bgcolor" value="#999966" />	
    <embed src="plugins/niftyplayer/niftyplayer.swf?file=<?php echo $this->audioFile; ?>" quality="high" wmode="transparent" bgcolor="#999966" width="550" height="400" name="flashLoader" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
	</object>
</noscript>
<a href="javascript:niftyplayer('niftyPlayer1').playToggle()"><?php echo $this->playSoundMessage; ?></a>
