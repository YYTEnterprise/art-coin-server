### Install
```
composer clearcache
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate

chown -R www storage
chgrp -R www storage
```

