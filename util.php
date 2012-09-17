<?
function validate_and_parse($src, $required_fields)
{
    $ok = true;
    $data = array();
    foreach($required_fields as $key=>$item)
    {
        switch(gettype($item))
        {
            case 'array':
                $data[$key] = array();
                foreach($item as $sub_item)
                {
                    if(!isset($src[$key][$sub_item]))
                    {
                        $ok = false;
                    }
                    else
                    {
                        $data[$key][$sub_item] = $src[$key][$sub_item];
                    }
                }
                break;
            case 'string':
                if(!isset($src[$item]))
                {
                    $ok = false;
                }
                else
                {
                    $data[$item] = $src[$item];
                }
                break;
        }
    }
    if($ok)
    {
        return $data;
    }
    else
    {
        return $ok;
    }
}
?>
