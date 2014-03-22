<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 - 2014 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

JFactory::getDocument()->addStyleSheet(JUri::base(true) . '/modules/' . $module->module . '/tmpl/default.css');
?>
<?php if ($params->get('ajax')) : ?>
    <div class="mod_wow_guild_members ajax"></div>
<?php else: ?>
    <table class="mod_wow_guild_members">
        <?php if ($params->get('display_thead')) { ?>
            <thead>
            <tr>
                <?php if ($params->get('display_index')) { ?>
                    <th class="index">#</th>
                <?php } ?>
                <th class="name"><?php echo JText::_('MOD_WOW_GUILD_MEMBERS_NAME'); ?></th>
                <?php if ($params->get('display_ranks')) { ?>
                    <th class="rank"><?php echo JText::_('MOD_WOW_GUILD_MEMBERS_RANK'); ?></th>
                <?php } ?>
                <?php if ($params->get('display_level')) { ?>
                    <th class="level"><?php echo JText::_('MOD_WOW_GUILD_MEMBERS_LEVEL'); ?></th>
                <?php } ?>
                <?php if ($params->get('display_points')) { ?>
                    <th class="level"><?php echo JText::_('MOD_WOW_GUILD_MEMBERS_POINTS'); ?></th>
                <?php } ?>
            </tr>
            </thead>
        <?php } ?>
        <tbody>
        <?php foreach ($members as $member) : ?>
            <tr>
                <?php if ($params->get('display_index')) { ?>
                    <td class="index idx_<?php echo $member['index']; ?>"><?php echo $member['index']; ?>.</td>
                <?php } ?>
                <td class="name">
                    <?php if ($params->get('display_race')) echo $member['race']; ?>
                    <?php if ($params->get('display_class')) echo $member['class']; ?>
                    <?php echo $member['name']; ?>
                </td>
                <?php if ($params->get('display_ranks')) { ?>
                    <td class="rank"><?php echo $member['rank']; ?></td>
                <?php } ?>
                <?php if ($params->get('display_level')) { ?>
                    <td class="level"><?php echo $member['level']; ?></td>
                <?php } ?>
                <?php if ($params->get('display_points')) { ?>
                    <td class="points"><?php echo $member['achievementPoints']; ?></td>
                <?php } ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>