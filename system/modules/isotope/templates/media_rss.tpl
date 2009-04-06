<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">
	<channel>
		<title><?php echo $this->playlistTitle; ?></title>
		<link><?php echo $this->baseURL; ?></link>
		<?php foreach($this->files as $file): ?>
		<item>
			<title><?php echo $file['title']; ?></title>

			<description><?php echo $file['description']; ?></description>
			<media:content url="<?php echo $file['path']; ?>" type="<?php echo $file['type']; ?>" />
			<?php if($file['is_audio']): ?>
            	<media:thumbnail url="<?php echo $file['thumbnail_image']; ?>" />            
            <?php endif; ?>
        </item>
		<?php endforeach; ?>
	</channel>
</rss>