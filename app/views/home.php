<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
</head>
<body>
    <h1>
        Welcome this is a Simple PHP MVC Framework
    </h1>

    <button id="btn_submit">submit</button>

    <script src="<?php assets('js/jquery.js') ?>"></script>
    <script>
        $('#btn_submit').click(function() {
            $.ajax({
                type: "POST",
                url: "app/post/test.php",
                data: {
                    test:'test'
                },
                success: function (response) {
                    console.log(response)
                }
            });
        });
    </script>
</body>
</html>