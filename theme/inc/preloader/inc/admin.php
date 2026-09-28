<?php
/**
 * Settings screen — Settings → LMF Preloader.
 *
 * Everything here writes one option array, which LMF_Preloader::options()
 * reads. Filters still run last, so a developer's `lmf_preloader_settings`
 * filter always wins over what is saved here.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'LMF_Preloader_Admin' ) ) {

final class LMF_Preloader_Admin {

	const PAGE  = 'lmf-preloader';
	const GROUP = 'lmf_preloader_group';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_filter(
			'plugin_action_links_' . plugin_basename( dirname( __DIR__ ) . '/lmf-preloader.php' ),
			array( __CLASS__, 'action_link' )
		);
	}

	public static function action_link( $links ) {
		array_unshift(
			$links,
			'<a href="' . esc_url( admin_url( 'options-general.php?page=' . self::PAGE ) ) . '">'
				. esc_html__( 'Settings', 'lmf-preloader' ) . '</a>'
		);
		return $links;
	}

	public static function menu() {
		add_options_page(
			__( 'LMF Preloader', 'lmf-preloader' ),
			__( 'LMF Preloader', 'lmf-preloader' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'page' )
		);
	}

	public static function assets( $hook ) {
		if ( 'settings_page_' . self::PAGE !== $hook ) {
			return;
		}
		// Needed for the logo chooser.
		wp_enqueue_media();
	}

	public static function register() {
		register_setting(
			self::GROUP,
			LMF_Preloader::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => LMF_Preloader::defaults(),
			)
		);
	}

	/**
	 * Every field is validated, not just escaped. A bad hex or a logo without
	 * the required group ids would produce a broken preloader on the front
	 * page, which is the one place a silent failure is least acceptable — so
	 * those are rejected with a message rather than saved.
	 */
	public static function sanitize( $in ) {
		$d   = LMF_Preloader::defaults();
		$out = LMF_Preloader::options();   // start from what is already stored
		$in  = is_array( $in ) ? $in : array();

		foreach ( array( 'enabled', 'hold', 'once_per_session', 'show_bar' ) as $k ) {
			$out[ $k ] = empty( $in[ $k ] ) ? 0 : 1;
		}

		$out['scope']   = ( isset( $in['scope'] ) && 'site' === $in['scope'] ) ? 'site' : 'front';
		$out['staging'] = ( isset( $in['staging'] ) && 'light' === $in['staging'] ) ? 'light' : 'room';

		// 0 disables the wait entirely; anything longer than 20s is a visitor
		// staring at an overlay, which is never the right answer.
		$out['max_wait'] = isset( $in['max_wait'] ) ? min( 20000, max( 0, (int) $in['max_wait'] ) ) : $d['max_wait'];
		$out['z_index']  = isset( $in['z_index'] ) ? max( 1, (int) $in['z_index'] ) : $d['z_index'];

		foreach ( array( 'accent', 'ground', 'rest' ) as $k ) {
			$raw = isset( $in[ $k ] ) ? trim( (string) $in[ $k ] ) : '';
			if ( '' === $raw ) {
				$out[ $k ] = '';
				continue;
			}
			$hex = sanitize_hex_color( $raw );
			if ( $hex ) {
				$out[ $k ] = $hex;
			} else {
				$out[ $k ] = '';
				add_settings_error(
					LMF_Preloader::OPTION,
					'bad-hex-' . $k,
					sprintf(
						/* translators: %s: field name */
						__( '%s was not a valid colour, so it has been left on the staging default.', 'lmf-preloader' ),
						esc_html( $k )
					)
				);
			}
		}

		foreach ( array( 'hero_selector', 'video_selector' ) as $k ) {
			$out[ $k ] = isset( $in[ $k ] ) ? sanitize_text_field( $in[ $k ] ) : $d[ $k ];
		}

		$logo = isset( $in['logo_id'] ) ? (int) $in['logo_id'] : 0;
		if ( $logo ) {
			$err = self::check_logo( $logo );
			if ( $err ) {
				$logo = 0;
				add_settings_error( LMF_Preloader::OPTION, 'bad-logo', $err );
			}
		}
		$out['logo_id'] = $logo;

		return $out;
	}

	/**
	 * The engine animates the logo by reaching named groups inside it. An SVG
	 * without them would render as a still image with nothing moving, so the
	 * check happens at save time where it can be explained, rather than on the
	 * front end where it cannot.
	 */
	private static function check_logo( $id ) {
		$file = get_attached_file( $id );
		if ( ! $file || ! is_readable( $file ) ) {
			return __( 'That file could not be read, so the bundled logo is still in use.', 'lmf-preloader' );
		}
		if ( 'svg' !== strtolower( pathinfo( $file, PATHINFO_EXTENSION ) ) ) {
			return __( 'The logo has to be an SVG — the animation moves its vector groups, so a PNG or JPG cannot work.', 'lmf-preloader' );
		}
		$markup  = (string) file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$missing = array();
		foreach ( array( 'lmf-camera', 'lmf-reel-large', 'lmf-reel-small', 'lmf-wordmark' ) as $id_needed ) {
			if ( false === strpos( $markup, 'id="' . $id_needed . '"' ) ) {
				$missing[] = $id_needed;
			}
		}
		if ( $missing ) {
			return sprintf(
				/* translators: %s: comma separated list of ids */
				__( 'That SVG is missing these group ids, so there would be nothing to animate: %s. Add them to the groups in your vector file and re-upload.', 'lmf-preloader' ),
				esc_html( implode( ', ', $missing ) )
			);
		}
		return '';
	}

	/* ================================================================= */

	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$o       = LMF_Preloader::options();
		$preview = add_query_arg( 'lmf_preview', '1', home_url( '/' ) );
		?>
		<div class="wrap lmf-settings">
			<h1><?php esc_html_e( 'LMF Preloader', 'lmf-preloader' ); ?></h1>
			<p class="description" style="max-width:60em">
				<?php esc_html_e( 'The brand ident that plays before the homepage. It holds until the hero media is genuinely ready, then hands over.', 'lmf-preloader' ); ?>
				<a href="<?php echo esc_url( $preview ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Preview it now', 'lmf-preloader' ); ?></a>
				<?php esc_html_e( '(opens the homepage and replays it, ignoring the once-per-visit rule).', 'lmf-preloader' ); ?>
			</p>

			<form action="options.php" method="post">
				<?php
				settings_fields( self::GROUP );
				$n = LMF_Preloader::OPTION;
				?>

				<h2 class="title"><?php esc_html_e( 'Where and when', 'lmf-preloader' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable', 'lmf-preloader' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[enabled]" value="1" <?php checked( $o['enabled'], 1 ); ?>>
							<?php esc_html_e( 'Show the preloader', 'lmf-preloader' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Pages', 'lmf-preloader' ); ?></th>
						<td>
							<label><input type="radio" name="<?php echo esc_attr( $n ); ?>[scope]" value="front" <?php checked( $o['scope'], 'front' ); ?>>
								<?php esc_html_e( 'Homepage only', 'lmf-preloader' ); ?></label><br>
							<label><input type="radio" name="<?php echo esc_attr( $n ); ?>[scope]" value="site" <?php checked( $o['scope'], 'site' ); ?>>
								<?php esc_html_e( 'Every page', 'lmf-preloader' ); ?></label>
							<p class="description"><?php esc_html_e( 'An ident is usually an arrival moment, not page furniture.', 'lmf-preloader' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Repeat', 'lmf-preloader' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[once_per_session]" value="1" <?php checked( $o['once_per_session'], 1 ); ?>>
							<?php esc_html_e( 'Only once per visit', 'lmf-preloader' ); ?></label>
							<p class="description"><?php esc_html_e( 'With this off it replays on every internal link, which gets old fast.', 'lmf-preloader' ); ?></p></td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Look', 'lmf-preloader' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Staging', 'lmf-preloader' ); ?></th>
						<td>
							<label><input type="radio" name="<?php echo esc_attr( $n ); ?>[staging]" value="room" <?php checked( $o['staging'], 'room' ); ?>>
								<strong><?php esc_html_e( 'Darkened room', 'lmf-preloader' ); ?></strong></label>
							<p class="description" style="margin:2px 0 10px 25px">
								<?php esc_html_e( 'The signed-off version. The lamp strikes, the camera crosses a dim screen, the frame develops up to paper. Puts the mark on near-black for about two seconds.', 'lmf-preloader' ); ?></p>
							<label><input type="radio" name="<?php echo esc_attr( $n ); ?>[staging]" value="light" <?php checked( $o['staging'], 'light' ); ?>>
								<strong><?php esc_html_e( 'Light first', 'lmf-preloader' ); ?></strong></label>
							<p class="description" style="margin:2px 0 0 25px">
								<?php esc_html_e( 'Warm white throughout, the projection a pool of light on paper. Follows Brand Guidelines Ed.02 — no black backgrounds, mark never on a black panel.', 'lmf-preloader' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="lmf-accent"><?php esc_html_e( 'Progress colour', 'lmf-preloader' ); ?></label></th>
						<td><?php self::color_field( $n, 'accent', $o['accent'], 'room' === $o['staging'] ? '#f8be1b' : '#f5b82e' ); ?></td>
					</tr>
					<tr>
						<th scope="row"><label for="lmf-ground"><?php esc_html_e( 'Opening colour', 'lmf-preloader' ); ?></label></th>
						<td><?php self::color_field( $n, 'ground', $o['ground'], 'room' === $o['staging'] ? '#080a0c' : '#f7f5ef' ); ?></td>
					</tr>
					<tr>
						<th scope="row"><label for="lmf-rest"><?php esc_html_e( 'Resting colour', 'lmf-preloader' ); ?></label></th>
						<td><?php self::color_field( $n, 'rest', $o['rest'], 'room' === $o['staging'] ? '#f6f3ec' : '#f7f5ef' ); ?>
							<p class="description"><?php esc_html_e( 'What the screen settles to before it hands over. Match this to your hero so the transition is seamless.', 'lmf-preloader' ); ?></p></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Progress bar', 'lmf-preloader' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[show_bar]" value="1" <?php checked( $o['show_bar'], 1 ); ?>>
							<?php esc_html_e( 'Show the bar under the logo', 'lmf-preloader' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Logo', 'lmf-preloader' ); ?></th>
						<td><?php self::logo_field( $n, (int) $o['logo_id'] ); ?></td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'What it waits for', 'lmf-preloader' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Hold for media', 'lmf-preloader' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[hold]" value="1" <?php checked( $o['hold'], 1 ); ?>>
							<?php esc_html_e( 'Wait until the hero video and images are ready', 'lmf-preloader' ); ?></label>
							<p class="description"><?php esc_html_e( 'With this off it plays for a fixed length and hands over regardless.', 'lmf-preloader' ); ?></p></td>
					</tr>
					<tr>
						<th scope="row"><label for="lmf-maxwait"><?php esc_html_e( 'Give up after', 'lmf-preloader' ); ?></label></th>
						<td><input id="lmf-maxwait" type="number" min="0" max="20000" step="500" class="small-text"
							name="<?php echo esc_attr( $n ); ?>[max_wait]" value="<?php echo esc_attr( $o['max_wait'] ); ?>"> <?php esc_html_e( 'ms', 'lmf-preloader' ); ?>
							<p class="description"><?php esc_html_e( 'A stalled CDN must never mean a permanently blocked page. After this the hero shows its poster and the site carries on.', 'lmf-preloader' ); ?></p></td>
					</tr>
					<tr>
						<th scope="row"><label for="lmf-hero"><?php esc_html_e( 'Hero selector', 'lmf-preloader' ); ?></label></th>
						<td><input id="lmf-hero" type="text" class="regular-text code"
							name="<?php echo esc_attr( $n ); ?>[hero_selector]" value="<?php echo esc_attr( $o['hero_selector'] ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="lmf-video"><?php esc_html_e( 'Video selector', 'lmf-preloader' ); ?></label></th>
						<td><input id="lmf-video" type="text" class="regular-text code" placeholder="#hero-video"
							name="<?php echo esc_attr( $n ); ?>[video_selector]" value="<?php echo esc_attr( $o['video_selector'] ); ?>">
							<p class="description"><?php esc_html_e( 'Leave blank to use the first video inside the hero.', 'lmf-preloader' ); ?></p></td>
					</tr>
					<tr>
						<th scope="row"><label for="lmf-z"><?php esc_html_e( 'Stacking order', 'lmf-preloader' ); ?></label></th>
						<td><input id="lmf-z" type="number" min="1" class="small-text"
							name="<?php echo esc_attr( $n ); ?>[z_index]" value="<?php echo esc_attr( $o['z_index'] ); ?>">
							<p class="description"><?php esc_html_e( 'Raise this only if something in your theme sits over the preloader.', 'lmf-preloader' ); ?></p></td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	private static function color_field( $name, $key, $value, $fallback ) {
		printf(
			'<input type="color" id="lmf-%1$s" value="%2$s" data-target="lmf-%1$s-text" class="lmf-color">
			 <input type="text" id="lmf-%1$s-text" name="%3$s[%1$s]" value="%4$s" placeholder="%5$s" class="code" size="9">
			 <button type="button" class="button-link lmf-clear" data-color="lmf-%1$s" data-text="lmf-%1$s-text">%6$s</button>',
			esc_attr( $key ),
			esc_attr( $value ? $value : $fallback ),
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( $fallback ),
			esc_html__( 'use default', 'lmf-preloader' )
		);
	}

	private static function logo_field( $name, $id ) {
		$url = $id ? wp_get_attachment_url( $id ) : '';
		?>
		<input type="hidden" id="lmf-logo-id" name="<?php echo esc_attr( $name ); ?>[logo_id]" value="<?php echo esc_attr( $id ); ?>">
		<button type="button" class="button" id="lmf-logo-pick"><?php esc_html_e( 'Choose SVG…', 'lmf-preloader' ); ?></button>
		<button type="button" class="button-link" id="lmf-logo-reset"><?php esc_html_e( 'use bundled logo', 'lmf-preloader' ); ?></button>
		<p class="description" id="lmf-logo-name">
			<?php
			echo $url
				? esc_html( basename( wp_parse_url( $url, PHP_URL_PATH ) ) )
				: esc_html__( 'Using the bundled artwork.', 'lmf-preloader' );
			?>
		</p>
		<p class="description">
			<?php esc_html_e( 'A replacement must carry the group ids lmf-camera, lmf-reel-large, lmf-reel-small and lmf-wordmark — that is what the animation moves. It is checked when you save.', 'lmf-preloader' ); ?>
		</p>
		<script>
		jQuery(function($){
			$('.lmf-color').on('input change', function(){ $('#'+$(this).data('target')).val(this.value); });
			$('.lmf-clear').on('click', function(){ $('#'+$(this).data('text')).val(''); });
			var frame;
			$('#lmf-logo-pick').on('click', function(e){
				e.preventDefault();
				if (frame) { frame.open(); return; }
				frame = wp.media({
					title: '<?php echo esc_js( __( 'Choose the logo SVG', 'lmf-preloader' ) ); ?>',
					library: { type: 'image/svg+xml' },
					button: { text: '<?php echo esc_js( __( 'Use this file', 'lmf-preloader' ) ); ?>' },
					multiple: false
				});
				frame.on('select', function(){
					var a = frame.state().get('selection').first().toJSON();
					$('#lmf-logo-id').val(a.id);
					$('#lmf-logo-name').text(a.filename || a.url);
				});
				frame.open();
			});
			$('#lmf-logo-reset').on('click', function(e){
				e.preventDefault();
				$('#lmf-logo-id').val(0);
				$('#lmf-logo-name').text('<?php echo esc_js( __( 'Using the bundled artwork.', 'lmf-preloader' ) ); ?>');
			});
		});
		</script>
		<?php
	}
}

} // class_exists guard
