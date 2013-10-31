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





