<?php

/**
 * sfDynamicsPlugin configuration.
 *
 * @package     sfDynamicsPlugin
 * @subpackage  config
 * @author      Romain Dorgueil <romain.dorgueil@symfony-project.com>
 * @version     SVN: $Id: PluginConfiguration.class.php 12628 2008-11-04 14:43:36Z Kris.Wallsmith $
 */
class sfDynamicsPluginConfiguration extends sfPluginConfiguration
{
  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    require dirname(__FILE__).'/config.php';
    require_once dirname(__FILE__).'/../lib/debug/sfWebDebugPanelDynamics.class.php';

    $this->dispatcher->connect('debug.web.load_panels', array('sfWebDebugPanelDynamics', 'listenToLoadPanelEvent'));
  }
}
