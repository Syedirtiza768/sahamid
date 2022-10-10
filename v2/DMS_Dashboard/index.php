

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="sidebarStyles.css">
    <script src="js/jquery.min.js"></script>
    <title>DMS</title>
</head>

<body>
    <header>
    </header>
    <div class="container">

        <div class="sidebar">
            <nav>
                <h1 style="color: white;
                margin-left: 42px;
                margin-top: 30px;
                margin-bottom: 20px;
                font-size: 12px;
                text-decoration: underline;"> Document Managment System</h1>
                <ul>
                    <!-- <li class="active"><a href="#">Dashboard</a></li> -->
                    <li><a class='show-category'>Category</a></li>
                    <li><a class="user-page">Users</a></li>
                </ul>
            </nav>
        </div>

        <div class="main-content">
            <h1>Dashboard</h1>
            <p>Here you can manage users and create new categories!</p>

        </div>
    </div>



    <!-- Show users page -->-->
    <script>
        $(document).on('click', '.user-page', function() {
            window.location.replace('user/users.php');
        });
    </script>

    <!-- Show category page-->-->
    <script>
        $(document).on('click', '.show-category', function() {
            window.location.replace('category/category.php');
        });
    </script>
</body>

</html>