<?php
/**
 * Header — the lockup, primary navigation and the single lemon action.
 *
 * @package LemonMintFilms
 */
defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'lemonmint' ); ?></a>

<header id="nav">
	<div class="wrap nav-in">
		<a class="lockup-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<?php
			// The preloader flies its mark to this one when it finishes.
			lmf_lockup( false, array( 'data-lmf-logo-target' => '' ) );
			?>
		</a>

		<!-- The five destinations live in the full-screen menu. The bar keeps
		     them as a hidden list too, so they stay in the page for search
		     engines and for anyone reading without CSS. -->
		<nav class="nav-links" aria-label="<?php esc_attr_e( 'Primary', 'lemonmint' ); ?>">
			<?php foreach ( lmf_nav_items() as $it ) : ?>
				<div><a class="nlink" href="<?php echo esc_url( $it['url'] ); ?>"><?php echo esc_html( $it['label'] ); ?></a></div>
			<?php endforeach; ?>
		</nav>

		<span class="nav-sp"></span>
		<a class="btn btn-lemon nav-cta" href="<?php echo esc_url( home_url( '/contact/?i=production' ) ); ?>" style="padding:13px 20px"><?php echo esc_html( lmf_opt( 'nav_cta', __( 'Start a Production', 'lemonmint' ) ) ); ?></a>
		<button class="burger" id="burger" type="button" aria-controls="lmf-menu" aria-expanded="false">
			<span class="burger-word" data-open="<?php esc_attr_e( 'Menu', 'lemonmint' ); ?>" data-close="<?php esc_attr_e( 'Close', 'lemonmint' ); ?>"><?php esc_html_e( 'Menu', 'lemonmint' ); ?></span>
			<span class="burger-lines" aria-hidden="true"><i></i><i></i></span>
		</button>
	</div>
</header>

<!-- FULL-SCREEN MENU — big words over the page, blurred. -->
<div id="lmf-menu" class="menu" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'lemonmint' ); ?>" data-cursor-tone="dark" hidden>
	<div class="menu-veil" aria-hidden="true"></div>
	<div class="wrap menu-in">
		<nav aria-label="<?php esc_attr_e( 'Main', 'lemonmint' ); ?>">
			<ol class="menu-list">
				<?php foreach ( lmf_nav_items() as $i => $it ) : ?>
					<li class="menu-item" style="--i:<?php echo (int) $i; ?>">
						<a class="menu-link" href="<?php echo esc_url( $it['url'] ); ?>">
							<span class="meta menu-n"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
							<span class="menu-w"><span class="menu-w-in"><?php echo esc_html( $it['label'] ); ?></span></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		</nav>

		<div class="menu-foot">
			<div class="menu-ch">
				<span class="meta"><?php esc_html_e( 'Write', 'lemonmint' ); ?></span>
				<a href="mailto:<?php echo esc_attr( lmf_opt( 'email', 'info@lemonmintfilms.com' ) ); ?>"><?php echo esc_html( lmf_opt( 'email', 'info@lemonmintfilms.com' ) ); ?></a>
			</div>
			<?php
			$lmf_phone = lmf_opt( 'phone', '+971 4 332 3054' );
			$lmf_wa    = preg_replace( '/\D/', '', (string) lmf_opt( 'whatsapp', '' ) );
			?>
			<div class="menu-ch">
				<span class="meta"><?php echo esc_html( $lmf_wa ? __( 'Call / WhatsApp', 'lemonmint' ) : __( 'Call', 'lemonmint' ) ); ?></span>
				<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $lmf_phone ) ); ?>"><?php echo esc_html( $lmf_phone ); ?></a>
				<?php if ( $lmf_wa ) : ?>
					<a href="<?php echo esc_url( 'https://wa.me/' . $lmf_wa ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp', 'lemonmint' ); ?> <span aria-hidden="true">&nearr;</span></a>
				<?php endif; ?>
			</div>
			<div class="menu-ch">
				<span class="meta"><?php esc_html_e( 'Studio', 'lemonmint' ); ?></span>
				<span><?php echo esc_html( lmf_opt( 'address', 'Warehouse 28, Al Quoz Industrial Third' ) ); ?></span>
			</div>
			<a class="btn btn-lemon menu-cta" href="<?php echo esc_url( home_url( '/contact/?i=production' ) ); ?>"><?php esc_html_e( 'Start a Production', 'lemonmint' ); ?> <span aria-hidden="true">&rarr;</span></a>
		</div>
	</div>
</div>

<main id="content">
