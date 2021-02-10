<?php
$selected_genelist = trim($_POST['selected_genelist']);
$primaryGenes = trim($_POST['primaryGenes']);
$primaryGenes = trim($primaryGenes, ', ');

include "../../genelist/crud/getgenelist.php";

if (isset($_POST['selected_genelist'])) {

    $geneids_array = explode(",", trim($_POST['selected_genelist']));
    echo trim(updategenebasketall($geneids_array, "explot list"));
    exit;
}

$primaryGenes2 = getdefaultgenelist();
$new_array = getdata($primaryGenes2, $samplelist_array);

print json_encode($new_array);

function getdata($primaryGenes, $source)
{
    mysql_connect("localhost", "popuser", "poppass") or die(mysql_error());
    mysql_select_db("popgenie_potra_v11") or die(mysql_error());
    $result = array();
    $result2 = array();

    $source_view = 'expression_tissues';

    $geneids_array = $primaryGenes; 
    for ($t = 0; $t < count($geneids_array); $t++) {
        $pattern = '/^[a-zA-Z0-9]+[.]+[a-zA-Z0-9]+[.]+[0-9]?[0-9]$/';
        if (preg_match($pattern, $geneids_array[$t]) == true) {
            $newpattern = '/^[a-zA-Z0-9]+[.]+[a-zA-Z0-9]+[.]+[0-9]$/';
            if (preg_match($newpattern, $geneids_array[$t]) == true) {
                $newid = substr_replace($geneids_array[$t], "", -2);
                $resultprobeset = mysql_query("SELECT DISTINCT sample,id,log2 FROM $source_view WHERE id='$newid' order by sample;;") or die(mysql_error());
            } else {
                $newid2 = substr_replace($geneids_array[$t], "", -3);
                $resultprobeset = mysql_query("SELECT DISTINCT sample,id,log2 FROM $source_view WHERE id='$newid2' order by sample;;") or die(mysql_error());
            }
        } else {
            $resultprobeset = mysql_query("SELECT DISTINCT sample,id,log2 FROM $source_view WHERE id='$geneids_array[$t]' order by sample;;") or die(mysql_error());
        }

        $rows = array();
        $rows2 = array();

        $rows['name'] = $geneids_array[$t];
        while ($rowPROBE_ID = mysql_fetch_assoc($resultprobeset)) {
            $rows['data'][] = (float) $rowPROBE_ID['log2'];
            $rows2['sample'][] = $rowPROBE_ID['sample'];
        }

        array_push($result, $rows);
        array_push($result2, $rows2);
    }
    $result2 = $result2[0]['sample'];

    if ($source == "eplant_sex") {
        $result2 = array("alidhem", "ersboda", "leaf-01", "leaf-02", "leaf-03", "leaf-04", "leaf-05", "leaf-06", "leaf-07", "leaf-08", "leaf-09", "leaf-10", "leaf-11", "leaf-12", "leaf-13", "leaf-14", "leaf-15", "leaf-16", "leaf-17", "sculPark");
    }
    if ($source == "eplant_asp201") {
        $result2 = array("Buds-Dormant", "Buds-Pre-chilling", "Cambium-Phloem-Dormant", "Flowers-Dormant", "Flowers-Expanded", "Flowers-Expanding", "Leaves-Beetle-Damaged", "Leaves-Control", "Leaves-Drought", "Leaves-Drought-2", "Leaves-Freshly-Expanded", "Leaves-Girdled", "Leaves-Mature", "Leaves-Mature-2", "Leaves-Mechanical-Damage", "Leaves-Non-Girdled", "Leaves-Undamaged", "Leaves-Young-Expanding", "Petiole-Mature", "Roots-Control", "Roots-Drought", "Seeds-Mature", "Suckers-Whole-Sucker", "Twigs-Non-Girdled");
    }

    $arrsg = array('popdata' => $result, 'samples' => $result2);
    return $arrsg;
}

#####################################
//Check prefix
#####################################
function checkprefix($source, $prefix)
{
    if (str_startswith($source, $prefix)) {
        return true;
    } else {
        return false;
    }
}
function str_startswith($source, $prefix)
{
    return strncmp($source, $prefix, strlen($prefix)) == 0;
}
/////////////////////////////////////
