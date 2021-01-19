<?php
/**
* Eval Expression
*
* @package           Eval Expression
* @author            Steven A. Zahm
* @copyright         2021 Steven A. Zahm
* @license           GPL-2.0-or-later
*
* @wordpress-plugin
* Plugin Name:       Eval Expression Library
* Plugin URI:        https://connections-pro.com
* Description:       The Eval Expression Library. Can be utilized in the Code Snippet plugin.
* Version:           1.0.0
* Requires at least: 5.6
* Requires PHP:      7.2
* Author:            Steven A. Zahm
* Author URI:        https://connections-pro.com/
* Text Domain:       n/a
* License:           GPL v2 or later
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
*/

namespace Easy_Plugins;

use Easy_Plugins\Evaluate\Expression;
use ReflectionException;

final class Calculate {

	const VERSION = '1.0';

	/**
	 * @var Calculate Stores the instance of this class.
	 *
	 * @since 1.0
	 */
	private static $instance;

	/**
	 * @var string The absolute path this this file.
	 *
	 * @since 1.0
	 */
	private $file = '';

	/**
	 * @var string The URL to the plugin's folder.
	 *
	 * @since 1.0
	 */
	private $url = '';

	/**
	 * @var string The absolute path to this plugin's folder.
	 *
	 * @since 1.0
	 */
	private $path = '';

	/**
	 * @var string The basename of the plugin.
	 *
	 * @since 1.0
	 */
	private $basename = '';

	/**
	 * A dummy constructor to prevent the class from being loaded more than once.
	 *
	 * @since 1.0
	 */
	protected function __construct() { /* Do nothing here */ }

	/**
	 * The main plugin instance.
	 *
	 * @since 1.0
	 *
	 * @return self
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = $self = new self;

			$self->file     = __FILE__;
			$self->url      = plugin_dir_url( $self->file );
			$self->path     = plugin_dir_path( $self->file );
			$self->basename = plugin_basename( $self->file );

			$self->includeDependencies();
			$self->hooks();
		}

		return self::$instance;

	}

	/**
	 * @since 1.0
	 */
	private function hooks() {

		add_shortcode( 'eval_expression', array( __CLASS__, 'shortcode' ) );
	}

	/**
	 * @since 1.0
	 */
	private function includeDependencies() {

		require_once 'src/Evaluate/Expression.php';
		require_once 'src/Evaluate/Stack.php';
	}

	/**
	 * @since 1.0
	 *
	 * @param array  $untrusted
	 * @param string $content
	 * @param string $tag
	 *
	 * @throws ReflectionException
	 * @return string
	 */
	public static function shortcode( $untrusted, $content, $tag ) {

		$defaults = array(
			'precision' => 2,
		);

		$atts = shortcode_atts( $defaults, $untrusted, $tag );

		$content    = str_replace( '&#8211;', '-', $content );
		$content    = do_shortcode( $content );
		$expression = strip_tags( $content );

		$math   = new Expression();
		$result = $math->evaluate( $expression );

		if ( is_numeric( $result ) ) {

			$result = round( $result, absint( $atts['precision'] ) );

		} elseif ( is_bool( $result ) ) {

			$result = true === $result ? 'true' : 'false';
		}

		return (string) $result;
	}
}

/**
 * @since 1.0
 *
 * @return Calculate
 */
function Calculate() {

	return Calculate::instance();
}

Calculate();
