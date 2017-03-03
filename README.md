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