<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Grav;
use Grav\Common\Page\Page;
use Grav\Common\Page\Pages;
use Grav\Common\Data\Data;
use Grav\Common\Twig\Twig;
use RocketTheme\Toolbox\Event\Event;
//use RocketTheme\Toolbox\File\File;

/**
 * Class CloudinaryPlugin
 * @package Grav\Plugin
 */
require_once(__DIR__.'/vendor/autoload.php');

class CloudinaryPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
      return [
  			'onGetPageTemplates'        => ['onGetPageTemplates', 0],
        'onTwigTemplatePaths'       => ['onTwigTemplatePaths', 0],
        'onTwigInitialized'         => ['onTwigInitialized', 0]
      ];
    }

    /**
     * Add cl_tags and srcset logic to Twig
     */
    public function onTwigInitialized(Event $event)
    {
        $this->grav['twig']->twig()->addFunction(
            new \Twig_SimpleFunction('cl_tag', [$this, 'clTag'])
        );
        $this->grav['twig']->twig()->addFunction(
            new \Twig_SimpleFunction('cl_url', [$this, 'clUrl'])
        );
    }

    /**
     * pass those functions from Helpers.php through to Twig --^
     */
    public function clUrl($type, $public_id, $options = array())
    {
      \Cloudinary::config(array(
        "cloud_name"  => $this->config->get('plugins.cloudinary.cloud_name'),
        "api_key"     => $this->config->get('plugins.cloudinary.key'),
        "api_secret"  => $this->config->get('plugins.cloudinary.secret'),
        "secure"      => true
      ));
        switch ($type) {
          case 'video':
            return cl_video_path($public_id, $options);

          case 'image':
            return cloudinary_url($public_id, $options);

          case 'thumbnail':
            return cl_video_thumbnail_path($public_id, $options);
        }
    }

    public function clTag($type, $public_id, $options = array())
    {
      \Cloudinary::config(array(
        "cloud_name"  => $this->config->get('plugins.cloudinary.cloud_name'),
        "api_key"     => $this->config->get('plugins.cloudinary.key'),
        "api_secret"  => $this->config->get('plugins.cloudinary.secret'),
        "secure"      => true
      ));
        switch ($type) {
          case 'video':
            return cl_video_tag($public_id, $options);

          case 'image':
            return cl_image_tag($public_id, $options);

          case 'thumbnail':
            return cl_video_thumbnail_tag($public_id, $options);
        }
    }

    /*public static function srcset($max, $min, $step)
    {
        $i = $min;
        $srcset = "";
        while ($i<= $max) {

        }
    }*/

    /**
     * Add blueprint directory to page templates.
     */
    public function onGetPageTemplates(Event $event)
    {
        $types = $event->types;
        $locator = Grav::instance()['locator'];
        $types->scanBlueprints($locator->findResource('plugin://' . $this->name . '/blueprints'));
        $types->scanTemplates($locator->findResource('plugin://' . $this->name . '/templates'));
    }

    /**
     * Add templates to twig paths.
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }
}
