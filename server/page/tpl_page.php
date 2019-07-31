<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="de">
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Security-Policy" content="<?php echo $this->get_csp_rules(); ?>" />
<meta http-equiv="WebKit-CSP" content="<?php echo $this->get_csp_rules(); ?>" />
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<?php
    $this->output_meta_tags();
?>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<?php
    $this->output_css_includes();
    $this->output_js_includes();
?>
    <script><?php echo $this->get_js_constants() ?></script>
</head>
<body>
<?php
    $this->output_warnings();
    $this->output_base_content();
?>
</body>
</html>
