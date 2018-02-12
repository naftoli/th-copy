<!DOCTYPE html>
<html>
    <head>
        <script>
            var cart = [{'test':1},{'test':2}];
            localStorage.setItem('cart', JSON.stringify(cart));
            var local = JSON.parse( localStorage.getItem('cart') );
            local.push({'test':3});
            localStorage.setItem('cart', JSON.stringify(local));
            var cart = JSON.parse( localStorage.getItem('cart') );
            alert(cart);
        </script>
    </head>
    
</html>