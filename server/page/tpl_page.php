<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="de">
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' 'unsafe-inline';" />
<meta http-equiv="WebKit-CSP" content="default-src 'self'; style-src 'self' 'unsafe-inline';" />
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<?php
    $this->output_meta_tags();
?>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<?php
    $this->output_css_includes();
    $this->output_js_includes();
?>
</head>
<body>
<?php
    if( DEBUG ) echo '<div class="alert alert-warning m-0" role="alert">Test Mode!</div>';
    $this->output_base_content();
?>
</body>
</html>
