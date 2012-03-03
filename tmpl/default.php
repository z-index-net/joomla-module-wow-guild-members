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
<div class="mod_wow_guild_members">
    <ul>
        <?php foreach ($news as $row) { ?>
            <li><?php echo $row; ?></li>
        <?php } ?>
    </ul>
</div>
