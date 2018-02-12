<!DOCTYPE html>
    <html>
        <head>
            <meta charset="UTF-8" />
        </head>
        
        <body>
            <form action="getSchools.php" method="post">
                <input type="hidden" name="key" value="th5776" />
                <input type="submit" value="Get Schools" />
            </form>
            <br />
            <form action="getStudents.php" method="post">
                <input type="hidden" name="key" value="th5776" />
                <input type="hidden" name="id" value="61" />
                <input type="hidden" name="name" value="men" />
                <input type="submit" value="Get Students" />
            </form>
        </body>
    </html>