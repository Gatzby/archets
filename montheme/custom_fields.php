<?php $lieu=get_post_meta(get_the_ID(),'wpcf-lieu',true); ?>

<div class="meta">
	<?php if (!empty($lieu)): ?>
		<span><?php echo $lieu; ?></span>
	<?php endif; ?>
</div>

<div class="entry-content">
	<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'twentythirteen' ) ); ?>
	<?php wp_link_pages( array( 'before' => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'twentythirteen' ) . '</span>', 'after' => '</div>', 'link_before' => '<span>', 'link_after' => '</span>' ) ); ?>
</div>
