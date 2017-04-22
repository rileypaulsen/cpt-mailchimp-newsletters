<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Test Newsletter</title>
</head>
<body>
<h1>You will want to change this file.</h1>
<p>Add a file called <code>single-newsletter.php</code> to your theme. The output of that file will be emailed verbatim. Speak with your developer if you need assistance.</p>
	<?php while(have_posts()): the_post(); the_content(); endwhile; ?>
</body>
</html>