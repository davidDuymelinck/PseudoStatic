#PseudoStatic

This is a php powered static file CMS.

You can use it on a server with php, which gives you:

* administration of the site
* not found url handling

Or you can use it as a static site generator.

##Requirements

* php 5.6+
* [composer](https://getcomposer.org/)

##Installation

* clone the project
* run `composer install`
* create a .env file in the root of you project with the variables ADMIN_USER, ADMIN_PASS. The variable DEVELOPING can have two value, YES and NO
* create pages

##How it works

For the pages you have to create a html.twig file in a site subfolder.

If you want to add data to the twig file you have to add a data.[yaml](http://lzone.de/cheat-sheet/YAML) file in the same folder.
The data.yaml file can get data from other yaml files if you add a file sequence in the imports section.
If you add `!site` in front of the filename you can get yaml files from all subfolders of site. If you don't the yaml file needs to be in the same folder als the data.yaml.

In case you need other output than html, create a X.twig file where X will be the extension added tot the end of the url.
For example test/json.twig can be reached by the test.json url.

##Special pages

* site/landing: the homepage of the site
* subfolders of site/admin: administration pages
* site/error/not-found: url not found page 

##Administration
There are a few administration urls:

* admin/refresh-site: when the DEVELOPING is set to NO the twig files are cached. To see the changes you can use this url
* admin/create-page: the form will create a html.twig file for you

##Terminal commands
All commands start with `php console.php` and have a space after console.php.

* `create:page`: asks you a few questions to create a page with an optional data file
* `build:site`: creates a static site in the distr folder

##Extend the CMS
PseudoStatic is build with [Slim](https://www.slimframework.com), [Twig](http://twig.sensiolabs.org) and [Symfony console](http://symfony.com/doc/current/components/console.html).
Read their documentation if you don't know how to start. Check the code of the CMS to see working examples.

###An administration page with an action which requires no user interaction

* create a page in a subfolder of site/admin
* create a php class in src/AdminAction folder
* instantiate the class in the `$config['adminActions']` array found in the public/index.php file

###An administration page with user interaction

* create a page in a subfolder of site/admin
* create a php class in the src/RouteHandler folder
* if the page needs data from the RouteHandler class create a php class in src/AdminAction folder
* add an `$app->post()` route in the public/index.php file

###Add a command

* create a php class in the src/Command folder
* add an `$application->add()` in the console.php file