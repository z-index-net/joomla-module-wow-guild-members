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

defined('_JEXEC') or die;

JFactory::getDocument()->addStyleSheet(JURI::base(true) . '/modules/' . $module->module . '/tmpl/stylesheet.css');
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
    	<th align="right"><?php echo JText::_('Lvl'); ?></th>
        <?php } ?>
    </tr>
</thead>
<?php } ?>
<tbody>
    <?php foreach ($members as $member) { ?>
    <tr>
        <td>
        <?php if($params->get('display_race')) echo $member['race']; ?> 
        <?php if($params->get('display_class')) echo $member['class']; ?> 
        <?php echo $member['name']; ?>
        </td>
        <?php if($params->get('display_ranks')) { ?>
        <td><?php echo $member['rank']; ?></td>
        <?php } ?>
        <?php if($params->get('display_level')) { ?>
        <td align="right"><?php echo $member['level']; ?></td>
        <?php } ?>
    </tr>
    <?php } ?>
</tbody>
</table>