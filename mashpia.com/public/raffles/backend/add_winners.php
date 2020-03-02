<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
    </head>
    <body>
        
    </body>
    <script
        src="https://code.jquery.com/jquery-1.12.4.min.js"
        integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
        crossorigin="anonymous"></script>
    <script>
        const raffle_id = 189;
        const winners = [
            { a: 185, b: 7752444, c: 480 },
            { a: 185, b: 7756456, c: 222 },
            { a: 185, b: 7760598, c: 78 }, 
            { a: 81, b: 7764725, c: 198 }, 
            { a: 615, b: 7748532, c: 197 }, 
            { a: 89, b: 7753007, c: 87 },
            { a: 39, b: 7772497, c: 481 },
            { a: 84, b: 7761173, c: 346 }, 
            { a: 87, b: 7753505, c: 255 }, 
            { a: 194, b: 7752484, c: 476 }, 
            { a: 60, b: 7760229, c: 92 }, 
            { a: 3, b: 7756410, c: 405 }, 
            { a: 112, b: 7753057, c: 199 }
        ];
        for ( let w in winners ) {
            $.post("../monthly/ajax/set_winner.php", { 
                raffle_id: raffle_id, 
                school_id: winners[w].a, 
                serial_number: winners[w].b,
                prize_id: winners[w].c 
            }, function( result ) {
                const res = JSON.parse( result );
                if ( !res.success ) {
                    console.log( res.error );
                }
            });
        }
    </script>
</html>