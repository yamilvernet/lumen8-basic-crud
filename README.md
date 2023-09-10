# Basic Lumen 8 CRUD

Simple REST API to manage elements; In future versions we will add more features such as JWT authentication

Our Items API will have 5 Endpoints:
- `GET` /item to get all items data
- `GET` /item/{id} to get a item by its ID
- `POST` /item to create new item
- `PUT` /item/{id} to update a item base on its id
- `DELETE` /item/{id} to delete a item base on its id

## How to run

    $ php -S localhost:8000 -t public


# Example to use from Javascript

## Get all items

    fetch('/items')
    .then((r)=>r.json())
    .then((data)=>{
        console.log(data)
    })

## Get item with id 1

    fetch('/items/1')
    .then((r)=>r.json())
    .then((data)=>{
        console.log(data)
    })

## Create new item from Javascript Object

    fetch('/items', {
        method: "POST",
        body: JSON.stringify({
            name:'coca cola 3 lt',
            description:'large soda'
        }),
        headers: {
            "Content-type": "application/json; charset=UTF-8"
        }
    })
    .then((r)=>r.json())
    .then((data)=>console.log(data))
    .catch((error)=>console.log(error));

## Create item with id 1 from Javascript Object

    fetch('/items/1', {
        method: "PUT",
        body: JSON.stringify({
        name:'coca cola 3.5 lt',
        }),
        headers: {
            "Content-type": "application/json; charset=UTF-8"
        }
    })
    .then((r)=>r.json())
    .then((data)=>console.log(data))
    .catch((error)=>console.log(error));


## Delete item with id 1

    fetch('/items/1', {
        method: "DELETE",
    })
    .then((r) => r.json())
    .then((data) => console.log(data))
    .catch((error) => console.log(error));


# Remember to add .env file

    APP_NAME=Qubit
    APP_ENV=local
    APP_KEY=
    APP_DEBUG=true
    APP_URL=http://localhost
    APP_TIMEZONE=UTC

    LOG_CHANNEL=stack
    LOG_SLACK_WEBHOOK_URL=

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=db_name
    DB_USERNAME=db_username
    DB_PASSWORD=secret

    CACHE_DRIVER=file
    QUEUE_CONNECTION=sync


# Auth

### Get composer jwt package

    $ composer require tymon/jwt-auth

### Register auth middleware and providers in bootstrap/app.php

    // Uncomment 
    $app->withFacades();
    $app->withEloquent();

    // Then uncomment the auth middleware 

    $app->routeMiddleware([
        'auth' => App\Http\Middleware\Authenticate::class,
    ]);

    $app->register(App\Providers\AuthServiceProvider::class);

    // Add this line in the same file:

    $app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);

### Add auth config in config/auth.php (create config directory if it's necesary)

    <?php

    return [
        'defaults' => [
            'guard' => 'api',
            'passwords' => 'users',
        ],

        'guards' => [
            'api' => [
                'driver' => 'jwt',
                'provider' => 'users',
            ],
        ],

        'providers' => [
            'users' => [
                'driver' => 'eloquent',
                'model' => \App\Models\User::class
            ]
        ]
    ];

### Generate secret key

    $ php artisan jwt:secret

### Create migration

    $ php artisan make:migration create_users_table

### Set fields into user table

    // database\migrations*_create_users_table.php

    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateUsersTable extends Migration
    {
        /**
        * Run the migrations.
        *
        * @return void
        */
        public function up()
        {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->timestamps();
            });
        }

        /**
        * Reverse the migrations.
        *
        * @return void
        */
        public function down()
        {
            Schema::dropIfExists('users');
        }
    }

### Migrate

    $ php artisan migrate

### Add auth controller in app\Http\Controllers\AuthController.php

    <?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use  App\Models\User;

    class AuthController extends Controller
    {


        public function __construct()
        {
            $this->middleware('auth:api', ['except' => ['login', 'refresh', 'logout']]);
        }

        public function register(Request $request) {
            $this->validate($request, [
                'name' => 'required|string',
                'email' => 'required|unique:users|string',
                'password' => 'required|string',
                'secret' => 'required|string',
            ]);

            if ($request['secret']!=env('JWT_SECRET')) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $user = User::create([
                'name' => $request['name'], 
                'email' => $request['email']
            ]);

            $user->password = Hash::make($request['password']);
            $user->save();


            return response()->json(['message' => 'User registered, please login'], 201);
        }



        /**
        * Get a JWT via given credentials.
        *
        * @param  Request  $request
        * @return Response
        */
        public function login(Request $request)
        {

            $this->validate($request, [
                'email' => 'required|string',
                'password' => 'required|string',
            ]);

            $credentials = $request->only(['email', 'password']);

            if (! $token = Auth::attempt($credentials)) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return $this->respondWithToken($token);
        }

        /**
        * Get the authenticated User.
        *
        * @return \Illuminate\Http\JsonResponse
        */
        public function me()
        {
            return response()->json(auth()->user());
        }

        /**
        * Log the user out (Invalidate the token).
        *
        * @return \Illuminate\Http\JsonResponse
        */
        public function logout()
        {
            auth()->logout();

            return response()->json(['message' => 'Successfully logged out']);
        }

        /**
        * Refresh a token.
        *
        * @return \Illuminate\Http\JsonResponse
        */
        public function refresh()
        {
            return $this->respondWithToken(auth()->refresh());
        }

        /**
        * Get the token array structure.
        *
        * @param  string $token
        *
        * @return \Illuminate\Http\JsonResponse
        */
        protected function respondWithToken($token)
        {
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => auth()->user(),
                'expires_in' => auth()->factory()->getTTL() * 60 * 24
            ]);
        }
    }

### Set user model into app/Models/User.php

    <?php

    namespace App\Models;

    use Illuminate\Auth\Authenticatable;
    use Laravel\Lumen\Auth\Authorizable;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
    use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

    //this is new
    use Tymon\JWTAuth\Contracts\JWTSubject;

    class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject 
    {
        use Authenticatable, Authorizable;

        /**
        * Get the identifier that will be stored in the subject claim of the JWT.
        *
        * @return mixed
        */
        public function getJWTIdentifier()
        {
            return $this->getKey();
        }

        /**
        * Return a key value array, containing any custom claims to be added to the JWT.
        *
        * @return array
        */
        public function getJWTCustomClaims()
        {
            return [];
        }
    }

### Register user through secret key in .env file

    fetch('/api/register', {
    method: "POST",
    body: JSON.stringify({
        name:'admin',
        email:'admin@email.com',
        password:'123456',
        secret:'jwt_secret_setted_in_env'
    }),
    headers: {"Content-type": "application/json; charset=UTF-8"}
    }).then((r)=>r.json()).then((data)=>{
    console.log(data);
    }).catch((error)=>console.error(error))

### Secure all controller routes in ItemController.php

    (...)
    class ItemController extends Controller {
        public function __construct() {
            $this->middleware('auth:api');
        }
        (...)
    }

### Login the request and get the access token

    fetch('/api/login', {
    method: "POST",
    body: JSON.stringify({
        email:'admin@email.com',
        password:'123456'
    }),
    headers: {"Content-type": "application/json; charset=UTF-8"}
    })
    .then(response => response.json())
    .then(json => console.log(json))
    .catch(err => console.log(err));

### Use the access token to create new item

    fetch('/api/items',{
        method: 'POST',
        body: JSON.stringify({
            name:'coca cola 3 lt',
            description:'large soda'
        }),
        headers: {
            'Authorization': 'Bearer eyJ0e..(access_token_from_login_requets)..J4Vcu6YI',
            'Content-Type': 'application/json'
        }
    }).then((r)=>r.json())
    .then((data)=>{
        console.log(data)
    });

### Use the access token to get items

    fetch('/api/items',{
        method: 'GET',
        headers: {
            'Authorization': 'Bearer eyJ0e..(access_token_from_login_requets)..J4Vcu6YI',
            'Content-Type': 'application/json'
        }
    })
    .then((r)=>r.json())
    .then((data)=>{
        console.log(data)
    });
