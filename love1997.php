<?php
if(php_sapi_name() !== 'cli')
{
    exit(0);
}
error_reporting(E_ALL|E_STRICT);

require_once '3rdparty/simple_html_dom.php';

class Love1997
{
    // settings
    protected $old_mb_encoding = null;
    protected $usleep_time;

    // data
    protected $url = array(
        'list' => 'http://www.lovechoco1997.com.tw/list.php', 
        'new' => 'http://www.lovechoco1997.com.tw/new.php', 
        'delete' => 'http://www.lovechoco1997.com.tw/delete.php', 
        'result' => 'http://www.lovechoco1997.com.tw/result.php'
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
    protected $remote_products = array();
    protected $remote_schools = array();
    // remote school name (without 台臺mapping) => local school name
    protected $school_mapping = array(
        '淡江大學 淡水校區' => '淡江大學（淡水校區）', 
        '國立臺北教育大學' => '國立台北教育大學（國北師）', 
        '實踐大學 高雄校區' => '實踐大學（高雄校區）', 
        '實踐大學 臺北校區' => '實踐大學（台北校區）', 
        '銘傳大學 臺北校區' => '銘傳大學（台北校區）', 
        '真理大學 淡水校區' => '真理大學（淡水校區）', 
        '銘傳大學 桃園校區' => '銘傳大學（桃園校區日間部）', 
        '馬偕醫護管理專科學校' => '馬偕醫護管理專科學校（關渡/三芝校區）', 
        '開南大學' => '開南大學（日間部）', 
        '中山醫學大學' => '中山醫學大學（日間部）'
    );

    public function __construct()
    {
        $this->ch = curl_init();
        $this->loadStaticData();
        $this->old_mb_encoding = mb_internal_encoding();
        $this->usleep_time = 500 * 1000; // in milliseconds
        mb_internal_encoding('UTF-8');
    }

    public function __destruct()
    {
        curl_close($this->ch);
        mb_internal_encoding($this->old_mb_encoding);
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
        $html = str_get_html(curl_exec($this->ch));
        foreach($html->find('tr') as $row)
        {
            $id = $row->children(0)->innertext;
            if(!$this->checkID($id))
            {
                continue;
            }
            // some products have trailing \n
            $products = explode("<br>", $row->children(5)->innertext);
            for($j = 0; $j < count($products); $j++)
            {
                $arr = array();
                $products[$j] = trim($products[$j]);
                if(!preg_match("/^(?P<id>\d+)\.\s*(?P<name>.+)\s+(?P<count>\d+)個$/", $products[$j], $arr))
                {
                    echo "Error parsing product list; ID = ".$id."\n";
                    continue;
                }
                if(!isset($this->remote_products[$arr['id']]))
                {
                    $this->remote_products[$arr['id']] = $arr['name'];
                }
                else if($this->remote_products[$arr['id']] !== $arr['name'])
                {
                    echo "Error: inconsistent product name; ID = ".$id."\n";
                }
                $products[$j] = array(
                    'id' => $arr['id'], 
                    'count' => $arr['count']
                );
            }
            $this->remote_data[$id] = array(
                'ID' => $id, 
                'to_school' => $row->children(2)->innertext, 
                'grade' => $row->children(3)->innertext, 
                'products' => $products
            );
        }
        ksort($this->remote_products);
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
        // first line is the number of lines in the file
        for($i = 1; $i < count($data); $i++)
        {
            if($data[$i] == "")
            {
                continue;
            }
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
                echo 'Info: discarded or not paid. ID = '.$data[$i]['id']."\n";
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

    public function loadRemoteSchools()
    {
        curl_reset($this->ch);
        curl_setopt_array($this->ch, array(
            CURLOPT_URL => $this->url['new'], 
            CURLOPT_BINARYTRANSFER => true, 
            CURLOPT_RETURNTRANSFER => true, 
            CURLOPT_POST => true, 
            CURLOPT_POSTFIELDS => "school=ntu"
        ));
        $data = array();
        $results = curl_exec($this->ch);
        preg_match_all('/new Option\(\'(?P<name>[^()]+)\',\'(?P<id>[a-z]+)\'\)/m', $results, $data);
        for($i = 0; $i < count($data[0]); $i++)
        {
            $this->remote_schools[$data['id'][$i]] = $data['name'][$i];
        }
        return $this->remote_schools;
    }

    public function check()
    {
        echo "Checking local and remote data...\n".
             count($this->local_data).' local data and '.
             count($this->remote_data)." remote data\n";
        foreach($this->local_data as $id => $data)
        {
            if(!isset($this->remote_data[$id]))
            {
                echo 'Error: remote id not found: '.$id."\n";
                $this->uploadLocal($id);
                continue;
            }
            $remote_data = $this->remote_data[$id];
            // checking products
            $local_products = $data['products'];
            if(!isset($remote_data['products'][0]['id']))
            {
                echo 'Check failed: invalid remote product list; ID = '.$id."\n";
                $this->reUpload($id);
                continue;
            }
            for($j = 0; $j < count($remote_data['products']); $j++)
            {
                $current_product = $remote_data['products'][$j];
                if($current_product['id'] <= 0 || $current_product['id'] > count($local_products))
                {
                    echo 'Check failed: wrong product id; ID = '.$id."\n";
                    continue;
                }
                $local_products[$current_product['id'] - 1] -= $current_product['count'];
            }
            if(array_count_values($local_products)[0] !== count($local_products))
            {
                echo 'Check failed: products count not match; ID = '.$id."\n";
                $this->reUpload($id);
                continue;
            }
            // checking schools
            $local_school = $data['receiver']['school'];
            $remote_school = $remote_data['to_school'];
            if($local_school !== str_replace('臺', '台', $remote_school))
            {
                if(!isset($this->school_mapping[$remote_school]) || 
                    $this->school_mapping[$remote_school] !== $local_school)
                {
                    echo 'Check failed: different school: '.$local_school.', '.$remote_school.'; ID = '.$id."\n";
                    $this->reUpload($id);
                    continue;
                }
            }
            // checking grades
            $local_grade = $data['receiver']['grade'];
            $remote_grade = $remote_data['grade'];
            if(mb_substr($remote_grade, 0, 1) === '大')
            {
                $remote_grade = mb_substr($remote_grade, 1, null);
            }
            $invalid_grade = true;
            if($remote_grade === $local_grade)
            {
                $invalid_grade = false;
            }
            else if(isset($this->grade_map[$remote_grade]) && $this->grade_map[$remote_grade] == $local_grade)
            {
                $invalid_grade = false;
            }
            else if($remote_grade === '其他' && $local_grade === '無')
            {
                $invalid_grade = false;
            }
            else if($remote_grade === '研究所' && 
                   (mb_substr($local_grade, 0, 1) === '碩' || 
                    mb_substr($local_grade, 0, 1) === '博'))
            {
                $invalid_grade = false;
            }
            else if($remote_grade === '其他')
            {
                $invalid_grade = false;
            }
            if($invalid_grade)
            {
                echo 'Check failed: different grade; ';
                echo $remote_grade;
                if(isset($this->grade_map[$remote_grade]))
                {
                    echo ' '.$this->grade_map[$remote_grade];
                }
                echo ' '.$local_grade.'; ID = '.$id."\n";
                $this->reUpload($id);
                continue;
            }
        }
        echo "Checking end.\n";
    }

    protected function reUpload($id)
    {
        $this->deleteRemote($id);
        $this->uploadLocal($id);
    }

    protected function deleteRemote($id)
    {
        if(!$this->checkID($id))
        {
            return;
        }
        echo 'Deleting '.$id."\n";
        curl_reset($this->ch);
        curl_setopt_array($this->ch, array(
            CURLOPT_URL => $this->url['delete'], 
            CURLOPT_POST => true, 
            CURLOPT_POSTFIELDS => array('account' => 'ntu', 'delete' => $id), 
            CURLOPT_BINARYTRANSFER => true, 
            CURLOPT_RETURNTRANSFER => true
        ));
        $result = curl_exec($this->ch);
        if($result === false)
        {
            echo 'Deleting failed; ID = '.$id."\n";
        }
        usleep($this->usleep_time);
    }

    protected function uploadLocal($id)
    {
        if(!$this->checkID($id))
        {
            return;
        }
        echo 'Uploading '.$id."\n";
        curl_reset($this->ch);
        $record = $this->local_data[$id];
        $post_data = array(
            'from' => 'ntu', 
            'number' => $id, 
            'area' => $record['area'], 
            'to' => $record['to'], 
            'grade' => $record['grade'], 
            'sex' => 1
        );
        for($j = ord('a'); $j <= ord('w'); $j++)
        {
            $curN = $record['products'][$j - ord('a')];
            if($curN <= 4)
            {
                $post_data[chr($j)] = $curN;
                $post_data[chr($j)."other"] = "";
            }
            else
            {
                $post_data[chr($j)] = 0;
                $post_data[chr($j)."other"] = $curN;
            }
        }
        curl_setopt_array($this->ch, array(
            CURLOPT_URL => $this->url['result'], // a bad naming
            CURLOPT_POST => 1, 
            CURLOPT_POSTFIELDS => $post_data, 
            CURLOPT_RETURNTRANSFER => 1, 
            CURLOPT_BINARYTRANSFER => 1
        ));
        curl_exec($this->ch);
        usleep($this->usleep_time);
    }

    protected function checkID($id)
    {
        if(!preg_match('/^\d{6}$/', $id))
        {
            echo 'Invalid id: '.$id."\n";
            return false;
        }
        return true;
    }

    public function getLocalData()
    {
        return $this->local_data;
    }

    public function getRemoteData()
    {
        return $this->remote_data;
    }

    public function getRemoteProducts()
    {
        return $this->remote_products;
    }

    static function error_handler($errno, $errstr)
    {
        print_r(func_get_args());
        exit(1);
    }
}

set_error_handler(array('Love1997', 'error_handler'));

$instance = new Love1997();
$data_dir = '/home/yen/School/102-1/Activities/chocolate/data/';
$instance->loadFile(array(
    $data_dir.'receiptdata1.cycamp', 
    $data_dir.'receiptdata2.cycamp'
));
$instance->getRemoteList();
file_put_contents('./data/parsed_local_data', print_r($instance->getLocalData(), true));
file_put_contents('./data/parsed_remote_data', 
    print_r($instance->getRemoteData(), true)."\n".
    print_r($instance->getRemoteProducts(), true)."\n".
    print_r($instance->loadRemoteSchools(), true)
);
$instance->check();
?>
