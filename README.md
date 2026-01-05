# Cloudinary Plugin

**Please note: I have no plans to develop this plugin further, and may not have the time for maintenance should anything come up. Feel free to contact me if you’d like to take over ownership.**

The **Cloudinary** Plugin is for [Grav CMS](http://github.com/getgrav/grav). [Cloudinary](https://cloudinary.com/) is an asset management and delivery service for images and videos, providing on the fly transformation and many other useful options. This plugin currently lets you build listing pages of videos with linked thumbnails and single pages that display the actual video. See below for future plans.

You need a Cloudinary account to use this plugin! There is a [free tier with generous limitations](https://cloudinary.com/pricing) available.

### Credits
Many, many thanks go to the folks at Cloudinary for offering such easily integratable APIs and widgets and things, but also to Team Grav for making Admin integration mostly a matter of properly-formatted yaml files in the right places. I would also like to shout out to quite a few other plugin writers whose plugins I used to learn from, and especially to the ones whose code I shamelessly copied in places: [Simon Cramer](https://github.com/simoncramer) (srcset-helper plugin) and [Aaron Dalton](https://github.com/Perlkonig) (blogroll plugin, among others). Check out their stuff, it's good!

## Installation

Installing the Cloudinary plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install cloudinary

This will install the Cloudinary plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/cloudinary`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `cloudinary`. You can find these files on [GitHub](https://github.com/skinofthesoul/grav-plugin-cloudinary) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/cloudinary

> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav) and the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) to operate.

### Admin Plugin

If you use the admin plugin, you can install directly through the admin plugin by browsing the `Plugins` tab and clicking on the `Add` button.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/cloudinary/cloudinary.yaml` to `user/config/plugins/cloudinary.yaml` and only edit that copy.

You also need to:
1. get a Cloudinary account
2. configure an unsigned preset, *OR* upload your files directly in Cloudinary
3. **if you're planning to show videos > 40 MB** you MUST configure eager transformations in the preset you are using to upload the files, and set eager async to enabled!
4. make sure none of your files are larger than 100 MB

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
cloud_name: your_cloud_name
key: your_key
secret: your_secret
unsigned_preset: unsigned_preset
defaults_gallery:
  options:
    fetch_format: auto
    quality: 70
maxWidth: 600
minWidth: 200
stepSize: 200
defaultImageSize: 100vw
size:
  -
    screenSize: (min-width:36em)
    imageSize: 50vw
  -
    screenSize: (min-width:45em)
    imageSize: 33vw
defaults_video:
  options:
    width: 900
    controls: true
    fetch_format: auto
defaults_image:
  options:
    width: 1280
    fetch_format: auto
```
These are just example settings. Your cloud name, key and secret are required to find actual files to display. If you wish to use the embedded upload widget, you need to define a preset for unsigned uploads in Cloudinary and enter that preset's name in the plugin configuration. You can set default options for your gallery pages as well as for single pages with videos or images, Cloudinary has an extensive list of available [options for videos](https://cloudinary.com/documentation/video_transformation_reference) as well as [options for images](https://cloudinary.com/documentation/image_transformation_reference). Just list them under the appropriate heading, with proper indentation (or use Grav Admin).

Note that if you use the Admin plugin, a file with your configuration, and named cloudinary.yaml will be saved in the `user/config/plugins/` folder once the configuration is saved in the admin. Saving a page with a cloudinary template will also automagically add these default options to that page.

## Templates

If you need to make any changes in the templates, copy the ones you wish to change from `user/plugins/cloudinary/templates` to the `templates` folder of your preferred theme, and adapt them as needed.

## Usage

To add a new page that displays a thumbnail gallery of either videos or images that are linked to a page with the original file and optional text:
1. Add a page with the `cloudinary-gallery` template.
2. Put `resource_type: video` or `resource_type: image` in that page's header, depending on what you want displayed on that page, along with the options you want (Grav Admin will give you some defaults to start with!).
3. Add child pages to the gallery page that use either the `cloudinary-video` or the `cloudinary-image` template.
4. Set the child pages' `header` entries like this (again, Grav Admin will give you some default options):

```yaml
title: 'Title of your video/image'
public_id: cloudinary_public_id
options:
    width: 900
    controls: true
```

You can find and change an asset's public id in the media library of your Cloudinary account.

### Admin upload options

If you're using the Admin plugin, you can either upload your files to Cloudinary separately and just copy and paste the public id of the file you want in the appropriate field of the Cloudinary tab when creating or editing a page with the `cloudinary-video` or `cloudinary-image` template. OR you can use the "Upload file" button and enjoy some javascript widget magic!

## Language settings

If you want to change the text that will appear in the alt tag of the thumbnails on listing pages, copy the language contents of `user/plugins/cloudinary/languages.yaml` that you wish to change (`ALT_PREFIX`) to `user/languages/en.yaml` or another language file respectively, and change that so your custom text will not get overridden when the plugin is updated.

## Future plans
At the moment you need to ~upload and~ manage files directly in your Cloudinary account, and you need to add a child page for each single file. If you wish to stay within the limits of the free plan, I suggest you regularly check your Cloudinary account and prune any files you do not need any more. I have some ideas for :

- [x] add format options fields to Admin
- [x] add management options, especially uploading, via Grav Admin plugin
- [x] add support for images
- [ ] write up a detailed howto for working with large video files
- [ ] add support for image galleries with all images attached directly to a page
- [ ] add option for featherlight galleries for images
- [ ] add support for Markdown shortcodes
- [ ] test whether the plugin clashes with responsive image plugins or the like
- [ ] add support for subfolders in Cloudinary

Let me know if you have suggestions for improvement!
