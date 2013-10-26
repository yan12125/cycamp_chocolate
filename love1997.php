<?php
if(php_sapi_name() !== 'cli')
{
    exit(0);
}
error_reporting(E_ALL|E_STRICT);

require_once '3rdparty/simple_html_dom.php';

class Love1997
{
    protected $url = array(
        'list' => 'http://www.lovechoco1997.com.tw/list.php'
    );
    protected $grade_map = array(
        '一' => 1, '二' => 2, '三' => 3, 
        '四' => 4, '五' => 6, '六' => 6, '七' => 6
    );
    protected $remote_data = array();
    protected $local_data = array();
    protected $ch = null;
    protected $schools = null;
    protected $prices = array();

    public function __construct()
    {
        $this->ch = curl_init();
        $this->loadStaticData();
    }

    public function __destruct()
    {
        curl_close($this->ch);
    }

    public function loadStaticData()
    {
        $data = json_decode(file_get_contents('./data.json'), true);
        $this->schools = $data['companies']['A']['schools'];
        $products = $data['companies']['A']['products'];
        for($i = 0; $i < count($products); $i++)
        {
            $this->prices[] = $products[$i]['price'];
        }
    }

    public function getRemoteList()
    {
        $this->remote_data = array();
        curl_reset($this->ch);
        curl_setopt_array($this->ch, array(
            CURLOPT_URL => $this->url['list'], 
            CURLOPT_RETURNTRANSFER => true, 
            CURLOPT_BINARYTRANSFER => true, 
            CURLOPT_POST => true, 
            CURLOPT_POSTFIELDS => 'school=ntu'
        ));
        $html = str_get_html(curl_exec($ch));
        foreach($html->find('tr') as $row)
        {
            $this->remote_data[] = array(
                'ID' => $row->children(1)->innertext, 
                'to_school' => $row->children(3)->innertext, 
                'grade' => $row->children(4)->innertext, 
                'products' => $row->children(5)->innertext
            );
        }
    }

    public function loadFile(array $files)
    {
        for($i = 0; $i < count($files); $i++)
        {
            $this->loadSingleFile($files[$i]);
        }
    }

    public function loadSingleFile($file)
    {
        $data = explode("\n", file_get_contents($file));
        for($i = 0; $i < count($data); $i++)
        {
            $item = explode("*", $data[$i]);
            if(count($item) != 46)
            {
                echo 'Invalid row: '.$data[$i]."\n";
                continue;
            }
            // parse products
            $products = array_map(function ($n) {
                return intval($n);
            }, array_splice($item, 2, 23));
            $total_price = 0;
            if(array_count_values($products)[0] == 23)
            {
                echo "Error: empty products list. ID = ".$item[0]."\n";
            }
            else
            {
                for($j = 0; $j < count($products); $j++)
                {
                    $total_price += $products[$j] * $this->prices[$j];
                }
                if($item[6] == '台灣大學')
                {
                    $total_price += 30;
                }
                else
                {
                    $total_price += 40;
                }
                if($total_price !== intval($item[2]))
                {
                    echo 'Error: price mismatch: ('.$total_price.','.intval($item[2])."); ID = ".$item[0]."\n";
                }
            }
            // parse basic data
            $data[$i] = array(
                'id' => $item[0], 
                'products' => $products, 
                'paid' => intval($item[3]), 
                'receiver' => array()
            );
            array_splice($item, 0, 5);
            // parse receiver's data
            $fields = array('name', 'school', 'department', 'grade', 'class', 'phone', 'mail', 'comment');
            for($j = 0; $j < count($fields); $j++)
            {
                $data[$i]['receiver'][$fields[$j]] = $item[$j];
            }
            array_splice($item, 0, 2 * count($fields));
            $data[$i]['date'] = $item[0];
            $data[$i]['discarded'] = intval($item[1]);
            if($data[$i]['discarded'] != 0 || $data[$i]['paid'] == 0)
            {
                echo 'Info: discarded or not unpaid. ID = '.$data[$i]['id']."\n";
                continue;
            }
            // parse school
            $school_id = '';
            $school_index1 = -1;
            $school_index2 = -1;
            for($j = 0; $j < count($this->schools); $j++)
            {
                $school_index2 = array_search($data[$i]['receiver']['school'], $this->schools[$j]['schools']);
                if($school_index2 !== false)
                {
                    $school_id = $this->schools[$j]['id'][$school_index2];
                    $school_index1 = $j;
                }
            }
            $data[$i]['to'] = $school_id;
            $data[$i]['area'] = $school_index1 + 1;
            if(preg_match('/[a-z]+/', $school_id) !== 1)
            {
                echo 'Error: invalid school_id; ID = '.$data[$i]['id']."\n";
            }
            if(!($data[$i]['area'] >= 1 && $data[$i]['area'] <= 6))
            {
                echo 'Error: invalid area; ID = '.$data[$i]['id']."\n";
            }
            // parse_grade
            $grade = 6;
            if($data[$i]['receiver']['grade'] !== '無')
            {
                $grade2 = $data[$i]['receiver']['grade'];
                if(isset($this->grade_map[$grade2]))
                {
                    $grade = $this->grade_map[$grade2];
                }
                else if(intval($grade2) >= 5)
                {
                    $grade = 6;
                }
                else if(strstr($grade2, '碩') !== false || strstr($grade2, '博') !== false)
                {
                    $grade = 5;
                }
                else
                {
                    if(!(intval($grade2) >= 1 && intval($grade2) <= 4))
                    {
                        echo 'Info: special grade = '.$grade2.'; ID = '.$data[$i]['id']."\n";
                        $grade = 6;
                    }
                    else
                    {
                        $grade = intval($grade2);
                    }
                }
            }
            $data[$i]['grade'] = $grade;
            // store data
            $key = $data[$i]['id'];
            if(isset($this->local_data[$key]))
            {
                echo 'Duplicate key '.$key.'; ';
                if($this->local_data[$key] != $data[$i])
                {
                    echo 'Contents different'."\n";
                }
                else
                {
                    echo 'Contents same'."\n";
                }
            }
            else
            {
                $this->local_data[$key] = $data[$i];
            }
        }
    }

    public function check()
    {
        for($i = 0; $i < count($this->remote_data); $i++)
        {
        }
    }

    public function getLocalData()
    {
        return $this->local_data;
    }

    static function error_handler($errno, $errstr)
    {
        print_r(array(
            'errno' => $errno, 
            'errstr' => $errstr
        ));
    }
}

set_error_handler(array('Love1997', 'error_handler'));

$instance = new Love1997();
// $instance->getRemoteList();
$data_dir = '/home/yen/School/102-1/Activities/chocolate/data/';
$instance->loadFile(array(
    $data_dir.'receiptdata1.cycamp', 
    $data_dir.'receiptdata2.cycamp'
));
file_put_contents('./data/parsed_local_data', print_r($instance->getLocalData(), true));
?>
