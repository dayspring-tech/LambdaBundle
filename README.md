# DayspringLambdaBundle - Run Symfony on Lambda

This bundle provides tooling for using Lambda to run Symfony code. Your application will run as a [custom Lambda runtime](https://docs.aws.amazon.com/lambda/latest/dg/runtimes-custom.html)

This is based on bref's [console example bootstrap script](https://github.com/brefphp/bref/blob/master/runtime/layers/console/bootstrap) and removes the need to use only console commands. It also boots the Symfony kernel once for the lifetime of the Lambda worker.

bref provides a Lambda runtime API client implementation. This handles communication with the [Lambda runtime API](https://docs.aws.amazon.com/lambda/latest/dg/runtimes-api.html) 


## Using this bundle
### 1. Add this bundle to your Symfony project.
- Add this package to your project by running `composer require dayspring-tech/lambda-bundle`
- Package your project in a docker image.
- Add `Bootstrap/service.php` to your image as `/var/run/bootstrap`, and make it executable. This is the foundation of your custom Lambda runtime.
  ``` 
  # this is really what does the magic
  COPY symfony/vendor/dayspring-tech/lambda-bundle/Bootstrap/service.php /var/runtime/bootstrap
  RUN chmod a+x /var/runtime/bootstrap
  ``` 

### 2. Create a class that implements `LambdaHandlerServiceInterface`
- Create a class that implements `LambdaHandlerServiceInterface` and register it with Symfony's dependency injection container. It should be marked as public so it can be fetched by name. 
- Implement the `handle()` function
  - The custom runtime will call the `handle()` function for each Lambda event, passing the event object as the `$event` argument.
  - Lambda context information will be provided via the `$context` argument.
  - The custom runtime will provide an `OutputInterface` object with which you can provide any output that should be returned from the Lambda function. 

### 3. Register a Lambda function
- Create a Lambda function in AWS using your docker image. Set the Lambda command to the service name.


## Background on the custom Lambda runtimes and the Lambda Runtime API
If there's a `/var/run/bootstrap` file in your docker image, Lambda will execute it. This is your custom runtime.
1. The Lambda runtime API exposes three APIs. Your custom runtime should call the `/next` API to request the next event.
2. Your custom runtime should execute code to handle the event. For LambdaBundle, this is calling the `handle()` function on the `LambdaHandlerServiceInterface` specified via the Lambda function's command parameter.
3. If successful, your custom runtime calls the `/response` API and POSTs the function's response.
4. On an error, your custom runtime calls the `/error` API with details of the error. 

See also:
- https://docs.aws.amazon.com/lambda/latest/dg/runtimes-custom.html
- https://docs.aws.amazon.com/lambda/latest/dg/runtimes-api.html


## Provided Handlers
The following handlers are provided as examples or common implementations of `LambdaHandlerServiceInterface`

### EchoLambdaHandlerService
This handler will echo the event `body` value to Lambda's output. Useful for testing your container and Lambda configuration.
```
{
    "body": "hello world"
}
```

### ServiceFunctionHandlerService
This handler can handle lambda events and call any public function on any service available via Symfony's dependency injection container.

It expects a lambda event in the format:
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


### SqsServiceFunctionHandlerService
This handler does the same thing as `ServiceFunctionHandlerService` but for messages sent via SQS

It expects payloads to be sent via SQS instead of directly to Lambda.

### Other implementation possibilities
- API Gateway - Configure a Lambda integration with API Gateway and use Symfony to handle API requests
- SNS - Subscribe Lambda to a SNS topic and handle messages from SNS
- Custom events - Configure Lambda as a task within Step Functions
