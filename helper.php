<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 - 2014 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

final class ModWowGuildMembersHelper
{
    private $params = null;

    private function __construct(JRegistry &$params)
    {
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
        $params->set('order', $params->get('order'));

        $this->params = & $params;
    }

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

    public static function getData(JRegistry &$params)
    {
        if ($params->get('ajax')) {
            return;
        }

        $instance = new self($params);

        return $instance->getMembers();
    }

    private function getMembers()
    {
        if ($this->params->get('ajax')) {
            return;
        }

        if (!$this->params->get('guild') || !$this->params->get('realm')) {
            return JString::_('MOD_WOW_GUILD_MEMBERS_CONFIGURATION_MISSING');
        }

        $url = 'http://' . $this->params->get('region') . '.battle.net/api/wow/guild/' . $this->params->get('realm') . '/' . $this->params->get('guild') . '?fields=members,achievements&locale=' . $this->params->get('lang');

        $cache = JFactory::getCache('wow', 'output');
        $cache->setCaching(1);
        $cache->setLifeTime($this->params->get('cache_time', 60) * 60);

        $result = $this->remoteContent($url);

        if (is_string($result)) {
            return $result;
        }

        $result->classes = $this->getClasses();
        $result->races = $this->getRaces();

        $ranks = $this->params->get('ranks', array());

        foreach ($result->members as $key => $row) {
            if (($this->params->get('level_min') && $row->character->level < $this->params->get('level_min')) || ($this->params->get('level_max') && $row->character->level > $this->params->get('level_max')) || (!empty($ranks) && !in_array($row->rank, $ranks))) {
                unset($result->members[$key]);
                continue;
            }

            $member = new stdClass;

            $member->name = new stdClass;
            $member->name->title = $row->character->name;
            $member->name->avatar = $row->character->thumbnail;
            $member->name->link = $this->link($row->character->name);

            $member->level = new stdClass;
            $member->level->title = $row->character->level;

            $member->achievementPoints = new stdClass;
            $member->achievementPoints->title = $row->character->achievementPoints;

            $member->rank = new stdClass;
            $member->rank->id = $row->rank;
            $member->rank->title = $this->params->get('rank_' . $row->rank, JText::_('MOD_WOW_GUILD_MEMBERS_RANK_' . $row->rank . '_LABEL'));

            $member->race = new stdClass;
            $member->race->id = $result->races[$row->character->race]->id;
            $member->race->title = $result->races[$row->character->race]->name;
            $member->race->icon = 'race_' . $member->race->id . '_' . $row->character->gender . '.jpg';

            $member->class = new stdClass;
            $member->class->id = $result->classes[$row->character->class]->id;
            $member->class->title = $result->classes[$row->character->class]->name;
            $member->class->icon = 'class_' . $result->classes[$row->character->class]->id . '.jpg';

            $member->role = new stdClass;
            $member->role->title = JText::_('MOD_WOW_GUILD_MEMBERS_' . $row->character->spec->role);
            $member->role->spec = $row->character->spec->name;
            $member->role->icon = $row->character->spec->icon . '.jpg';

            $member->gender = new stdClass;
            $member->gender->id = $row->character->gender;
            $member->gender->title = JText::_('MOD_WOW_GUILD_MEMBERS_GENDER_' . $row->character->gender);

            $result->members[$key] = $member;
        }

        if ($this->params->get('table_break')) {
            $this->params->set('order', $this->params->get('table_break'));
        }

        usort($result->members, array($this, 'sort'));

        if (empty($result->members)) {
            return JText::_('MOD_WOW_GUILD_MEMBERS_NOTHING_FOUND');
        }

        if ($this->params->get('display_index')) {
            $this->addIndex($result->members);
        }

        return array_slice($result->members, 0, $this->params->get('rows') ? $this->params->get('rows') : count($result->members));
    }

    private function sort($a, $b)
    {
        if ($this->params->get('order') == 'rank') {
            $field = 'id';
        } else {
            $field = 'title';
        }

        if ($this->params->get('sort', 'ASC') == 'ASC') {
            return strcmp($a->{$this->params->get('order')}->{$field}, $b->{$this->params->get('order')}->{$field});
        } else {
            return strcmp($b->{$this->params->get('order')}->{$field}, $a->{$this->params->get('order')}->{$field});
        }
    }

    private function getRaces()
    {
        $result = self::remoteContent('http://' . $this->params->get('region') . '.battle.net/api/wow/data/character/races?locale=' . $this->params->get('lang'), true);

        $tmp = array();
        foreach ($result->races as $race) {
            $tmp[$race->id] = $race;
        }

        unset($result);

        return $tmp;
    }

    private function getClasses()
    {
        $result = self::remoteContent('http://' . $this->params->get('region') . '.battle.net/api/wow/data/character/classes?locale=' . $this->params->get('lang'), true);

        $tmp = array();
        foreach ($result->classes as $class) {
            $tmp[$class->id] = $class;
        }

        unset($result);

        return $tmp;
    }

    private function link($member)
    {
        $sites['battle.net'] = 'http://' . $this->params->get('region') . '.battle.net/wow/' . $this->params->get('lang') . '/character/' . $this->params->get('realm') . '/' . $member . '/';
        $sites['wowhead.com'] = 'http://' . $this->params->get('lang') . '.wowhead.com/profile=' . $this->params->get('region') . '.' . $this->params->get('realm') . '.' . $member;
        return $sites[$this->params->get('link')];
    }

    private function addIndex(array &$members)
    {
        $index = ($this->params->get('display_index') == 2) ? count($members) : 1;

        foreach ($members as &$member) {
            $member->index = ($this->params->get('display_index') == 2) ? $index-- : $index++;
        }
    }

    private function remoteContent($url, $persistent = false)
    {
        $cache = JFactory::getCache('wow', 'output');
        $cache->setCaching(1);
        $cache->setLifeTime($this->params->get('cache_time', 60) * ($persistent ? 172800 : 60));

        $key = md5($url);

        if (!$result = $cache->get($key)) {
            try {
                $http = JHttpFactory::getHttp();
                $http->setOption('userAgent', 'Joomla! ' . JVERSION . '; WoW Guild Members; php/' . phpversion());
                $result = $http->get($url, null, $this->params->get('timeout', 10));
            } catch (Exception $e) {
                return $e->getMessage();
            }

            $cache->store($result, $key);
        }

        if ($result->code != 200) {
            return __CLASS__ . ' HTTP-Status ' . JHtml::_('link', 'http://wikipedia.org/wiki/List_of_HTTP_status_codes#' . $result->code, $result->code, array('target' => '_blank'));
        }

        return json_decode($result->body);
    }
}