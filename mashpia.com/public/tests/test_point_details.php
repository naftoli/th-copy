<!DOCTYPE html>
<html>
    <head>
        <style>
            tr, th, td {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 14px;
                padding: 5px;
            }
        </style>
        <script
            src="https://code.jquery.com/jquery-1.12.4.min.js"
            integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
            crossorigin="anonymous"></script>
        <script>
            const url = location.href
            const pos = url.indexOf('user=')
            if (pos > 0) {
                const user = url.substring(pos+5)
                const start = '2020-03-05'
                const end = '2020-05-05'
                let info = []
                let html = `
                    <table>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Card</th>
                            <th>Points</th>
                            <th>Balance</th>
                        </tr>
                    `
                if (user > 0) {
                    $.post('/api/points/details.php', { start: start, end: end, user_id: user }, function(result) {
                        const res = JSON.parse(result)
                        if (res.length) {
                            for (let r of res) {
                                const date = r.date
                                const type = r.type
                                const name = r.name
                                const card = r.card
                                const points = r.points
                                info.push({
                                    date,
                                    type,
                                    name,
                                    card,
                                    points
                                })
                            }
                            console.log(info)
                            points = 0
                            for (let i of info) {
                                points += i.points
                                html += `<tr><td>${i.date}</td><td>${i.type}</td><td>${i.name}</td><td>${i.card}</td><td>${i.points}</td><td>${points}</td></tr>`
                            }
                            html += '</table>'
                        }
                        $("#main").html(html)
                    })
                }
            }
        </script>
    </head>
    <body>
        <div id="main">
            
        </div>
    </body>
</html>