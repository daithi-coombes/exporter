<?php
if( $_REQUEST ){

	print json_encode($_REQUEST);
	die();

	$start = date(  )
}
?>
<!DOCTYPE html>
<html>

	<head>
		<script src="http://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript"></script>

		<script type="text/javascript">
			$(document).ready(function(){
			});

			function do_it(){
				$.post(
					'foo.php',
					{
						'var 1' : 'one'
					},
					function(res){
						console.log(res);
					}
				)
			}
		</script>
	</head>

	<body>
		<button onclick="do_it()">Click me</button>
	</body>

</html>