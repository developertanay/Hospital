
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Form</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 20%;
            height: auto;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            font-weight: bold;
        }

        input, select {
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            background-color: white;
        }

        textarea {
            font-size: 16px;
        }

        .rating {
            margin-bottom: 16px;
        }

        .stars {
            display: flex;
            justify-content: center;
        }

        .star {
            font-size: 34px;
            cursor: pointer;
            color: #ccc;
            transition: color 0.3s;
            margin: 0 2px;
        }

        .star.active, .star:hover {
            color: #ffd700; /* Yellow color */
        }

        input[type="submit"] {
            background-color: #3f3f97;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: darkblue;
        }
        @media screen and (max-width: 600px) {
            .container {
                margin: 20px; /* Add margin on both sides */
            }
            .logo img {
            max-width: 30%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="http://college.msell.in/public/assets/images/logos/Logo_LBC.png" alt="Logo">
        </div>
        <h1 style="text-align:center; color: #3f3f97;">FEEDBACK</h1>
        <h3 style="text-align:center; margin-top:-1rem; color: #3f3f97;">(New College Portal Workshop 22 Nov. 2023)</h3>
        <form action="{{url('submit_feedback')}}" method="POST">
            @csrf
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>

            <label for="name">Department</label>
            <select class="form-select" name="dept">
                <option value="">Select</option>
                 @foreach($dept_arr as $key => $value)
                    <option value="{{$key}}">{{$value}}</option>
                @endforeach
            </select>


            <label for="response">Suggestion/Comments</label>
            <textarea id="response" name="response" rows="4" required></textarea>

            <div class="rating">
                <label for="rating"></label>
                <div class="stars" id="stars">
                    <span class="star" data-rating="1">&#9733;</span>
                    <span class="star" data-rating="2">&#9733;</span>
                    <span class="star" data-rating="3">&#9733;</span>
                    <span class="star" data-rating="4">&#9733;</span>
                    <span class="star" data-rating="5">&#9733;</span>
                </div>
                <input type="hidden" name="rating" id="rating">
            </div>

            <input type="submit" class="" value="Submit">
        </form>
    </div>

    <script>
        const stars = document.querySelectorAll('.star');
        const ratingInput = document.getElementById('rating');

        stars.forEach((star) => {
            star.addEventListener('click', () => {
                const rating = star.getAttribute('data-rating');
                ratingInput.value = rating;

                stars.forEach((s) => {
                    if (s.getAttribute('data-rating') <= rating) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
        });
    </script>
</body>
</html>