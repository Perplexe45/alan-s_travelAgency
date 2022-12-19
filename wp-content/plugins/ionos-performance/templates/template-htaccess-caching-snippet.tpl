<IfModule mod_setenvif.c>
    SetEnvIf REQUEST_METHOD "^(?!GET).*$" INITIAL_REQUEST_METHOD=NOGET
</IfModule>

<IfModule mod_rewrite.c>
    # ENGINE ON
    RewriteEngine on

    # set hostname directory
    RewriteCond %{HTTPS} on
    RewriteRule .* - [E=IONOS_PERFORMANCE_HOST:https-%{HTTP_HOST}]

    RewriteCond %{HTTPS} off
    RewriteRule .* - [E=IONOS_PERFORMANCE_HOST:%{HTTP_HOST}]

    # set subdirectory
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_METHOD} GET
    RewriteCond %{REQUEST_URI} !(.*)/$
    RewriteCond %{REQUEST_FILENAME} !.(gif|jpg|png|jpeg|css|xml|txt|js|php|scss|webp|mp3|avi|wav|mp4|mov)$ [NC]
    RewriteRule .* - [E=IONOS_PERFORMANCE_DIR:%{REQUEST_URI}/]

    RewriteCond %{REQUEST_URI} /$
    RewriteRule .* - [E=IONOS_PERFORMANCE_DIR:%{REQUEST_URI}]

    RewriteCond %{REQUEST_URI} ^$
    RewriteRule .* - [E=IONOS_PERFORMANCE_DIR:/]

    # gzip
    RewriteRule .* - [E=IONOS_PERFORMANCE_SUFFIX:]
    <IfModule mod_mime.c>
        RewriteCond %{HTTP:Accept-Encoding} gzip
        RewriteRule .* - [E=IONOS_PERFORMANCE_SUFFIX:.gz]
        AddType text/html .gz
        AddEncoding gzip .gz
    </IfModule>

    # Main Rules
    RewriteCond %{HTTP_ACCEPT} .*text/html.*
    RewriteCond %{ENV:INITIAL_REQUEST_METHOD} ^$
    RewriteCond %{ENV:REDIRECT_INITIAL_REQUEST_METHOD} ^$
    RewriteCond %{QUERY_STRING} ^$
    RewriteCond %{REQUEST_URI} !^/(wp-admin|wp-content/cache)/.*
    RewriteCond %{HTTP_COOKIE} !(wp-postpass|wordpress_logged_in|comment_author)_
    RewriteCond {{IONOS_PERFORMANCE_CACHE_DIR}}/%{ENV:IONOS_PERFORMANCE_HOST}%{ENV:IONOS_PERFORMANCE_DIR}index.html%{ENV:IONOS_PERFORMANCE_SUFFIX} -f
    RewriteRule ^(.*) /wp-content/cache/ionos-performance/%{ENV:IONOS_PERFORMANCE_HOST}%{ENV:IONOS_PERFORMANCE_DIR}index.html%{ENV:IONOS_PERFORMANCE_SUFFIX} [L]
</IfModule>