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