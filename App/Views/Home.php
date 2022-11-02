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
        Welcome this is a Simple PHP Framework
    </h1>

    
        <?php
            foreach($data as $user){
                echo $user['post_content'];
            }
        ?>

    <button id="btn_submit">submit</button>

    <script src="<?= assets('js/jquery.js') ?>"></script>
    <script>
        $('#btn_submit').click(function() {
            $.ajax({
                type: "POST",
                url: "home/test",
                data: {
                    test: 'this is a string',
                    test1: false,
                    test2: 1234566789
                },
                success: function (response) {

                    console.log(response);
                }
            });
        });
    </script>
</body>
</html>