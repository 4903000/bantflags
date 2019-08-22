<?php
$suggestions = fopen("suggestions_list.txt", "r");
if ($suggestions) {
	$suggestArray = array();
	while (($sline = fgets($suggestions)) !== false) {
		$sline = substr($sline, 0, -1);
		$sname = substr($sline, 0, strpos($sline, "||"));
		$suggestion = substr($sline, 2 + strpos($sline, "||"));
		$suggestArray["{$sname}"] = $suggestion;
	}
}
fclose($suggestions);
ob_start(); 	// listen to output

echo "<table>";

$handle = fopen("{$flagDir}/actual_flags/flag_list.txt", "r");
if ($handle) {
    	while (($line = fgets($handle)) !== false) {
	    	$line = substr($line, 0, -1); 	// each line has a newline at the end, gut it
	    	echo "<tr><td><input type=\"checkbox\" name=\"selected[]\" value=\"{$line}\"></td>
			<td><img src=\"{$flagDir}/actual_flags/{$line}.png\"></td>
			<td id=\"flagName\">{$line}</td>
			<td><input type=\"text\" name=\"rename[{$line}]\" value=\"{$suggestArray[$line]}\"/></td>
			</tr>";
    	}

    	fclose($handle);
} else {
    	// lol nothing
} 

echo "</table>";

$htmlStr = ob_get_contents(); 	// place listened output to variable
ob_end_clean();			// stop listening to output
file_put_contents("flags_list.html", $htmlStr);		// write flags list to file
//$output = $output . "form updated <br />";
?>
