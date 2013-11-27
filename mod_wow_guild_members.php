<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

require_once dirname(__FILE__) . '/helper.php';

if (version_compare(JVERSION, 3, '>=')) {
    $params->set('guild', rawurlencode(JString::strtolower($params->get('guild'))));
    $params->set('realm', rawurlencode(JString::strtolower($params->get('realm'))));
} else {
    $params->set('realm', str_replace(array('%20', ' '), '-', $params->get('realm')));
    $params->set('guild', str_replace(array('%20', ' '), '%2520', $params->get('guild')));
}

$params->set('region', JString::strtolower($params->get('region')));
$params->set('language', JString::strtolower($params->get('language', 'en')));
$params->set('link', $params->get('link', 'battle.net'));

$members = mod_wow_guild_members::_($params, $module);

if (!is_array($members)) {
    echo $members;
    return;
}

require JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));