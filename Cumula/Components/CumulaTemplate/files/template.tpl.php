<!doctype html>
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]--> 
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]--> 
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]--> 
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]--> 
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]--> 
<head> 
	<meta charset="utf-8"> 
	<title><?php if (isset($title)) echo $title ?></title>
	
	<meta name="description" content="Cumula Welcome Screen">
	<meta name="author" content="Jay Contonio">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php if (isset($js)) echo $js ?>
<?php if (isset($css)) echo $css ?>


	<style type="text/css">
		body { 
			background-image:url('/assets/CumulaTemplate/images/cumula-background.png');
			background-color:#fbf3e2; background-repeat:repeat-x; color:#444; font-family:"Helvetica Neue",sans-serif; font-style:normal; font-size:18px; line-height:24px; margin:0;
		}
		/* Links */
		a:hover, a:active { outline: none; }
		a, a:active, a:visited { border-bottom:1px solid #e88801; color:#444; text-decoration:none; }
		a:hover { color:#AD6602; }
		/*  j.mp/webkit-tap-highlight-color */
		a:link { -webkit-tap-highlight-color: #BE2A00; }
		/* Selection hilighting */
		::-moz-selection{ background: #FFB900; color:#fff; text-shadow: none; }
		::selection { background:#FFB900; color:#fff; text-shadow: none; }
		
		.container { margin:auto; max-width:960px; position:relative; }
		.ir { overflow:hidden; text-indent:-7000px; }
		
		/* 
		 * -- Masthead --
		 */
		#masthead { background:#ffc200; box-shadow:0px 4px 10px rgba(227,192,202,.2); height:48px; position:relative; }
			#masthead #logo { background-image:url('/assets/CumulaTemplate/images/cumula-logo.png'); height:42px; float:left; margin:2px 14px 2px 0; width:56px; }

		.primary li { float:left; margin:0; }
			.primary li:hover { background:#fecf38; }
		.primary li:hover .sub { display: block; }
		.sub { display: none; margin:0; position: absolute; top; 0px; border: none; }
		.sub li {float: none; margin:0; background:#fecf38; border:1px solid #efb510; }
		.sub a {border: none;}
		.primary .home { background-image:url('/assets/CumulaTemplate/images/home-button.png'); border-right:none; height:57px; padding:0; width:44px; }
		.primary a, .primary span { border:none; border-right:1px solid #efb510; color:#730046; display:block; font-size:14px; height:48px; line-height:46px; padding:0 1em; text-decoration:none; text-shadow:1px 1px 1px #fff; }
			.primary a strong { background:#444; border-radius:6px; color:#fff; font-size:12px; font-weight:normal; margin:0 0 0 .5em; padding:.25em .5em; text-shadow:none; }

		#container { margin:120px auto; width:959px; }
			#container h1 { color:#c93c00; font-size:36px; font-weight:normal; margin-bottom:18px; }
			#container .bucket { background:#fff; border-radius:12px 12px; box-shadow:1px 4px 13px rgba(0,0,0,.1); padding:1em 3.25em 2em 3em; width:560px; }
			#container .button {
				background: #f2825b; /* Old browsers */
				background: -moz-linear-gradient(top,  #f2825b 0%, #e55b2b 50%, #f07146 100%); /* FF3.6+ */
				background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f2825b), color-stop(50%,#e55b2b), color-stop(100%,#f07146)); /* Chrome,Safari4+ */
				background: -webkit-linear-gradient(top,  #f2825b 0%,#e55b2b 50%,#f07146 100%); /* Chrome10+,Safari5.1+ */
				background: -o-linear-gradient(top,  #f2825b 0%,#e55b2b 50%,#f07146 100%); /* Opera 11.10+ */
				background: -ms-linear-gradient(top,  #f2825b 0%,#e55b2b 50%,#f07146 100%); /* IE10+ */
				background: linear-gradient(top,  #f2825b 0%,#e55b2b 50%,#f07146 100%); /* W3C */
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f2825b', endColorstr='#f07146',GradientType=0 ); /* IE6-9 */
				
				border:none; border-radius:6px; color:#fff; display:inline-block; font-size:14px; padding:.25em 1em; text-decoration:none; text-shadow:1px 1px 1px #A33611; -webkit-appearance:none; }
			#container .button:hover { background:#E14A17; }
			#container .button:active { background:#A33611; }
			#container input.button { display:block; margin:2em 0 1em 0; }
		
		hr { border:none; border-top:1px dashed #ccc; display:block; margin:1.75em 0; }
		
		ul { list-style:none; margin:0; padding:0; }
		.box { background:#232323; color:#fff; font-size:14px; margin:0 0 2em 0; padding:1em; }
			#container .box h1 { background:#0C0C0C; border-bottom:1px solid #282828; color:#8A8A8A; font-size:14px; margin:-1em -1em 1em -1em; padding:.5em 1em; }
			.box a { color:#fff; }
		.success { color:#99E100; }
		.error { color:red; }
		
		fieldset { border:none; margin:0 0 1em 0; padding:0; }
		label { display:block; font-size:14px; }
		.checkbox label { display:inline-block; font-size:14px; }
		fieldset input { border:1px solid #ccc; color:#666; font-size:13px; margin-bottom:1em; outline:none; padding:6px 12px 6px 6px; }
			fieldset input:focus { border:1px solid #333; }

	</style>
 
</head>
<body>
	
	<header id="masthead" role="banner">
		<div class="container">
			<h1 id="logo" class="ir">Cumula</h1>
			<nav id="primary" class= 'primary' role="navigation">
				<ul class="group">
					<li><a class="ir home" href="/admin">Home</a></li>
					<?php if(isset($adminMenu)) echo $adminMenu; ?>
				</ul>
			</nav>
		</div>
	</header>
	
	<div id="container">
		<div class="bucket">
			<?php echo \A("\\Cumula\\Components\\Session\\Session")->warning; ?>
			<?php echo \A('\\Cumula\\Components\\Session\\Session')->notice; ?>
			<?php echo $content; ?>
		</div>
	</div>
	<!-- /container -->
	<div class='debugOutput'>
		<div class='debugOutputContent' style=''>
			<!-- $debugOutput -->
		</div>
	</div>
</body>
</html>
