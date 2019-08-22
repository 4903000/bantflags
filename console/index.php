<!DOCTYPE html>
<html>
<style>
body {
	background: #ffe url(fade.png) top center repeat-x;
	color: maroon;
	font-family: arial, helvetica, sans-serif;
	font-size: 10pt;
}
hr {
	border: none;
	border-top: 1px solid #d9bfb7;
	padding: 10px;
}
#content {
	padding-top: 15px;
}
#installLink {
 	font-size: 22pt;
}
#subLink {
	font-size: 16pt;
}
#management {
	display: flex;
	text-align: top;
}
#log {
	margin: 0px 8px;
	padding: 0px 4px;
	min-width: 300px;
	height: 14em;
	border: 1px solid #d9bfb7;
	float: top;
	overflow: auto;
	background-color: #fff
}
table {
	border-collapse: collapse;
	margin-top: 8px;
}
tr {
	display: inline-block;
	margin: 0px 4px;
}
tr:nth-child(odd) {
	background-color: #f0e0d6;
}
tr > td {
 	min-width: 24px;
}
#flagName {
	width: 150px;
}
th, td {
	border-top: 1px solid #d9bfb7;
}
</style>
<body>

<?php
// TODO: 
//	 1. keep a database of flag hashes for duplicate checking
//	 	implementation:	Create a new database table or print hashes to a text file. 
//	 			The hashes don't need to be linked to actual flags, since they're only used to check new uploads.
//				Deleting and merging flags should also remove the hashes used for duplicate checking.
//	 3. log all activity, possibly log IP addresses in order to block possible abusers
//	 	implementation:	Start each event by opening a file for writing and write the timestamp and a mnemonic of the user or something.
//	 			With each echo statement, also print equivalent thing to the file.
//	 4. have a decent way of backing up flags in case rename() fucks up again
//	 	implementation:	????
//	 5. have a way to expose parts of the log so the userscript can use it for various things
//	 	implementation: ???
//	 6. place some of these things that should be variables into variables and fix the relative paths because we're actually changing pwd here

// run when updating flags. Rebuilds the API lists and the management form
// Since this part is writing to a couple files and runs the bash script, permissions should be set accordinarly. >

$output = "";
$hashArray = array();
$flagDir = "../flags";

function update_flags() {
        global $flagDir;
	global $output;

        $flagList = "";
        chdir('../flags/actual_flags');
        $fileList = glob("*.png");
        foreach ($fileList as $fileName) {
                $flagName = substr($fileName, 0, -4);
                $flagList .= "{$flagName}\n";
        }

        $stack = new Imagick();
        foreach( $fileList as $fileName) {
                $stack->addImage(new Imagick($fileName));
        }
        $montage = $stack->montageImage( new ImagickDraw(), "0x0", "16x11", 0, "0");
        $montage->writeImage("../../console/montage.png");

        file_put_contents("flag_list.txt", $flagList);
        chdir('../../console');
	include "list_flags.php"; 	// updates the management form. 
	$output = $output . "flags updated <br />";
}

function check_name($name) {
	if (strpos($name, '||') !== false ) { 
		return true; 
	}
}

function gloss($flag) {
	global $output;
	$target = new Imagick($flag);
	$gloss = new Imagick("gloss.png");
	$target->compositeImage($gloss, Imagick::COMPOSITE_OVER, 0, 0);
	$target->writeImage($flag);
	$target->destroy();
	$gloss->destroy();
	$output = $output . "{$flag} was glossed <br />";
}

function readHashArray($file) {
	// 1. read file
	// 2. parse the contents and place into an array
	// 3. return the array
}

function makeHashArray() {
	// 1. loop through files and find their hashes
	// 2. place hashes in an array
	// 3. return the array
	global $hashArray;
	$flags = fopen("{$flagDir}/actual_flags/flag_list.txt", "r");
	if ($flags) {
		while (($line = fgets($flags)) !== false) {
			$line = substr($line, 0, -1);
			$hashArray[hash_file("md5", "actual_flags/{$line}.png")] = "{$line}.png";
		}
	}
}

function saveHashArray ($array, $file) {
	// 1. loop through array
	// 2. format array contents into readable format and accumulate into a variable
	// 3. write contents of variable to file
}

if (isset($_POST["update_flags"])) {
	if ($_POST["passwd"] != "supersecretpassword") { 
		echo "wrong password <br />";
		die;
	}
	update_flags();
}


// process flag upload
if(isset($_POST["upload_flag"])) {
	if ($_POST["passwd"] != "supersecretpassword") { 
		echo "wrong password <br />";
		die;
	}
	$target_dir = "actual_flags/"; 		// Is this one even used? I tried, but it broke things.
	$files = array_filter($_FILES['upflags']['name']);
	$total = count($_FILES['upflags']['name']);

	// check if the flag is 
	// 1. a png image, 
	// 2. the right resolution, 
	// 3. there isn't a flag with the same name,
	// 4. the flag name doesn't contain unallowed substrings like regionDividers
	function checkflag($flag_file, $flag_name) {
		global $output;
		$flag_dimensions = array(16,11);

		if (exif_imagetype($flag_file) != IMAGETYPE_PNG ) {
			$output = $output . "{$flag_name} is not a .png<br />";
			return false;
		} else if (getimagesize($flag_file)[0] != $flag_dimensions[0] && getimagesize($flag_file)[1] != $flag_dimensions[1]) {
			$output = $output . "{$flag_name} has wrong dimensions<br />";
			return false;
		} else if (check_name($flag_name)) {
			$output = $output . "{$flag_name} contains illegalities.<br />";
			return false;
		} else if (file_exists("actual_flags/{$flag_name}")) {
			$output = $output . "{$flag_name} already exists<br />";
			return false;
		} else {
			return true;
		}
	}
	// unused for now. For converting .gif flags to APNG, but doesn't seem to work. Timing issues with ffmpeg.
	function convertanim($flag_file, $flag_name) {
		global $output;
		// returns a new file I guess?
		$name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $flag_name); 
		shell_exec("ffmpeg -i {$flag_file} -f apng -plays 0 /tmp/ {$name} .png");
		return $name;
	}

	$hashArray = array();
	// loop through the uploaded files
	for($i=0; $i<$total; $i++) {
		$tmpFilePath = $_FILES['upflags']['tmp_name'][$i]; 	// $tmpFilePath points to the temporary location of the file in /tmp.
		$newName = $_FILES['upflags']['name'][$i];
		$currentHash = hash_file("md5", $tmpFilePath); 		// for duplicate checking
		if (!$hashArray[$currentHash]) { 	
			$hashArray[$currentHash] = $newName;
//			if (exif_imagetype($tmpFilePath) == IMAGETYPE_GIF) {	// I don't think this works.
//				$newName = preg_replace('/\\.[^.\\s]{3,4}$/', '', $flag_name) . ".png"; 
//				$tmpFilePath = "/tmp/{$newName}";
//			}
			if (checkflag($tmpFilePath, $newName)) {
				if ($tmpFilePath != "") {
					$newFilePath = "{$flagDir}/actual_flags/{$_FILES['upflags']['name'][$i]}"; 	// this is where $target_dir broke so it's hardcoded
					if (move_uploaded_file($tmpFilePath, $newFilePath)) {			// this nesting is ugly
						$output = $output . "{$_FILES['upflags']['name'][$i]}  was uploaded<br />";
						if (isset($_POST["gloss"]) && $_POST["gloss"] == "doGloss") {
							gloss($newFilePath);
						}
//					} else if (rename($tmpFilePath, $newFilePath)) {
//						$output = $output . "{$_FILES['upflags']['name'][$i]} was converted into apng and uploaded<br />";
					}
				}
			}
		} else {
			$output = $output . "{$newName} is a duplicate of {$hashArray[$currentHash]} <br />";
		}
	}
	update_flags();
}



// deleting flags. All the selected flags have their value placed in an array, and this part should 
// 1. test if there's anything to delete
// 2. delete selected flags, as in, move them away to dead_flags/
if (isset($_POST["delete_flags"])) {
	if ($_POST["passwd"] != "nowthatwasfuckingstupid") {
		echo "wrong password";
		die;
	}
	if (!isset($_POST["selected"])) {
		$output = $output . "nothing selected, nothing deleted <br />";
	} else {
		foreach($_POST["selected"] as $delete_flag) {
			if (strpos($delete_flag, '/') !== false) {
				$output = $output . "flag files don't exactly contain '/' <br />";
			} else if (rename("{$flagDir}/actual_flags/{$delete_flag}.png", "{$flagDir}dead_flags/{$delete_flag}.png")) {
				$output = $output . "{$delete_flag} was deleted <br />";
				$old_name = $delete_flag;
				$new_name = "missingflag";
				include "update_db.php";
			} else {
				$output = $output . "error deleting {$delete_flag} <br />";
			}
		}
		update_flags();
	}
}

if (isset($_POST["gloss_flags"])) {
	if ($_POST["passwd"] != "nowthatwasfuckingstupid") {
		echo "wrong password";
		die;
	}
	if (!isset($_POST["selected"])) {
		$output = $output . "nothing selected, nothing gloss <br />";
	} else {
		foreach($_POST["selected"] as $gloss_flag) {
			copy("{$flagDir}actual_flags/{$gloss_flag}.png", "{$flagDir}dead_flags/{$gloss_flag}.png");
			gloss("{$flagDir}actual_flags/{$gloss_flag}.png");
		}
		update_flags();
	}
}

if (isset($_POST['rename_flags'])) {
	if ($_POST["passwd"] == "nowthatwasfuckingstupid") {
		$testagainst = array_flip($_POST['rename']);
		$_POST['rename'] = array_diff($_POST['rename'], [""]);
		$_POST['rename'] = array_intersect($testagainst, $_POST['selected']);
		$_POST['rename'] = array_flip($_POST['rename']);
		if (empty($_POST['rename'])) {
			$output = $output . "Nothing to rename <br />";
		} else {
			// process rename
			foreach ($_POST['rename'] as $old_name => $new_name) {
				if (file_exists("{$flagDir}actual_flags/{$new_name}.png")) {
					$output = $output . "can't rename {$old_nmae}, file exists. <br />";
				} else if (check_name($new_name)) {
					$output = $output . "can't rename {$old_name}, name contains illegalities. <br />";
				} else {
					if (rename("{$flagDir}actual_flags/{$old_name}.png", "actual_flags/{$new_name}.png")) {
						$output = $output . "{$old_name} renamed as {$new_name} <br />";
						include "update_db.php";
					} else {
						$output = $output . "error renaming {$old_name} <br />";
						// should actually handle this fuckery because this deletes the files it can't move
						// What a piece of shit
					}
				}
			}
			update_flags();
		}
	} else if ($_POST["passwd"] == "supersecretpassword") {
		$_POST['rename'] = array_diff($_POST['rename'], [""]);
		if (empty($_POST['rename'])) {
			$output = $output . "No suggestions to add <br />";
		} else { 
			ob_start();
			foreach ($_POST['rename'] as $old_name => $new_name) {
				echo "{$old_name}||{$new_name}\r\n";
			}
			$suggestList = ob_get_contents();
			ob_end_clean();
			file_put_contents("suggestions_list.txt", $suggestList);
			$output = $output . "suggestions list written <br />";
			update_flags();
		}
	} else {
		echo "wrong password";
		die;
	}
}

// basically the same as the above except don't let flags be renamed to something that doesn't exist 
// and archive the obsolete flag.
if (isset($_POST['merge_flags'])) {
	if ($_POST["passwd"] == "nowthatwasfuckingstupid") {
		$_POST['rename'] = array_diff($_POST['rename'], [""]);
		if (empty($_POST['rename'])) {
			$output = $output . "Nothing to merge <br />";
      		} else { 
			foreach ($_POST['rename'] as $old_name => $new_name) {
				if ($old_name != $new_name) {
					if (file_exists("actual_flags/{$new_name}.png")) {
      						if (rename ("{$flagDir}actual_flags/{$old_name}.png", "{$flagDir}dead_flags/{$old_name}.png")) {
      						include "update_db.php";
	      						$output = $output . "{$old_name} was merged into {$new_name} <br />";
	      					} else {
	      						$output = $output . "error renaming {$old_name} <br />";
	      						// should handle those weird rename issues here but how?
						}
      					} else {
      					$output = $output . "Can't merge {$old_name}, {$new_name} doesn't exist <br />";
					}
				}
			}
			update_flags();
		}
	} else {
		echo "wrong password";
		die;
	}
}

//echo $output;
?>
<div id=content>
	<div align="center">
	<img src="montage.png" id="overview"><br />
	<a href="bantflags.user.js" id="installLink"><b>install bantflags</b></a> <br />
	<a href="https://nineball.party/srsbsn/3521" id="subLink">official thread</a> <br />
	</div>
<hr>
<form action="index.php" method="post" enctype="multipart/form-data">
<div id="management">
	<div id="buttons">
	password (used for managing flags only): <input type="password" name="passwd" 
		title="Different functions of different potential destructiveness use different passwords." /> <br />
	files (name properly): <input type="file" name="upflags[]" type "file" multiple="multiple" /> <br />
	<input type="submit" name="upload_flag" value="Upload flags" 
		title="Up to 20 at once. The filename is used as the flag's name, so please name it properly. Spaces are allowed, '||' is not." />
	<input type="checkbox" name="gloss" value="doGloss"/> Apply gloss (Do not use with partially transparent flags).<br />
	<input type="submit" name="update_flags" value="Update flags" 
		title="Update the plaintext list, the management list on this page, and the flag overview image. Other functions do this automatically." /> <br />
	<input type="submit" name="rename_flags" value="Rename flags" 
		title="Write the new name in the text field. Old database entries are updated, so they'll keep resolving in the archives." />
		You can suggest names with the normal password now. <br />
	<input type="submit" name="delete_flags" value="Delete flags" 
		title="Check the flags you want to delete. This doesn't change the database, so consider not removing flags that are in use. If you wish to replace the flag, merge it instead." /> <br />
	<input type="submit" name="merge_flags" value="Merge flags" 
		title="For merging duplicate flags and such. Write the name of the displacing flag into the text field. The removed flag is archived and the database entries are updated."/> <br />
	<input type="submit" name="gloss_flags" value="Gloss flags" 
		title="Gloss the selected flags. Please don't gloss them repeatedly." /> <br />
	</div>
	<?php 
		if ($output != "") { echo "<div id=\"log\"> {$output} </div>"; }
	?>
</div>
<?php 
	include "flags_list.html";
?>
</form>
</div>
</body>
</html>
