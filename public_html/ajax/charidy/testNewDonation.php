<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
        <script>
            var obj = {
                "name": "donor name",
                "donor_id": 1,
                "parent_id": 2,
                "total_donation_amount": 310,
                "children": [
                    {
                        "amount": 10,
                        "user_id": 3,
                        "name": "name",
                        "first_name": "Avi",
                        "last_name": "B",
                        "picture": "https://secure.img1-ag.wfcdn.com/im/45185536/resize-h800%5Ecompr-r85/4307/43074449/Hanging+Pug+Puppy+Statue.jpg",
                        "school": "School1",
                        "school_id": "269"
                    }
                ],
                "donation_last_year": 100,
                "donation_suggested_this_year": 200,
                "address": "address line 1",
                "phone_number": "123",
                "dedication_text": "",
                "dedication_name": "donor name",
                "date_time": "2018-04-30T19:47:49.871Z"
            };
            $.post('newDonation.php', { info : obj }, function( success ) {
                alert( success );
            });
        </script>
    </head>
    <body>
        
    </body>
</html>