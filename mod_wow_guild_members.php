<?php
/**
 * WoW Guild Members
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  (c) 2012 Branko Wilhelm
 * @package    mod_wow_guild_members
 * @license    GNU Public License <http://www.gnu.org/licenses/gpl.html>
 * @version    $Id$
 */
// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';

$members = mod_wow_guild_members::onload($params, $module);

if(!is_array($members)) {
    echo $members;
    return;
}

require JModuleHelper::getLayoutPath('mod_wow_guild_members', $params->get('layout', 'default'));
