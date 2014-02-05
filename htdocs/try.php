<?php
global $master_list;

$txt_file    = file_get_contents('de.txt');
$rows        = explode("\n", $txt_file);
array_shift($rows);

function display($list)
{
	for($i=0; $i<count($list); $i++)
	{	
		echo"Element ".($i+1).": ".$list[$i]."</br>";
	}
}

function check($node, $element1, $condition, $element2)
{	$count=count($master_list);
		if($element1 == $node || $element2 == $node)
		{
			if($condition == "ALWAYS" || $condition == "MAYBE")
			{	//echo"here: ".$element1." ".$condition." ".$element2."</br>";

				if($element1 == $node)
				{	
					return $element2;
				
				}
				else if ($element2 == $node)
				{	
					return $element1;
				
				}
				else
				{
					return NULL;
				}
			}
		else
		{
			return NULL;
		}

		}
		
}


$nodeValue = 'Team';

foreach($rows as $row => $data)
{	   //get row data
    $row_data = explode(' ', $data);

    $info[$row]['element1']  = $row_data[0];
    $info[$row]['condition'] = $row_data[1];
    $info[$row]['element2']  = $row_data[2];
    
    if (count($master_list) == 0) {
    	$master_list[0] = $nodeValue;
    	$getNum=1;
    }
    else
    {
    	$getNum = count($master_list)+1; 
    }
    
    $toEvaluate = check($nodeValue, $info[$row]['element1'], $info[$row]['condition'], $info[$row]['element2']);
    
    if($toEvaluate != NULL)
    {
    	$master_list[$getNum] = $toEvaluate;
	}
    //display data
    echo $row .' :		'.$info[$row]['element1'] . ' ';
    echo $row .' :		'.$info[$row]['condition'] . ' ';
    echo $row .' :		'. $info[$row]['element2'] . '<br />';
}


// die("Number of elements : ".count($master_list));

display($master_list);

?>