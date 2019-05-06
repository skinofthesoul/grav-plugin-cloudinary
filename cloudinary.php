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
        'onPluginsInitialized'      => ['onPluginsInitialized', 0],
        //'onPageInitialized'         => ['onPageInitialized', 0],
  			'onGetPageTemplates'        => ['onGetPageTemplates', 0],
        'onTwigSiteVariables'       => ['onTwigSiteVariables', 0],
        'onOutputGenerated'         => ['onOutputGenerated', 0],
        //'onAdminTwigTemplatePaths'  => ['onAdminTwigTemplatePaths', 0],
        'onTwigTemplatePaths'       => ['onTwigTemplatePaths', 0],
        'onTwigInitialized' => ['onTwigInitialized', 0]
      ];
    }

    /**
     * Initialize the plugin -> add JS to Admin
     */
    public function onPluginsInitialized()
    {
  		if ( $this->isAdmin() ) {
        // add cloudinary JS file for cloudinary single file pages
        //if ($this->grav['page']->template() == 'cloudinary-image' || $this->grav['page']->template() == 'cloudinary-video') {
          //$this->grav["assets"]->addJs('https://widget.cloudinary.com/v2.0/global/all.js');
        //}
        $this->enable([
          'onAdminTaskExecute' => ['onAdminTaskExecute', 0],
          'onAdminSave'        => ['onAdminSave', 0]
  			]);
  		}
      /*$this->enable([
        'onTwigInitialized' => ['onTwigInitialized', 0],
        'onFormProcessed' => ['onFormProcessed', 0],
        'onAdminSave'     => ['onAdminSave', 0]
      ]);*/
    }


    public function onAdminTwigTemplatePaths($event)
    {
        //$event['paths'] = [__DIR__ . '/admin/themes/grav/templates'];
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
    public static function clUrl($type, $public_id, $options = array())
    {
        \Cloudinary::config(array(
          "cloud_name" => $this->config->get('plugins.cloudinary.cloud_name')
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

    public static function clTag($type, $public_id, $options = array())
    {
        \Cloudinary::config(array(
          "cloud_name" => $this->config->get('plugins.cloudinary.cloud_name')
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
     * Add inline JS to Admin somewhere AFTER the button it's looking for
     */
    public function onOutputGenerated()
    {
  		if ( $this->isAdmin() ) {
        //if ($this->grav['page']->template() == 'cloudinary-image' || $this->grav['page']->template() == 'cloudinary-video') {
          $raw = $this->grav->output;
          $code = '
<script type="text/javascript">
  var myWidget = cloudinary.createUploadWidget({
    cloudName: "'.$this->config->get('plugins.cloudinary.cloud_name').'",
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
          $raw_replaced = str_replace('-replace this with inline js-', $code, $raw);
          $this->grav->output = $raw_replaced;
        //}
        //$this->grav["assets"]->addInlineJs($code);

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

    /**
     * Add templates to twig paths.
     */
    public function onTwigTemplatePaths()
    {
        //Load the built-in twig unless overridden
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * Save file to Cloudinary.
     */
    public function onAdminTaskExecute(Event $event)
    {
        \Cloudinary::config(array(
          "cloud_name" => $this->config->get('plugins.cloudinary.cloud_name'),
          "api_key" => $this->config->get('plugins.cloudinary.key'),
          "api_secret" => $this->config->get('plugins.cloudinary.secret')
        ));
        dump($event); exit;
        $form = $event['form'];
        $action = $event['action'];
        $params = $event['params'];

        $post = isset($_POST['data']) ? $_POST['data'] : [];
        $event->stopPropagation();

        switch ($action) {
          case 'uploadFile':
            return $result = \Cloudinary\Uploader::upload($post['file_upload'], "s4o4cqgw");
            /*array("responsive_breakpoints" => array(
                  "create_derived" => true,
                  "bytes_step" => 20000,
                  "min_width" => 200,
                  "max_width" => 1000,
                  "transformation" => array("crop" => "fill", "aspect_ratio" => "16:9", "gravity" => "auto" ))))*/
        }
    }

    /**
     * Main part: Cloudinary output!
     */
    public function onTwigSiteVariables()
    {
        \Cloudinary::config(array(
          "cloud_name" => $this->config->get('plugins.cloudinary.cloud_name'),
          "api_key" => $this->config->get('plugins.cloudinary.key'),
          "api_secret" => $this->config->get('plugins.cloudinary.secret')
        ));

        // build thumbnail url for admin pages with Cloudinary files
        if ( $this->isAdmin() ) {
          /*if ($this->data['template'] == 'cloudinary-image' || $this->data['template'] == 'cloudinary-video') {
            if (property_exists($this->data['header'], 'public_id')) {
              $options = array("format" => "jpg", "width" => 400);
              $thumb = cl_image_tag($this->data['header']->public_id, $options);
              $this->config->set('plugins.cloudinary.thumbnail', $thumb);
            }
          }*/
        }
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
     * Add default options to new cloudinary pages >> done with function callbacks!
     */
    public function onAdminSave(Event $event)
    {
  		// get the object being saved
    	$obj = $event['object'];

      // check to see if the object is a `Page` with template `cloudinary-video` or `cloudinary-image`
      if ($obj instanceof Page && ($obj->template() == 'cloudinary-video' || $obj->template() == 'cloudinary-image')) {
        \Cloudinary::config(array(
          "cloud_name" => $this->config->get('plugins.cloudinary.cloud_name'),
          "api_key" => $this->config->get('plugins.cloudinary.key'),
          "api_secret" => $this->config->get('plugins.cloudinary.secret')
        ));
        dump($obj); exit;
        $header = $obj->header();
        if (isset($header->file_upload)) { // only proceed if there's a new file
          // get the header
          $fup = $header->file_upload;
          $path_parts = pathinfo($fup[array_key_first($fup)]['path']);
          // turn filename into usable public id
          $clean_name = preg_replace("/[^a-zA-Z0-9_\s-]/", "", $path_parts['filename']);
          //Clean up multiple dashes or whitespaces
          $clean_name = preg_replace("/[\s-]+/", " ", $clean_name);
          //Convert whitespaces and underscore to dash
          $clean_name = preg_replace("/[\s_]/", "-", $clean_name);

          $clean_name = strtolower($clean_name);
          //dump($clean_name); exit;
          $header->public_id = $clean_name;
          // unset file_upload in header
          // $header->

          // stop upload to Grav destination

          // set the header
    			$obj->header($header);
        }
      }
    }
}
