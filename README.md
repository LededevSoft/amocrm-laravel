AmoCRM lib for API version 4

For install add to composer file:
````
"repositories": [
        {
            "type": "composer",
            "url": "https://github.com/LededevSoft/amocrm-laravel"
        }
    ],
    

````

Run console command:
```
composer req lebedevsoft/amocrm-laravel
```

After install lib run console command:
```
php artisan vendor:publish --provider="LebedevSoft\AmoCRM\AmoServiceProvider"
```

For use this lib, create object $amo = new AmoCRM(config("amo.app_id")) and use this object.
