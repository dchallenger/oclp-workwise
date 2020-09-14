<ul class="aside-nav">
    <h3 class="menu-header">
        <img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/icon-default-16.png" />
        <span class="header-link">IT-IMS</span>
        <span class="slidetoggle"><a class="icon-16-portlet-fold"></a> </span>         
    </h3>
    <ul>
    <?php foreach ($records as $record):
        if( substr(site_url($record->content), -4) == '.pdf' ){
    ?>
        <li class="li-manual">
            <a class="a-manual pdf-file" href="" rel="<?=site_url($record->content)?>">
                <span class="align-left aside-link"><?=$record->description?></span>
            </a>
        </li>
    <?php 
        }
        else{
    ?>
        <li class="li-manual">
            <a class="a-manual" href="<?=site_url($record->content)?>" rel="<?=site_url($record->content)?>">
                <span class="align-left aside-link"><?=$record->description?></span>
            </a>
        </li>
    <?php             
        }
    endforeach;
    ?>
    </ul>
</ul>