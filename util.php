<?php
function validate_and_parse($src, &$dst, $required_fields, &$invalid_fields)
{
    foreach($required_fields as $key=>$item)
    {
        switch(gettype($item))
        {
            case 'array':
                $dst[$key] = array();
                foreach($item as $sub_key=>$sub_item) // here $sub_item are regexps
                {
                    if(isset($src[$key][$sub_key])&&preg_match($sub_item, $src[$key][$sub_key])===1)
                    {
                        $dst[$key][$sub_key] = $src[$key][$sub_key];
                    }
                    else
                    {
                        $invalid_fields[] = $key.'_'.$sub_key;
                    }
                }
                break;
            case 'string': // here $item are regexps
                if(isset($src[$key])&&(preg_match($item, $src[$key])===1))
                {
                    $dst[$key] = $src[$key];
                }
                else
                {
                    $invalid_fields[] = $key;
                }
                break;
        }
    }
    return (count($invalid_fields) === 0);
}
?>
