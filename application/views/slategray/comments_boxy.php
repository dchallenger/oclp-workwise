<div class="spacer"></div>
<form id="comment-form" method="post">
	<input type="hidden" name="identifier" value="<?=$identifier?>"/>
	<input type="hidden" name="type" value="<?=$type?>"/>	
	<input type="hidden" name="user_id" value="<?=$this->userinfo['user_id']?>"/>
	<input type="hidden" name="record_id" value="-1"/>
	<fieldset>
		<legend><img src="<?= base_url() . $this->userinfo['theme']; ?>/icons/balloon.png" alt="comments" >Add your comment:</legend>
		<textarea name="comment"></textarea>
	</fieldset>
	<div class="spacer"></div>
	
	<div class="form-submit-btn">
	<div class="icon-label-group align-center">
	        <div class="icon-label"> <a href="javascript:void(0);" onclick="save_comment( '<?php echo addslashes($callback)?>' );" class="icon-16-add " id="boxy-save-comment"> <span>Add Comment</span> </a> </div>
	        <div class="icon-label"> <a href="javascript:void(0);" class="icon-16-close" onclick="Boxy.get(this).hide().unload();"> <span>Close</span> </a> </div>	    
	</div>	
	</div>
	
</form>


<div id="comment-list">
<h3 class="lightred"><img src="<?= base_url() . $this->userinfo['theme']; ?>/icons/balloons-box.png" alt="latest comments" >Latest Comments</h3>
<div class="spacer"></div>
<ul id="comments-list">

<?php if ($comments && count($comments) > 0):?>
<?php foreach ($comments as $comment):?>
	<li>
		<div>
			<span class="comment-name"><strong><?=$comment->firstname . ' ' . $comment->lastname;?></strong></span>
			<span class="comment-comment"><?=$comment->comment;?></span>
		</div>
		<div class="comment-date">
			<small><?=date($this->config->item('display_datetime_format'), strtotime($comment->date_created));?></small>
		</div>
	</li>
<?php endforeach;?>
<?php endif;?>

</ul>
</div>