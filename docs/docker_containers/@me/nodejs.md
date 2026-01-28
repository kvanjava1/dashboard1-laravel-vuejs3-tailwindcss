this project uses nodejs container 'php_dev_nodejs_20':

    - root project on this container :
        - cmd : docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && pwd'
            - result : 
                - /var/www/nodejs_20/php8.2/laravel/dashboard1
    
    - how to use npm :
        - cmd : docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && npm -v'
            - result : 
                - 10.8.2
    
    - how to use node :
        - cmd : docker exec php_dev_nodejs_20 sh -c 'cd php8.2/laravel/dashboard1 && node -v'
            - result : 
                - v20.19.6

            