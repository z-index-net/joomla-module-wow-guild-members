<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 - 2014 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

abstract class ModWowGuildMembersHelper
{

    public static function getAjax()
    {
        $module = JModuleHelper::getModule('mod_' . JFactory::getApplication()->input->get('module'));

        if (empty($module)) {
            return false;
        }

        JFactory::getLanguage()->load($module->module);

        $params = new JRegistry($module->params);
        $params->set('ajax', 0);

        ob_start();

        require(dirname(__FILE__) . '/' . $module->module . '.php');

        return ob_get_clean();
    }

    public static function getData(JRegistry &$params, stdClass &$module)
    {
        if ($params->get('ajax')) {
            return;
        }

        if (version_compare(JVERSION, 3, '>=')) {
            $params->set('guild', rawurlencode(JString::strtolower($params->get('guild'))));
            $params->set('realm', rawurlencode(JString::strtolower($params->get('realm'))));
        } else {
            $params->set('realm', str_replace(array('%20', ' '), '-', $params->get('realm')));
            $params->set('guild', str_replace(array('%20', ' '), '%2520', $params->get('guild')));
        }

        $params->set('region', JString::strtolower($params->get('region')));
        $params->set('lang', JString::strtolower($params->get('lang', 'en')));
        $params->set('link', $params->get('link', 'battle.net'));

        $url = 'http://' . $params->get('region') . '.battle.net/api/wow/guild/' . $params->get('realm') . '/' . $params->get('guild') . '?fields=members,achievements';

        $cache = JFactory::getCache('wow', 'output');
        $cache->setCaching(1);
        $cache->setLifeTime($params->get('cache_time', 60) * 60);

        $key = md5($url);

        if (!$result = $cache->get($key)) {
            try {
                $http = JHttpFactory::getHttp();
                $http->setOption('userAgent', 'Joomla! ' . JVERSION . '; WoW Guild Members Module; php/' . phpversion());

                $result = $http->get($url, null, $params->get('timeout', 10));
            } catch (Exception $e) {
                return $e->getMessage();
            }

            $cache->store($result, $key);
        }

        if ($result->code != 200) {
            return __CLASS__ . ' HTTP-Status ' . JHtml::_('link', 'http://wikipedia.org/wiki/List_of_HTTP_status_codes#' . $result->code, $result->code, array('target' => '_blank'));
        }

        $result->body = json_decode($result->body, true); // must be an array!

        foreach ($result->body['members'] as $key => $member) {
            $member['character']['rank'] = $member['rank'];
            $member['character']['race'] = self::getRace($member['character']['race'], $member['character']['gender']);
            $member['character']['class'] = self::getClass($member['character']['class']);
            $result->body['members'][$key] = $member['character'];
        }

        $img_path = JUri::root() . 'modules/' . $module->module . '/tmpl/images/';

        self::sort($result->body['members'], $params);

        $ranks = $params->get('ranks', array());

        foreach ($result->body['members'] as $key => &$member) {
            if (($params->get('level_min') && $member['level'] < $params->get('level_min')) || ($params->get('level_max') && $member['level'] > $params->get('level_max'))) {
                unset($result->body['members'][$key]);
            }

            if (empty($ranks) || in_array($member['rank'], $ranks)) {
                $class = $params->get('display_colored', 1) ? basename(strtolower($member['class']), '.gif') : '';
                $member['link'] = self::link($member['name'], $params);
                $member['name'] = JHtml::_('link', $member['link'], $member['name'], array('target' => '_blank', 'class' => 'name ' . $class));
                $member['race'] = JHtml::_('link', $member['link'], JHtml::_('image', $img_path . $member['race'], $member['race']), array('target' => '_blank', 'class' => 'race'));
                $member['class'] = JHtml::_('link', $member['link'], JHtml::_('image', $img_path . $member['class'], $member['class']), array('target' => '_blank', 'class' => 'class'));
                $member['rank'] = $params->get('rank_' . $member['rank'], 'Rank ' . $member['rank']);
                continue;
            }
            unset($result->body['members'][$key]);
        }

        if (empty($result->body['members'])) {
            return JText::_('MOD_WOW_GUILD_MEMBERS_NOTHING_FOUND');
        }

        if ($params->get('display_index')) {
            self::addIndex($result->body['members'], $params);
        }

        return array_slice($result->body['members'], 0, $params->get('rows') ? $params->get('rows') : count($result->body['members']));
    }

    private static function getRace($race, $gender)
    {
        $rc[1] = array('Human_Male.gif', 'Human_Female.gif');
        $rc[2] = array('Orc_Male.gif', 'Orc_Female.gif');
        $rc[3] = array('Dwarf_Male.gif', 'Dwarf_Female.gif');
        $rc[4] = array('NightElf_Male.gif', 'NightElf_Female.gif');
        $rc[5] = array('Undead_Male.gif', 'Undead_Female.gif');
        $rc[6] = array('Tauren_Male.gif', 'Tauren_Female.gif');
        $rc[7] = array('Gnome_Male.gif', 'Gnome_Female.gif');
        $rc[8] = array('Troll_Male.gif', 'Troll_Female.gif');
        $rc[9] = array('Goblin_Male.gif', 'Goblin_Female.gif');
        $rc[10] = array('BloodElf_Male.gif', 'BloodElf_Female.gif');
        $rc[11] = array('Draenei_Male.png', 'Draenei_Female.png');
        $rc[22] = array('Worgen_Male.gif', 'Worgen_Female.gif');
        $rc[25] = array('Pandaren_Male.gif', 'Pandaren_Female.gif');
        $rc[26] = array('Pandaren_Male.gif', 'Pandaren_Female.gif');

        return isset($rc[$race][$gender]) ? $rc[$race][$gender] : 'unknow.gif';
    }

    private static function getClass($class)
    {
        $cl = array(null, 'Warrior.gif', 'Paladin.gif', 'Hunter.gif', 'Rogue.gif', 'Priest.gif', 'Deathknight.gif', 'Shaman.gif', 'Mage.gif', 'Warlock.gif', 'Monk.gif', 'Druid.gif');

        return isset($cl[$class]) ? $cl[$class] : 'unknow.gif';
    }

    private static function sort(array &$members, JRegistry &$params)
    {
        $col = $params->get('order', 'name');
        $sort = ($params->get('sort', 'ASC') == 'ASC') ? SORT_ASC : SORT_DESC;

        $sort_col = array();
        foreach ($members as $key => $row) {
            $sort_col[$key] = $row[$col];

        }

        array_multisort($sort_col, $sort, $members);
    }

    private static function link($member, JRegistry &$params)
    {
        $sites['battle.net'] = 'http://' . $params->get('region') . '.battle.net/wow/' . $params->get('lang') . '/character/' . $params->get('realm') . '/' . $member . '/';
        $sites['wowhead.com'] = 'http://' . $params->get('lang') . '.wowhead.com/profile=' . $params->get('region') . '.' . $params->get('realm') . '.' . $member;
        return $sites[$params->get('link')];
    }

    private static function addIndex(array &$members, JRegistry &$params)
    {
        $index = ($params->get('sort', 'ASC') == 'ASC') ? count($members) : 1;

        foreach ($members as &$member) {
            $member['index'] = ($params->get('sort', 'ASC') == 'ASC') ? $index-- : $index++;
        }
    }
}