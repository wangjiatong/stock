<?php
require_once('./PHPExcel-1.8/Classes/PHPExcel/IOFactory.php');

$dir = dirname(__FILE__);//获取php文件目录路径

$filenames = scandir($dir);//扫描该目录下文件

$con = mysql_connect('localhost', 'root', 'root');//连接数据库

if(!$con)//如果连接失败
{
	echo '连接数据库失败：' . mysql_error();
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

        $i = 0;

		mysql_query('CREATE TABLE `' . $_filename . '` (
			date VARCHAR(15),
			kp float,
			highest float,
			lowest float,
			sp float,
			cjl int,
			macd.dif float,
			macd.dea float,
			macd.macd float,
			kdj.k float,
			kdj.d float,
			kdj.j float,
			boll.boll float,
			boll.ub float,
			boll.lb float,
			engine=MyISAM
		)', $con);

        foreach($data as $d)
        {
        	var_dump($d);
        	echo $i;
			if($i == 0)
			{
				$company = iconv('utf-8','GB2312', $d[0]);//公司名
				echo $company;
				$i++;
				continue;
			}elseif($i == 1 || 3)
			{
				$i++;
				continue;
			}

			// if($createTalbe)
			// {
				mysql_query('INSERT INTO `' . $_filename . '`(				
				date,
				kp,
				highest,
				lowest,
				sp,
				cjl,
				macd.dif,
				macd.dea,
				macd.macd,
				kdj.k,
				kdj.d,
				kdj.j,
				boll.boll,
				boll.ub,
				boll.lb,			
				) VALUES ('.
				trim($d[0]),
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
				trim($d[23])
				.')', $con);
				echo "导入成功！";
			// }else{
			// 	echo '数据表创建失败：' . mysql_error();//如果创建表失败
			// }
			$i++;
        }
	}
}

//关闭数据库连接
mysql_close($con);



