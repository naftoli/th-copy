<html>
<head>
    <script src="js/jquery-1.8.1.min.js"></script>
    <script>
        $(".create_voucher").click( function() {
            const admin = $(this).parent().parent().find('.refund').attr('id')
            const amount = parseFloat($(this).parent().find('.voucher').val())
            if (amount) {
                const that = this;
                $.post('createVoucher.php', { admin: admin, amount: amount }, function(res) {
                    if (res.success) {
                        alert('Voucher created.')
                        $(that).after(res.info)
                    } else {
                        alert(res.error)
                    }
                });
            }
        })
    </script>
</head>
<body>
</body>
</html>