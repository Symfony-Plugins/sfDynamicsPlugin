<?php

/**
 * sfDynamicsStylesheetDefinition
 *
 * @package    sfDynamicsPlugin
 * @subpackage configuration
 * @version    SVN: $Id: $
 * @author     Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @license    MIT License
 */
class sfDynamicsStylesheetDefinition extends sfDynamicsAssetDefinition
{
  public function getExtension()
  {
    return 'css';
  }

  // @todo refactor
  // we have to check for $this->path somehow
  public function getFilteredContent(sfDynamicsAssetCollectionDefinition $package)
  {
    $code = parent::getFilteredContent($package);

    // @todo move isSomethingEnabled to $package->isSomethingEnabled()
    if (sfDynamicsConfig::isStylesheetImportResolutionEnabled($package))
    {
      $callback = create_function('$v', sprintf('return file_get_contents(%s.\'/\'.$v[2]);', var_export(dirname($this->path), true)));
      $code = preg_replace_callback('/@import\s+url\((["\']?)([a-z\/\\._-]+)(\1)\);/i', $callback, $code);
    }

    $findUrlRegexp = '/url\((\'|")?([^\/][^\'"]+)(\'|")?\)/iU';

    if (isset($this->options['image_path_prefix']))
    {
      $callback = create_function('$v', sprintf('return %s::addImagePrefixPathCallback($v, \'%s\');', __CLASS__, $this->options['image_path_prefix']));
      $code = preg_replace_callback($findUrlRegexp, $callback, $code);
    }

    if (sfDynamicsConfig::isStylesheetRelativePathsResolutionEnabled($package))
    {
      $callback = create_function('$v', sprintf('return %s::resolveRelativePathsCallback($v, \'%s\');', __CLASS__, $this->path));
      $code = preg_replace_callback($findUrlRegexp, $callback, $code);
    }

    return sprintf('%s /* */', $code); // this avoid CSS hacks to break next stylesheet
  }

  /**
   * Callback to add the image_path_prefix
   *
   * @param $matches
   * @param $image_path_prefix
   * @return string
   */

  static public function addImagePrefixPathCallback($matches, $image_path_prefix)
  {
    return sprintf('url("%s%s")', $image_path_prefix, $matches[2]);
  }

  /**
   * Callback to resolve assets relative paths in stylesheets
   *
   * @param array $matches The array of matches from pcre
   * @param string $filename The filename of the stylesheet
   * @return string
   */

  static public function resolveRelativePathsCallback($matches, $filename)
  {
    $countSubDirs = substr_count($matches[2], '../');
    $relativePath = str_replace(sfConfig::get('sf_web_dir'), '', dirname($filename));
    if ($countSubDirs > substr_count($relativePath, '/'))
    {
      return $matches[0];
    }
    $absolutePath = implode('/', explode('/', $relativePath, $countSubDirs * -1));
    $absolutePath = $absolutePath.'/'.str_replace('../', '', $matches[2]);
    return sprintf('url("%s")', $absolutePath);
  }

  /**
   * @todo remove in php 5.3
   */
  static public function __set_state($state)
  {
    return self::build(new self(), array('resource', 'options', 'path'), $state);
  }
}

