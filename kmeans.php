<?php
function draw($file, $datas, $clusters = array(), $averages = array())
{
    $im = imagecreatetruecolor(300, 300);
    imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255));
    imagesetthickness($im, 3);
    foreach ($datas as $v) {
        imageellipse($im, $v[0] * 100, $v[1] * 100, 8, 8, imagecolorallocate($im, 0, 0, 0));
    }
    foreach ($clusters as $v) {
        imagefilledellipse($im, $v[0] * 100, $v[1] * 100, 8, 8, imagecolorallocate($im, 0, 0, 255));
    }
    foreach ($averages as $k => $v) {
        imageellipse($im, $clusters[$k][0] * 100, $clusters[$k][1] * 100, $v * 200, $v * 200, imagecolorallocate($im, 255, 0, 0));
    }
    imagepng($im, $file);
    imagedestroy($im);
}

/* 准备数据 */
$datas = [];
for ($i = 0; $i < 50; $i++) {
    $x = mt_rand() / mt_getrandmax();
    $y = mt_rand() / mt_getrandmax();
    $datas[] = [$x, $y];/* 0-1 */
}

for ($i = 0; $i < 50; $i++) {
    $x = mt_rand() / mt_getrandmax() + 1;
    $y = mt_rand() / mt_getrandmax() + 1;
    $datas[] = [$x, $y];/* 1-2 */
}

for ($i = 0; $i < 50; $i++) {
    $x = mt_rand() / mt_getrandmax() + 2;
    $y = mt_rand() / mt_getrandmax() + 2;
    $datas[] = [$x, $y];/* 2-3 */
}

/* 初始化聚类中心 */
$center = 4;
$length = 2;
$clusters = [];
for ($i = 0; $i < $center; $i++) {
    $clusters[$i] = [];
    for ($j = 0; $j < $length; $j++) {
        $clusters[$i][$j] = mt_rand() / mt_getrandmax() * 3;
    }
}
var_dump($clusters);
draw('data.png', $datas, $clusters);

for ($i = 0; $i < 10; $i++) {
    $labels = [];
    foreach ($datas as $dk => $data) {
        $minLen = null;
        $minCluster = null;
        foreach ($clusters as $ck => $cluster) {/* 计算数据到每个簇的距离 */
            $len = 0;
            foreach ($cluster as $k => $v) {
                $len += pow($v - $data[$k], 2);
            }
            $len = sqrt($len);
            if (is_null($minLen) || is_null($minCluster)) {
                $minLen = $len;
                $minCluster = $ck;
            } else {
                if ($len < $minLen) {
                    $minLen = $len;
                    $minCluster = $ck;
                }
            }
        }
        $labels[$dk] = $minCluster;
        foreach ($clusters[$minCluster] as $k => $v) {/* 更新分配到的簇的中心位置 */
            $sum = 0;
            $num = 0;
            foreach ($labels as $lk => $label) {
                if ($label == $minCluster) {
                    $sum += $datas[$lk][$k];
                    $num += 1;
                }
            }
            $num = $num == 0 ? 1 : $num;
            $clusters[$minCluster][$k] = $sum / $num;
        }
    }
}
var_dump($clusters);

/* 给每个类计算平均半径 */
$averages = [];
foreach ($clusters as $k => $v) {
    $averages[$k] = 0;
    $num = 0;
    foreach ($labels as $lk => $label) {
        if ($label == $k) {
            $num++;
            $len = 0;
            foreach ($v as $vk => $vv) {
                $len += pow($vv - $datas[$lk][$vk], 2);
            }
            $len = sqrt($len);
            $averages[$k] += $len;
        }
    }
    if ($num > 0) {
        $averages[$k] /= $num;
    }
}
var_dump($averages);
draw('data2.png', $datas, $clusters, $averages);

/* 每个聚类的半径的平均值 */
$sum = 0;
foreach ($averages as $v) {
    $sum += $v;
}
echo $sum / count($averages);
echo PHP_EOL;