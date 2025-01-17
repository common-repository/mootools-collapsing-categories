<?php
/*
Moo Collapsing Categories version: 0.5.8

This work is largely based on the Collapsing Categories plugin by Robert Felty
(http://robfelty.com), which was also distributed under the GPLv2.
His website has lots of informations.

This file is part of Moo Collapsing Categories

    Moo Collapsing Categories is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Moo Collapsing Archives is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Collapsing Categories; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
check_admin_referer();

$options=get_option('collapsCatOptions');
$widgetOn=0;
$number='%i%';
if (empty($options)) {
  $number = '-1';
} elseif (!isset($options['%i%']['title']) || 
    count($options) > 1) {
  $widgetOn=1; 
}

if( isset($_POST['resetOptions']) ) {
  if (isset($_POST['reset'])) {
    delete_option('collapsCatOptions');   
		$widgetOn=0;
    $number = '-1';
  }
} elseif (isset($_POST['infoUpdate'])) {
  $style=$_POST['collapsCatStyle'];
  $defaultStyles=get_option('collapsCatDefaultStyles');
  $selectedStyle=$_POST['collapsCatSelectedStyle'];
  $defaultStyles['selected']=$selectedStyle;
  $defaultStyles['custom']=$_POST['collapsCatStyle'];
  $MTversion = $_POST['MTversion'];

  update_option('collapsCatStyle', $style);
  update_option('collapsCatSidebarId', $_POST['collapsCatSidebarId']);
  update_option('collapsCatInFooter', $_POST['collapsCatInFooter']);
  update_option('collapsCatDefaultStyles', $defaultStyles);
  update_option('MTversion', $MTversion);

  if ($widgetOn==0) {
    include('updateOptions.php');
  }
}
include('processOptions.php');
?>
<div class=wrap>
 <form method="post">
  <h2><? _e('Moo Collapsing Categories Options', 'moo-collapsing-cat'); ?></h2>
  <fieldset name="Collapsing Categories Options">
    <p>
 <?php _e('Id of the sidebar where collapsing categories appears:', 'moo-collapsing-cat'); ?>
   <input id='collapsCatSidebarId' name='collapsCatSidebarId' type='text' size='20' value="<?php echo
   get_option('collapsCatSidebarId')?>" onchange='changeStyle("collapsCatStylePreview","collapsCatStyle", "collapsCatDefaultStyles", "collapsCatSelectedStyle", false);' />
<select id='MTversion' name='MTversion'>
<option value="12" <?php if(get_option('MTversion') == '12') echo 'selected="selected"'; ?>>MooTools v.1.2.5</option>
<option value="13" <?php if(get_option('MTversion') == '13') echo 'selected="selected"'; ?>>MooTools v.1.3.2</option>
</select>
   <table>
     <tr>
       <td>
  <input type='hidden' id='collapsCatCurrentStyle' value="<?php echo
stripslashes(get_option('collapsCatStyle')) ?>" />
  <input type='hidden' id='collapsCatSelectedStyle'
  name='collapsCatSelectedStyle' />
<label for="collapsCatStyle"><?php _e('Select style:', 'moo-collapsing-cat'); ?></label>
       </td>
       <td>
       <select name='collapsCatDefaultStyles' id='collapsCatDefaultStyles'
         onchange='changeStyle("collapsCatStylePreview","collapsCatStyle", "collapsCatDefaultStyles", "collapsCatSelectedStyle", false);' />
       <?php
    $url = get_settings('siteurl') . '/wp-content/plugins/mootools-collapsing-categories';
       $styleOptions=get_option('collapsCatDefaultStyles');
       //print_r($styleOptions);
       $selected=$styleOptions['selected'];
       foreach ($styleOptions as $key=>$value) {
         if ($key!='selected') {
           if ($key==$selected) {
             $select=' selected=selected ';
           } else {
             $select=' ';
           }
           echo '<option' .  $select . 'value="'.
               stripslashes($value) . '" >'.$key . '</option>';
         }
       }
       ?>
       </select>
       </td>
       <td><?php _e('Preview', 'moo-collapsing-cat'); ?><br />
       <img style='border:1px solid' id='collapsCatStylePreview' alt='preview'/>
       </td>
    </tr>
    </table>
    <?php _e('You may also customize your style below if you wish', 'moo-collapsing-cat'); ?><br />
   <input type='button' value='<?php _e('restore current style', 'moo-collapsing-cat'); ?>'
onclick='restoreStyle();' /><br />
   <textarea onchange='changeStyle("collapsCatStylePreview","collapsCatStyle", "collapsCatDefaultStyles", "collapsCatSelectedStyle", true);' cols='78' rows='10' id="collapsCatStyle" name="collapsCatStyle"><?php echo stripslashes(get_option('collapsCatStyle'))?></textarea>
   </p>
   <p>
   <input type="checkbox" name="collapsCatInFooter" id ="collapsCatInFooter"
   <?php if (get_option('collapsCatInFooter')) echo
   'checked'; ?> id="collapsCatInFooter"></input> 
<label for="collapsCatInFooter"><?php _e('Put javascript file in footer (speeds page load, but is not compatible with all themes)', 'moo-collapsing-cat'); ?></label>  
    </p>
<script type='text/javascript'>

function changeStyle(preview,template,select,selected,custom) {
  var preview = document.getElementById(preview);
  var pageStyles = document.getElementById(select);
  var selectedStyle;
  var hiddenStyle=document.getElementById(selected);
  var pageStyle = document.getElementById(template);
  if (custom==true) {
    selectedStyle=pageStyles.options[pageStyles.options.length-1];
    selectedStyle.value=pageStyle.value;
    selectedStyle.selected=true;
  } else {
    for(i=0; i<pageStyles.options.length; i++) {
      if (pageStyles.options[i].selected == true) {
        selectedStyle=pageStyles.options[i];
      }
    }
  }
  hiddenStyle.value=selectedStyle.innerHTML
  preview.src='<?php echo $url ?>/img/'+selectedStyle.innerHTML+'.png';
  var sidebarId=document.getElementById('collapsCatSidebarId').value;

  if (sidebarId!='') {
  var theStyle = selectedStyle.value.replace(/#[a-zA-Z]+\s/g, '#'+sidebarId + ' ');
  } else {
  var theStyle = selectedStyle.value.replace(/#[a-zA-Z]+\s/g, '');
  }
  pageStyle.value=theStyle
}

function restoreStyle() {
  var defaultStyle = document.getElementById('collapsCatCurrentStyle').value;
  var pageStyle = document.getElementById('collapsCatStyle');
  pageStyle.value=defaultStyle;
}
  changeStyle('collapsCatStylePreview','collapsCatStyle', 'collapsCatDefaultStyles', 'collapsCatSelectedStyle', false);

</script>
  </fieldset>
  <div class="submit">
   <input type="submit" name="infoUpdate" value="<?php _e('Update options', 'moo-collapsing-cat'); ?> &raquo;" />
  </div>
 </form>
</div>