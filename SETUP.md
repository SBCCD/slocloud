Setting up SLOCloud on a Web Server
=========

## Windows/IIS
A `web.config` file in the `/public` directory is required for IIS 7+. A sample one is provided. You will need to 
update or remove the "PHP_via_FastCGI" line to match your configuration.

The following must be installed to work with IIS:

* PHP Support for IIS
    * [Instructions from Microsoft are available]
    (http://www.iis.net/learn/application-frameworks/install-and-configure-php-applications-on-iis/using-fastcgi-to-host-php-applications-on-iis)
* CGI/FastCGI support
    * from "Programs and Features", it is "CGI" under "Internet Information Services" -> "World Wide Web Services" -> 
      "Application Development Features"
* URL Rewrite 2.0
    * from the Web Platform Installer
* ASP.Net 4.5 or 3.5
    * from "Programs and Features" in windows
    * either works

