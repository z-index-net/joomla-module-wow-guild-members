<?php

/**
 * WoW Guild Members
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  (c) 2012 - 2013 Branko Wilhelm
 * @package    mod_wow_guild_members
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version    $Id$
 */

defined('_JEXEC') or die;

abstract class mod_wow_guild_members {

    public static function _(JRegistry &$params, stdClass &$module) {
        $url = 'http://' . $params->get('region') . '.battle.net/api/wow/guild/' . $params->get('realm') . '/' . $params->get('guild') . '?fields=members';

        $cache = JFactory::getCache(__CLASS__, 'output');
    	$cache->setCaching(1);
    	$cache->setLifeTime($params->get('cache_time', 60));
    	
    	$key = md5($url);
    	domix($url);
    	if(!$result = $cache->get($key)) {
    		try {
    			$http = new JHttp(new JRegistry, new JHttpTransportCurl(new JRegistry));
    			$http->setOption('userAgent', 'Joomla! ' . JVERSION . '; WoW Guild Members Module; php/' . phpversion());
    		
    			$result = $http->get($url, null, $params->get('timeout', 10));
    		}catch(Exception $e) {
    			return $e->getMessage();
    		}
    		
    		$cache->store($result, $key);
    	}
    	
    	if($result->code != 200) {
    		return __CLASS__ . ' HTTP-Status ' . JHtml::_('link', 'http://wikipedia.org/wiki/List_of_HTTP_status_codes#'.$result->code, $result->code, array('target' => '_blank'));
    	}
        
        $result->body = json_decode($result->body, true); // must be an array!
        
        foreach($result->body['members'] as $key => $member) {
        	$member['character']['rank'] = $member['rank'];
        	$member['character']['race'] = self::getRace($member['character']['race'], $member['character']['gender']);
        	$member['character']['class'] = self::getClass($member['character']['class']);
        	unset($member['character']['thumbnail'], $member['character']['achievementPoints'], $member['character']['realm']); // is not required
        	$result->body['members'][$key] = $member['character'];
        }

        $img_path = JUri::root() . 'modules/' . $module->module . '/tmpl/images/';

        self::sort($result->body['members'], $params);
        
		$ranks = $params->get('ranks', array());
        
        foreach($result->body['members'] as $key => &$member) {
            if(empty($ranks) || in_array($member['rank'], $ranks)) {
            	$member['link'] = self::link($member['name'], $params);
                $member['race'] = JHtml::_('link', $member['link'], JHtml::_('image', $img_path . $member['race'], $member['race']), array('target' => '_blank', 'class' => 'race'));
                $member['class'] = JHtml::_('link', $member['link'], JHtml::_('image', $img_path . $member['class'], $member['class']), array('target' => '_blank', 'class' => 'class'));
                $member['name'] = JHtml::_('link', $member['link'], $member['name'], array('target' => '_blank', 'class' => 'name'));
                $member['rank'] = $params->get('rank_' . $member['rank'], 'Rank ' . $member['rank']);
                continue;
            }
            unset($result->body['members'][$key]);
        }
        
        return !empty($result->body['members']) ? $result->body['members'] : JText::_('MOD_WOW_GUILD_MEMBERS_NOTHING_FOUND');
   }
    
   private static function sort(&$members, &$params) {
        $col = $params->get('order', 'name');
        $sort = ($params->get('sort', 'ASC') == 'ASC') ? SORT_ASC : SORT_DESC;
        
        $sort_col = array();
        foreach ($members as $key => $row) {
            $sort_col[$key] = $row[$col];
        }
        array_multisort($sort_col, $sort, $members);
    }

    private static function getRace($race, $gender) {
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

    private static function getClass($class) {
        $cl = array(0 => null, 'Warrior.gif', 'Paladin.gif', 'Hunter.gif', 'Rogue.gif', 'Priest.gif', 'Deathknight.gif', 'Shaman.gif', 'Mage.gif', 'Warlock.gif', 'Monk.gif', 'Druid.gif');

        return isset($cl[$class]) ? $cl[$class] : 'unknow.gif';
    }

    private static function link($member, JRegistry &$params) {
    	$sites['battle.net'] = 'http://' . $params->get('region') . '.battle.net/wow/' . $params->get('lang') . '/character/' . $params->get('realm') . '/' . $member . '/';
    	$sites['wowhead.com'] = 'http://' . $params->get('lang') . '.wowhead.com/profile=' . $params->get('region') . '.' . $params->get('realm'). '.' . $member;
    	return $sites[$params->get('link')];
    }
}