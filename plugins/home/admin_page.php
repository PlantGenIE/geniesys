<link rel="stylesheet" type="text/css" href="plugins/home/css/admin.css" />
<script type="text/javascript" src="plugins/home/js/jquery.hashchange.min.js"></script>
<script type="text/javascript" src="plugins/home/js/jquery.easytabs.min.js"></script> 
<script type="text/javascript">
    var editor_content = "<?php echo content($c['page'],$c['content']);?>"; 
</script>
<div id="tab-container" class='tab-container'>
     <ul class='etabs'>
     <li class='tab'><a href="#page">Edit page</a></li>
       <li class='tab'><a href="#site">Site settings</a></li>
       <li class='tab'><a href="#db">Database settings</a></li>
       <li class='tab'><a href="#annotation">Annotation</a></li>
       <li class='tab'><a href="#expression">Expression</a></li>
       <li class='tab'><a href="#summary">Summary</a></li>
     </ul>
    <div class='panel-container'>
      <div id="page"> <br>
       <!--page section start id="main_editor"-->
       <br><br>
       <textarea  class="ckeditor" name="editor"><?php content($c['page'],$c['content']);?></textarea>
               <script type="text/javascript">
                  var key = <?php echo json_encode($c['page']); ?>
               </script>
               <button id="btn_submit" onclick="save(key);">save</button>
               <?php  include('themes/genie/msg_box.php'); ?> 
       <!--page section ended-->
      </div> 
      <div id="site"> <br>
       <!--site section start-->
<?php settings();?>
<button id="btn_submit"  onclick='javascript:location.reload(true);'>save</button>
       <!--site section ended-->
</div>
      <div id="db"><br>
      <!--db section start-->
      <h3>Loading novel genome to GenIE-Sys database. Follow the steps one at a time.</h3>
<span style="overflow: hidden;position: absolute;top:30px" id="error_msg"></span>
<form  id="db_form">
   <p><label for="host">Host:&nbsp;&nbsp;&nbsp;</label><input autocomplete="host" id="mhost" value="localhost" placeholder="MySQL host : localhost" type="text"/> &#9432; This is the default host </p>
   <p><label for="username">Username:</label><input value="admin" autocomplete="username" id="musername" placeholder="MySQL username: admin" type="text"/> &#9432; This is the default username (MAMP uses root as default username) </p>
   <p><label for="password">Password:</label><input value="mypass" autocomplete="password" id="mpassword" placeholder="MySQL password : mypass" type="text"/> &#9432; This is the default password (MAMP uses root as default password)</p>
   <p><label for="database">Database:</label><input id="mdbname" placeholder="Type in new database name" value="" type="text"/> &#9432; Current database name should be type in here</p>
</form>
<br>
<!--<h3> There is a database name stated in the setting file. Howevere that database does not exsist in MySQL server. Do you want to create a new database?</h3>-->
<button class="upbtn"  id="create_db">create a fresh database</button>
<!--<button class="upbtn"  id="create_db_arabidopsis">create a database with <i>Arabidopsis thaliana</i></button>-->
<button id="drop_db" class="upbtn"  style="background:red;color:white">Delete current database</button>&nbsp; <span class="help_span">&#9432; First you have to create a database or use the existing database.</span> 
<button class="upbtn" style="display:none"  id="download_indices">Download indices</button>
<br><br>
<div id="clone_div" style="border:dotted thin black;width:60%;border-radius:5px;padding:6px;display:none">
<h4>Clone from the PlantGenIE core species. This includes all the annotation and expression data.</h4>
<button class="upbtn" onClick="clone_genome(this)"  id="potra">Populus tremula</button>
<button class="upbtn" onClick="clone_genome(this)" id="piabi">Picea abies</button>
<button class="upbtn" onClick="clone_genome(this)"  id="artha">Arbidopsis thaliana</button>
<button class="upbtn" onClick="clone_genome(this)"  id="eugra">Eucalyptus grandis</button></br></br>
</div>
</br></br> 
<a target="_blank" id="myadmin_links" style="color:blue;font-weight:bold;float:right;cursor:pointer">External link to phpMyAdmin page >></a><br>
      <!--db section ended-->
      </div>
      <div id="annotation"><br>
        <!--annotation section started-->
      </br></br>

<ol>
  <li>Check files in the data folder.  <button onClick="check_files();"  class="upbtn"  id = "upidgff3">check_initial_files</button></li>




  <li>make correct files using gff3 files.</li>
  <li>load gff3 files into databases.</li>
  <li>make indices of fasta files.</li>
<ol>

<table id="upload_table" style="width:100%">
<tr style="font-weight:bold" align="left">

<tr>




   <tr style="font-weight:bold" align="left">
      <th>Upload GFF3</th>
      <th >Upload FASTA</th>
      <th>Upload Annotation</th>
   </tr>
   <tr>
      <td height="40">
           <!--Progress bar html element-->
               <progress id = "progress_upidgff3" value = "0" max = "100"> </progress> <span style="width: 40px; display: inline-block" id = "mySpan_upidgff3"> 0% </span><button  class="upbtn"  id = "upidgff3"> Upload GFF3  </button> &nbsp; <span class="help_span">&#9432; </span>  <br/>
      </td>
      <td><input disabled placeholder="Path to BLAST directory"  style="width:60%" value="/plugins/home/service/upload" type="text" id = "upid_fp_path">  </input>&nbsp; <span class="help_span">&#9432; </span>   </td>
      <td><progress id = "progress_upid_a" value = "0" max = "100"> </progress> <span style="width: 40px; display: inline-block" id = "mySpan_upid_a"> 0% </span><button  class="upbtn" id = "upid_a"> Upload annotation </button>&nbsp; <span class="help_span">&#9432; </span>  </td>
   </tr>
   <tr >
       <td height="40"></td>
       <td><progress id = "progress_upid_fg" value = "0" max = "100"> </progress> <span style="width: 40px; display: inline-block"  id = "mySpan_upid_fg"> 0% </span><button class="upbtn" id = "upid_fg"> Upload genome FASTA </button>&nbsp; <span class="help_span">&#9432; </span> </td>
       <td></td>
   </tr>
   <tr>
       <td height="40"></td>
       <td><progress id = "progress_upid_ft" value = "0" max = "100"> </progress> <span style="width: 40px; display: inline-block" id = "mySpan_upid_ft"> 0% </span><button class="upbtn" id = "upid_ft"> Upload transcript FASTA </button>&nbsp; <span class="help_span">&#9432; </span> </td>
       <td></td>
   </tr>
   <tr>
       <td height="40"></td>
       <td ><progress id = "progress_upid_fc" value = "0" max = "100"> </progress> <span style="width: 40px; display: inline-block"   id = "mySpan_upid_fc"> 0% </span><button class="upbtn" id = "upid_fc"> Upload CDS FASTA </button>&nbsp; <span class="help_span">&#9432; </span> </td>
       <td></td>
   </tr>   
   <tr>
       <td height="40"></td>
       <td><progress id = "progress_upid_fp" value = "0" max = "100"> </progress> <span style="width: 40px; display: inline-block"  id = "mySpan_upid_fp"> 0% </span><button class="upbtn" id = "upid_fp"> Upload protein FASTA </button>&nbsp; <span class="help_span">&#9432; </span> </td>
       <td></td>
   </tr>   
</table>
<br><br>
     <!--annotation section ended-->
    </div>
      <div id="expression"><br>Expression
        <!--expression section started-->
        <!--expression section ended-->
      </div>
      <div id="summary"><br>Summary
        <!--summary section started-->
        <!--summary section ended-->
      </div>
    </div>
    <br>
</div>
<script src="plugins/home/js/init.js"></script>
<script src="plugins/home/js/annotation.js"></script>
