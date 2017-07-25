<?php
require_once('./PHPExcel-1.8/Classes/PHPExcel/IOFactory.php');

$dir = dirname(__FILE__);//获取php文件目录路径

$filenames = scandir($dir);//扫描该目录下文件

$con = mysql_connect('localhost', 'root', 'root');//连接数据库

if(!$con)//如果连接失败
{
	die('连接数据库失败：' . mysql_error());
}

mysql_select_db('stock', $con);//选择数据库

foreach($filenames as $filename)//遍历文件名数组
{
	$_filename = basename($filename, '.txt');//去掉文件扩展名的文件名
	
	if(is_numeric($_filename))//如果文件名是数字
	{

		$reader = PHPExcel_IOFactory::createReader('CSV')->setDelimiter('	')
                            							->setInputEncoding('GBK')
                            							->setSheetIndex(0);
        $objPHPExcel = $reader->load($filename);

        $data = $objPHPExcel->getSheet()->toArray();

        $dropTableIfExists = mysql_query('DROP TABLE `' . $_filename . '` IF EXISTS', $con);

		$createTable = mysql_query('CREATE TABLE `' . $_filename . '` (
			date VARCHAR(15),
			kp float,
			highest float,
			lowest float,
			sp float,
			cjl int,
			macd_dif float,
			macd_dea float,
			macd_macd float,
			kdj_k float,
			kdj_d float,
			kdj_j float,
			boll_boll float,
			boll_ub float,
			boll_lb float
		)', $con);

        $i = 0;

        foreach($data as $d)
        {
			if($i === 0)
			{
				// echo mb_detect_encoding($d[0], array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
				$company = trim(iconv('utf-8','GB2312', $d[0]));//公司名
				$searchSQL = "SELECT  FROM code_relations WHERE code=" . intval($_filename);
				// echo $searchSQL;
				$ifRelationsExists =  mysql_fetch_array(mysql_query($searchSQL));
				var_dump($ifRelationsExists);
				exit();

				if(!$ifRelationsExists)
				{
					$createRelation = mysql_query("INSERT INTO code_relations (code, company) VALUES (".$_filename.", ". "'".trim($d[0])."'" .")", $con);//插入股票代码和公司名关系
					if(!$createRelation)
					{
						die(mysql_error());
					}
				}
				$i++;
				continue;
			//这里赋值要分开写否则永为真，且变量最好用intval()处理
			}elseif($i == 1 || $i == 2 || $i == 3){
				$i++;
				continue;
			}else{
				//将所需数据先转化为数组，其中第一列值为字符串所以加上引号
				$res_arr = ["'".trim($d[0])."'",
					trim($d[1]),
					trim($d[2]),
					trim($d[3]),
					trim($d[4]),
					trim($d[5]),
					trim($d[15]),
					trim($d[16]),
					trim($d[17]),
					trim($d[18]),
					trim($d[19]),
					trim($d[20]),
					trim($d[21]),
					trim($d[22]),
					trim($d[23])];
				//如果值为空则赋值，否则无法插入数据库
				foreach($res_arr as $k => $v)
				{
					if($v == '')
					{
						$res_arr[$k] = 0;
					}
				}
				//再将处理好的数组转化为字符串以便SQL语句使用
				$res_str = implode(',', $res_arr);

				//酷炫运行效果
				echo $res_str;

				//插入数据库
				$insertTabel = mysql_query('INSERT INTO `' . $_filename . '`(				
					date,
					kp,
					highest,
					lowest,
					sp,
					cjl,
					macd_dif,
					macd_dea,
					macd_macd,
					kdj_k,
					kdj_d,
					kdj_j,
					boll_boll,
					boll_ub,
					boll_lb		
					) VALUES ('.
					$res_str
					.')', $con);
				//处理插入数据库操作结果
				if(!$insertTabel)
				{
					die(mysql_error());
				}else{
					echo $_filename . '插入成功！';
				}

				$i++;
			}
        }
	}
}

//关闭数据库连接
mysql_close($con);



