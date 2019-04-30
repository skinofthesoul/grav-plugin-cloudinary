<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Grav;
use Grav\Common\Page\Page;
use Grav\Common\Page\Pages;
use RocketTheme\Toolbox\Event\Event;

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
      //require_once(__DIR__.'/vendor/cloudinary_php/autoload.php');

      return [
        //'onPluginsInitialized' => ['onPluginsInitialized', 0],
  			'onGetPageTemplates'   => ['onGetPageTemplates', 0],
        'onTwigSiteVariables'      => ['onTwigSiteVariables', 0],
        'onTwigTemplatePaths'      => ['onTwigTemplatePaths', 0]
      ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
      // Nothing else is needed for admin so close it out
  		if ( $this->isAdmin() ) {

  			$this->enable([
  				'onAdminSave' => ['onAdminSave', 0],
  			]);

  			return;
  		}
    }

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

    // found that in Blogroll plugin: https://github.com/Perlkonig/grav-plugin-blogroll/blob/master/blogroll.php
    public function onTwigTemplatePaths()
    {
        //Load the built-in twig unless overridden
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    // from Blogroll as well
    public function onTwigSiteVariables()
    {
        \Cloudinary::config(array(
          "cloud_name" => $this->config->get('plugins.cloudinary.cloud_name'),
          "api_key" => $this->config->get('plugins.cloudinary.key'),
          "api_secret" => $this->config->get('plugins.cloudinary.secret')
        ));
        // old version: cloudinary tag in header of page
        /*if (property_exists($this->grav['page']->header(),'cloudinary')) {
          foreach($this->grav['page']->header()->cloudinary as $pid => $arrVid) {
            $arrThumbs[$pid] = array(
              "public_id" => $arrVid["public_id"],
              "title"     => $arrVid["title"],
              "url"       => cloudinary_url($pid, $options)
            );
          }}*/

        // listing page for videos of child pages
        $children = $this->grav['page']->children();
        if (count($children)) {
          $arrThumbs = array();
          //$options = array();
          $options = $this->config->get('plugins.cloudinary.options.video.listing');
          $options['resource_type'] = 'video';
          foreach($children as $child) {
            if (property_exists($child->header(), 'public_id')) {
              $arrThumbs[$child->header()->public_id] = array(
                "public_id" => $child->header()->public_id,
                "title"     => $child->header()->title,
                "url"       => $child->url(),
                "url_img"   => cloudinary_url($child->header()->public_id, $options)
              );
            }
          }
          $this->config->set('plugins.cloudinary.list', $arrThumbs);
        }

        // single page with video
        if (property_exists($this->grav['page']->header(), 'public_id')) {
          //$options = array("controls" => true, "width" => 800);
          $options = $this->config->get('plugins.cloudinary.options.video.single');
          $this->config->set('plugins.cloudinary.video', cl_video_tag($this->grav['page']->header()->public_id, $options));
        }
    }
    public function onAdminSave(Event $event)
    {
  		// get the ojbect being saved
    	$obj = $event['object'];

  		// check to see if the object is a `Page` with template `cloudinary-single`
      if ($obj instanceof Page &&  $obj->template() == 'cloudinary-single' ) {

  			// get the header
  			$header = $obj->header();
  			$obj->header($header);
      }
    }
}
