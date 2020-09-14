<!-- start alerts from redirection/user access level -->
<?php
if ($this->session->flashdata('msg_type') == '') {
	$msg_type = 'attention';
} else {
	$msg_type = $this->session->flashdata('msg_type');
}
?>
<script type="text/javascript">
	$(document).ready( function(){
		$( '#message-container' ).html( message_growl( '<?=$msg_type?>', '<?=$flashdata?>' ) );
	});
</script>
<!-- end alerts from redirection/user access level -->
