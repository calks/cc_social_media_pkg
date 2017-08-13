<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Social Media Connect</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<script type="text/javascript" src="<?php echo $this->domain?><?php echo $this->client_script_url?>"></script>
	</head>
	<body>
		
		<script type="text/javascript">
			<!--				
				window.opener.<?php echo $listener; ?>.responseListener(<?php echo $response; ?>);
				window.close();
			// -->
		</script>
			
	
	</body>
</html>