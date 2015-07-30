#Myopenletter

##Overview

Myopenletter was a simple platform where anyone could send an open letter to anyone else. All you need was to enter your name, and the other person's name and type away.

Once saved, each letter gets an unique public URL and a private URL and as the writer of the letter, you could change the content of the letter using the private URL.

Disqus is used to integrate comments

An admin panel to manage the letters is present. It **DOES NOT** have any auth except the URL to access it being unique and configurable
 
There's also some code to generate a sitemap to make it SEO friendly. This URL is also secure and configurable

It also uses a simple ORM called Idiorm along with an active record library called Paris

##Deployment

1. Checkout the repository
2. Run composer
3. Create an .env file and fill it up with the following info:
    * Recaptcha public and private keys
    * Database host, db name, user name and password
    * Admin panel unique path
    * Sitemap generator unique path
4. Import the file letter.sql under the sql directory into mysql
5. Upload everything to your web root
6. You're good to go!