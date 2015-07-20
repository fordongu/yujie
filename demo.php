<?php
$url = 'http://www.baidu.com/';
$url1 = 'http://www.xiaoshuo77.com/page_lastupdate_1.html';
$con = file_get_contents($url1);

//$preg="/·<a href=(.*) target=_blank>(.*)<\/a>/U";
//$preg = '/<div class="con3"><a class="tit" href=".+?" title=".+?" target="_blank">(.+?)<\/a>.+?<a href="(.+?)" title=".+?" target="_blank">(.+?)<\/a><\/div>/';
//$preg = '/<li>\s*<div class="con1">\d*<\/div>\s*<div class="con2"><a href=".+?">[.+?]<\/a><\/div>\s*<div class="con3"><a class="tit" href="(.+?)" title="(.+?)" target="_blank">.+?<\/a>.+?<a href="(.+?)" title="(.+?)" target="_blank">.+?<\/a><\/div>\s*<div class="con4">\d*<\/div>\s*<div class="con5">.+?<\/div>\s*<div class="con6">.+?<\/div>\s*<\/li>/';
//$preg = '/<\/div>\s*<div class="con6">(.+?)<\/div>\s*<\/li>/';
//$preg = '/<div class="con3"><a class="tit" href="http:\/\/www.xiaoshuo77.com\/view\/\d*\/\d*\/" title=".+?" target="_blank">.+?<\/a>.+?<a href="http:\/\/www.xiaoshuo77.com\/view\/\d*\/\d*\/\d*.html" title="(.+?)" target="_blank">.+?<\/a><\/div>\s*<div class="con4">/';
$preg = '/<div class="con3"><a  class="tit" href=".+?" title=".+?" target="_blank">.+?<\/a> \/ <a href=".+?" title="(.+?)" target="_blank">.+?<\/a><\/div>\s*<div class="con4">/';

preg_match_all($preg,$con,$arr);

echo '<pre>';
print_r($arr);
echo '</pre>';
exit();
foreach($arr[1] as $id=>$v){

 echo "<a href=view.php?url=$v>".$arr[2][$id]."</a><br>";


}