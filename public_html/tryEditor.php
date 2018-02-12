<?
$admin_auth = array('school'); 
require('header.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title></title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style> 
        	fieldset {
                border: 1px solid white;
                padding: 10px;
                padding-top: 0px;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
                font-size: 16px;
            }
            legend {
                margin-left: 20px;
                padding: 5px;
                color: purple;
            }
        </style>
        
        <script src="//tinymce.cachefly.net/4.0/tinymce.min.js"></script>
        <script>
        	tinymce.init({
				selector:'textarea'
			});
        </script>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
        <h1></h1>
        
        <form action="parent_letter2.php" method="post">	        
	        <div id='editFile'>
		        <fieldset>
		        	<legend>Edit File</legend>
		        	<textarea rows="50" cols="85" id='editor'>
		        		<html>

<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<meta name=Generator content="Microsoft Word 12 (filtered)">
<style>
<!--
 /* Font Definitions */
 @font-face
	{font-family:Wingdings;
	panose-1:5 0 0 0 0 0 0 0 0 0;}
@font-face
	{font-family:"Cambria Math";
	panose-1:2 4 5 3 5 4 6 3 2 4;}
@font-face
	{font-family:Calibri;
	panose-1:2 15 5 2 2 2 4 3 2 4;}
 /* Style Definitions */
 p.MsoNormal, li.MsoNormal, div.MsoNormal
	{margin-top:0in;
	margin-right:0in;
	margin-bottom:10.0pt;
	margin-left:0in;
	line-height:115%;
	font-size:11.0pt;
	font-family:"Calibri","sans-serif";}
p
	{margin-right:0in;
	margin-left:0in;
	font-size:12.0pt;
	font-family:"Times New Roman","serif";}
p.MsoListParagraph, li.MsoListParagraph, div.MsoListParagraph
	{margin-top:0in;
	margin-right:0in;
	margin-bottom:10.0pt;
	margin-left:.5in;
	line-height:115%;
	font-size:11.0pt;
	font-family:"Calibri","sans-serif";}
p.MsoListParagraphCxSpFirst, li.MsoListParagraphCxSpFirst, div.MsoListParagraphCxSpFirst
	{margin-top:0in;
	margin-right:0in;
	margin-bottom:0in;
	margin-left:.5in;
	margin-bottom:.0001pt;
	line-height:115%;
	font-size:11.0pt;
	font-family:"Calibri","sans-serif";}
p.MsoListParagraphCxSpMiddle, li.MsoListParagraphCxSpMiddle, div.MsoListParagraphCxSpMiddle
	{margin-top:0in;
	margin-right:0in;
	margin-bottom:0in;
	margin-left:.5in;
	margin-bottom:.0001pt;
	line-height:115%;
	font-size:11.0pt;
	font-family:"Calibri","sans-serif";}
p.MsoListParagraphCxSpLast, li.MsoListParagraphCxSpLast, div.MsoListParagraphCxSpLast
	{margin-top:0in;
	margin-right:0in;
	margin-bottom:10.0pt;
	margin-left:.5in;
	line-height:115%;
	font-size:11.0pt;
	font-family:"Calibri","sans-serif";}
span.apple-tab-span
	{mso-style-name:apple-tab-span;}
.MsoPapDefault
	{margin-bottom:10.0pt;
	line-height:115%;}
@page Section1
	{size:8.5in 11.0in;
	margin:1.0in 1.0in 1.0in 1.0in;}
div.Section1
	{page:Section1;}
 /* List Definitions */
 ol
	{margin-bottom:0in;}
ul
	{margin-bottom:0in;}
-->
</style>

</head>

<body lang=EN-US>

<div class=Section1>

<p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
normal;background:white;vertical-align:baseline'><b><span style='font-size:
16.5pt;font-family:"Arial","sans-serif";color:#222222;background:white'>Welcome!</span></b></p>

<p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
normal;background:white;vertical-align:baseline'><b><span style='font-size:
16.5pt;font-family:"Arial","sans-serif";color:black'>&nbsp;</span></b></p>

<p class=MsoListParagraphCxSpFirst style='margin-bottom:0in;margin-bottom:.0001pt;
text-indent:-.25in;line-height:normal'><span style='font-size:12.0pt;
font-family:Symbol;color:black'>·<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</span></span><span dir=LTR></span><b><span style='font-size:16.5pt;font-family:
"Arial","sans-serif";color:black;background:white'>Chanukah Mivtzayim!</span></b></p>

<p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:1.0in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;background:white;vertical-align:baseline'><span
style='font-size:16.5pt;font-family:"Courier New";color:black'>o<span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp; </span></span><span dir=LTR></span><b><span
style='font-size:16.5pt;font-family:"Arial","sans-serif";color:black;
background:white'>Send us reports and pictures</span></b></p>

<p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
normal'><span style='font-size:12.0pt;font-family:"Times New Roman","serif";
color:black'><br>
<br>
</span></p>

<p class=MsoListParagraphCxSpFirst style='margin-bottom:0in;margin-bottom:.0001pt;
text-indent:-.25in;line-height:normal;background:white;vertical-align:baseline'><span
style='font-size:16.5pt;font-family:Symbol;color:black'>·<span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span><span
dir=LTR></span><b><span style='font-size:16.5pt;font-family:"Arial","sans-serif";
color:black;background:white'>Tanya </span></b></p>

<p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:1.0in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;background:white;vertical-align:baseline'><span
style='font-size:16.5pt;font-family:"Courier New";color:black'>o<span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp; </span></span><span dir=LTR></span><b><span
style='font-size:16.5pt;font-family:"Arial","sans-serif";color:black;
background:white'>In honor of Chof Daled Teves - 200,000</span></b></p>

<p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:1.0in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;background:white;vertical-align:baseline'><span
style='font-size:16.5pt;font-family:"Courier New";color:black'>o<span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp; </span></span><span dir=LTR></span><b><span
style='font-size:16.5pt;font-family:"Arial","sans-serif";color:black;
background:white'>School commitment on homepage</span></b></p>

<p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:1.0in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;background:white;vertical-align:baseline'><span
style='font-size:16.5pt;font-family:"Courier New";color:black'>o<span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp; </span></span><span dir=LTR></span><b><span
style='font-size:16.5pt;font-family:"Arial","sans-serif";color:black;
background:white'>Daily mission and fill it out every week!</span></b></p>

<p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:1.0in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;background:white;vertical-align:baseline'><span
style='font-size:16.5pt;font-family:"Courier New";color:black'>o<span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp; </span></span><span dir=LTR></span><b><span
style='font-size:16.5pt;font-family:"Arial","sans-serif";color:black;
background:white'>Tanya Checkpoint</span></b></p>

<p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:1.0in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;background:white;vertical-align:baseline'><span
style='font-size:16.5pt;font-family:"Courier New";color:black'>o<span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp; </span></span><span dir=LTR></span><b><span
style='font-size:16.5pt;font-family:"Arial","sans-serif";color:black;
background:white'>Tanya Report</span></b></p>

<p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:1.0in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;background:white;vertical-align:baseline'><span
style='font-size:16.5pt;font-family:"Courier New";color:black'>o<span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp; </span></span><span dir=LTR></span><b><span
style='font-size:16.5pt;font-family:"Arial","sans-serif";color:black;
background:white'>Can include students not registered in T.H.</span></b></p>

<p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:1.0in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;background:white;vertical-align:baseline'><span
style='font-size:16.5pt;font-family:"Courier New";color:black'>o<span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp; </span></span><span dir=LTR></span><b><span
style='font-size:16.5pt;font-family:"Arial","sans-serif";color:black;
background:white'>Model School- Bnos Menachem</span></b></p>

<p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
normal'><span style='font-size:12.0pt;font-family:"Times New Roman","serif"'><br>
<br>
</span></p>

<p class=MsoListParagraphCxSpFirst style='margin-bottom:0in;margin-bottom:.0001pt;
text-indent:-.25in;line-height:normal'><span style='font-size:12.0pt;
font-family:Symbol'>·<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</span></span><span dir=LTR></span><b><span style='font-size:16.5pt;font-family:
"Arial","sans-serif";color:#222222;background:white'>Discussion: Rabbi Perl
animated cd- can we get the buying power through the schools to make this
possible?<br>
</span></b><span style='font-size:12.0pt;font-family:"Times New Roman","serif"'><br>
<br>
</span></p>

<p class=MsoListParagraphCxSpMiddle style='margin-bottom:0in;margin-bottom:
.0001pt;text-indent:-.25in;line-height:normal'><span style='font-size:12.0pt;
font-family:Symbol'>·<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</span></span><span dir=LTR></span><b><span style='font-size:16.5pt;font-family:
"Arial","sans-serif";color:#222222;background:white'>Resources</span></b></p>

<p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:1.0in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;background:white;vertical-align:baseline'><span
style='font-size:16.5pt;font-family:"Courier New";color:#222222'>o<span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp; </span></span><span dir=LTR></span><b><span
style='font-size:16.5pt;font-family:"Arial","sans-serif";color:#222222;
background:white'>Yud Shvat</span></b></p>

<p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:1.0in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;background:white;vertical-align:baseline'><span
style='font-size:16.5pt;font-family:"Courier New";color:#222222'>o<span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp; </span></span><span dir=LTR></span><b><span
style='font-size:16.5pt;font-family:"Arial","sans-serif";color:#222222;
background:white'>100 years since Rebbetzin Rivka’s Yarzheit</span></b></p>

<p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:1.0in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;background:white;vertical-align:baseline'><span
style='font-size:16.5pt;font-family:"Courier New";color:#222222'>o<span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp; </span></span><span dir=LTR></span><b><span
style='font-size:16.5pt;font-family:"Arial","sans-serif";color:#222222;
background:white'>Frierdiker Rebbe’s Histalkus and Rebbe’s Nisuis</span></b></p>

<p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:1.0in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;background:white;vertical-align:baseline'><span
style='font-size:16.5pt;font-family:"Courier New";color:#222222'>o<span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp; </span></span><span dir=LTR></span><b><span
style='font-size:16.5pt;font-family:"Arial","sans-serif";color:#222222;
background:white'>Basi Legani</span></b></p>

<p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:1.0in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;background:white;vertical-align:baseline'><span
style='font-size:16.5pt;font-family:"Courier New";color:#222222'>o<span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp; </span></span><span dir=LTR></span><b><span
style='font-size:16.5pt;font-family:"Arial","sans-serif";color:#222222;
background:white'>Yud Shvat Rally (Ches Shvat)</span></b></p>

<p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:1.0in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;background:white;vertical-align:baseline'><span
style='font-size:16.5pt;font-family:"Courier New";color:#222222'>o<span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp; </span></span><span dir=LTR></span><b><span
style='font-size:16.5pt;font-family:"Arial","sans-serif";color:#222222;
background:white'>In general</span></b></p>

<p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
normal'><span style='font-size:12.0pt;font-family:"Times New Roman","serif"'><br>
<br>
</span></p>

<p class=MsoListParagraph style='margin-bottom:0in;margin-bottom:.0001pt;
text-indent:-.25in;line-height:normal'><span style='font-size:12.0pt;
font-family:Symbol'>·<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</span></span><span dir=LTR></span><b><span style='font-size:16.5pt;font-family:
"Arial","sans-serif";color:#222222;background:white'>Discussion: Should we
include Siyum Harambam in the rally on Ches Shvat?</span></b></p>

<p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
normal'><span style='font-size:12.0pt;font-family:"Times New Roman","serif"'><br>
<br>
</span></p>

<p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
normal'><b><span style='font-size:16.5pt;font-family:"Arial","sans-serif";
color:#222222;background:white'>&nbsp;</span></b></p>

<p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
normal'><b><span style='font-size:16.5pt;font-family:"Arial","sans-serif";
color:#222222;background:white'>&nbsp;</span></b></p>

<p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
normal'><b><span style='font-size:16.5pt;font-family:"Arial","sans-serif";
color:#222222;background:white'>&nbsp;</span></b></p>

<p class=MsoListParagraph style='margin-bottom:0in;margin-bottom:.0001pt;
text-indent:-.25in;line-height:normal'><span style='font-size:12.0pt;
font-family:Symbol'>·<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</span></span><span dir=LTR></span><b><span style='font-size:16.5pt;font-family:
"Arial","sans-serif";color:#222222;background:white'>Limud and Chidon -
&nbsp;&nbsp;</span></b></p>

<p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
margin-left:.75in;margin-bottom:.0001pt;text-indent:-.25in;line-height:normal;
background:white;vertical-align:baseline'><span style='font-size:10.0pt;
font-family:"Courier New";color:black'>o<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;
</span></span><span dir=LTR></span><b><span style='font-size:16.5pt;font-family:
"Arial","sans-serif";color:#222222;background:white'>Even if you are not making
it a part of your school, at least have children learn it to represent your
school at the chidon.</span></b></p>

<p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
margin-left:.75in;margin-bottom:.0001pt;text-indent:-.25in;line-height:normal;
background:white;vertical-align:baseline'><span style='font-size:10.0pt;
font-family:"Courier New";color:black'>o<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;
</span></span><span dir=LTR></span><b><span style='font-size:16.5pt;font-family:
"Arial","sans-serif";color:#222222;background:white'>Promotion Video</span></b></p>

<p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
margin-left:.75in;margin-bottom:.0001pt;text-indent:-.25in;line-height:normal;
background:white;vertical-align:baseline'><span style='font-size:10.0pt;
font-family:"Courier New";color:black'>o<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;
</span></span><span dir=LTR></span><b><span style='font-size:16.5pt;font-family:
"Arial","sans-serif";color:#222222;background:white'>link available on
mashpia.com</span></b></p>

<p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
margin-left:.75in;margin-bottom:.0001pt;text-indent:-.25in;line-height:normal;
background:white;vertical-align:baseline'><span style='font-size:10.0pt;
font-family:"Courier New";color:black'>o<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;
</span></span><span dir=LTR></span><b><span style='font-size:16.5pt;font-family:
"Arial","sans-serif";color:#222222;background:white'>Brochure</span></b></p>

<p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
margin-left:.75in;margin-bottom:.0001pt;text-indent:-.25in;line-height:normal;
background:white;vertical-align:baseline'><span style='font-size:10.0pt;
font-family:"Courier New";color:black'>o<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;
</span></span><span dir=LTR></span><b><span style='font-size:16.5pt;font-family:
"Arial","sans-serif";color:#222222;background:white'>Quizzes in Dropbox (link
on homepage)</span></b></p>

<p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
margin-left:.75in;margin-bottom:.0001pt;text-indent:-.25in;line-height:normal;
background:white;vertical-align:baseline'><span style='font-size:10.0pt;
font-family:"Courier New";color:black'>o<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;
</span></span><span dir=LTR></span><b><span style='font-size:16.5pt;font-family:
"Arial","sans-serif";color:#222222;background:white'>Mark the Quizzes</span></b></p>

<p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
normal'><span style='font-size:12.0pt;font-family:"Times New Roman","serif"'><br>
<br>
</span></p>

<p class=MsoListParagraph style='margin-bottom:0in;margin-bottom:.0001pt;
text-indent:-.25in;line-height:normal'><span style='font-size:12.0pt;
font-family:Symbol'>·<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</span></span><span dir=LTR></span><b><span style='font-size:16.5pt;font-family:
"Arial","sans-serif";color:red;background:white'>Discussion: End of year
auction </span></b></p>

<p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
normal'><span style='font-size:12.0pt;font-family:"Times New Roman","serif"'><br>
<br>
</span></p>

<ul style='margin-top:0in' type=disc>
 <li class=MsoNormal style='color:#222222;margin-bottom:0in;margin-bottom:.0001pt;
     line-height:normal;background:white;vertical-align:baseline'><b><span
     style='font-size:16.5pt;font-family:"Arial","sans-serif";background:white'>Upcoming
     Rally</span></b></li>
</ul>

<p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:.75in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;background:white;vertical-align:baseline'><span
style='font-size:16.5pt;font-family:"Courier New";color:#222222'>o<span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp; </span></span><span dir=LTR></span><b><span
style='font-size:16.5pt;font-family:"Arial","sans-serif";color:#222222;
background:white'>Promotion Pictures </span></b></p>

<p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:.75in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;background:white;vertical-align:baseline'><span
style='font-size:16.5pt;font-family:"Courier New";color:#222222'>o<span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp; </span></span><span dir=LTR></span><b><span
style='font-size:16.5pt;font-family:"Arial","sans-serif";color:#222222;
background:white'>Please dont have us run after you!</span></b></p>

<p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
normal'><span style='font-size:12.0pt;font-family:"Times New Roman","serif"'><br>
<br>
</span></p>

<ul style='margin-top:0in' type=disc>
 <li class=MsoNormal style='color:#222222;margin-bottom:0in;margin-bottom:.0001pt;
     line-height:normal;background:white;vertical-align:baseline'><b><span
     style='font-size:16.5pt;font-family:"Arial","sans-serif";background:white'>Discussion:
     Should we send out the winning poster now or wait till next year as the
     introduction poster for the Poster Contest?</span></b></li>
</ul>

<p class=MsoNormal style='margin-bottom:0in;margin-bottom:.0001pt;line-height:
normal'><span style='font-size:12.0pt;font-family:"Times New Roman","serif"'><br>
<br>
</span></p>

<ul style='margin-top:0in' type=disc>
 <li class=MsoNormal style='color:black;margin-bottom:0in;margin-bottom:.0001pt;
     line-height:normal;vertical-align:baseline'><b><span style='font-size:
     16.5pt;font-family:"Arial","sans-serif";color:#222222;background:white'>Follow
     up</span></b><b><span style='font-size:16.5pt;font-family:"Arial","sans-serif";
     color:#222222'>       </span></b></li>
</ul>

<p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:1.0in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;vertical-align:baseline'><span style='font-size:10.0pt;
font-family:"Courier New";color:black'>o<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;
</span></span><span dir=LTR></span><b><span style='font-size:16.5pt;font-family:
"Arial","sans-serif";color:#222222;background:white'>Binders / Sticker Boards
$30,000</span></b></p>

<p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:1.0in;margin-bottom:.0001pt;text-indent:-.25in;
line-height:normal;vertical-align:baseline'><span style='font-size:10.0pt;
font-family:"Courier New";color:black'>o<span style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;
</span></span><span dir=LTR></span><b><span style='font-size:16.5pt;font-family:
"Arial","sans-serif";color:#222222;background:white'>Shabbos Mevarchim Tehillim</span></b></p>

<p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
margin-bottom:0in;margin-left:1.0in;margin-bottom:.0001pt;line-height:normal;
vertical-align:baseline'><b><span style='font-size:16.5pt;font-family:"Arial","sans-serif";
color:#222222;background:white'> </span></b></p>

<ul style='margin-top:0in' type=disc>
 <li class=MsoNormal style='color:#222222;margin-bottom:0in;margin-bottom:.0001pt;
     line-height:normal;background:white;vertical-align:baseline'><b><span
     style='font-size:16.5pt;font-family:"Arial","sans-serif";background:white'>Questions/Comments</span></b></li>
</ul>

<p class=MsoNormal>&nbsp;</p>

</div>

</body>

</html>

		        		
		        	</textarea>
		        </fieldset>
		    </div>
	    </form>
	</body>
</html>