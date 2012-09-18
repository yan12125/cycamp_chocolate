<?
function validate_and_parse($src, $required_fields)
{
    $invalid_fields = array();
    $data = array();
    foreach($required_fields as $key=>$item)
    {
        switch(gettype($item))
        {
            case 'array':
                $data[$key] = array();
                foreach($item as $sub_key=>$sub_item) // here $sub_item are regexps
                {
                    if(isset($src[$key][$sub_key])&&preg_match($sub_item, $src[$key][$sub_key])===1)
                    {
                        $data[$key][$sub_key] = $src[$key][$sub_key];
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
                    $data[$key] = $src[$key];
                }
                else
                {
                    $invalid_fields[] = $key;
                }
                break;
        }
    }
    if(count($invalid_fields) === 0)
    {
        return $data;
    }
    else
    {
        echo 'Invalid fields: '.implode(', ', $invalid_fields)."\n";
        return false;
    }
}
?>
