<!-- routes/web.php -->
<?php

/** @var \Laravel\Lumen\Routing\Router $router */
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;


Route::group(['prefix' => 'api'], function ($router) {
    // Auth routes
    Route::post('login', 'AuthController@login');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('register', 'AuthController@register');


    Route::get('/itemad', ['middleware' => 'role:admin', 'uses' => 'ItemController@index']);
    Route::get('/itemad', ['middleware' => 'role:admin', 'uses' => 'ItemController@index']);



    // Items routes
    Route::group(['prefix' => 'items'], function ($router) {
        // Listar todos los items
        Route::get('/', 'ItemController@index');
    
        // Obtener un item por ID
        Route::get('/{id}', 'ItemController@show');

        Route::group(['middleware' => 'role:admin'], function ($router) {
            // Crear un nuevo item
            Route::post('/','ItemController@store');
        
            // Actualizar un item por ID
            Route::put('/{id}','ItemController@update');
        
            // Eliminar un item por ID
            Route::delete('/{id}','ItemController@destroy');
        });
    });
});

<!-- database/migrations/2023_09_10_073136_create_users_table.php -->
(...)
class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('role')->default('user');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('users');
    }
}

<!-- app/Models/User.php -->

<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject 
{
    protected $fillable = ['name','email','password'];
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

<!-- app/Http/Controllers/AuthController.php -->
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller{
    
    public function __construct(){
        $this->middleware('auth:api', ['except' => ['login', 'refresh','register']]);
    }

    /**
     * Register new users using JWT_SECRET
     */
    public function register(Request $request) {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|unique:users|string',
            'password' => 'required|string',
            'secret' => 'required|string',
            'role' => 'string|nullable',
        ]);
    
        if ($request['secret']!=env('JWT_SECRET')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    
        $user = User::create([
            'name' => $request['name'], 
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
        ]);

        if(isset($request['role'])){
            $user->role = $request['role'];
        }
    
        // $user->password = Hash::make($request['password']);
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

        // return response()->json(Auth::attempt($credentials), 201);


        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
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

<!-- app/Http/Middleware/Authenticate.php -->

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($this->auth->guard($guard)->guest()) {
            return response('Unauthorized.', 401);
        }

        return $next($request);
    }
}

<!-- An example item -->

<!-- app/Models/Item.php -->

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model {
    protected $fillable = ['name'];
}

<!-- app/Http/Controllers/ItemController.php -->

<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Item;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $items = Item::all();
        return response()->json($items);
    }

    public function show($id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        return response()->json($item);
    }

    public function store(Request $request) {
        $this->validate($request, [
            'name' => 'required|unique:items',
        ]);

        $item = Item::create($request->all());

        return response()->json(['message' => 'Item created', 'id' => $item->id], 201);
    }

    public function update(Request $request, $id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $this->validate($request, [
            'name' => 'required|unique:items',
        ]);

        $item->update($request->all());

        return response()->json(['message' => 'Item updated']);
    }

    public function destroy($id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted']);
    }
}


<!-- app/Http/Middleware/RoleMiddleware.php -->

<?php

namespace App\Http\Middleware;

use Closure;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if ($request->user() && $request->user()->role != $role) {
            return response('No tienes permiso para acceder a esta ruta.', 403);
        }

        return $next($request);
    }
}

<!-- bootstrap/app.php -->

<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();
$app->withEloquent();

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->configure('app');

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'role' => App\Http\Middleware\RoleMiddleware::class,
]);

$app->register(App\Providers\AuthServiceProvider::class);
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
$app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;


<!-- API examples -->

POST: http://localhost:8000/api/register
BODY: {
    "name":"cuantica",
    "email":"admin@cuantica.com",
    "password":"235711",
    "secret":"FsJJgQTOULLtVAn7J2Uxl6MNVhyif6Aq72hI7Ddt5J8yYqRzhgmTT4jWb8hYgzN6" // provided in .env file
    "role":"admin"
}

POST: http://localhost:8888/api/login
BODY: {
    "email":"admin@cuantica.com",
    "password":"235711"
}
RESPONSE: {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0Ojg4ODgvYXBpL2xvZ2luIiwiaWF0IjoxNjk4MzkyODg4LCJleHAiOjE2OTgzOTY0ODgsIm5iZiI6MTY5ODM5Mjg4OCwianRpIjoiaWk1aG1TZzFDVWFnZHRTWSIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.cl90Bh_lVptNQw6ymVj8D3ibOaTaVWO-DuDomvDG8xI",
    "token_type": "bearer",
    "user": {
        "id": 2,
        "name": "cuantica",
        "email": "admin@cuantica.com",
        "password": "$2y$10$GUGE2P5zfjJmDcvQ2PshcuhpBrYmQLYaBjp10JmR7kTi0A4mTK99W",
        "created_at": "2023-09-24T09:35:31.000000Z",
        "updated_at": "2023-09-24T09:35:31.000000Z"
    },
    "expires_in": 86400
}

Esta respuesta nos provee el "access_token" necesario para futuras solicitudes, el acces token debe ser incluido en el header de las próximas solicitudes en el campo Authorization: 'Bearer ' + access_token

POST: http://localhost:8888/api/items
HEADER: 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0Ojg4ODgvYXBpL2xvZ2luIiwiaWF0IjoxNjk4MzkyODg4LCJleHAiOjE2OTgzOTY0ODgsIm5iZiI6MTY5ODM5Mjg4OCwianRpIjoiaWk1aG1TZzFDVWFnZHRTWSIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.cl90Bh_lVptNQw6ymVj8D3ibOaTaVWO-DuDomvDG8xI'
BODY: {
    "name":"test item"
}
RESPONSE: {
    "message": "Item created",
    "id": 4
}

Esta ruta está protegida para que unicamente usuarios con rol admin puedan consultar recursos. En el ejemplo a continuacion se ve como se registra un usuario con rol user (implicito se no se provee) y cuando intenta acceder al recurso POST: http://localhost:8888/api/items se bloquea el acceso.

POST: http://localhost:8888/api/register
BODY: {
    "name":"bartulocuantico",
    "email":"yamil.vernet@gmail.com",
    "password":"235711",
    "secret":"FsJJgQTOULLtVAn7J2Uxl6MNVhyif6Aq72hI7Ddt5J8yYqRzhgmTT4jWb8hYgzN6"
}
RESPONSE: {
    "message": "User registered, please login"
}

POST: http://localhost:8888/api/login
BODY: {
    "email":"yamil.vernet@gmail.com",
    "password":"235711"
}
RESPONSE: {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0Ojg4ODgvYXBpL2xvZ2luIiwiaWF0IjoxNjk4NDI5NjkzLCJleHAiOjE2OTg0MzMyOTMsIm5iZiI6MTY5ODQyOTY5MywianRpIjoiM3RRSkoxcDdxY29oM1lZMiIsInN1YiI6IjciLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.vxiNSUj1_g0qEWaZX3xhGKBBj3QjVvdeULVan3XcusU",
    "token_type": "bearer",
    "user": {
        "id": 7,
        "name": "bartulocuantico",
        "email": "yamil.vernet@gmail.com",
        "password": "$2y$10$Kj7ERmpQAISTj2OKeNJOl.2dvpoplNtNnp3EzIRgCodtCJgoZF4Xy",
        "role": "user",
        "created_at": "2023-10-27T08:50:46.000000Z",
        "updated_at": "2023-10-27T08:50:46.000000Z"
    },
    "expires_in": 86400
}

POST: http://localhost:8888/api/items
HEADER: 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0Ojg4ODgvYXBpL2xvZ2luIiwiaWF0IjoxNjk4NDI5NjkzLCJleHAiOjE2OTg0MzMyOTMsIm5iZiI6MTY5ODQyOTY5MywianRpIjoiM3RRSkoxcDdxY29oM1lZMiIsInN1YiI6IjciLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.vxiNSUj1_g0qEWaZX3xhGKBBj3QjVvdeULVan3XcusU' 
BODY: {
    "name":"new item normal user try"
}
RESPONSE: No tienes permiso para acceder a esta ruta.

<!-- Por otro lado es posible acceder a la lista de items, mediante GET por cualquier tipo de usuario -->
GET: http://localhost:8888/api/items
HEADER:'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0Ojg4ODgvYXBpL2xvZ2luIiwiaWF0IjoxNjk4NDI5NjkzLCJleHAiOjE2OTg0MzMyOTMsIm5iZiI6MTY5ODQyOTY5MywianRpIjoiM3RRSkoxcDdxY29oM1lZMiIsInN1YiI6IjciLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.vxiNSUj1_g0qEWaZX3xhGKBBj3QjVvdeULVan3XcusU'
RESPONSE:[
    {
        "id": 1,
        "name": "test",
        "created_at": "2023-10-27T08:47:15.000000Z",
        "updated_at": "2023-10-27T08:47:15.000000Z"
    },
    {
        "id": 2,
        "name": "test with auth",
        "created_at": "2023-10-27T08:50:02.000000Z",
        "updated_at": "2023-10-27T08:50:02.000000Z"
    },
    {
        "id": 4,
        "name": "new item",
        "created_at": "2023-10-27T17:56:46.000000Z",
        "updated_at": "2023-10-27T17:56:46.000000Z"
    }
]









