<!-- indexer::stop -->
<div class="<?php echo $this->class; ?>"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php foreach ($this->banners as $banner): ?>
<?php if ($banner['banner_pic']) : ?>
    <div class="banner_image">
        <?php if ($banner['banner_url']): ?><a href="banner_clicks.php?<?php echo $banner['banner_key'].$banner['banner_id']; ?>" <?php echo $banner['banner_target']; ?> ><?php endif; ?><img src="<?php echo $banner['src']; ?>"<?php echo $banner['size']; ?> title="<?php echo $banner['banner_comment']; ?>" alt="<?php echo $banner['alt']; ?>" /><?php if ($banner['banner_url']): ?></a><?php endif; ?>
    </div>
<?php endif; ?>
<?php if ($banner['banner_flash']) : ?>
    <div class="banner_flash block" style="height:<?php echo $banner['swf_height']; ?>px; width:<?php echo $banner['swf_width']; ?>px;">
        <?php if ($banner['banner_url']): ?><a href="banner_clicks.php?<?php echo $banner['banner_key'].$banner['banner_id']; ?>" <?php echo $banner['banner_target']; ?> ><?php endif; ?>
        <span id="swf_<?php echo $banner['swf_id']; ?>">
            <?php echo $banner['swf_src']; ?><br /><?php echo $banner['banner_comment']; ?><br /><?php echo $banner['alt']; ?>
        </span>
        <img src="system/modules/banner/leer.gif" alt="" style="position:relative; margin-top:<?php echo "-".$banner['swf_height']; ?>px; left:0; z-index: 10; width:<?php echo $banner['swf_width']; ?>px; height:<?php echo $banner['swf_height']; ?>px;" />
        <?php if ($banner['banner_url']): ?></a><?php endif; ?>
    </div>
    <script type="text/javascript">
	<!--//--><![CDATA[//><!--
	new Swiff("<?php echo $banner['swf_src']; ?>", {
	  id: "swf_<?php echo $banner['swf_id']; ?>",
	  width: <?php echo $banner['swf_width']; ?>,
	  height: <?php echo $banner['swf_height']; ?>,
	  params : {
	  allowfullscreen: "false",
	  wMode: "transparent",
	  flashvars: ""
	  }
	}).replaces($("swf_<?php echo $banner['swf_id']; ?>"));
	//--><!]]>
	</script> 
<?php endif; ?>
<?php if ($banner['banner_text']) : ?>
    <div class="banner_text">
    	<div class="banner_text_name"><?php if ($banner['banner_url']): ?><a href="banner_clicks.php?<?php echo $banner['banner_key'].$banner['banner_id']; ?>" <?php echo $banner['banner_target']; ?> ><?php endif; ?><?php echo $banner['banner_name']; ?><?php if ($banner['banner_url']): ?></a><?php endif; ?></div>
    	<div class="banner_text_comment"><?php echo $banner['banner_comment']; ?></div>
        <?php if ($banner['banner_url']): ?><div class="banner_text_url"><a href="banner_clicks.php?<?php echo $banner['banner_key'].$banner['banner_id']; ?>" <?php echo $banner['banner_target']; ?> ><?php echo $banner['banner_url_kurz']; ?></a></div><?php endif; ?>
    </div>
<?php endif; ?>
<?php if ($banner['banner_empty']) : ?>
	<div class="banner_empty">
	<!-- <?php echo $banner['banner_name']; ?> -->
	</div>
<?php endif; ?>
<?php endforeach; ?>
</div>
<!-- indexer::continue -->
