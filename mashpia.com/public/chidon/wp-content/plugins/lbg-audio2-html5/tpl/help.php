<style type="text/css">
.lbg_subtitle {color:#21759b;
	font-weight:bold;
	font-size:14px;
}
.regGray {	font-weight:normal;
	color:#555555;
}
.regb {	font-weight:bold;
}
</style>
<div class="wrap">
<div id="lbg_logo">
			<h2>Help</h2>
  </div>
<p>This plugin requires at least WordPress 3.0</p>
<ul class="lbg_list-1">
  <li><a href="#videotutorials">Video Tutorials</a></li>
  <li><a href="#manage">Manage Players</a></li>
  <li><a href="#categories">Manage Categories</a></li>
  <li><a href="#settings">Player Settings</a></li>
  <li><a href="#playlist">Playlist</a></li>
  <li><a href="#shortcode">ShortCode</a></li>
  <li><a href="#facebook_share">Facebook Share</a></li>
  <li>.<a href="#htaccess">htaccess</a></li>
</ul>
<p>&nbsp;</p>
<p><span class="lbg_subtitle"><a name="videotutorials" id="videotutorials"></a>1. Video Tutorials</span></p>
<p>Installation – <a href="https://www.youtube.com/watch?v=BIZp-_MqSJ0" target="_blank">https://www.youtube.com/watch?v=BIZp-_MqSJ0</a><br />
Create a new player and manage the player settings – <a href="https://www.youtube.com/watch?v=ibja8DM_Zis" target="_blank">https://www.youtube.com/watch?v=ibja8DM_Zis</a><br />
Manage the playlist and categories – <a href="https://www.youtube.com/watch?v=dq8Gal0XGPU" target="_blank">https://www.youtube.com/watch?v=dq8Gal0XGPU</a><br />
  How To Automatically generate the player playlist by reading the folder which contains the MP3 files   – <a href="https://www.youtube.com/watch?v=iBOPpPxQqvw" target="_blank">https://www.youtube.com/watch?v=iBOPpPxQqvw</a></p>
<p>&nbsp;</p>
<p class="lbg_subtitle"><a name="manage" id="manage"></a>2. Manage Players</p>
<p class="">From this section you can:<br />
- add a new player <br />
-
select the player you want to edit by clicking &quot;Player Settings&quot;<br />
- add/edit playlist videos by clicking &quot;Playlist&quot;
<br />
- delete an existing player by clicking &quot;Delete&quot;
</p>
<p class="">&nbsp;</p>
<p class="lbg_subtitle"><a name="manage" id="manage"></a>3. Manage Categories</p>
<p class="">From this section you can:<br />
- add a new category <br />
 - edit an existing category
<br />
- delete an existing category
</p>
<p class="">&nbsp;</p>
<p class="lbg_subtitle"><a name="settings" id="settings"></a>4. Player Settings</p>
<p>From this section you can define the video player  settings.</p>
<table class="wp-list-table widefat fixed pages" cellspacing="0">
  <tr>
    <td width="30%" align="left" valign="top" class="row-title"></td>
    <td width="83%" align="left" valign="top"></td>
  </tr>
  <tr>
    <td colspan="2" align="left" valign="top" class="lbg_regGray">General settings</td>
  </tr>
  <tr style="border:1px solid #CCC;">
    <td align="left" valign="top" class="row-title"> Skin</td>
    <td align="left" valign="top">The audio player comes with 2 predefined controllers skins (white and black). By controllers we understand rewind, play, pause, previous, next, volume, shuffle, download, buy now, lyrics, facebook, twitter, show/hide playlist buttons. These buttons are saved as .png images. If you need another color for these controllers, you have the PSD files included and you can save over one skin, the controllers with another color.
      <p>Available values:<br />
        - whiteControllers<br />
        - blackControllers</p></td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Responsive<br /></td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - the player will responsive<br />
      <strong>false</strong> - the player will not be responsive</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Player Width</td>
    <td align="left" valign="top">player width</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Auto Play</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - autoplays audio file<br />
      <strong>false</strong> - doesn't autoplay audio file</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Loop</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - starts next audio file after current audio file has finished<br />
      <strong>false</strong> - doesn't start next audio file after current audio file has finished</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Shuffle</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - the playlist will be played in shuffle mode<br />
      <strong>false</strong> - the playlist will be played in normal mode</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Initial Volume<br /></td>
    <td align="left" valign="top">You can initialize the volume. The range is 0 to 1</td>
  </tr>
  <!--<tr class="wp-list-table widefat fixed pages">
    <td align="left" valign="top" class="row-title">Float</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>none</strong> - the audio player has no floating<br />
      <strong>left</strong> - the audio player floats left<br />
      <strong>true</strong> - the audio player floats right</td>
  </tr>-->
  <tr>
    <td align="left" valign="top" class="row-title">Player Background</td>
    <td align="left" valign="top">Player background color (hexa)</td>
  </tr>
  <tr>
    <td width="30%" align="left" valign="top" class="row-title">Empty Buffer Color</td>
    <td width="83%" align="left" valign="top">Player buffer color (hexa)- empty state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Full Buffer Color</td>
    <td align="left" valign="top">Player buffer color (hexa) - full state state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">SeekBar Color</td>
    <td align="left" valign="top">Seekbar color (hexa)</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Volume Off State Color</td>
    <td align="left" valign="top">Volume slide color (hexa) - off state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Volume On State Color</td>
    <td align="left" valign="top">Volume slide color (hexa) - on state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Timer Color</td>
    <td align="left" valign="top">Timer color (hexa)</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Song Title - Text Color</td>
    <td align="left" valign="top">Audio file Title text area color (hexa) which resides next to the image</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Song Author -Text Color</td>
    <td align="left" valign="top">Audio file Author text area color (hexa) which resides next to the image</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Small buttons area (rewind, shuffle etc.) Borders Color</td>
    <td align="left" valign="top">The borders color which <span id="result_box" lang="en" xml:lang="en">delineates</span> the small buttons area (rewind, shuffle etc.)</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Show Rewind Button</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - rewind button will appear<br />
      <strong>false</strong> - rewind button will not appear</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Show Shuffle Button</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - shuffle button will appear<br />
      <strong>false</strong> - shuffle button will not appear</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Show Download Button</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - download button will appear<br />
      <strong>false</strong> - download track button will not appear</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Show Buy Button</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - buy button will appear<br />
      <strong>false</strong> - buy button button will not appear</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Buy Button Title</td>
    <td align="left" valign="top">The buy button title</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Buy Button Target Window</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>'_blank'</strong> - the buy link will open in a new window<br />
      <strong>'_slef'</strong>- the buy link will open in the same window</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Show Lyrics Button</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - buy lyrics will appear<br />
      <strong>false</strong> - buy lyrics button will not appear</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Lyrics Button Title</td>
    <td align="left" valign="top">The lyrics button title</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Buy Lyrics Target Window</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>'_blank'</strong> - the lyrics link will open in a new window<br />
      <strong>'_slef'</strong>- the buy lyrics will open in the same window</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Show Twitter Button</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - twitter button will appear<br />
      <strong>false</strong> - twitter button will not appear</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Show Author</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - audio track  Author will appear<br />
      <strong>false</strong> - audio track  Author will not appear</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Show Title</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - audio track  Title will appear<br />
      <strong>false</strong> - audio track  Title will not appear</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Show FaceBook Button</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - facebook button will appear<br />
      <strong>false</strong> - facebook button will not appear</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">FaceBook AppID</td>
    <td align="left" valign="top">FaceBook AppID. Please check <a href="#facebook_share">Facebook Share</a> section, for more informations</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Face Book Share Title</td>
    <td align="left" valign="top">The title which will appear on FaceBook share. Please check <a href="#facebook_share">Facebook Share</a> section, for more informations</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">FaceBook Share Description</td>
    <td align="left" valign="top">The description which will appear on FaceBook share. Please check <a href="#facebook_share">Facebook Share</a> section, for more informations</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Preload</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>metadata</strong><br />
      <strong>auto </strong><br />
      <strong>none</strong></td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Activate Google Analytics Traking</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - Google Analytics tracking will be enabled<br />
      <strong>false</strong> - Google Analytics tracking will be disabled</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Your Google Analytics Traking Code</td>
    <td align="left" valign="top">Your  Google Analytics code.<br />
Example: UA-3245593-1</td>
  </tr>
  <tr>
    <td colspan="2" align="left" valign="top" class="lbg_regGray">Playlist  Settings</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Show Playlist On Init</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - playlist will appear on init<br />
      <strong>false</strong> - playlist will not appear on init</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Show Playlist Button</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - show/hide playlist button will appear<br />
      <strong>false</strong> - show/hide playlist  button will not appear</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Show Playlist</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - playlist will appear<br />
      <strong>false</strong> - playlist will not appear</td>
  </tr>
  <!--<tr>
    <td align="left" valign="top" class="row-title">Playlist Over Website Content</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - the playlist will appear over the website content<br />
      <strong>false</strong> - the playlist will not appear over the website content</td>
  </tr>-->
  <tr>
    <td align="left" valign="top" class="row-title">Playlist Top Position</td>
    <td align="left" valign="top">Playlist distance from the audio player</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Playlist Background Color</td>
    <td align="left" valign="top">Playlist background color (hexa)</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Playlist Record Background Off Color</td>
    <td align="left" valign="top">Playlist item background color (hexa) - OFF state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Playlist Record Background On Color</td>
    <td align="left" valign="top">Playlist item background color (hexa) - ON state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Playlist Record Bottom Border Off Color</td>
    <td align="left" valign="top">Playlist item bottom border color (hexa) - OFF state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Playlist Record Bottom Border On Color</td>
    <td align="left" valign="top">Playlist item bottom border color (hexa) - ON state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Playlist Record Text Off Color</td>
    <td align="left" valign="top">Playlist item text color (hexa) - OFF state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Playlist Record Text On Color</td>
    <td align="left" valign="top">Playlist item text color (hexa) - ON state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Number Of Items Per Screen</td>
    <td align="left" valign="top">Number of items shown in the playlist. You'll have to scroll to see the rest</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Playlist Padding</td>
    <td align="left" valign="top">Playlist inner padding</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Show Playlist Number</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - the item number in the playlist will appear<br />
      <strong>false</strong> - the item number in the playlist will not appear</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">&nbsp;</td>
    <td align="left" valign="top">&nbsp;</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Category Record Background Off Color</td>
    <td align="left" valign="top">Category item background color (hexa) - OFF state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Category Record Background On Color</td>
    <td align="left" valign="top">Category item background color (hexa) - ON state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Category Record Bottom Border Off Color</td>
    <td align="left" valign="top">Category item bottom border color (hexa) - OFF state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Category Record Bottom Border On Color</td>
    <td align="left" valign="top">Category item bottom border color (hexa) - ON state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Category Record Text Off Color</td>
    <td align="left" valign="top">Category item text color (hexa) - OFF state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Category Record Text On Color</td>
    <td align="left" valign="top">Category item text color (hexa) - ON state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">&nbsp;</td>
    <td align="left" valign="top">&nbsp;</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Show Categories</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - the categories will appear<br />
      <strong>false</strong> - the categories will not appear</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">First Selected Category</td>
    <td align="left" valign="top">The name of the first displayed category (in the top of the playlist). If no value is selected, since the categories will be alphabetically ordered,  the first one will be displayed as the first selected category</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Selected Categ Background Color</td>
    <td align="left" valign="top">Selected category background color (hexa)</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Selected Categ Off Color</td>
    <td align="left" valign="top">Selected category color (hexa) - OFF state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Selected Categ On Color</td>
    <td align="left" valign="top">Selected category color (hexa) - ON state</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Selected Category Bottom Margin </td>
    <td align="left" valign="top">Selected category bottom margin</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">&nbsp;</td>
    <td align="left" valign="top">&nbsp;</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Show Search Area</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - the search area will appear<br />
      <strong>false</strong> - the search area  will not appear </td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Search Area Background Color</td>
    <td align="left" valign="top">Search area  background color (hexa)</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Search Input Text</td>
    <td align="left" valign="top">Search input initial text, useful for translation purpose</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Search Input Background Color</td>
    <td align="left" valign="top">Search input  background color (hexa)</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Search Input Border Color</td>
    <td align="left" valign="top">Search input  border color (hexa)</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Search Input Text Color</td>
    <td align="left" valign="top">Search input  text color (hexa)</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Search Inside Author Field</td>
    <td align="left" valign="top">Possible values: <br />
      <strong>true</strong> - it will search in author filed, too<br />
      <strong>false</strong> - it will not search in author filed</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">&nbsp;</td>
    <td align="left" valign="top">&nbsp;</td>
  </tr>
</table>
<p>&nbsp;</p>
<p class="lbg_subtitle"><a name="playlist" id="playlist"></a>5. Playlist</p>
<p>From this section you can define audio files in the playlist</p>
<table class="wp-list-table widefat fixed pages" cellspacing="0">
  <tr>
    <td width="30%" align="left" valign="top" class="row-title"></td>
    <td width="70%" align="left" valign="top"></td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Title</td>
    <td align="left" valign="top">audio file title</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Author</td>
    <td align="left" valign="top">audio file author</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Category</td>
    <td align="left" valign="top">audio file category. An audio file can belong to multiple categories</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Playlist image</td>
    <td align="left" valign="top">audio file author <span class="row-title">playlist image</span></td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Buy Link</td>
    <td align="left" valign="top">buy link</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">Lyrics Link</td>
    <td align="left" valign="top">lyrics link</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">MP3 file (Chrome, IE, Safari) </td>
    <td align="left" valign="top">.mp3 file name.</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">OGG (Firefox &amp; Opera)</td>
    <td align="left" valign="top">.ogg file name.</td>
  </tr>
  <tr>
    <td align="left" valign="top" class="row-title">&nbsp;</td>
    <td align="left" valign="top">&nbsp;</td>
  </tr>
</table>
<p>&nbsp;</p>
<p><span class="lbg_subtitle"><a name="shortcode" id="shortcode"></a>6. ShortCode</span></p>
<p>The shortcode is: <br />
[lbg_audio2_html5 settings_id='1']<br />
where <br />
 settings_id is the player ID defined in &quot;Manage Players&quot; section</p>
<p>&nbsp;</p>
<p><span class="lbg_subtitle"><a name="facebook_share" id="facebook_share"></a>7. FaceBook Share</span></p>
<p>In order for the Facebook share button to work you need to obtain a Facebook Application ID</p>
<p>1. Go to the <a href="https://developers.facebook.com/apps" target="_blank">Facebook Developers Apps page</a> and and sign in with your Facebook username and password.</p>
<p>2. Click the &quot;Create New App&quot; button.</p>
<p><i>If you do not see the option to create a new app in the upper right hand corner, click on &quot;Register as Developer.&quot;</i> </p>
<p>3. After that you'll obtain an 'App ID' which you'll paste in <span class="regb">FaceBook AppID</span> field in Manage Players->Player Settings area</p>
<p>4. Go to Settings->Basic tab (left area), select 'Website' and insert your website URL and fill all the other information. Below you have a demo screenshot. Of course, you'll use your own info.</p>
<p><img src="<?php echo plugins_url('images/facebookAppId.jpg', dirname(__FILE__))?>" width="1600" height="1511" alt="facebook appid" /></p>
<p>5. Go to Settings->Advanced tab (left area) and activate 'Social Discovery'</p>
<p><img src="<?php echo plugins_url('images/status_and_review.jpg', dirname(__FILE__))?>" width="1600" height="770" alt="status and review" /></p>
<p>6. To personalize more the share content you can use <span class="regb">FaceBook Share Title	</span> and <span class="regb">FaceBook Share Description		</span> fields in Manage Players->Player Settings area. Please check <a href="#settings">Player Settings</a> section, to see all available parameters</p>
<p>&nbsp;</p>
<p><span class="lbg_subtitle"><a name="htaccess" id="htaccess"></a>8.htaccess</span></p>
<p>If you need to increase the wordpress media library upload file size limit add the following definitions in the .htaccess file</p>
<p>&lt;IfModule mod_php5.c&gt;<br />
  php_value post_max_size           10M<br />
  php_value upload_max_filesize     40M<br />
  php_value memory_limit            500M<br />
  &lt;/IfModule&gt;</p>
<p>&nbsp;</p>
</div>
