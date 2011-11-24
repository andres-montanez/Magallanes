# Magallanes #

### What's Magallanes? ###
Magallanes is a deployment tool for PHP applications; it's quite simple to use and manage.
It will get your application to a safe harbor.

### So, What can it do? ###
You can instruct Magallanes to deploy your code to all the servers you want (via rsync over ssh, dah),
and run tasks for that freshly deployed code.

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
Then, on that environment you can configure a set up specifing to which hosts you want to deploy and what tasks to run (*after*, *on*, and *before* deploying).

### And what spells... TASKS! What tasks has it built in? ###
Just a few, for now...
  * **deployment/rsync**  - This task is for deploying your code to the remote servers.
  * **scm/update**        - This task is for updating (git/svn) your base wokring copy.

But! You can create your own taks, and execute commands on your working copy and in your deployed code!

### This is awesome! Where can I learn more? ###
You can read the whole source code (naaah!); or checkout the wiki at: http://magallanes.zenreworks.com/wiki


Enjoy your magic trip with **Magallanes* to the land of the easily deployable apps!!
