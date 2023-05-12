<?php
/**
 * Plugin Name:     Crush Auditor
 * Plugin URI:      https://www.gmbcrush.com/
 * Description:     Find the exact ranking factors that are influencing 3-pack rankings in your industry. Use data to SEO your GMB’s, monitor ranking patterns and create stunning SEO proposals that make your pitches irrefutable.
 * Author:          upnrunn™ technologies
 * Author URI:      https://upnrunn.com/
 * Text Domain:     mini-audit
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Mini_Audit
 */

namespace Upnrunn;

// Exit if accessed directly.
defined('ABSPATH') || exit;

define('GMB_CRUSH_MINI_AUDIT_FILE', __FILE__);

include_once dirname(GMB_CRUSH_MINI_AUDIT_FILE) . '/includes/class-mini-audit.php';

function mini_audit() {
	return Mini_Audit::instance();
}

// Global for backwards compatibility.
$GLOBALS['mini_audit'] = mini_audit();
