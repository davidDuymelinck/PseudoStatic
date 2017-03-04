This is a tutorial in commits how tor create a static file cms.

# Why i did this

I have looked at quite a few static site creators and i didn't like the build process. 
I want to see what i'm doing when i work on the content. Not create all the files, check the output, change, over and over again.
 
The other thing i don't like is that the (meta)data of the page is in the same file as markup.
 
# How did i start?

I'm most comfortable with php so i searched for the most lightweight way to have routes and a template engine.
I found [Slim](https://www.slimframework.com) and it's twig addon.

I used composer to add the depenencies. I created a frontcontroller, public/index.php. And added a twig template file. 
For the moment all of this you can find in the slim documentation.

This is the moment the cms starts forming. I have added middleware for the url, `->add()`.
Inside that function I do three things:

* When the url is an empty string render a default template.
* When the template is not found render a not found template.
* When there is a template render it.

At the moment i only fill the `$request` template variable, but you see there is also a data variable.

Now i added a new dependency to our composer file and i told it where to look for our own classes which all start with the namespace PseudoStatic.
In the `RouterMiddleware` class i added a `getYamlData` method. This method contains an extension of the yaml format.
I you add `imports:` followed by an array, the method will find the file(s) and adds its data to the data of the yaml file that belongs to the page.
If there is no `!site` before the filename the file has to be in the page folder. When `!site` is present you have to start the filepath from the site folder.
All this provides a flexible way to place your data files.

If you followed with the code changes and you went to the site you may have noticed the landing page content didn't change.
This is because the twig cache is on.

This is the moment the cms is beginning to shine. Next to adding a new template, admin/refresh-site, i added a configurable way to add admin actions.
In the src/AdminAction folder you can now add classes with an `__invoke` method which will get executed by the `excecuteAdmin` method in the `RouteMidelware` class.
So now you can open a tab with the /admin/refresh-site and refresh the page to see your changes. It takes less time than waiting for the whole site to get build.
For the more impatient people you can change `'cache' => $projectRoot.'/cache'` to `'cache' => false`. 

Now that we are extending the cms i want to give you an other tip. In the previous commit you saw `$app = new \Slim\App($config);`.
You can add `'settings' => ['displayErrorDetails' => true,]` to `$config` so you don't have the production error page of the slim framework.
 
For the templates and data files I always use the filenames page.html.twig and data.yaml. 
After a while you will get tired of typing it over and over. That is why i made a command `php console.php create:page`.
It will ask for the url and create the page based on that. If you type y with the next question the data file will be created.

Until now I was focused on displaying data and markup, but sites display other files too like images, javascript files and so on.
The problem with the php build in server is that there is a bug with urls containing a point character. So i got an apache server running.
In the .htaccess file i excluded javascript, css and a few image file types. All other file types like json, xml can be rendered by a template.