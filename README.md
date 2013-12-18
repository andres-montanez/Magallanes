# Magallanes #

### What's Magallanes? ###
Magallanes is a deployment tool for PHP applications; it's quite simple to use and manage.
It will get your application to a safe harbor.


### So, What can it do? ###
You can instruct Magallanes to deploy your code to all the servers you want (via rsync over ssh),
and run tasks for that freshly deployed code.

### How can I install it via composer? ###

Simply add the following dependency to your project’s composer.json file:

```js
    "require": {
        // ...
        "andres-montanez/magallanes": "1.0.*"
        // ...
    }
```
Now tell we update the vendors:

```bash
$ php composer update
```

And finally we can use Magallanes from the vendor's bin:

```bash
$ bin/mage version
```

### Can you give me some examples/ideas? ###
**Sure!**
Suppose you have a checkout of your app and you have to deploy it to four servers;
and after each deploy you have to run some boring tasks, like fixing file permissions, creating symlinks, etc.
You can define all this on Magallanes and with *just one command* you can do all this at once!

Like this:
```
$ mage deploy to:production
```


### What's this sorcery?! ###
Easy boy. It's not sorcery, just some *technomagick*!

In Magallanes you define environments like *testing*, *staging*, or *production* like on the example above.
Then, on that environment, you can configure a setup specifying to which hosts you want to deploy and what tasks to run (*after*, *on*, and *before* deploying).
And you are done!


### This is awesome! Where can I learn more? ###
You can read the whole source code (naaah!); or checkout the documentation at: http://magephp.com


Enjoy your magic trip with **Magallanes** to the land of the easily deployable apps!!
