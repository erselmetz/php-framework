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
            if($data != null){
                foreach($data['post'] as $post){
                    echo $post['post_content'].'<br>';
                }
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