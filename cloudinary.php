<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Page\Header;
use RocketTheme\Toolbox\Event\Event;
//use Cloudinary;

/**
 * Class CloudinaryPlugin
 * @package Grav\Plugin
 */

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
      require_once(__DIR__.'/vendor/autoload.php');
      //require_once(__DIR__.'/vendor/cloudinary_php/autoload.php');

      return [
          'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
          'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0]
      ];
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
          "cloud_name" => "netzhexe",
          "api_key" => "my_key",
          "api_secret" => "my_secret"
        ));
        $arrThumbs = array();
        $options = array('resource_type' => 'video', "format"=>"jpg");
        if (property_exists($this->grav['page']->header(),'cloudinary')) {
          foreach($this->grav['page']->header()->cloudinary as $pid => $arrVid) {
            $arrThumbs[$pid] = array(
              "public_id" => $arrVid["public_id"],
              "title"     => $arrVid["title"],
              "url"       => cloudinary_url($pid, $options)
            );
          }
          //Pass the array to the templates
          $this->config->set('plugins.cloudinary.list', $arrThumbs);
          //$this->config->set('plugins.cloudinary.list', $test);
      }
    }
}
