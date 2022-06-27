# mediapi
Small API with frontend made for a job interview

## Running

Go in the `docker-compose.yml` and replace those environment variables:
```
OMDB_KEY: "your key for IMDB public API"
SECRET_KEY: "secret key generated"
```

To generate a secret key easily, issue this command: `< /dev/urandom tr -dc _A-Z-a-z-0-9 | head -c${1:-32};echo;`

Do not change the `_ENDPOINT` environment variables unless you have a testing version of those APIs running at the new specified endpoint.

You can customize the Redis install with those variables:
 - REDIS_ENDPOINT
 - REDIS_PORT
 - REDIS_DB

But if you want to do a simple install, leave them as it is.  
The backend uses SqLite at the moment which is enough for a project this small.  
Other DB backends may be implemented later.