<?php
/**
 * THE STUDIO — "why us", third section of the homepage.
 *
 * Three parts, top to bottom:
 *   1. the headline — pulls into focus word by word as it scrolls in;
 *   2. four reasons — a typeset index; the line in the middle of the screen
 *      is lit, the rest wait at a quarter strength; on desktop a small film
 *      frame with a still follows the cursor over the line;
 *   3. four figures — they roll into place like a camera's frame counter.
 *
 * Content lives in lmf_studio_content() below and can be replaced whole
 * through the 'lmf_studio_content' filter.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Section content. Reasons and figures are PLACEHOLDERS drafted from the
 * strategy document — every claim and number must be confirmed by the
 * client before launch.
 *
 * @return array
 */
function lmf_studio_content() {
	$img = get_template_directory_uri() . '/assets/img/reel/';

	$c = array(
		'kicker'   => __( 'The studio', 'lemonmint' ),
		'headline' => lmf_opt( 'studio_line', 'We make films for people who have something specific to say, and forty seconds to say it.' ),
		'reasons'  => array(
			array(
				'title' => 'Streaming pedigree',
				'text'  => 'Netflix-original and broadcast credits on the leadership team.',
				'image' => $img . '07.jpg',
			),
			array(
				'title' => 'One roof',
				'text'  => 'Shot, edited, graded and mixed in-house. No vendor chain.',
				'image' => $img . '04.jpg',
			),
			array(
				'title' => 'GCC, handled',
				'text'  => 'Permits, locations, drones and government approvals.',
				'image' => $img . '01.jpg',
			),
			array(
				'title' => 'Written before it’s shot',
				'text'  => 'You see the film as a treatment before a camera is booked.',
				'image' => $img . '06.jpg',
			),
		),
		'figures'  => array(
			array( 'value' => '120', 'suffix' => '+', 'label' => 'Films delivered' ),
			array( 'value' => '09', 'suffix' => '', 'label' => 'Services, one team' ),
			array( 'value' => '2023', 'suffix' => '', 'label' => 'Founded in Dubai' ),
			array( 'value' => '06', 'suffix' => '', 'label' => 'GCC countries filmed' ),
		),
	);

	return apply_filters( 'lmf_studio_content', $c );
}

/**
 * Markup.
 */
function lmf_studio_section() {
	$c = lmf_studio_content();
	$words = preg_split( '/\s+/', trim( (string) $c['headline'] ) );
	?>
	<section class="studio" data-lmf-studio aria-labelledby="studio-title">
		<div class="wrap">
			<?php lmf_kicker( $c['kicker'] ); ?>

			<h2 class="studio-title" id="studio-title">
				<?php
				foreach ( $words as $i => $w ) {
					echo ( $i ? ' ' : '' ) . '<span class="sw">' . esc_html( $w ) . '</span>';
				}
				?>
			</h2>

			<ol class="studio-list">
				<?php foreach ( $c['reasons'] as $i => $r ) : ?>
					<li class="studio-row" data-img="<?php echo esc_url( $r['image'] ); ?>">
						<span class="meta studio-n"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
						<h3 class="studio-name"><?php echo esc_html( $r['title'] ); ?></h3>
						<p class="studio-text"><?php echo esc_html( $r['text'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>

			<dl class="studio-figures">
				<?php foreach ( $c['figures'] as $f ) : ?>
					<div class="studio-fig">
						<dt class="meta"><?php echo esc_html( $f['label'] ); ?></dt>
						<dd class="studio-num" data-value="<?php echo esc_attr( $f['value'] ); ?>">
							<span class="studio-digits"><?php echo esc_html( $f['value'] ); ?></span><?php if ( $f['suffix'] ) : ?><span class="studio-suffix"><?php echo esc_html( $f['suffix'] ); ?></span><?php endif; ?>
						</dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</div>

		<!-- the frame that follows the cursor over the list (desktop only) -->
		<figure class="studio-loupe" aria-hidden="true"><img alt="" src="" decoding="async"></figure>
	</section>
	<?php
}
