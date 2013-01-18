ProjectSend (previously cFTP) is a free, clients-oriented, private file
sharing web application.
Clients are created and assigned a username and a password. Then you can
upload as much files as you want under each account, and optionally add
a name and description to them. 

ProjectSend is hosted on Google Code.
Feel free to participate!

http://code.google.com/p/clients-oriented-ftp/

--------------------------------------------------------------------------------------------

How to install on your server:

Preparations:
1. Download and unzip the lastest version of ProjectSend to a folder of your choice.
2. Create a new database on your server. Create/assign a user to it.

When those are steps are completed, follow this instructions:
1. Rename includes/sys.config.sample.php to sys.config.php and set your database info there.
2. Upload ProjectSend to your selected destination.
3. Open your browser and go to http://your-projectsend-folder/install
4. Complete the information there and wait for the correct installation message.

Congratulations! ProjectSend is now installed and ready for action!
You may login with your new username and password.

--------------------------------------------------------------------------------------------

How to upgrade to a newer version:

1. Download your version of choice from the official project page.
2. Upload the files via FTP to your server and replace the ones of the older version.

That's it!
Your personal configuration file (sys.config.php) is never included on the downloadable
versions, so it will not be replaced while upgrading.

When a system user logs in to the system version, a check for database missing data will be
made, and if anything is found, it will be updated automatically and a message will appear
under the menu one time only.

--------------------------------------------------------------------------------------------

Questions, ideas? Want to join the project?
Send your message to contact@projectsend.org or join us on Facebook, on
https://www.facebook.com/pages/ProjectSend/333455190044627

--------------------------------------------------------------------------------------------

Many thanks to the authors of the following scripts, which are used on ProjectSend:

- jQuery
  http://www.jquery.com/

- Bootstrap (custom download)
  http://twitter.github.com/bootstrap/

- Superfish
  http://users.tpg.com.au/j_birch/plugins/superfish/

- EasyTabs.js
  http://os.alfajango.com/easytabs/

- hashchange
  http://benalman.com/projects/jquery-hashchange-plugin/

- Plupload
  http://www.plupload.com/

- Timthumb
  http://code.google.com/p/timthumb/

- TextboxList.js
  http://www.devthought.com/projects/mootools/textboxlist/

- tablesorter
  http://tablesorter.com/docs/

- multiselect.js
  http://loudev.com/
