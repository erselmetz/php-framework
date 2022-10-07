# php-mvc-framework

This is a small PHP mvc framework and it works very simple 

## How to use?

- First is that there is no routes file unlike laravel or other php frameworks

- Let's take a look in a simple url

        https:example.com/class/method/params1/params2/
    
- The first parameter of the url (which is class) will going to find if it's exist in the controller folder.

- The name of the url should be the same to the file (file extension is not included), and the class name as well

    ![](./public/img/pic1.png)

- If it's exist (class) then it will open it or it run the class and it will execute the method base on the second parameter (which is the params1).

- If the first parameter (which is class) did not exists on the controller then it will be automatically open the home as the default, and the first parameter will become the method

## Example

