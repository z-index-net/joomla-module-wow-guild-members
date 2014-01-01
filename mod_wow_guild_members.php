<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

JLoader::register('ModWowGuildMembersHelper', dirname(__FILE__) . '/helper.php');

$members = ModWowGuildMembersHelper::getData($params, $module);

if (!$params->get('ajax') && !is_array($members)) {
    echo $members;
    return;
}

require JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));