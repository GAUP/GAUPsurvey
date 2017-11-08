# GAUPsurvey

GAUPSurvey is an open source web tool that is possible produce surveys relating geospacial information to questions and people answers, what it makes possible produce public maps and spatial data that represent their perceptions of the urban space in an answer of question.

The project on [LimeSurvey Project](https://www.limesurvey.org/), surveys creator platform and uses LeafLet javascript framework.

## Requirements

The necessary packages:
 - PHP >= 5.4
 - PHP Extensions: 
   - PHP-FPM
   - MbString
   - GD 2 With freetype
   - IMAP
   - LDAP
   - zip
   - zlib
   - PDO
   - Database Driver
 - Supported Database:
   - MariaDb >= 5.5
   - Microsoft SQL >= 2005
   - MySQL >= 5.5.3
   - PostgreSQL >= 9.0
 - Supported Web Server:
   - Apache >= 2.4
   - NGinx >= 1.1
   - Other WebServer with PHP Support

### Installation with NGinx

Nginx `default.conf` file configuration:
```
server {
    listen 8889;
    index index.php index.html;
    server_name localhost;
    error_log  /var/log/nginx/error.log;
    access_log /var/log/nginx/access.log;
    root /var/www/html;

    location / {
        try_files $uri $uri/ =404;
    }
    
    location ~ \.php$ { 
       #If a file isn’t found, 404
       try_files $uri =404; 
       #Include Nginx’s fastcgi configuration
       include /etc/nginx/fastcgi.conf;
       #Look for the FastCGI Process Manager at this location
       fastcgi_pass unix:/run/php/php7.0-fpm.sock
    } 
}

```

## Releases
- [Inteligencia Coletiva pela la Democracia (MediaLab-Prado)](docs/icdemocracia17.md)

## Licence
GAUPsurvey software is licenced under the [GPL 2.0](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html).
