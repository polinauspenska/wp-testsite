<?php
/**
 * Search form.
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
?>
<form role="search" method="get" class="cform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="full">
		<span><?php esc_html_e( 'Search', 'lemonmint' ); ?></span>
		<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Projects, services, journal…', 'lemonmint' ); ?>">
	</label>
</form>
