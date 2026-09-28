<?php
/**
 * Preloader markup.
 *
 * Included by LMF_Preloader::render() with $svg and $staging in scope.
 * Override by filtering 'lmf_preloader_template' with your own path.
 *
 * @var string $svg     inline, sanitised logo markup
 * @var string $staging  'light' | 'room'
 * @var bool   $show_bar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="lmf-stage" class="lmf-stage" data-staging="<?php echo esc_attr( $staging ); ?>"
     role="img" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">

	<canvas id="lmf-gl-back" class="lmf-gl-back" aria-hidden="true"></canvas>

	<div class="lmf-lockup">
		<div id="lmf-logo" class="lmf-logo">
			<?php
			/*
			 * Deliberately not escaped: this is SVG markup, and it has already
			 * been through LMF_Preloader::clean_svg(). Escaping it would print
			 * the source instead of drawing the logo.
			 */
			echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</div>

		<?php if ( ! isset( $show_bar ) || $show_bar ) : ?>
		<div id="lmf-progress" class="lmf-progress" role="progressbar"
		     aria-label="<?php esc_attr_e( 'Loading', 'lmf-preloader' ); ?>"
		     aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
			<span id="lmf-progress-fill" class="lmf-progress-fill"></span>
		</div>
		<?php endif; ?>
	</div>

	<div id="lmf-flash" class="lmf-flash" aria-hidden="true"></div>
	<canvas id="lmf-gl-front" class="lmf-gl-front" aria-hidden="true"></canvas>
</div>
