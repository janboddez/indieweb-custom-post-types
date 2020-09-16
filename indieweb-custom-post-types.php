<?php
/**
 * Plugin Name:       IndieWeb Custom Post Types
 * Description:       Easily "IndieWebify" your WordPress site.
 * GitHub Plugin URI: https://github.com/janboddez/indieweb-custom-post-types
 * Author:            Jan Boddez
 * Author URI:        https://janboddez.tech/
 * License:           GNU General Public License v3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       iwcpt
 * Version:           0.1.0
 *
 * @author  Jan Boddez <jan@janboddez.be>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @package IWCPT
 */

namespace IWCPT;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require dirname( __FILE__ ) . '/includes/class-iwcpt.php';

$iwcpt = IWCPT::get_instance();
$iwcpt->register();
