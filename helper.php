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
// no direct access
defined('_JEXEC') or die;

jimport('joomla.cache.cache');

class mod_wow_guild_members {

    public static function onload(&$params) {

        // all required paramters set?
        if (!$params->get('lang') || !$params->get('realm') || !$params->get('guild')) {
            return JText::_('please configure Module') . ' - ' . __CLASS__;
        }

        // if curl installed?
        if (!function_exists('curl_init')) {
            return JText::_('php-curl extension not found');
        }

        $realm = rawurlencode(strtolower($params->get('realm')));
        $guild = rawurlencode(strtolower($params->get('guild')));
        $region = strtolower($params->get('region'));
        $url = 'http://' . $region . '.battle.net/api/wow/guild/' . $realm . '/' . $guild . '?fields=members';

        $cache = & JFactory::getCache(); // get cache obj
        $cache->setCaching(0); // enable cache for this module
        $cache->setLifeTime($params->get('cache_time', 15)); // time to cache

        $result = $cache->call(array(__CLASS__, 'curl'), $url, $params->get('timeout', 10)); // Joomla has nice functions ;)

        $cache->setCaching(JFactory::getConfig()->getValue('config.caching')); // restore default cache mode

        /*

        [...]

        */

        return $members;
    }

    public static function curl($url, $timeout=10) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Joomla! WoW Guild Members Module; php/' . phpversion());
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Connection: Close'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);

        $body = curl_exec($curl);
        $info = curl_getinfo($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);

        curl_close($curl);

        return array('info' => $info, 'errno' => $errno, 'error' => $error, 'body' => json_decode($body));
    }

}