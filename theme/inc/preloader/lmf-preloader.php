<?php
/**
 * Plugin Name:       Lemon Mint Films — Cinematic Preloader
 * Description:       Brand ident preloader. The camera rolls in, the citrus reels turn, the projector exposes the wordmark, and it locks to the supplied logo exactly. Holds until the hero media is genuinely ready.
 * Version:           1.3.2
 * Requires at least: 5.2
 * Requires PHP:      7.0
 * Author:            Lemon Mint Films
 * License:           GPL-2.0-or-later
 * Text Domain:       lmf-preloader
 *
 * ---------------------------------------------------------------------
 * This one file runs BOTH ways.
 *
 *   As a plugin   drop the folder in wp-content/plugins/ and activate.
 *   In the theme  drop the folder in your theme and add one line to
 *                 functions.php:
 *
 *                 require_once get_theme_file_path( 'inc/preloader/lmf-preloader.php' );
 *
 * Asset URLs are resolved from the file's own location relative to
 * wp-content, so plugins, themes, child themes and mu-plugins all work
 * without configuration.
 * ---------------------------------------------------------------------
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * The class is wrapped in this conditional rather than guarded by an early
 * return, and that is load-bearing. PHP early-binds an UNCONDITIONAL class
 * declaration at compile time, so `if (class_exists(...)) return;` at the top
 * of the file is already true on the very first load — the file returns
 * before ever registering its hooks, and the plugin silently does nothing.
 * A second include would also fatal on redeclare before that return ran.
 * Declaring inside the conditional fixes both: the class binds at runtime,
 * only once, and whichever copy loads first wins.
 *
 * That matters here because this package is designed to be installed either
 * as a plugin or inside the theme — and nothing stops someone doing both.
 */
if ( ! class_exists( 'LMF_Preloader' ) ) {

final class LMF_Preloader {

	const VERSION  = '1.3.2';
	const OPTION   = 'lmf_preloader_options';

	/** @var bool set once the markup has actually been printed */
	private static $rendered = false;

	/** @var string|null memoised inline SVG */
	private static $svg = null;

	/** @var string the copy that actually won the race */
	public static $loaded_from = __FILE__;

	/** @var array copies that lost it — {path, version} */
	public static $shadowed = array();

	/**
	 * Called by any *other* copy of this file that loads after this one.
	 *
	 * Two copies installed is the quiet failure mode of shipping one folder
	 * that runs both ways: plugins load before themes, so an old plugin
	 * silently wins and the theme's newer copy never runs at all. Nothing
	 * breaks — you just keep looking at the old behaviour and wondering why
	 * your changes did nothing. So the loser records itself and says so on
	 * the admin screen rather than disappearing.
	 */
	public static function note_shadowed( $file, $version ) {
		self::$shadowed[] = array( 'path' => $file, 'version' => $version );
	}

	/** Where a path sits relative to wp-content, for a readable notice. */
	public static function short_path( $file ) {
		$file = wp_normalize_path( $file );
		$root = wp_normalize_path( WP_CONTENT_DIR );
		return 0 === strpos( $file, $root ) ? ltrim( substr( $file, strlen( $root ) ), '/' ) : $file;
	}

	public static function conflict_notice() {
		if ( empty( self::$shadowed ) || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$running = self::short_path( self::$loaded_from );
		$lines   = array();
		foreach ( self::$shadowed as $s ) {
			$lines[] = sprintf(
				/* translators: 1: file path, 2: version */
				__( '%1$s (version %2$s) — ignored', 'lmf-preloader' ),
				'<code>' . esc_html( self::short_path( $s['path'] ) ) . '</code>',
				esc_html( $s['version'] )
			);
		}
		echo '<div class="notice notice-warning"><p><strong>' .
			esc_html__( 'Lemon Mint Films preloader: two copies are installed.', 'lmf-preloader' ) .
			'</strong></p><p>' .
			sprintf(
				/* translators: 1: file path, 2: version */
				esc_html__( 'Running: %1$s (version %2$s).', 'lmf-preloader' ),
				'<code>' . esc_html( $running ) . '</code>',
				esc_html( self::VERSION )
			) .
			'</p><p>' . implode( '<br>', $lines ) . '</p><p>' .
			esc_html__( 'Only the first one loads, and plugins load before themes — so an older plugin copy will hide a newer one inside the theme. Remove whichever you are not using.', 'lmf-preloader' ) .
			'</p></div>';
	}

	public static function init() {
		if ( is_admin() ) {
			$admin = __DIR__ . '/inc/admin.php';
			if ( file_exists( $admin ) ) {
				require_once $admin;
				LMF_Preloader_Admin::init();
			}
			/* runs late, so every other copy has had its chance to register */
			add_action( 'admin_notices', array( __CLASS__, 'conflict_notice' ) );
		}
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'wp_head', array( __CLASS__, 'head' ), 1 );
		add_action( 'wp_body_open', array( __CLASS__, 'render' ), 1 );
		// Themes that predate wp_body_open (or forget to call it) still work:
		// the stage is position:fixed, so where it lands in the DOM does not
		// matter — only that it lands.
		add_action( 'wp_footer', array( __CLASS__, 'render_fallback' ), 1 );
	}

	/* =================================================================
	   Paths and URLs
	   ================================================================= */

	private static function dir() {
		return rtrim( wp_normalize_path( __DIR__ ), '/' );
	}

	/**
	 * Resolve a file inside this folder to a URL, wherever the folder lives.
	 * Everything WordPress serves sits under wp-content, so deriving the URL
	 * from that root covers plugin, theme, child theme and mu-plugin installs
	 * with no branching.
	 */
	private static function url( $rel ) {
		$path    = self::dir() . '/' . ltrim( $rel, '/' );
		$content = rtrim( wp_normalize_path( WP_CONTENT_DIR ), '/' );

		if ( 0 === strpos( $path, $content ) ) {
			return content_url( substr( $path, strlen( $content ) ) );
		}
		// Unusual layout (wp-content moved, symlinked plugin dir): fall back to
		// the plugin API, which understands its own registrations.
		return plugins_url( $rel, __FILE__ );
	}

	private static function ver( $rel ) {
		$path = self::dir() . '/' . ltrim( $rel, '/' );
		return file_exists( $path ) ? self::VERSION . '.' . filemtime( $path ) : self::VERSION;
	}

	/* =================================================================
	   Stored options — what the settings screen writes
	   ================================================================= */

	/**
	 * Defaults. 'room' is the staging the ident was signed off on: the
	 * darkened cinema, the lamp striking, the screen developing up to paper.
	 * 'light' is the Brand Guidelines Ed.02 alternative, which keeps warm
	 * white as the ground throughout and never puts the mark on a black
	 * panel — switch to it in Settings when that rule is enforced.
	 */
	public static function defaults() {
		return array(
			'enabled'          => 1,
			'scope'            => 'front',   // 'front' | 'site'
			'staging'          => 'room',    // 'room'  | 'light'
			'hold'             => 1,
			'max_wait'         => 6000,
			'once_per_session' => 1,
			'show_bar'         => 1,
			'accent'           => '',        // blank = the staging's own token
			'ground'           => '',        // opening colour
			'rest'             => '',        // colour it settles on
			'hero_selector'    => '.hero, #hero, [data-lmf-hero], .wp-block-cover',
			'video_selector'   => '',
			'logo_id'          => 0,         // 0 = the bundled artwork
			'z_index'          => 99990,
		);
	}

	public static function options() {
		$saved = get_option( self::OPTION, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return array_merge( self::defaults(), $saved );
	}

	public static function opt( $key ) {
		$o = self::options();
		return isset( $o[ $key ] ) ? $o[ $key ] : null;
	}

	/** '#f8be1b' -> array(0.972, 0.745, 0.106) for the shader */
	private static function hex_to_rgb( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( ! preg_match( '/^[0-9a-f]{6}$/i', $hex ) ) {
			return null;
		}
		return array(
			round( hexdec( substr( $hex, 0, 2 ) ) / 255, 4 ),
			round( hexdec( substr( $hex, 2, 2 ) ) / 255, 4 ),
			round( hexdec( substr( $hex, 4, 2 ) ) / 255, 4 ),
		);
	}

	public static function staging() {
		return ( 'light' === self::opt( 'staging' ) ) ? 'light' : 'room';
	}

	/** The colour the stage opens on, so the critical CSS can match it. */
	public static function ground_color() {
		$set = self::opt( 'ground' );
		if ( $set ) {
			return $set;
		}
		return ( 'light' === self::staging() ) ? '#f7f5ef' : '#080a0c';
	}

	/* =================================================================
	   Should it run at all
	   ================================================================= */

	public static function should_display() {
		if ( is_admin() || is_feed() || is_embed() || is_preview() ) {
			return false;
		}
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return false;
		}
		if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
			return false;
		}
		// The Customizer preview reloads constantly; an ident on every refresh
		// makes the theme impossible to work on.
		if ( is_customize_preview() ) {
			return false;
		}

		$o = self::options();

		// Admins can force it past the once-per-session gate to review it.
		if ( self::is_preview_request() ) {
			return true;
		}

		if ( empty( $o['enabled'] ) ) {
			return false;
		}

		$show = ( 'site' === $o['scope'] ) ? true : is_front_page();

		/**
		 * Where the preloader appears. Filters win over the stored setting.
		 *
		 * @param bool $show
		 */
		return (bool) apply_filters( 'lmf_preloader_display', $show );
	}

	public static function is_preview_request() {
		return isset( $_GET['lmf_preview'] ) && current_user_can( 'manage_options' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/* =================================================================
	   Front-end config
	   ================================================================= */

	public static function settings() {
		$o = self::options();

		$cfg = array(
			'staging'        => self::staging(),
			'hold'           => (bool) $o['hold'],
			'maxWait'        => (int) $o['max_wait'],
			'minFill'        => 900,
			'videoSeconds'   => 4,
			// An admin previewing must be able to replay it, so the gate is
			// lifted for ?lmf_preview=1 rather than making them clear storage.
			'oncePerSession' => self::is_preview_request() ? false : (bool) $o['once_per_session'],
			'sessionKey'     => 'lmfPreloaderSeen',
			'heroSelector'   => (string) $o['hero_selector'],
			'videoSelector'  => (string) $o['video_selector'],
			'watchVideo'     => true,
			'watchImages'    => true,
			'fadeMs'         => 620,
			'failsafeMs'     => 12000,
			'autoBoot'       => true,
		);

		/* Colour overrides have to reach the SHADER, not just the CSS: the GL
		   canvas paints over the stage background, so changing only the CSS
		   token would be invisible. Blank means "use the staging's own". */
		$palette = array();
		$ground  = self::hex_to_rgb( $o['ground'] );
		$rest    = self::hex_to_rgb( $o['rest'] );
		if ( $ground ) {
			$palette['room'] = $ground;
			if ( 'light' === self::staging() ) {
				// in light staging these three are the same surface
				$palette['ambient'] = $ground;
				$palette['paper']   = $ground;
			}
		}
		if ( $rest ) {
			$palette['paper'] = $rest;
			if ( 'light' === self::staging() ) {
				$palette['room']    = isset( $palette['room'] ) ? $palette['room'] : $rest;
				$palette['ambient'] = $rest;
			}
		}
		if ( $palette ) {
			$cfg['palette'] = $palette;
		}

		/**
		 * Everything the front end reads, in one array. Filters run last, so
		 * code always wins over the settings screen.
		 *
		 * @param array $settings
		 */
		return apply_filters( 'lmf_preloader_settings', $cfg );
	}

	/* =================================================================
	   Output
	   ================================================================= */

	public static function enqueue() {
		if ( ! self::should_display() ) {
			return;
		}

		wp_enqueue_style(
			'lmf-preloader',
			self::url( 'assets/css/lmf-preloader.css' ),
			array(),
			self::ver( 'assets/css/lmf-preloader.css' )
		);

		wp_enqueue_script(
			'lmf-preloader',
			self::url( 'assets/js/lmf-preloader.js' ),
			array(),
			self::ver( 'assets/js/lmf-preloader.js' ),
			true                       // footer: it must never block first paint
		);

		$cfg        = self::settings();
		$cfg['ids'] = array(
			'stage'         => 'lmf-stage',
			'logo'          => 'lmf-logo',
			'svg'           => 'lmf-svg',
			'flash'         => 'lmf-flash',
			'progress'      => 'lmf-progress',
			'progress-fill' => 'lmf-progress-fill',
			'gl-back'       => 'lmf-gl-back',
			'gl-front'      => 'lmf-gl-front',
		);

		wp_add_inline_script(
			'lmf-preloader',
			'window.LMF_PRELOADER = ' . wp_json_encode( $cfg ) . ';',
			'before'
		);
	}

	/**
	 * Printed first thing in <head>.
	 *
	 * Three jobs, all of which have to happen before anything else paints:
	 *   1. lock scrolling,
	 *   2. inline just enough CSS that the stage covers the page immediately
	 *      rather than after the stylesheet arrives,
	 *   3. arm the failsafe that removes the overlay even if the module never
	 *      loads at all. A preloader is the one component that can take a
	 *      whole site down by doing nothing, so this is not optional.
	 */
	public static function head() {
		if ( ! self::should_display() ) {
			return;
		}

		$s        = self::settings();
		$o        = self::options();
		$ground   = self::ground_color();
		$z        = max( 1, (int) $o['z_index'] );
		$failsafe = (int) $s['failsafeMs'] + 3000;
		$accent   = $o['accent'] ? $o['accent'] : '';

		?>
<style id="lmf-preloader-critical">
html.lmf-locked,html.lmf-locked body{overflow:hidden!important;height:100%}
#lmf-stage{position:fixed;inset:0;z-index:<?php echo (int) $z; ?>;background:<?php echo esc_html( $ground ); ?>;display:grid;place-items:center;overflow:hidden;transition:opacity .6s ease}
<?php if ( $accent ) : ?>#lmf-stage{--lmf-accent:<?php echo esc_html( $accent ); ?>}<?php endif; ?>
#lmf-stage.is-leaving{opacity:0;pointer-events:none}
#lmf-stage .lmf-lockup{width:min(33.6vw,455px);display:flex;flex-direction:column;align-items:center;gap:clamp(26px,5vh,52px)}
#lmf-stage svg{width:100%;height:auto;display:block}
@media(max-width:700px){#lmf-stage .lmf-lockup{width:39.2vw}}
</style>
<script id="lmf-preloader-guard">
(function(d,w){
	var r = d.documentElement;
	r.className += ' lmf-locked';
	/* If the module loads, boot() clears this. If the script 404s, throws on
	   parse, or the browser blocks it, this is what gives the site back. */
	w.__lmfFailsafe = w.setTimeout(function(){
		r.className = r.className.replace(/\blmf-locked\b/, '') + ' lmf-ready';
		var s = d.getElementById('lmf-stage');
		if (s && s.parentNode) { s.parentNode.removeChild(s); }
	}, <?php echo (int) $failsafe; ?>);
})(document, window);
</script>
<noscript><style>#lmf-stage{display:none!important}html.lmf-locked,html.lmf-locked body{overflow:auto!important;height:auto}</style></noscript>
		<?php
	}

	public static function render() {
		if ( self::$rendered || ! self::should_display() ) {
			return;
		}
		self::$rendered = true;

		$o       = self::options();
		$staging = self::staging();
		$show_bar = ! empty( $o['show_bar'] );
		$svg     = self::svg();

		if ( '' === $svg ) {
			return;   // no artwork, no overlay — better than an empty hold
		}

		$template = self::dir() . '/templates/preloader.php';

		/**
		 * Swap the markup wholesale if the theme needs different structure.
		 *
		 * @param string $template absolute path
		 */
		$template = apply_filters( 'lmf_preloader_template', $template );

		if ( file_exists( $template ) ) {
			include $template;
		}
	}

	public static function render_fallback() {
		// Only fires when the theme never called wp_body_open.
		self::render();
	}

	/* =================================================================
	   The artwork
	   ================================================================= */

	/**
	 * The logo is inlined rather than served as <img> or <object> because the
	 * engine has to reach its groups — #lmf-camera, #lmf-reel-large,
	 * #lmf-reel-small, #lmf-wordmark — in the same document to animate them.
	 * Nothing inside those groups is read or rewritten; they are only wrapped
	 * and transformed, and every transform is cleared at lockup so the resting
	 * frame is the file exactly.
	 */
	public static function svg() {
		if ( null !== self::$svg ) {
			return self::$svg;
		}

		$path = self::dir() . '/assets/img/lmf-logo.svg';

		$chosen = (int) self::opt( 'logo_id' );
		if ( $chosen ) {
			$file = get_attached_file( $chosen );
			if ( $file && is_readable( $file ) ) {
				$path = $file;
			}
		}

		/**
		 * Point at a different SVG. It must carry the same five group ids.
		 *
		 * @param string $path absolute path
		 */
		$path = apply_filters( 'lmf_preloader_logo_path', $path );

		if ( ! $path || ! is_readable( $path ) || 'svg' !== strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) ) {
			self::$svg = '';
			return self::$svg;
		}

		$markup = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		self::$svg = $markup ? self::clean_svg( $markup ) : '';
		return self::$svg;
	}

	/**
	 * The file ships with the package, but the path is filterable, so strip
	 * anything executable before echoing it unescaped. Not a substitute for
	 * trusting your own asset — a guard against pointing this at an upload.
	 */
	private static function clean_svg( $markup ) {
		$markup = preg_replace( '#<\?xml[^>]*\?>#i', '', $markup );
		$markup = preg_replace( '#<!DOCTYPE[^>]*>#i', '', $markup );
		$markup = preg_replace( '#<(script|foreignObject)\b[^>]*>.*?</\1>#is', '', $markup );
		$markup = preg_replace( '#<(script|foreignObject)\b[^>]*/?>#i', '', $markup );
		$markup = preg_replace( '#\son[a-z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)#is', '', $markup );
		$markup = preg_replace( '#(href|xlink:href)\s*=\s*(["\'])\s*javascript:[^"\']*\2#is', '', $markup );
		return trim( $markup );
	}
}

LMF_Preloader::init();

} else {

	/*
	 * Another copy got here first. Say so on the admin screen instead of
	 * vanishing — see LMF_Preloader::note_shadowed().
	 */
	if ( method_exists( 'LMF_Preloader', 'note_shadowed' ) ) {
		LMF_Preloader::note_shadowed( __FILE__, '1.3.2' );
	}
} // class_exists guard
