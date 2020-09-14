<?php if (!defined('BASEPATH')) exit('No direct script access allowed');?>

<?php 
foreach ($subgroups as $subgroup):
	if (count($subgroup['child']) == 0):
		$others[] = $subgroup;
	; else:
?>		
	<h4 class="icons-title"><?=$subgroup['short_name']?></h4>
		<?php foreach ($subgroup['child'] as $child):?>
			<?php if (trim($child['link']) != ''):?>
			<div class="icon-label-link">
				<a href="<?=site_url($child['link'])?>">
					<img src="<?php echo site_url($this->userinfo['theme'] . '/icons/' . $child['big_icon'])?>" />
					<span><?=$child['short_name']?></span>
				</a>
			</div>
			<?php endif;?>
		<?php endforeach;
		endif;?>
		<div class="spacer"></div>
<?php endforeach;?>
	


<?php if (count($others) > 0):?>
	<h4 class="subtitle-wide">Others</h4>
<?php foreach ($others as $other):?>
		<div class="icon-label-link">
			<a href="<?=site_url($other['link'])?>">
				<img src="<?php echo site_url($this->userinfo['theme'] . '/icons/' . $other['big_icon'])?>" />
				<span><?=$other['short_name']?></span>	
			</a>
		</div>

<?php endforeach;?>

<?php endif;?>