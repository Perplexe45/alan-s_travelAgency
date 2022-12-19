<?php

use Assistant\LoginRedesign\Branding;

?>

<section class="header">
	<?php
        $header_image_src = Branding::get_logo( 'variant1' );
        $header_image_alt = Branding::get_brand();
	?>
	<?php if ( $header_image_src ): ?>
        <img src="<?php echo $header_image_src; ?>" alt="<?php echo $header_image_alt; ?>" class="logo">
	<?php endif; ?>
</section>