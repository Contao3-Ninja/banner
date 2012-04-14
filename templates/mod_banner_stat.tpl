

<div class="clear"></div>
<script type="text/javascript">
PopUp = function(autoapply){
	this.types = [];
	this.defaults = {
		width:800,
		height:600,
		top:0,
		left:0,
		location:false,
		resizable:false,
		scrollbars:false,
		status:false,
		toolbar:false,
		menubar:false,
		center:true,
		title:"<?php echo $this->banner_export_title; ?>"
	}
	this.addType({
		name:"standard",
		location:true,
		resizable:true,
		scrollbars:true,
		status:true,
		toolbar:true,
		menubar:true
	});
	if(autoapply) this.apply();
}
o = PopUp.prototype;
o.apply = function(){
	var links = document.getElementsByTagName("form"); //a
	if(!links) return;
	for(var i=0;i<links.length;i++){
		var l = links[i];
		if(l.className.indexOf("popup") > -1){
			this.attachBehavior(l,this.getType(l));
		}
	}
}
o.addType = function(type){
	for(var prop in this.defaults){
		if(type[prop] == undefined) type[prop] = this.defaults[prop];
	}
	this.types[type.name] = type;
}
o.getType = function(l){
	for(var type in this.types){
		if(l.className.indexOf(type) > -1) return type;
	}
	return "standard";
}
o.attachBehavior = function(l,type){
	var t = this.types[type];
	l.title = t.title;
	l.popupProperties = {
		type: type,
		ref: this
	};
	l.onclick = function(){
		this.popupProperties.ref.open(this.action,this.popupProperties.type);
		return false;
	}
}
o.booleanToWord = function(bool){
	if(bool) return "yes";
	return "no";
}
o.getTopLeftCentered = function(typeObj){
	var t = typeObj;
	var r = {left:t.left, top:t.top};
	var sh = screen.availHeight-20;
	var sw = screen.availWidth-10;
	if(!sh || !sw) return r;
	r.left = (sw/2)-(t.width/2);
	r.top = (sh/2)-(t.height/2);
	return r;
}
o.getParamsOfType = function(typeObj){
	var t = typeObj;
	var c = this.booleanToWord;
	if(t.center){
		var tc = this.getTopLeftCentered(typeObj);
		t.left = tc.left;
		t.top = tc.top;
	}
	var p = "width="+t.width;
	p+=",height="+t.height;
	p+=",left="+t.left;
	p+=",top="+t.top;
	p+=",location="+c(t.location);
	p+=",resizable="+c(t.resizable);
	p+=",scrollbars="+c(t.scrollbars);
	p+=",status="+c(t.status);
	p+=",toolbar="+c(t.toolbar);
	p+=",menubar="+c(t.menubar);
	return p;
}
o.open = function(url,type){
	if(!type) type = "standard";
	var t = this.types[type];
	var p = this.getParamsOfType(t);
	var w = window.open(url,t.name,p);
	if(w) w.focus();
	return false;
}
</script>
<script type="text/javascript">
window.addEvent('domready', function() {
	if(document.getElementById && document.getElementsByTagName){ // Check DOM
		popup = new PopUp(); // create new PopUp-Instance
		popup.addType({
			name: "info",
			width: 300,
			height: 300,
			top: 300,
			status:true
		});
		popup.apply(); // Apply Popup-Behavior to all Links using the Class "popup"		
	}
});
</script>
<?php defined('REQUEST_TOKEN') or define('REQUEST_TOKEN', 'c0n740'); ?>
<div class="tl_panel">
    <!-- Export Zeile //-->
    <?php if ($this->bannerkatid>0  || $this->bannerkatid==-1) : ?>
    <fieldset style="margin-left: 6px; float:left;">
    <?php else: ?>
    <fieldset style="margin-left: 6px; float:left; visibility:hidden;">
    <?php endif; ?>
    <legend> <?php echo $this->exportfield; ?> </legend>
        <div style="float:left; padding-left: 4px;">
            <form method="get" class="popup info" id="banner_export1" action="<?php echo $this->banner_base; ?>system/modules/banner/export/BannerStatExport.php?tl_field=csvc&amp;tl_katid=<?php echo $this->bannerkatid; ?>">
            <div class="tl_formbody">
                <input type="submit" value="CSV ','" alt="Export CSV ," class="tl_submit" />
            </div>
            </form>
        </div>
        <div style="float:left; padding-left: 6px;">
            <form method="get" class="popup info" id="banner_export2" action="<?php echo $this->banner_base; ?>system/modules/banner/export/BannerStatExport.php?tl_field=csvs&amp;tl_katid=<?php echo $this->bannerkatid; ?>">
            <div class="tl_formbody">
                <input type="submit" value="CSV ';'" alt="Export CSV ;" class="tl_submit" />
            </div>
            </form>
        </div>
	    <div style="float:left; padding-left: 6px;">
            <form method="get" class="popup info" id="banner_export3" action="<?php echo $this->banner_base; ?>system/modules/banner/export/BannerStatExport.php?tl_field=excel&amp;tl_katid=<?php echo $this->bannerkatid; ?>">
            <div class="tl_formbody">
                <input type="submit" value="Excel" alt="Export Excel" class="tl_submit" />
            </div>
            </form>
        </div>
    </fieldset>
    <!-- Export Zeile Ende //-->
    <!-- Kategorie Zeile //-->
    <fieldset style="margin-right: 6px; float:right;">
    <legend> <?php echo $this->bannerstatkat; ?> </legend>
        <div style="float:left; padding-right: 6px;">
            <form method="post" class="info" id="banner_statistik" action="<?php echo $this->banner_base_be; ?>/main.php?do=bannerstat">
            <div class="tl_formbody">
                <select class="tl_select" name="id" style="width:200px;">
                <?php foreach ($this->bannerkats as $bannerkat): ?>
                    <?php if ($bannerkat['id']==$this->bannerkatid) : ?>
                    <option selected="selected" value="<?php echo $bannerkat['id']; ?>"><?php echo $bannerkat['title']; ?></option>
                    <?php else: ?>
                    <option value="<?php echo $bannerkat['id']; ?>"><?php echo $bannerkat['title']; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
                </select>
                <input type="hidden" name="REQUEST_TOKEN" value="<?php echo REQUEST_TOKEN; ?>" />
                <input class="tl_img_submit" type="image" value="<?php echo specialchars($GLOBALS['TL_LANG']['MSC']['showOnly']); ?>" title="<?php echo specialchars($GLOBALS['TL_LANG']['MSC']['showOnly']); ?>" src="system/themes/<?php echo $this->theme; ?>/images/reload.gif" name="filter" />
            </div>
            </form>
        </div>
    </fieldset>
    <!-- Kategorie Ende //-->
    <!-- Kategorie Reset //-->
<?php if ($this->bannerkatid>0) : ?>
    <fieldset style="margin-right: 6px; float:right;">
        <legend> <?php echo $this->bannercatzero; ?> </legend>
        <div style="float:left;">
            <form method="post" class="info" id="banner_cat_reset" action="<?php echo $this->banner_base_be; ?>/main.php?do=bannerstat">
            <div class="tl_formbody">
                <input type="hidden" name="REQUEST_TOKEN" value="<?php echo REQUEST_TOKEN; ?>" />
                <input type="hidden" name="act" value="zero" />
                <input type="hidden" name="id" value="<?php echo $this->bannerkatid; ?>" />
                <input type="hidden" name="catzid" value="<?php echo $this->bannerkatid; ?>" />
                <input type="submit" value="<?php echo $this->bannercatzerobutton; ?>" alt="<?php echo $this->bannercatzerotext; ?>" title="<?php echo $this->bannercatzerotext; ?>" class="tl_submit" onclick="if (!confirm('<?php echo $this->bannercatzeroconfirm; ?>')) return false; Backend.getScrollOffset();" />
            </div>
            </form>
        </div>
    </fieldset>
<?php endif; ?>
    <!-- Kategorie Reset Ende //-->
    <div class="clear"></div>
</div>
<br /> <br />
<div class="tl_formbody_edit">


<table cellpadding="0" cellspacing="0" summary="Table lists records" class="tl_listing">
<tbody>
<tr onmouseout="Theme.hoverRow(this, 0);" onmouseover="Theme.hoverRow(this, 1);">
    <td style="padding-left: 2px;" class="tl_folder_tlist"><?php echo $this->header_picture." / ".$this->header_name." / ".$this->header_url; ?></td>
    <td style="width:60px; padding-left: 2px; text-align: center;" class="tl_folder_tlist"><?php echo $this->header_active; ?></td>
    <td style="width:60px; padding-left: 2px; text-align: center;" class="tl_folder_tlist"><?php echo $this->header_prio; ?></td>
    <td style="width:60px; padding-left: 2px; text-align: center;" class="tl_folder_tlist"><?php echo $this->header_views; ?></td>
    <td style="width:60px; padding-left: 2px; text-align: center;" class="tl_folder_tlist"><?php echo $this->header_clicks; ?></td>
    <td style="width:16px; padding-left: 2px; text-align: center;" class="tl_folder_tlist">&nbsp;</td>
</tr>
<?php if ($this->bannerkatid>0 || $this->bannerkatid==-1) : ?>
    <?php foreach ($this->bannersstat as $bannerstat): ?>
        <?php if ($bannerstat['banner_pic']) : ?>
            <!-- Bild Start -->
            <tr onmouseout="Theme.hoverRow(this, 0);" onmouseover="Theme.hoverRow(this, 1);">
                <td style="padding-left: 2px; padding-bottom:6px;  padding-top:6px;" class="tl_file_list"><img style="<?php echo $bannerstat['banner_style']; ?>" alt="<?php echo $bannerstat['banner_alt']; ?>" src="<?php echo $bannerstat['banner_image']; ?>" height="<?php echo $bannerstat['banner_height']; ?>" width="<?php echo $bannerstat['banner_width']; ?>" title="<?php echo $bannerstat['banner_title']; ?>" /><br />&nbsp;<?php echo $bannerstat['banner_name']; ?><br />&nbsp;<?php echo $bannerstat['banner_url']; ?></td>
                <td style="padding-left: 2px; text-align: center;" class="tl_file_list"><?php echo $bannerstat['banner_active']; ?></td>
                <td style="padding-left: 2px; text-align: center;" class="tl_file_list"><?php echo $bannerstat['banner_prio']; ?></td>
                <td style="padding-left: 2px; text-align: center;" class="tl_file_list"><?php echo $bannerstat['banner_views']; ?></td>
                <td style="padding-left: 2px; text-align: center;" class="tl_file_list"><?php echo $bannerstat['banner_clicks']; ?></td>
                <td style="padding-left: 2px; text-align: center;" class="tl_file_list"><form method="post" class="info" id="banner_reset" action="<?php echo $this->banner_base_be; ?>/main.php?do=bannerstat">
                <input type="hidden" name="REQUEST_TOKEN" value="<?php echo REQUEST_TOKEN; ?>" />
                <input type="hidden" name="act" value="zero" />
                <input type="hidden" name="zid" value="<?php echo $bannerstat['banner_id']; ?>" />
                <input type="hidden" name="id" value="<?php echo $this->bannerkatid; ?>" />
                <input class="tl_img_submit" type="image" value="<?php echo $bannerstat['banner_zero']; ?>" title="<?php echo $bannerstat['banner_zero']; ?>" src="system/modules/banner/themes/<?php echo $this->theme0; ?>/down0.gif" name="filter" onclick="if (!confirm('<?php echo $bannerstat['banner_confirm']; ?>')) return false; Backend.getScrollOffset();" />
                </form></td>
            </tr>
            <!-- Bild Ende -->
        <?php endif; ?>
        <?php if ($bannerstat['banner_flash']) : ?>
            <!-- swf Start -->
            <tr onmouseout="Theme.hoverRow(this, 0);" onmouseover="Theme.hoverRow(this, 1);">
                <td style="padding-left: 2px; padding-bottom:6px; padding-top:6px;" class="tl_file_list"><div id="swf_<?php echo $bannerstat['banner_id']; ?>">Flash</div><br />&nbsp;<?php echo $bannerstat['banner_name']; ?><br />&nbsp;<?php echo $bannerstat['banner_url']; ?>
                <script type="text/javascript">
                /* <![CDATA[ */
				new Swiff("<?php echo $bannerstat['swf_src']; ?>", {
				  id: "swf_<?php echo $bannerstat['banner_id']; ?>",
				  width: <?php echo $bannerstat['swf_width']; ?>,
				  height: <?php echo $bannerstat['swf_height']; ?>,
				  params : {
				  allowfullscreen: "false",
				  wMode: "transparent",
				  flashvars: ""
				  }
				}).replaces($("swf_<?php echo $bannerstat['banner_id']; ?>"));
				/* ]]> */
				</script> 
                </td>
                <td style="padding-left: 2px; text-align: center;" class="tl_file_list"><?php echo $bannerstat['banner_active']; ?></td>
                <td style="padding-left: 2px; text-align: center;" class="tl_file_list"><?php echo $bannerstat['banner_prio']; ?></td>
                <td style="padding-left: 2px; text-align: center;" class="tl_file_list"><?php echo $bannerstat['banner_views']; ?></td>
                <td style="padding-left: 2px; text-align: center;" class="tl_file_list"><?php echo $bannerstat['banner_clicks']; ?></td>
                <td style="padding-left: 2px; text-align: center;" class="tl_file_list"><form method="post" class="info" id="banner_reset" action="<?php echo $this->banner_base_be; ?>/main.php?do=bannerstat">
                <input type="hidden" name="REQUEST_TOKEN" value="<?php echo REQUEST_TOKEN; ?>" />
                <input type="hidden" name="act" value="zero" />
                <input type="hidden" name="zid" value="<?php echo $bannerstat['banner_id']; ?>" />
                <input type="hidden" name="id" value="<?php echo $this->bannerkatid; ?>" />
                <input class="tl_img_submit" type="image" value="<?php echo $bannerstat['banner_zero']; ?>" title="<?php echo $bannerstat['banner_zero']; ?>" src="system/modules/banner/themes/<?php echo $this->theme0; ?>/down0.gif" name="filter" onclick="if (!confirm('<?php echo $bannerstat['banner_confirm']; ?>')) return false; Backend.getScrollOffset();" />
                </form></td>
            </tr>
            <!-- swf Ende -->
        <?php endif; ?>
        <?php if ($bannerstat['banner_text']) : ?>
            <!-- Text Start -->
            <tr onmouseout="Theme.hoverRow(this, 0);" onmouseover="Theme.hoverRow(this, 1);">
                <td style="padding-left: 2px; padding-bottom:6px;  padding-top:6px;" class="tl_file_list"><span style="font-weight:bold;"><?php echo $bannerstat['banner_name']; ?></span><br /><br /><?php echo $bannerstat['banner_comment']; ?><br /><br />&nbsp;&nbsp;<?php echo $bannerstat['banner_url_kurz']; ?></td>
                <td style="padding-left: 2px; text-align: center;" class="tl_file_list"><?php echo $bannerstat['banner_active']; ?></td>
                <td style="padding-left: 2px; text-align: center;" class="tl_file_list"><?php echo $bannerstat['banner_prio']; ?></td>
                <td style="padding-left: 2px; text-align: center;" class="tl_file_list"><?php echo $bannerstat['banner_views']; ?></td>
                <td style="padding-left: 2px; text-align: center;" class="tl_file_list"><?php echo $bannerstat['banner_clicks']; ?></td>
                <td style="padding-left: 2px; text-align: center;" class="tl_file_list"><form method="post" class="info" id="banner_reset" action="<?php echo $this->banner_base_be; ?>/main.php?do=bannerstat">
                <input type="hidden" name="REQUEST_TOKEN" value="<?php echo REQUEST_TOKEN; ?>" />
                <input type="hidden" name="act" value="zero" />
                <input type="hidden" name="zid" value="<?php echo $bannerstat['banner_id']; ?>" />
                <input type="hidden" name="id" value="<?php echo $this->bannerkatid; ?>" />
                <input class="tl_img_submit" type="image" value="<?php echo $bannerstat['banner_zero']; ?>" title="<?php echo $bannerstat['banner_zero']; ?>" src="system/modules/banner/themes/<?php echo $this->theme0; ?>/down0.gif" name="filter" onclick="if (!confirm('<?php echo $bannerstat['banner_confirm']; ?>')) return false; Backend.getScrollOffset();" />
                </form></td>
            </tr>
            <!-- Text Ende -->
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
<tr onmouseout="Theme.hoverRow(this, 0);" onmouseover="Theme.hoverRow(this, 1);">
    <td colspan="6">&nbsp;</td>
</tr>
<tr onmouseout="Theme.hoverRow(this, 0);" onmouseover="Theme.hoverRow(this, 1);">
    <td colspan="6" style="padding-left: 2px; text-align:right;" class="tl_folder_tlist"><?php echo $this->banner_version; ?></td>
</tr>
</tbody>
</table>
</div>
<br /> <br />
 <span style="padding-left: 18px;"><?php echo $this->banner_footer; ?></span>
<br /> <br />
