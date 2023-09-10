# Basic Lumen 8 CRUD

Simple REST API to manage elements; In future versions we will add more features such as JWT authentication

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
