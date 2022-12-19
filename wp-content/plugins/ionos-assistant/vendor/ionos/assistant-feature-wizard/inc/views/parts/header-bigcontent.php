<?php
/**
 * @global $args
 */

use Ionos\Assistant\Config;

?>
<div class="wrapper">
	<div class="header">
		<img class="logo" alt="logo" src="<?php echo Config::get( 'branding.logo_variant1' ); ?>" />
	</div>
	<div class="container big">
		<p class="counter"><?php echo isset( $args['counter_text'] ) && ! empty( $args['counter_text'] ) ? $args['counter_text'] : __( 'Counter missing', 'ionos-assistant' ); ?></p>
		<h1 class="headline"><?php echo isset( $args['heading_text'] ) && ! empty( $args['heading_text'] ) ? $args['heading_text'] : __( 'HEADING MISSING', 'ionos-assistant' ); ?></h1>
		<form method="get">
			<input type="hidden" name="page" value="ionos-assistant">
			<input type="hidden" name="step" value="<?php echo isset( $args['next_step'] ) && ! empty( $args['next_step'] ) ? $args['next_step'] : ''; ?>">
			<div class="content">
