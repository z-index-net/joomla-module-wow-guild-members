<?php
/**
 * WoW Guild Members
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  (c) 2012 Branko Wilhelm
 * @package    mod_wow_guild_members
 * @license    GNU Public License <http://www.gnu.org/licenses/gpl.html>
 * @version    $Id$
 */
// no direct accesss
defined('_JEXEC') or die;

JFactory::getDocument()->addStyleSheet(JURI::base(true) . '/modules/mod_wow_guild_members/tmpl/stylesheet.css');
?>
<table class="mod_wow_guild_members">
<?php if($params->get('display_thead')) { ?>
<thead>
    <tr>
    	<th><?php echo JText::_('Name'); ?></th>
        <?php if($params->get('display_ranks')) { ?>
    	<th><?php echo JText::_('Rank'); ?></th>
        <?php } ?>
        <?php if($params->get('display_level')) { ?>
    	<th><?php echo JText::_('Lvl'); ?></th>
        <?php } ?>
    </tr>
</thead>
<?php } ?>
<tbody>
    <?php foreach ($members as $member) { ?>
    <tr>
        <td><?php if($params->get('display_race')) { echo $member['race']; } ?> <?php if($params->get('display_class')) { echo $member['class']; } ?> <?php echo $member['name']; ?></td>
        <?php if($params->get('display_ranks')) { ?>
        <td><?php echo $member['rank']; ?></td>
        <?php } ?>
        <?php if($params->get('display_level')) { ?>
        <td><?php echo $member['level']; ?></td>
        <?php } ?>
    </tr>
    <?php } ?>
</tbody>
</table>
