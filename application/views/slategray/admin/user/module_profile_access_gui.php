<style type="text/css" media="screen">
	.text-left{ text-align: left !important}
    .rotate { height: 60px; vertical-align: bottom; cursor: pointer;  -moz-user-select: none; -webkit-user-select: none; }
    tbody tr th.module-name { cursor: pointer; -moz-user-select: none; -webkit-user-select: none;}
    .rotate div { -ms-transform: rotate(290deg); -o-transform: rotate(290deg); -moz-transform: rotate(290deg); -webkit-transform: rotate(290deg); display: block; width: 16px; text-align: center; margin: 0 auto;}
    <!--[if lt IE 9]>
    	.rotate div { filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3.1);}
    <![endif]-->
</style>
<h3 class="form-head">Profile Module Access</h3>
<p class="form-group-description align-left">Check all that applies. You can also click the <strong><em>action name</em></strong> or the <strong><em>module name</em></strong> to check the column or the rows respectively.</p>
<div class="form-submit-btn align-right nopadding">
    <div class="icon-label-group">
        <div class="icon-label">
	       	<a rel="action-checkall" class="icon-16-tick" href="" onclick="">
	       		<span>Check All</span>
	       	</a>            
        </div>
        <div class="icon-label">
	       	<a rel="action-uncheckall" class="icon-16-uncheck" href="" onclick="">
	       		<span>Uncheck All</span>
	       	</a>            
        </div>
    </div>
</div>
<div class="clear"></div>
<div class="spacer"></div>
<table id="module-access" style="width:100%" class="default-table boxtype">
    <colgroup width="15%"></colgroup><?php
	// get list of module actions
	$this->db->order_by('id');
	$actionlist = $this->db->get('module_action')->result_array();

	//get group access
	$this->db->select('module_access');
	$result = $this->db->get_where('profile', array('profile_id' => $this->input->post('record_id')));
	$module_access = $result->row_array();
	if ( $result->num_rows() > 0 )
		$module_access = unserialize($module_access['module_access']);
	else
		$module_access = array();         	
	
	foreach ( $actionlist as $action_index => $action ) { echo '<colgroup><colgroup/>'; } ?>
    <thead>
	    <tr>
	        <th style="vertical-align:middle">Module</th><?php
            	// Display the Actions
	            foreach ( $actionlist as $action_index => $action ) echo '<th class="action-name rotate font-smaller '.($action_index % 2 == 0 ? "even" : "odd").'"><div>'.($action['action']).'</div></th>'; ?>
	    </tr>
    </thead>
    <tbody><?php
		//get list of modules
		$modules = $this->hdicore->get_module_child( 0, 0 );
		foreach($modules as $module):
			show_access_gui( $module, $module_access, $actionlist);
		endforeach; ?>
    </tbody>
</table>
<div class="spacer"></div>