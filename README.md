
## About Project

Laravel v8.83.27 (PHP v8.1.25)


In project implemented ```AuthController``` with appropriates  
routings and auth methods.
When authorizing, the user uses the standard credentials ```“email”``` and ```“password”```. In response, using Laravel's internal tools - Sanctum, the user receives a Bearer token.
After authorization, the user can see and manage only his tasks list.
In project used default ```laravel/sanctum``` package. He is installed by default along with the project when it is created. Its advantage is that you do not need to use third-party packages to work with the jwt token

Implemented Task model, migrations, controller.

In the ```Database\Seeders\DatabaseSeeder::class```, filling in users along with tasks for them is implemented.
However, filling out subtasks is not implemented in seeders, since I decided to leave this mechanism manually

Implemented ```TaskController``` for task management.
The controller has access only if the user is authorized ```middleware('auth:sanctum')```

```TaskController``` includes resource methods:
- ```[GET] index()``` 
- ```[POST] store()```
- ```[GET] show()```
- ```[PUT|PATCH] update()```
- ```[DELETE] destroy()```


The logic for displaying a list of all user tasks is as follows. The method index shows only all parent tasks. To see any subtasks, you need to go to the target task view.

Resource routing is implemented accordingly ```Route::resource('tasks', \App\Http\Controllers\Api\TaskController::class);```

You can read documentation here ```127.0.0.1/docs```

The project will contain some implementation of the DTO pattern help with ```spatie/data-transfer-object``` package.

The project includes the implementation of user roles and permissions help with ```spatie/laravel-permission```

The ```knuckleswtf/scribe``` package was used to generate Open Api Documentation.

----------------------------------------------------------------------------

### Start project
- clone repo
- in .env set 

  ```DB_HOST=db```

  ```DB_DATABASE=todo_list_api```

  ```DB_USERNAME=root```

  ```DB_PASSWORD=password```

- composer install
- php artisan optimize
- docker-compose up -d --build
- docker-compose exec app php artisan key:generate
- docker-compose exec app php artisan optimize
- docker-compose exec app php artisan migrate
- docker-compose exec app php artisan db:seed

### In web browser run 127.0.0.1 
### Run to Postman and try test project. 

