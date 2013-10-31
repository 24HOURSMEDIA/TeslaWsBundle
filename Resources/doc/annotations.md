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




