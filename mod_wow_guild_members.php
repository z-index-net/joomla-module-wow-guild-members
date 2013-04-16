<?php

/**
 * WoW Guild Members
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  (c) 2012 - 2013 Branko Wilhelm
 * @package    mod_wow_guild_members
 * @license    GNU General Public License v3
 * @version    $Id$
 */

defined('_JEXEC') or die;

require_once dirname(__FILE__) . '/helper.php';

$params->set('guild', rawurlencode(strtolower($params->get('guild'))));
$params->set('realm', rawurlencode(strtolower($params->get('realm'))));
$params->set('region', strtolower($params->get('region')));
$params->set('lang', strtolower($params->get('lang', 'en')));
$params->set('link', $params->get('link', 'battle.net'));

$members = mod_wow_guild_members::_($params, $module);

if(!is_array($members)) {
    echo $members;
    return;
}

require JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));