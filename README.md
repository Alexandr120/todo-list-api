
## About Project

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

Resource routing is implemented accordingly ```Route::resource('tasks', \App\Http\Controllers\Api\TaskController::class);```

```TaskController``` also contains helper methods:

- Method ```[POST] ajaxFilter()``` for filtered Tasks list. It is assumed that this method is called using an "ajax" request from the front-end part.
- Method ```[POST] complete``` updated status task and set date time in field completed_at.

You can read documentation here ```127.0.0.1/docs```

The project will contain some implementation of the DTO pattern help with ```spatie/data-transfer-object``` package.

The ```knuckleswtf/scribe``` package was used to generate Open Api Documentation.

----------------------------------------------------------------------------

### Start project
- clone repo from ```url```
- in .env set 

  ```DB_HOST=db```

  ```DB_DATABASE=todo_list_api```

  ```DB_USERNAME=root```

  ```DB_PASSWORD=password```
- composer install
- docker-compose up -d --build
- docker-compose exec app php artisan key:generate
- docker-compose exec app php artisan optimize
- docker-compose exec app php artisan migrate
- docker-compose exec app php artisan db:seed

### Run to Postman and try test project. 

