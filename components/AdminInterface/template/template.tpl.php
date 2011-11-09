<?php 
/**
 *  @package Cumula
 *  @subpackage Templater
 *  @version    $Id$
 */
?>

<!doctype html>
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]--> 
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]--> 
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]--> 
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]--> 
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]--> 
<head>
	<meta charset="utf-8">
	<title><?php if (isset($title)) echo $title ?></title>
	
	<?php if (isset($meta)) echo $meta; ?>
	<?php if (isset($stylesheets)) echo $stylesheets; ?>
	
	<meta name="author" content="SEABOURNE">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
 
	<!-- CSS : implied media="all" -->
	<link rel="stylesheet" href="/assets/AdminInterface/css/admin.css">
 
	<!-- All JavaScript at the bottom, except for Modernizr which enables HTML5 elements & feature detects -->
	<script src="/templates/shared/js/libs/modernizr-1.6.min.js"></script>
 
</head>
<body>
	<div id="container">
		<header id="masthead" role="banner">
			<h1 id="logo" class="ir cumula">Cumula</h1>
		</header>
		<div id="main" class="group">
			<div id="sidebar">
				<nav role="navigation">
					<?php if(isset($adminMenu)) echo $adminMenu; ?>
				</nav>
			</div>
			<section id="content" role="main">
				<?php if(isset($content)) echo $content; ?>
			</section>
		</div>
		<footer id="page_footer">
		</footer>
	</div>
	<!-- /container -->
	
	<?php if (isset($javascript)) echo $javascript; ?>
	
</body> 
</html>
