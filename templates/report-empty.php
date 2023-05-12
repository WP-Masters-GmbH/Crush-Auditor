<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<div class="mini-audit-report empty">
		<p><?php echo __('We are aware of your request to create an audit report for the specified Google Business Profile. Our team is working on it and will notify you via email once the report is ready for you to review.'); ?></p>
	</div>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>