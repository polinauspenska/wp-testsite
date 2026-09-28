<?php
/**
 * Navigation walker.
 *
 * Wraps each top-level item in a div so the Services mega-panel has a hover
 * container, and gives links the .nlink class the stylesheet expects.
 *
 * @package LemonMintFilms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Primary nav walker.
 */
class LMF_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Open a submenu — rendered as the mega panel.
	 *
	 * @param string   $output Output.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<div class="mega">';
	}

	/**
	 * Close a submenu.
	 *
	 * @param string   $output Output.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</div>';
	}

	/**
	 * Render one item.
	 *
	 * @param string   $output Output.
	 * @param WP_Post  $item   Menu item.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 * @param int      $id     ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$current = in_array( 'current-menu-item', (array) $item->classes, true )
			|| in_array( 'current-menu-parent', (array) $item->classes, true )
			|| in_array( 'current-menu-ancestor', (array) $item->classes, true );

		if ( 0 === $depth ) {
			$output .= '<div>';
			$output .= sprintf(
				'<a class="nlink%s" href="%s">%s</a>',
				$current ? ' on' : '',
				esc_url( $item->url ),
				esc_html( $item->title )
			);
		} else {
			$this->sub_index = isset( $this->sub_index ) ? $this->sub_index + 1 : 1;
			$output         .= sprintf(
				'<a href="%s"><span class="n">%02d</span><span class="t">%s</span><span class="d">%s</span></a>',
				esc_url( $item->url ),
				$this->sub_index,
				esc_html( $item->title ),
				esc_html( $item->description )
			);
		}
	}

	/**
	 * Close one item.
	 *
	 * @param string   $output Output.
	 * @param WP_Post  $item   Menu item.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( 0 === $depth ) {
			$output .= '</div>';
		}
	}

	/**
	 * Sub-item counter for the mega panel numbering.
	 *
	 * @var int
	 */
	public $sub_index = 0;
}
