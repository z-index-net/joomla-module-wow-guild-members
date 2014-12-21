<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2012 - 2015 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class ModWowGuildMembersHelper extends WoWModuleAbstract
{
    protected function getInternalData()
    {
        try {
            $result = WoW::getInstance()->getAdapter('WoWAPI')->getData('members');
        } catch (Exception $e) {
            return $e->getMessage();
        }

        $result->body->classes = $this->getClasses();
        $result->body->races = $this->getRaces();

        $ranks = $this->params->module->get('ranks', array());

        foreach ($result->body->members as $key => $row) {
            if (($this->params->module->get('level_min') && $row->character->level < $this->params->module->get('level_min')) || ($this->params->module->get('level_max') && $row->character->level > $this->params->module->get('level_max')) || (!empty($ranks) && !in_array($row->rank, $ranks))) {
                unset($result->members[$key]);
                continue;
            }

            $member = new stdClass;

            $member->name = new stdClass;
            $member->name->title = $row->character->name;
            $member->name->realm = $row->character->realm;
            $member->name->avatar = $row->character->thumbnail;
            $member->name->link = $this->link($row->character->name, $row->character->realm);

            $member->level = new stdClass;
            $member->level->title = $row->character->level;

            $member->achievementPoints = new stdClass;
            $member->achievementPoints->title = $row->character->achievementPoints;

            $member->rank = new stdClass;
            $member->rank->id = $row->rank;
            $member->rank->title = $this->params->module->get('rank_' . $row->rank, JText::_('MOD_WOW_GUILD_MEMBERS_RANK_' . $row->rank . '_LABEL'));

            $member->race = new stdClass;
            $member->race->id = $result->body->races[$row->character->race]->id;
            $member->race->title = $result->body->races[$row->character->race]->name;
            $member->race->icon = 'race_' . $member->race->id . '_' . $row->character->gender . '.jpg';

            $member->class = new stdClass;
            $member->class->id = $result->body->classes[$row->character->class]->id;
            $member->class->title = $result->body->classes[$row->character->class]->name;
            $member->class->icon = 'class_' . $result->body->classes[$row->character->class]->id . '.jpg';

            $member->role = new stdClass;
            $member->role->title = JText::_('MOD_WOW_GUILD_MEMBERS_' . ($row->character->spec->role ? $row->character->spec->role : 'UNKNOWN'));
            $member->role->spec = ($row->character->spec->role ? $row->character->spec->name : $member->role->title);
            $member->role->icon = ($row->character->spec->role ? $row->character->spec->icon : 'inv_misc_questionmark') . '.jpg';

            $member->gender = new stdClass;
            $member->gender->id = $row->character->gender;
            $member->gender->title = JText::_('MOD_WOW_GUILD_MEMBERS_GENDER_' . $row->character->gender);

            $result->members[$key] = $member;
        }

        if ($this->params->module->get('table_break')) {
            $this->params->module->set('order', $this->params->module->get('table_break'));
        }

        usort($result->members, array($this, 'sort'));

        $result->members = array_slice($result->members, 0, $this->params->module->get('rows') ? $this->params->module->get('rows') : count($result->members));

        if (empty($result->members)) {
            return JText::_('MOD_WOW_GUILD_MEMBERS_NOTHING_FOUND');
        }

        if ($this->params->module->get('display_index')) {
            $this->addIndex($result->members);
        }

        /*
        if ($this->params->module->get('display_itemlvl') && $this->params->module->get('rows')) {
            $this->addItemLvl($result->members);
        } else {
            $this->params->module->set('display_itemlvl', 0);
        }
        */

        return $result->members;
    }

    private function sort($a, $b)
    {
        if ($this->params->module->get('order') == 'rank') {
            $field = 'id';
        } else {
            $field = 'title';
        }

        if ($this->params->module->get('sort', 'ASC') == 'ASC') {
            return ($a->{$this->params->module->get('order')}->{$field} > $b->{$this->params->module->get('order')}->{$field});
        } else {
            return ($a->{$this->params->module->get('order')}->{$field} < $b->{$this->params->module->get('order')}->{$field});
        }
    }

    private function getRaces($races = array())
    {
        try {
            $result = WoW::getInstance()->getAdapter('WoWAPI')->getData('races', true);
        } catch (Exception $e) {
            return $races;
        }

        foreach ($result->body->races as $race) {
            $races[$race->id] = $race;
        }

        return $races;
    }

    private function getClasses($classes = array())
    {
        try {
            $result = WoW::getInstance()->getAdapter('WoWAPI')->getData('classes', true);
        } catch (Exception $e) {
            return $classes;
        }

        foreach ($result->body->classes as $class) {
            $classes[$class->id] = $class;
        }

        return $classes;
    }

    private function link($member, $realm)
    {
        $sites['battle.net'] = 'http://' . $this->params->global->get('region') . '.battle.net/wow/' . $this->params->global->get('locale') . '/character/' . $realm . '/' . $member . '/';
        $sites['wowhead.com'] = 'http://' . $this->params->global->get('locale') . '.wowhead.com/profile=' . $this->params->global->get('region') . '.' . $realm . '.' . $member;
        return $sites[$this->params->global->get('link')];
    }

    /*
    private function addItemLvl(array &$members)
    {
        foreach ($members as &$member) {
            $member->itemlvl = new stdClass;
            try {
                $profile = WoW::getInstance()->getAdapter('WoWAPI')->getMember($member->name->title, $member->name->realm);
                if (!isset($profile->body->items)) {
                    throw new Exception();
                }
                $member->itemlvl->title = $profile->body->items->averageItemLevel;
                $member->itemlvl->equipped = $profile->body->items->averageItemLevelEquipped;
            } catch (Exception $e) {
                $member->itemlvl->title = 0;
                $member->itemlvl->equipped = 0;
            }
        }
    }
    */

    private function addIndex(array &$members)
    {
        $index = ($this->params->module->get('display_index') == 2) ? count($members) : 1;

        foreach ($members as &$member) {
            $member->index = ($this->params->module->get('display_index') == 2) ? $index-- : $index++;
        }
    }
}