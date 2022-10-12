AmoCRM lib for API version 4

For install add to composer file:
````
"repositories": [
        {
            "type": "composer",
            "url": "https://git.adminka.pro/api/v4/group/4/-/packages/composer/"
        }
    ],
    
"config": {
        "gitlab-domains": [
            "git.adminka.pro"
        ],
        // available rows
    },
````

In root directory create file auth.json with content:
````
{
    "gitlab-token": {
        "git.adminka.pro": "8nYKj6E7vx5PiynM8i3Y"
    }
}
````

Run console command:
```
composer req adminka/amocrm-laravel
```

After install lib run console command:
```
php artisan vendor:publish --provider="Adminka\AmoCRM\AmoServiceProvider"
```

For use this lib, create object $amo = new AmoCRM(config("amo.app_id")) and use this object.
