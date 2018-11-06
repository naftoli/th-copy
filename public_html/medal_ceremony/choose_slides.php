<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            fieldset {
                width: 50%;
                margin: auto;
                padding: 15px;
                border-radius: 20px;
                border: 2px solid #73AD21; 
            }
            legend {
                margin-left: 20px;
            }
        </style>
    </head>
    <body>
        <form method="post" action="slides.php">
            <fieldset>
                <legend>Slide Options</legend>
                <input type="radio" name="type" value="1" checked /> Show only current medals earned<br />
                <input type="radio" name="type" value="2" /> Show all medals earned<br />
                <input type="radio" name="type" value="3" /> Show all medals earned but show old medals as greyed out<br /><br />
                <input type="submit" name="submit" />
            </fieldset>
        </form>
    </body>
</html>