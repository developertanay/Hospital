<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            text-align: center;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            color: #3f3f97;
        }

        p {
            font-size: 18px;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 20%;
            height: auto;
        }
        @media only screen and (max-width: 600px) {
            .container {
                margin: 20px; 
            }
            
        .logo img {
            max-width: 30%;
            height: auto;
        }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="http://college.msell.in/public/assets/images/logos/Logo_LBC.png" alt="Logo">
        </div>
        <h2>{{$message_top}}</h2>
        <p>{{$message}}</p>
        <a href="http://college.msell.in" class="btn btn-primary">Click Here To Proceed to the Portal</a>
    </div>
</body>
</html>