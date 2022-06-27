<?php

    $pagetitle = 'Index';

    require('includes/header.php');

    if(!empty($_POST['submit'])) {
        $err = [];

        if (empty($_POST['query']))
            $err[] = "Please input a query.";
        if (!$loggedin)
            $err[] = "Please login.";

        if(empty($err)) {
            $mediapi = new Mediapi();

            $want = $_POST['type'];
            if ($want == "movie") {
                $res = $mediapi->queryMovies($_SESSION['token'], $_POST['query'], $_POST['year'], $_POST['scriptversion']);
            } else {
                $res = $mediapi->queryBooks($_SESSION['token'], $_POST['query']);
            }

            if(isset($res->err)) {
                $err[] = $res->err;
                $res = NULL;
            } else if(isset($res->Error)) {
                $err[] = $res->Error;
                $res = NULL;
            }
        }
    }
?>

<div class="medium">
    <h1>Mediapi</h1>
    <hr />
    <p>Either type a movie title or an ISBN in the box and hit 'search' to get<br/>the information about this media.</p>
    <?php

    if(isset($err)) {
        echo "<br/>";
        foreach($err as $e) {
            print("<p>" . $e . "</p><br/>");
        }
    }

    if(!$loggedin) { ?>
        <br/>
        <p><a href="user.php">Register an account or login to use the database.</a></p>
    <?php } else { ?>
        <br/>
        <p><a href="logout.php">Log out.</a></p>

        <form action="index.php" method="post">
            <p>I want <select name="type">
                <option value="movie">a movie</option>
                <option value="book">a book</option>
            </select>
            </p>
            <p>Query (Movie name or ISBN): <input type="text" name="query"/></p>
            <p>Year (only for movies): <input type="text" name="year"/></p>
            <p>Plot version (only for movies): <input type="text" name="scriptversion"/></p>
            <p><input type="submit" name="submit" /></p>
        </form>
    <?php }

    if (isset($res)) {
    ?>

    <hr />

    <h3><?php echo ucfirst($want); ?> info</h3>
    <div class="info">
    <?php if($want == "movie") { ?>
        <h4><?php echo $res->Title; ?> by <?php echo $res->Writer; ?></h4>
        <p>Featuring <?php echo $res->Actors; ?> and released on <?php echo $res->Released; ?>.</p><br/>
        <p><i><?php echo $res->Plot; ?></i></p><br/>
        <p>Ratings:</p>
        <ul>
            <?php
            foreach($res->Ratings as $rating) {
                echo "<li>" . $rating->Source . ": " . $rating->Value . "</li>";
            }
            ?>
        </ul><br/>

        <img src="<?php echo $res->Poster; ?>">
    <?php } else { 

        echo "<h4>" . $res->title;
        if (isset($res->edition_name))
            echo " " . $res->edition_name;
        if (isset($res->by_statement)) {
            echo " by ";
            echo $res->by_statement; 
        }
        echo "</h4>";
        echo "<p>Published in " . $res->publish_date . "</p><br/>";
        if(isset($res->description->value))
            echo "<p><i>" . $res->description->value . "</i></p><br/>";
        
        if(isset($res->notes->value)) {
            echo "<p>Notes: " . $res->notes->value . "</p><br/>";
        }

        if(isset($res->subjects)) {
            echo "<p>Subjects: ";

            foreach($res->subjects as $subject) {
                echo $subject . ", ";
            }
        }
        ?>
        </p><br/>
</div>

<?php
    }
    echo "</div>";
}

    require('includes/footer.php');
?>