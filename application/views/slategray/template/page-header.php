  <?php $module_info = $this->hdicore->get_module($this->module_id); ?>
 <header>
	<h2>
    <?= empty($module_info->long_name) ? $this->listview_title : $module_info->long_name; ?>
  </h2> 
    <!--jlm form name="quick-action-form" id="quick-action-form" method="post">
    	<fieldset class="header-search">
      		<input class="header-in" type="text" onblur="if (this.value == '') {this.value = 'SEARCH';}" onfocus="if (this.value == 'SEARCH') {this.value = '';}" value="SEARCH"  />
          <input class="header-btn" type="image" src="< ?php echo base_url() . $this->userinfo['theme']?>/images/header-find.png" />
      </fieldset>
      <fieldset class="header-create">
      		<div class="tip"><span>CREATE</span></div>
      		<select>
          		<option>new document</option>
              <option>file leave</option>
          </select>
      </fieldset>
    </form -->
    <!--jlm div class="clear"></div -->

	<?php if($this->listview_title=="Dashboard" && $this->method == "index"):?>
        <div class="form-submit-btn align-right nopadding icon-label" style="margin-top:5px;margin-right:15px;">
            <a class="icon-16-refresh" href="javascript:void(0);" onclick=""> <span>Reset Dashboard</span></a>
        </div>
	<?php endif; ?>
</header>
<div class="spacer"></div> 
<!--  Module Description -->
<!-- <div class="small"><?= empty($module_info->description) ? $this->listview_description : $module_info->description; ?></div> -->