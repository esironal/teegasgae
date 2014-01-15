<!DOCTYPE html>
<html lang="<?php echo LANG; ?>" >
<head>
<meta charset="UTF-8" />
<title><?php echo $post->title() . '|' . SITETITLE ; ?></title>
<meta name="robots" content="index, follow" />
<meta name="description" content="<?php echo ($post->description() != '') ? $post->description() : ''; ?>" />
<meta name="keywords" content="<?php echo ($post->keywords() != '') ? $post->keywords() : ''; ?>" />
<meta name="author" content="Author Name" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="shortcut icon" href="<?php echo URL_PUBLIC ;?>favicon.ico">
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" />
<link rel="stylesheet" href="<?php echo THEMES_URI; ?>jumbotron.css" />
<!-- Optional theme -->
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css" />
<!-- Font awesome -->
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.0.1/css/font-awesome.css">
<!-- Extension -->
<link rel="stylesheet" href="<?php echo THEMES_URI; ?>prettify.css" />
<link rel="stylesheet" href="<?php echo THEMES_URI; ?>bootstrapext.css" />
<!-- Fav and touch icons -->
<link rel="icon" href="<?php echo THEMES_URI; ?>images/favicon.ico" />
<!-- RSS -->
<link rel="alternate" type="application/rss+xml" title="RSS Feed" href="<?php echo URL_PUBLIC.((USE_MOD_REWRITE)?'':'?'); ?>rss.xml" />
</head><body>
