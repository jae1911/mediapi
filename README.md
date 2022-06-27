# mediapi
Small API with frontend made for a job interview

![](https://bm.jae.su/ShareX/2022/06/firefox_b37kYuE3rC.png)

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

The front-end doesn't requires any kind of configuration.  
The recommended setup is the one present by default in the `docker-compose.yaml`.  
If you want to serve this website to the public, it is recommended to use Caddy with the following Caddyfile config:
```caddyfile
https://mydomain.tld {
    reverse_proxy frontend
}
```

## Known issues

Known issues are:
 - Visual bugs when loading long titles (page going too wide)
