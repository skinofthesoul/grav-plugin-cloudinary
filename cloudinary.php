<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Grav;
use Grav\Common\Page\Page;
use Grav\Common\Page\Pages;
//use Grav\Common\AssetsGrav\Common\Assets;
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
        'onPluginsInitialized'   => ['onPluginsInitialized', 0],
        'onPageInitialized'      => ['onPageInitialized', 0],
  			'onGetPageTemplates'     => ['onGetPageTemplates', 0],
        'onTwigSiteVariables'    => ['onTwigSiteVariables', 0],
        'onTwigTemplatePaths'    => ['onTwigTemplatePaths', 0]
      ];
    }

    /**
     * Initialize the plugin -> add JS to Admin
     */
    public function onPluginsInitialized()
    {
  		if ( $this->isAdmin() ) {
        $this->grav["assets"]->addJs('https://widget.cloudinary.com/v2.0/global/all.js');
        $code = '
  <script type="text/javascript">
  var myWidget = cloudinary.createUploadWidget({
    cloudName: '.$this->config->get('plugins.cloudinary.cloud_name').',
    uploadPreset: \'s4o4cqgw\'}, (error, result) => {
      if (!error && result && result.event === "success") {
        console.log(\'Done! Here is the image info: \', result.info);
      }
    }
  )

  document.getElementById("cl_upload_widget").addEventListener("click", function(){
      myWidget.open();
    }, false);
  </script>';
        $this->grav["assets"]->addInlineJs($code);

  			$this->enable([
  				'onAdminSave' => ['onAdminSave', 0]
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

        // gallery page for videos or images of child pages
        if ($this->grav['page']->template() == 'cloudinary-gallery') {
          $children = $this->grav['page']->children();
          if (count($children)) {
            $arrThumbs = array();
            $options = array();
            if (property_exists($this->grav['page']->header(), 'options')) {
              $options = $this->grav['page']->header()->options;
            }
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
        }

        // page with single video or image
        if ($this->grav['page']->template() == 'cloudinary-image' || $this->grav['page']->template() == 'cloudinary-video') {
          if (property_exists($this->grav['page']->header(), 'public_id')) {
            $options = array();
            $options = $this->grav['page']->header()->options;
            if ($this->grav['page']->template() == 'cloudinary-image') {
              $this->config->set('plugins.cloudinary.image', cl_image_tag($this->grav['page']->header()->public_id, $options));
            }
            if ($this->grav['page']->template() == 'cloudinary-video') {
              $this->config->set('plugins.cloudinary.video', cl_video_tag($this->grav['page']->header()->public_id, $options));
            }
          }
        }
    }

    /**
     * Add widget button in Admin to cloudinary-single pages.
     */
    public function onPageInitialized()
    {
      if ( $this->isAdmin() ) {
        /*if ($this->grav['page']->template() == 'cloudinary-video') {
          $button = '<button id="upload_widget" class="cloudinary-button">'.PLUGIN_CLOUDINARY.UPLOAD_BUTTON.'</button>';
          $content = $this->grav['page']->getContent();
          $this->grav['page']->setContent($content.$button);
        }*/
      }
    }

    /**
     * Add default options to new cloudinary pages.
     */
    public function onAdminSave(Event $event)
    {
  		// get the object being saved
    	$obj = $event['object'];

      // check to see if the object is a `Page` with template `cloudinary-gallery`
      if ($obj instanceof Page &&  $obj->template() == 'cloudinary-gallery') {
        // get the header
  			$header = $obj->header();

        // check for options & add defaults if none exist
        if (!isset($header->options)) {
          $header->options = $this->config->get('plugins.cloudinary.defaults_gallery.options');
        }
        // set the header
  			$obj->header($header);
      }

  		// check to see if the object is a `Page` with template `cloudinary-video`
      if ($obj instanceof Page &&  $obj->template() == 'cloudinary-video') {
        // get the header
  			$header = $obj->header();

        // check for options & add defaults if none exist
        if (!isset($header->options)) {
          $header->options = $this->config->get('plugins.cloudinary.defaults_video.options');
        }
        // set the header
  			$obj->header($header);
      }

      // check to see if the object is a `Page` with template `cloudinary-image`
      if ($obj instanceof Page &&  $obj->template() == 'cloudinary-image' ) {
        // get the header
  			$header = $obj->header();

        // check for options & add defaults if none exist
        if (!isset($header->options)) {
          $header->options = $this->config->get('plugins.cloudinary.defaults_image.options');
        }
        // set the header
  			$obj->header($header);
      }
    }
}
