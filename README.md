# DayspringLambdaBundle
This bundle provides tooling for using Lambda to run Symfony code.

## Using this bundle
- Add this bundle to your Symfony project.
- Package your project in a docker image.
- Add `Bootstrap/service.php` to your image as `/var/run/bootstrap`, and make it executable
  ``` 
  # this is really what does the magic
  COPY symfony/src/Dayspring/LambdaBundle/Bootstrap/service.php /var/runtime/bootstrap
  RUN chmod a+x /var/runtime/bootstrap
  ``` 


## Provided Handlers
### EchoLambdaHandlerService
This handler will echo the event `body` value to Lambda's output. Useful for testing your container and Lambda configuration.
```
{
    "body": "hello world"
}
```

### SqsServiceFunctionHandlerService
This handler can call any public function on any service available via Symfony's dependency injection container. It expects SQS messages in the format:
```
{
    "serviceName": "AppBundle\\Service\\EchoService",
    "function": "echo", 
    "args": [
        "hello"
    ]
}
```
- `serviceName`: any service identifier valid with `Container::get()`. This will likely be a service class name.
- `function`: the function name
- `args`: an array of arguments to the function


