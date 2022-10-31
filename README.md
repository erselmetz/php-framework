# php-mvc-framework

This is a small PHP mvc framework and it works very simple 

## How to use?

1. First is that there is no routes file unlike laravel or other php frameworks.

2. Let's take a look in a simple url.

        https:example.com/home/test1/params1/params2/params3
    
3. The first parameter of the url will going to find if it's exist in the controller folder.

4. The name of the url should be the same to the file and the class name as well.

    ![](./Public/img/pic1.png)

5. If the first parameter did not exists on the controllers or class then it will automatically open the home as the default class and the first parameter will be set as a method.

### Example

        https://example.com/test1/params1/params2/params3


6. If the first parameter is exists on class and the second parameter did not exists on method then it will automatically execute index as the default method

### Example

        https://example.com/home/params1/params2/params3

## This is the example to get the value of POST and GET method

<img src="./Public/img/controller.png" width='650'>

## Example #1

        https://example.com/home/test1/params1/params2/params3

        home - name of the class
        test1 - name of the method
        params1, params2, params3 - the parameter

## Example #2

        https://example.com/test1/params1/params2/params3

        home - default class
        test1 - name of the method
        params1, params2, params3 - the parameter

## Example #3

        https://example.com/params1/params2/params3

        home - default class
        test1 - default method
        params1, params2, params3 - the parameter