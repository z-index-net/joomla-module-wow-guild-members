<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2012 - 2015 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @var        array $members
 * @var        stdClass $module
 * @var        Joomla\Registry\Registry $params
 */

defined('_JEXEC') or die;

$break = '';

$media_path = 'http://media.blizzard.com/wow/icons/18/';

JFactory::getDocument()->addStyleSheet('media/' . $module->module . '/css/default.css');
?>
<?php if ($params->get('ajax')) : ?>
    <div class="mod_wow_guild_members ajax"></div>
<?php else: ?>
    <table class="mod_wow_guild_members">
        <?php if ($params->get('display_thead')) { ?>
            <tr>
                <?php if ($params->get('display_index')) { ?>
                    <th class="index">#</th>
                <?php } ?>
                <th class="name"><?php echo JText::_('MOD_WOW_GUILD_MEMBERS_NAME'); ?></th>
                <?php if ($params->get('display_race', 1)) { ?>
                    <th class="rank"><?php echo JText::_('MOD_WOW_GUILD_MEMBERS_RACE'); ?></th>
                <?php } ?>
                <?php if ($params->get('display_class', 1)) { ?>
                    <th class="class"><?php echo JText::_('MOD_WOW_GUILD_MEMBERS_CLASS'); ?></th>
                <?php } ?>
                <?php if ($params->get('display_role', 1)) { ?>
                    <th class="role"><?php echo JText::_('MOD_WOW_GUILD_MEMBERS_ROLE'); ?></th>
                <?php } ?>
                <?php if ($params->get('display_ranks', 1)) { ?>
                    <th class="rank"><?php echo JText::_('MOD_WOW_GUILD_MEMBERS_RANK'); ?></th>
                <?php } ?>
                <?php if ($params->get('display_level', 1)) { ?>
                    <th class="level"><?php echo JText::_('MOD_WOW_GUILD_MEMBERS_LEVEL'); ?></th>
                <?php } ?>
                <?php if ($params->get('display_points', 1)) { ?>
                    <th class="points"><?php echo JText::_('MOD_WOW_GUILD_MEMBERS_POINTS'); ?></th>
                <?php } ?>
            </tr>
        <?php } ?>
        <?php foreach ($members as $member) : ?>
            <?php if ($params->get('table_break') && $break != $member->{$params->get('table_break')}->title) { ?>
                <tr class="break">
                    <th class="<?php echo JFilterOutput::stringURLSafe($member->{$params->get('table_break')}->title); ?>" colspan="5">
                        <?php echo $member->{$params->get('table_break')}->title; ?>
                    </th>
                </tr>
                <?php $break = $member->{$params->get('table_break')}->title; ?>
            <?php } ?>
            <tr>
                <?php if ($params->get('display_index')) { ?>
                    <td class="index idx_<?php echo $member->index; ?>">
                        <?php echo $member->index; ?>.
                    </td>
                <?php } ?>
                <td class="name">
                    <?php echo JHtml::_('link', $member->name->link, $member->name->title, array('target' => '_blank', 'class' => 'class_' . $member->class->id)); ?>
                </td>
                <?php if ($params->get('display_race', 1)) { ?>
                    <td class="race">
                        <?php echo JHtml::_('image', $media_path . $member->race->icon, $member->race->title, array('title' => $member->race->title)); ?>
                    </td>
                <?php } ?>
                <?php if ($params->get('display_class', 1)) { ?>
                    <td class="class">
                        <?php echo JHtml::_('image', $media_path . $member->class->icon, $member->class->title, array('title' => $member->class->title)); ?>
                    </td>
                <?php } ?>
                <?php if ($params->get('display_role', 1)) { ?>
                    <td class="role">
                        <?php echo JHtml::_('image', $media_path . $member->role->icon, $member->role->title, array('title' => $member->role->spec)); ?>
                    </td>
                <?php } ?>

                <?php if ($params->get('display_ranks', 1)) { ?>
                    <td class="rank"><?php echo $member->rank->title; ?></td>
                <?php } ?>
                <?php if ($params->get('display_level', 1)) { ?>
                    <td class="level"><?php echo $member->level->title; ?></td>
                <?php } ?>
                <?php if ($params->get('display_points', 1)) { ?>
                    <td class="points"><?php echo $member->achievementPoints->title; ?></td>
                <?php } ?>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>