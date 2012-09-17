<?php
function stand($stand_name)
{
    $stands = array(
        'A'=>'小福', 
        'B'=>'男二', 
        'C'=>'男四', 
        'D'=>'女四', 
        'E'=>'女六', 
        'F'=>'男七', 
        'G'=>'女X' 
    );
    return array_search($stand_name, $stands);
}
