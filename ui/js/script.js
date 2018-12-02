$(document).ready(function(){
	
	$('#awstable').on('click','.editbtn',function(){
		$('#editawsid').val($(this).data('awsid'));
		$('#editname').val($(this).data('name'));
		$('#editusername').val($(this).data('username'));
		$('#editpassword').val($(this).data('password'));
	});
	
});