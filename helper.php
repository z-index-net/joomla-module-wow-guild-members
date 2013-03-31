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

// no direct access
defined('_JEXEC') or die;

jimport('joomla.cache.cache');

class mod_wow_guild_members {

    public static function onload(&$params, &$module) {

        // all required paramters set?
        if (!$params->get('region') || !$params->get('realm') || !$params->get('guild')) {
            return 'please configure Module' . ' - ' . __CLASS__;
        }

        // if curl installed?
        if (!function_exists('curl_init')) {
            return 'php-curl extension not found';
        }

        // if json_decode availble?
        if (!function_exists('json_decode')) {
            return 'function "json_decode" not found';
        }

        $realm = rawurlencode(strtolower($params->get('realm')));
        $guild = rawurlencode(strtolower($params->get('guild')));
        $region = strtolower($params->get('region'));
        $url = 'http://' . $region . '.battle.net/api/wow/guild/' . $realm . '/' . $guild . '?fields=members';

        $cache = JFactory::getCache(); // get cache obj
        $cache->setCaching(1); // enable cache for this module
        $cache->setLifeTime($params->get('cache_time', 15)); // time to cache

        $result = $cache->call(array(__CLASS__, 'curl'), $url, $params->get('timeout', 10)); // Joomla has nice functions ;)
        
        $cache->setCaching(JFactory::getConfig()->get('caching')); // restore default cache mode

        // Error handling
        if(!is_array($result['body']) || isset($result['body']['reason'])) {
            $err[] = '<strong>' . __CLASS__ . ': no guid data (json) found</strong>';
            if($result['errno'] != 0) {
                $err[] = 'Error: ' . $result['error'] . ' (' . $result['errno'] . ')';
            }
            $err[] = 'battle.net URL: ' . JHTML::link($url, $guild);
            $err[] = 'HTTP Code: ' . $result['info']['http_code'];
            $err[] = 'JSON Error Code: ' . json_last_error();
            if(isset($result['body']['reason'])) {
                $err[] = 'battle.net Error: ' . $result['body']['reason'];
            }
            return implode('<br/>', $err);
        }
        
        $img_path = JURI::root() . 'modules/' . $module->module . '/tmpl/images/';
        $armory_path = 'http://' . $region . '.battle.net/wow/character/' . $realm . '/';
        
        self::_sort($result['body']['members'], $params);
        
        $ranks = $params->get('ranks', array());
        foreach($result['body']['members'] as $key => &$member) {
            if(empty($ranks) || in_array($member['rank'], $ranks)) {
                $member['race'] = JHtml::_('link', $armory_path . $member['name'] . '/', JHtml::_('image', $img_path . $member['race'], $member['race']), array('target' => '_blank', 'class' => 'race'));
                $member['class'] = JHtml::_('link', $armory_path . $member['name'] . '/', JHtml::_('image', $img_path . $member['class'], $member['class']), array('target' => '_blank', 'class' => 'class'));
                $member['name'] = JHtml::_('link', $armory_path . $member['name'] . '/', $member['name'], array('target' => '_blank'));
                $member['rank'] = $params->get('rank_' . $member['rank'], 'Rank ' . $member['rank']);
                continue;
            }
            unset($result['body']['members'][$key]);
        }
        
        return !empty($result['body']['members']) ? $result['body']['members'] : 'no members in configured ranks found?!';
    }
    
   private static function _sort(&$members, &$params) {
        $col = $params->get('order', 'name');
        $sort = ($params->get('sort', 'ASC') == 'ASC') ? SORT_ASC : SORT_DESC;
        
        $sort_col = array();
        foreach ($members as $key => $row) {
            $sort_col[$key] = $row[$col];
        }
        array_multisort($sort_col, $sort, $members);
    }

    private static function _race($race, $gender) {
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
        $rc[26] = array('Pandaren_Male.gif', 'Pandaren_Female.gif');

        return isset($rc[$race][$gender]) ? $rc[$race][$gender] : 'unknow.gif';
    }

    private static function _class($class) {
        $cl = array(0 => null, 'Warrior.gif', 'Paladin.gif', 'Hunter.gif', 'Rogue.gif', 'Priest.gif', 'Deathknight.gif', 'Shaman.gif', 'Mage.gif', 'Warlock.gif', 'Monk.gif', 'Druid.gif');

        return isset($cl[$class]) ? $cl[$class] : 'unknow.gif';
    }

    public static function curl($url, $timeout=10) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Joomla! ' . JVERSION . '; WoW Guild Members Module; php/' . phpversion());
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Connection: Close'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);

        $body = curl_exec($curl);
        $info = curl_getinfo($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);

        curl_close($curl);
        
        $body = json_decode($body, true);
        
        // prepare and cleanup members array 
        if(is_array($body) && isset($body['members'])) {
            foreach($body['members'] as $key => $member) {
                $member['character']['rank'] = $member['rank'];
                $member['character']['race'] = self::_race($member['character']['race'], $member['character']['gender']);
                $member['character']['class'] = self::_class($member['character']['class']);
                unset($member['character']['thumbnail'], $member['character']['achievementPoints'], $member['character']['realm']); // is not required
                $body['members'][$key] = $member['character'];
            }
        }

        return array('info' => $info, 'errno' => $errno, 'error' => $error, 'body' => $body);
    }
}