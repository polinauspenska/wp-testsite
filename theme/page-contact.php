<?php
/**
 * Template Name: Contact — one screen
 *
 * CONTACT US on a single screen: the map on one side, every direct line on
 * the other, one lemon action. The brief itself lives in the popup
 * (inc/brief.php), so nobody scrolls a form on this page.
 *
 * The map plate is drawn in SVG — warm ground, ink streets, a lemon pin —
 * and a live map is laid over it when it loads. If the embed is blocked,
 * switched off (Customizer → Map embed), or simply slow, the drawn plate is
 * what people see, and "Get directions" always works.
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
get_header();

$email    = lmf_opt( 'email', 'info@lemonmintfilms.com' );
$phone    = lmf_opt( 'phone', '+971 4 332 3054' );
$whatsapp = preg_replace( '/\D/', '', lmf_opt( 'whatsapp', '' ) );
$address  = lmf_opt( 'address', 'Warehouse 28, Al Quoz Industrial Third' );
$city     = lmf_opt( 'city', 'Dubai, United Arab Emirates' );
$hours    = lmf_opt( 'hours', 'Mon–Fri · 09:00–18:00' );
$response = lmf_opt( 'response', 'Under 4 working hours' );
$embed    = (bool) lmf_opt( 'map_embed', true );

// Page copy: ACF (Contact page) first, the theme's own words second.
$pid = get_queried_object_id();
$t   = array(
	'kicker'     => lmf_field( 'cx_kicker', __( 'Contact us', 'lemonmint' ), $pid ),
	'line1'      => lmf_field( 'cx_line1', __( 'Let’s shoot', 'lemonmint' ), $pid ),
	'line2'      => lmf_field( 'cx_line2', __( 'something.', 'lemonmint' ), $pid ),
	'lede'       => lmf_field( 'cx_lede', __( 'A producer — not a form robot — reads every brief and writes back.', 'lemonmint' ), $pid ),
	'write'      => lmf_field( 'cx_label_write', __( 'Write', 'lemonmint' ), $pid ),
	'call'       => lmf_field( 'cx_label_call', __( 'Call', 'lemonmint' ), $pid ),
	'studio'     => lmf_field( 'cx_label_studio', __( 'Studio', 'lemonmint' ), $pid ),
	'hours'      => lmf_field( 'cx_label_hours', __( 'Hours', 'lemonmint' ), $pid ),
	'whatsapp'   => lmf_field( 'cx_whatsapp_label', __( 'WhatsApp a producer', 'lemonmint' ), $pid ),
	'directions' => lmf_field( 'cx_directions_label', __( 'Get directions', 'lemonmint' ), $pid ),
	'cta'        => lmf_field( 'cx_cta', __( 'Start a production', 'lemonmint' ), $pid ),
	'card'       => lmf_field( 'cx_card_label', __( 'The studio', 'lemonmint' ), $pid ),
);

$q      = rawurlencode( lmf_opt( 'map_query', '' ) ? lmf_opt( 'map_query', '' ) : $address . ', ' . $city );
$maps   = 'https://www.google.com/maps/search/?api=1&query=' . $q;
$iframe = 'https://www.google.com/maps?q=' . $q . '&z=14&output=embed';
?>

<div class="cx">
	<div class="cx-in">

		<!-- ---------------------------------------------------------------
			 the lines
			 --------------------------------------------------------------- -->
		<section class="cx-info">
			<div class="cx-top">
				<span class="kicker"><?php echo esc_html( $t['kicker'] ); ?></span>
				<h1 class="cx-h"><?php echo esc_html( $t['line1'] ); ?><br><?php echo esc_html( $t['line2'] ); ?></h1>
				<p class="cx-lede"><?php echo esc_html( $t['lede'] ); ?></p>
			</div>

			<dl class="cx-lines">
				<div class="cx-line">
					<dt><?php echo esc_html( $t['write'] ); ?></dt>
					<dd><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></dd>
				</div>
				<div class="cx-line">
					<dt><?php echo esc_html( $t['call'] ); ?></dt>
					<dd>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
						<?php if ( $whatsapp ) : ?>
							<a class="cx-sm" href="<?php echo esc_url( 'https://wa.me/' . $whatsapp ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $t['whatsapp'] ); ?> <span aria-hidden="true">&nearr;</span></a>
						<?php endif; ?>
					</dd>
				</div>
				<div class="cx-line">
					<dt><?php echo esc_html( $t['studio'] ); ?></dt>
					<dd>
						<?php echo esc_html( $address ); ?><br><?php echo esc_html( $city ); ?>
						<a class="cx-sm" href="<?php echo esc_url( $maps ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $t['directions'] ); ?> <span aria-hidden="true">&nearr;</span></a>
					</dd>
				</div>
				<div class="cx-line">
					<dt><?php echo esc_html( $t['hours'] ); ?></dt>
					<dd>
						<?php echo esc_html( $hours ); ?>
						<span class="cx-now" data-lmf-clock data-days="1,2,3,4,5" data-from="9" data-to="18">
							<span class="cx-dot" aria-hidden="true"></span>
							<span class="ct-time">—:—</span>
							<span class="ct-state"><?php esc_html_e( 'in Dubai.', 'lemonmint' ); ?></span>
						</span>
					</dd>
				</div>
			</dl>

			<div class="cx-cta">
				<a class="btn btn-lemon" href="<?php echo esc_url( home_url( '/contact/?i=production' ) ); ?>" data-brief><?php echo esc_html( $t['cta'] ); ?> <span aria-hidden="true">&rarr;</span></a>
				<span class="cx-note meta"><?php echo esc_html( sprintf( /* translators: %s: response time */ __( 'Reply in %s', 'lemonmint' ), $response ) ); ?></span>
			</div>
		</section>

		<!-- ---------------------------------------------------------------
			 the map
			 --------------------------------------------------------------- -->
		<section class="cx-map" aria-label="<?php esc_attr_e( 'Where we are', 'lemonmint' ); ?>">
			<?php lmf_contact_map_plate(); ?>

			<?php if ( $embed ) : ?>
				<iframe class="cx-embed" src="<?php echo esc_url( $iframe ); ?>" title="<?php echo esc_attr( sprintf( /* translators: %s: address */ __( 'Map: %s', 'lemonmint' ), $address ) ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" tabindex="-1"></iframe>
			<?php endif; ?>

			<div class="cx-card">
				<span class="meta"><?php echo esc_html( $t['card'] ); ?></span>
				<p class="cx-card-a"><?php echo esc_html( $address ); ?></p>
				<a class="link-u" href="<?php echo esc_url( $maps ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $t['directions'] ); ?> <span aria-hidden="true">&nearr;</span></a>
			</div>
		</section>

	</div>
</div>

<?php
get_footer();
