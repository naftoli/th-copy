<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            @font-face {
                font-family: proxima;
                src: url('fonts/proxima.otf');
            }
            @font-face {
                font-family: proximab;
                src: url('fonts/proxima-bold.otf');
            }
            
            .postcard {
                text-align: center;
                height: 7.5in;
                width: 6.25in;
                position: relative;
            }
            
            .postcard img {
                max-height: 100%;
                max-width: 100%;
            }
            
            p {
                font-family: proxima;
                font-size: 14px;
            }
            
            .rank {
                font-family: proximab;
                width: 2.5in;
                margin-top: 250px;
            }
            
            .email {
                position: absolute;
                top: 6.4in;
                font-size: 18px;
                width: 6.25in;
                left: 0;
                margin: auto;
            }
            
            .mailingID {
                position: absolute;
                top: 6.9in;
                font-size: 12px;
                width: 6.25in;
                text-align: right;
                color: #fff;
                right: 25px;
            }
        </style>
    </head>
    <body>
        <?php for ($i = 0; $i < 200; $i++) : ?>
            <div class="postcard">
                <img src="Charidy-5777.jpg" />
            </div>
            <div style="page-break-after: always;"></div>
            
            <div class="postcard">
                <p>
                    Dear &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;,
                </p>
                
                <p>
                    Wow, what a year it's been at Tzivos Hashem,<br />
                    thanks to your generous contribution.
                </p>
                
                <p>
                    Read on to see the impact your investment has made on our soldiers.
                </p>
                
                <p>
                    This year, we are challenging ourselves to do even more.
                </p>
                
                <img src="Charidy-5777-3.jpg" style="margin-bottom: -20px;" />
    
                <p class="rank" style="float: left; margin-left: 50px;">
                    Last year, you gave the generous gift of $ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; earning you the honor of
                </p>
                
                <p class="rank" style="float: right; margin-right: 50px;">
                    This year, can you grow your rank to &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    by giving $ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;?
                </p>
                
                <img src="Charidy-5777-2.jpg" style="margin-top: 20px;" />
                
                <p class="email">
                    
                </p>
                
                <p class="mailingID">
                    
                </p>
            </div>
            <div style="page-break-after: always;"></div>
        <?php endfor; ?>
    </body>
</html>