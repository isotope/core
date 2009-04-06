<h3><?php echo $this->playSoundMessage; ?></h3>
<script type='text/javascript' src='plugins/mediaplayer/swfobject.js'></script>

<!-- media player -->
  <div id="preview">This div will be replaced</div>

  <script type='text/javascript'>
  var s1 = new SWFObject('plugins/mediaplayer/player.swf','ply','200','20','9','#');
  s1.addParam('allowfullscreen','false');
  s1.addParam('allowscriptaccess','always');
  s1.addParam('wmode','opaque');
  s1.addParam('flashvars','file=<?php echo $this->productBasePath; ?>mrss.xml');
  s1.write('preview');
</script>
<!-- end media player -->

