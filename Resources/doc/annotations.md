# ANNOTATIONS

## WS\Json

The Json annotation in a controller will cause its output to be formatted as JSON.
The response's content-type will automatically be set to application/json.

Example:


```php
use Tesla\Bundle\WsBundle\Annotation as WS;
class MyController {

    /**
     * @WS\Json
     */
    function indexAction() {
      return array('a'=>'b');
    }

}
```

will produce

```JSON
{"a":"b"}
```

## WS\Vary

Sets Vary headers in the response.

Example:
```php
use Tesla\Bundle\WsBundle\Annotation as WS;
class MyController {

    /**
     * @WS\Vary("user-agent")
     * @WS\Vary("accept-encoding")
     */
    function indexAction() {
      return array('a'=>'b');
    }

}
```

## WS\TransformHeader

Transforms a header value to another value before a controller method is executed.
Can be useful to standardize user agent strings for caching purposes.

Usage:
```php
use Tesla\Bundle\WsBundle\Annotation as WS;
class MyController {

    /**
     * @WS\TransformHeader(header="user-agent", service="_tesla_ws.header_test_transformer", method="normalize")
     */
    function indexAction(Request $request) {
      $transformed = $request->headers->get('User-Agent');
    }

}
```

Will pass the user agent header to the normalize filter of service with id _tesla_ws.header_test_transformer

## WS\ReverseProxyCache

Activates a reverse proxy cache on a controller method. This is an advanced cache that works like this:
- In the controller method, define a cache time using default cache responses in the Response object
- Set the WS\ReverseProxyCache annotation on the method and define a grace time
- When a cache entry is expired, the server still serves the cached entry unless the grace time has passed
- AFTER the above mentioned cache entry is served, the controller method is executed nonetheless and the new entry cached
- This results in creating new cache entries in the background
- So the maximum cache entry age is CACHE_EXPIRES + GRACE_TIME

Usage:
```php

    /**
     * @WS\ReverseProxyCache(grace="+20 seconds")
     * @SF\Cache(expires="+10 seconds", public="true")
     * @WS\Vary("user-agent")
     */
    function indexAction(Request $request) {
    }
```

This will lead to most entries having a max cache age of 10 seconds; incidently, an entry will be served with a max age of 30
seconds. If during 30 seconds no entry has been requested, no caching takes place; before 30 seconds, there will always be a
fast response.






